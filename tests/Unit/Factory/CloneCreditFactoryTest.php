<?php

namespace Tests\Unit\Factory;

use App\Factory\CloneCreditFactory;
use App\Models\Credit;
use Tests\TestCase;

class CloneCreditFactoryTest extends TestCase
{
    public function test_create_clones_credit_properly()
    {
        $userId = 123;

        // Create a Credit instance with non-null values
        $credit = new Credit([
            'number' => 'C-100',
            'date' => '2025-12-06',
            'due_date' => '2025-12-16',
            'partial_due_date' => '2025-12-11',
            'status_id' => Credit::STATUS_SENT,
        ]);

        $credit->line_items = ['item1', 'item2'];

        // Call the factory
        $clone = CloneCreditFactory::create($credit, $userId);

        // Assertions
        $this->assertInstanceOf(Credit::class, $clone);
        $this->assertSame(Credit::STATUS_DRAFT, $clone->status_id);
        $this->assertNull($clone->number);
        $this->assertNull($clone->date);
        $this->assertNull($clone->due_date);
        $this->assertNull($clone->partial_due_date);
        $this->assertSame($userId, $clone->user_id);
        $this->assertSame($credit->line_items, $clone->line_items);
    }
}

