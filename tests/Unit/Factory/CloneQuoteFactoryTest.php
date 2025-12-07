<?php

namespace Tests\Unit\Factory;

use App\Factory\CloneQuoteFactory;
use App\Models\Quote;
use Tests\TestCase;

class CloneQuoteFactoryTest extends TestCase
{
    public function test_create_clones_quote_properly()
    {
        $userId = 123;

        // Create a mock Quote
        $quote = $this->getMockBuilder(Quote::class)
                      ->onlyMethods(['replicate'])
                      ->getMock();

        // Mock the replicate method to return a new Quote instance
        $replicatedQuote = new Quote();
        $replicatedQuote->amount = 1000;
        $replicatedQuote->line_items = ['item1', 'item2'];
        $quote->expects($this->once())
              ->method('replicate')
              ->willReturn($replicatedQuote);

        // Call the factory
        $clone = CloneQuoteFactory::create($quote, $userId);

        // Assertions
        $this->assertInstanceOf(Quote::class, $clone);
        $this->assertSame(Quote::STATUS_DRAFT, $clone->status_id);
        $this->assertNull($clone->number);
        $this->assertNull($clone->date);
        $this->assertNull($clone->due_date);
        $this->assertNull($clone->partial_due_date);
        $this->assertSame($userId, $clone->user_id);
        $this->assertSame($replicatedQuote->amount, $clone->amount);
        $this->assertSame($replicatedQuote->line_items, $clone->line_items);
    }
}

