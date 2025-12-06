<?php

namespace Tests\Job;

use Tests\TestCase;
use App\Jobs\Credit\ApplyCreditPayment;
use App\Models\Credit;
use App\Models\Payment;
use App\DataMapper\InvoiceItem;
use Mockery;

class ApplyCreditPaymentTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mocked client with expected methods and currency object
     */
    protected function createMockClient()
    {
        $client = Mockery::mock();
        
        // Method calls used in the job
        $client->shouldReceive('date_format')->andReturn('Y-m-d');

        // Currency object expected by Number::formatMoney
        $client->currency = (object)[
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'symbol' => '$',
            'swap_symbol' => false,
            'code' => 'USD',
        ];

        return $client;
    }

    /**
     * Create a mocked credit
     */
    protected function createMockCredit(float $balance)
    {
        $pivot = Mockery::mock();
        $pivot->shouldReceive('save')->andReturnNull();

        $creditService = Mockery::mock();
        $creditService->shouldReceive('markSent->setStatus->adjustBalance->updatePaidToDate->save')
            ->andReturnNull();

        $credit = Mockery::mock(Credit::class)->makePartial();
        $credit->id = rand(1, 1000);
        $credit->balance = $balance;
        $credit->paid_to_date = 0.0;
        $credit->line_items = [];
        $credit->pivot = $pivot;
        $credit->shouldReceive('save')->andReturnNull();
        $credit->shouldReceive('service')->andReturn($creditService);
        $credit->client = $this->createMockClient();

        return $credit;
    }

    /**
     * Create a mocked payment
     */
    protected function createMockPayment(array $credits, array $invoiceNumbers)
    {
        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->credits = collect($credits);
        $payment->invoices = collect(array_map(fn($n) => (object)['number' => $n], $invoiceNumbers));
        $payment->client = $credits[0]->client;
        $payment->date = '2025-12-03';
        $payment->shouldReceive('save')->andReturnNull();

        return $payment;
    }

    public function test_total_credit_applied()
    {
        $credit = $this->createMockCredit(100.0);
        $payment = $this->createMockPayment([$credit], ['INV-001']);

        $job = new ApplyCreditPayment($credit, $payment, 100.0);
        $job->handle();

        $this->assertEquals(100.0, $credit->paid_to_date);
        $this->assertCount(1, $credit->line_items);
        $this->assertInstanceOf(InvoiceItem::class, $credit->line_items[0]);
    }

    public function test_partial_credit_applied()
    {
        $credit = $this->createMockCredit(200.0);
        $payment = $this->createMockPayment([$credit], ['INV-002']);

        $job = new ApplyCreditPayment($credit, $payment, 50.0);
        $job->handle();

        $this->assertEquals(50.0, $credit->paid_to_date);
        $this->assertCount(1, $credit->line_items);
    }

    public function test_no_credit_matches_in_payment()
    {
        $credit = $this->createMockCredit(50.0);
        $otherCredit = $this->createMockCredit(50.0);
        $otherCredit->id = $credit->id + 1; // ensure different ID
        $payment = $this->createMockPayment([$otherCredit], ['INV-003']);

        $job = new ApplyCreditPayment($credit, $payment, 50.0);
        $job->handle();

        $this->assertEmpty($credit->line_items);
    }
}

