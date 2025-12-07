<?php

namespace Tests\Unit\Factory;

use App\Factory\CloneCreditToQuoteFactory;
use App\Models\Credit;
use App\Models\Quote;
use Tests\TestCase;

class CloneCreditToQuoteFactoryTest extends TestCase
{
    public function test_create_clones_credit_to_quote_properly()
    {
        $userId = 123;

        // Create a Credit instance with sample values
        $credit = new Credit([
            'client_id' => 1,
            'company_id' => 2,
            'discount' => 5,
            'is_amount_discount' => true,
            'po_number' => 'PO-100',
            'footer' => 'Footer text',
            'public_notes' => 'Public notes',
            'private_notes' => 'Private notes',
            'terms' => 'Terms text',
            'tax_name1' => 'GST',
            'tax_rate1' => 10,
            'tax_name2' => 'PST',
            'tax_rate2' => 5,
            'custom_value1' => 'CV1',
            'custom_value2' => 'CV2',
            'custom_value3' => 'CV3',
            'custom_value4' => 'CV4',
            'amount' => 1000,
            'partial' => 500,
            'partial_due_date' => '2025-12-11',
            'last_viewed' => '2025-12-06',
        ]);

        $credit->line_items = ['item1', 'item2'];

        // Call the factory
        $quote = CloneCreditToQuoteFactory::create($credit, $userId);

        // Assertions
        $this->assertInstanceOf(Quote::class, $quote);
        $this->assertSame($credit->client_id, $quote->client_id);
        $this->assertSame($userId, $quote->user_id);
        $this->assertSame($credit->company_id, $quote->company_id);
        $this->assertSame($credit->discount, $quote->discount);
        $this->assertSame($credit->is_amount_discount, $quote->is_amount_discount);
        $this->assertSame($credit->po_number, $quote->po_number);
        $this->assertFalse($quote->is_deleted);
        $this->assertSame($credit->footer, $quote->footer);
        $this->assertSame($credit->public_notes, $quote->public_notes);
        $this->assertSame($credit->private_notes, $quote->private_notes);
        $this->assertSame($credit->terms, $quote->terms);
        $this->assertSame($credit->tax_name1, $quote->tax_name1);
        $this->assertSame($credit->tax_rate1, $quote->tax_rate1);
        $this->assertSame($credit->tax_name2, $quote->tax_name2);
        $this->assertSame($credit->tax_rate2, $quote->tax_rate2);
        $this->assertSame($credit->custom_value1, $quote->custom_value1);
        $this->assertSame($credit->custom_value2, $quote->custom_value2);
        $this->assertSame($credit->custom_value3, $quote->custom_value3);
        $this->assertSame($credit->custom_value4, $quote->custom_value4);
        $this->assertSame($credit->amount, $quote->amount);
        $this->assertSame($credit->partial, $quote->partial);
        $this->assertNull($quote->date);
        $this->assertNull($quote->due_date);
        $this->assertNull($quote->partial_due_date);
        $this->assertSame('', $quote->number);
        $this->assertSame(Quote::STATUS_DRAFT, $quote->status_id);
        $this->assertSame($credit->line_items, $quote->line_items);
    }
}

