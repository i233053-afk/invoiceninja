<?php

namespace Tests\Unit\Factory;

use App\Factory\RecurringQuoteFactory;
use App\Models\RecurringQuote;
use Tests\TestCase;

class RecurringQuoteFactoryTest extends TestCase
{
    public function test_create_initializes_recurring_quote_properly()
    {
        $companyId = 1;
        $userId = 123;

        // Call the factory
        $quote = RecurringQuoteFactory::create($companyId, $userId);

        // Assertions
        $this->assertInstanceOf(RecurringQuote::class, $quote);
        $this->assertSame(RecurringQuote::STATUS_DRAFT, $quote->status_id);
        $this->assertSame(0, $quote->discount);
        $this->assertTrue($quote->is_amount_discount);
        $this->assertSame('', $quote->po_number);
        $this->assertSame('', $quote->number);
        $this->assertSame('', $quote->footer);
        $this->assertSame('', $quote->terms);
        $this->assertSame('', $quote->public_notes);
        $this->assertSame('', $quote->private_notes);
        $this->assertNull($quote->date);
        $this->assertNull($quote->due_date);
        $this->assertNull($quote->partial_due_date);
        $this->assertFalse($quote->is_deleted);
        $this->assertSame(json_encode([]), $quote->line_items);
        $this->assertSame('', $quote->tax_name1);
        $this->assertSame(0, $quote->tax_rate1);
        $this->assertSame('', $quote->tax_name2);
        $this->assertSame(0, $quote->tax_rate2);
        $this->assertSame('', $quote->custom_value1);
        $this->assertSame('', $quote->custom_value2);
        $this->assertSame('', $quote->custom_value3);
        $this->assertSame('', $quote->custom_value4);
        $this->assertSame(0, $quote->amount);
        $this->assertSame(0, $quote->balance);
        $this->assertSame(0, $quote->partial);
        $this->assertSame($userId, $quote->user_id);
        $this->assertSame($companyId, $quote->company_id);
        $this->assertSame(RecurringQuote::FREQUENCY_MONTHLY, $quote->frequency_id);
        $this->assertNull($quote->last_sent_date);
        $this->assertNull($quote->next_send_date);
        $this->assertSame(0, $quote->remaining_cycles);
        $this->assertSame(0, $quote->paid_to_date);
    }
}

