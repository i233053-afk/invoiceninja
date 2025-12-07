<?php

namespace Tests\Unit\Factory;

use App\Factory\CloneInvoiceToQuoteFactory;
use App\Models\Invoice;
use App\Models\Quote;
use Tests\TestCase;

class CloneInvoiceToQuoteFactoryTest extends TestCase
{
    public function test_create_clones_invoice_to_quote_properly()
    {
        $userId = 123;

        // Create a mock Invoice
        $invoice = new Invoice();
        $invoice->discount = 10;
        $invoice->is_amount_discount = true;
        $invoice->po_number = 'PO123';
        $invoice->footer = 'Footer text';
        $invoice->public_notes = 'Public notes';
        $invoice->private_notes = 'Private notes';
        $invoice->terms = 'Terms text';
        $invoice->tax_name1 = 'GST';
        $invoice->tax_rate1 = 5;
        $invoice->tax_name2 = 'PST';
        $invoice->tax_rate2 = 8;
        $invoice->tax_name3 = 'HST';
        $invoice->tax_rate3 = 13;
        $invoice->custom_value1 = 'Custom1';
        $invoice->custom_value2 = 'Custom2';
        $invoice->custom_value3 = 'Custom3';
        $invoice->custom_value4 = 'Custom4';
        $invoice->amount = 1000;
        $invoice->partial = 500;
        $invoice->partial_due_date = '2025-12-06';
        $invoice->last_viewed = '2025-12-06';
        $invoice->line_items = ['item1', 'item2'];

        // Call the factory
        $quote = CloneInvoiceToQuoteFactory::create($invoice, $userId);

        // Assertions
        $this->assertInstanceOf(Quote::class, $quote);

        $this->assertSame($invoice->discount, $quote->discount);
        $this->assertSame($invoice->is_amount_discount, $quote->is_amount_discount);
        $this->assertSame($invoice->po_number, $quote->po_number);
        $this->assertSame($invoice->footer, $quote->footer);
        $this->assertSame($invoice->public_notes, $quote->public_notes);
        $this->assertSame($invoice->private_notes, $quote->private_notes);
        $this->assertSame($invoice->terms, $quote->terms);
        $this->assertSame($invoice->tax_name1, $quote->tax_name1);
        $this->assertSame($invoice->tax_rate1, $quote->tax_rate1);
        $this->assertSame($invoice->tax_name2, $quote->tax_name2);
        $this->assertSame($invoice->tax_rate2, $quote->tax_rate2);
        $this->assertSame($invoice->tax_name3, $quote->tax_name3);
        $this->assertSame($invoice->tax_rate3, $quote->tax_rate3);
        $this->assertSame($invoice->custom_value1, $quote->custom_value1);
        $this->assertSame($invoice->custom_value2, $quote->custom_value2);
        $this->assertSame($invoice->custom_value3, $quote->custom_value3);
        $this->assertSame($invoice->custom_value4, $quote->custom_value4);
        $this->assertSame($invoice->amount, $quote->amount);
        $this->assertSame($invoice->partial, $quote->partial);
        $this->assertNull($quote->partial_due_date); // reset in factory
        $this->assertNull($quote->date);
        $this->assertNull($quote->due_date);
        $this->assertSame($invoice->line_items, $quote->line_items);

        $this->assertSame(Quote::STATUS_DRAFT, $quote->status_id);
        $this->assertSame('', $quote->number);
    }
}

