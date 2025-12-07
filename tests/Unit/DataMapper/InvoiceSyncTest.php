<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\InvoiceSync;
use App\Casts\InvoiceSyncCast;
use Tests\TestCase;

class InvoiceSyncTest extends TestCase
{
    /** @test */
    public function it_initializes_with_default_empty_qb_id()
    {
        $sync = new InvoiceSync();

        $this->assertSame('', $sync->qb_id, "qb_id should default to empty string.");
    }

    /** @test */
    public function it_initializes_with_provided_qb_id()
    {
        $sync = new InvoiceSync(['qb_id' => 'QB123']);

        $this->assertSame('QB123', $sync->qb_id, "qb_id should match provided value.");
    }

    /** @test */
    public function it_creates_instance_from_array()
    {
        $data = ['qb_id' => 'QB999'];

        $sync = InvoiceSync::fromArray($data);

        $this->assertInstanceOf(InvoiceSync::class, $sync);
        $this->assertSame('QB999', $sync->qb_id);
    }

    /** @test */
    public function it_returns_correct_cast_class()
    {
        $className = InvoiceSync::castUsing([]);

        $this->assertSame(InvoiceSyncCast::class, $className);
    }
}

