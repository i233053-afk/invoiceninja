<?php

namespace Tests\Unit\DataMapper;

use Tests\TestCase;
use App\DataMapper\ProductSync;
use App\Casts\ProductSyncCast;

class ProductSyncTest extends TestCase
{
    /** @test */
    public function it_initializes_qb_id_with_empty_string_when_no_data_given()
    {
        $sync = new ProductSync();

        $this->assertSame('', $sync->qb_id);
    }

    /** @test */
    public function it_initializes_qb_id_from_array()
    {
        $sync = new ProductSync(['qb_id' => '12345']);

        $this->assertSame('12345', $sync->qb_id);
    }

    /** @test */
    public function from_array_creates_instance_properly()
    {
        $sync = ProductSync::fromArray(['qb_id' => 'ABC']);

        $this->assertInstanceOf(ProductSync::class, $sync);
        $this->assertSame('ABC', $sync->qb_id);
    }

    /** @test */
    public function cast_using_returns_correct_cast_class()
    {
        $castClass = ProductSync::castUsing([]);

        $this->assertSame(ProductSyncCast::class, $castClass);
    }
}

