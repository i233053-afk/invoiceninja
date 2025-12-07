<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\PaymentSync;
use App\Casts\PaymentSyncCast;
use Tests\TestCase;

class PaymentSyncTest extends TestCase
{
    /** @test */
    public function it_initializes_with_default_values()
    {
        $sync = new PaymentSync();

        $this->assertEquals('', $sync->qb_id);
    }

    /** @test */
    public function it_initializes_with_given_attributes()
    {
        $sync = new PaymentSync(['qb_id' => 'PAY456']);

        $this->assertEquals('PAY456', $sync->qb_id);
    }

    /** @test */
    public function cast_using_returns_correct_cast_class()
    {
        $this->assertEquals(
            PaymentSyncCast::class,
            PaymentSync::castUsing([])
        );
    }

    /** @test */
    public function it_creates_instance_from_array()
    {
        $sync = PaymentSync::fromArray(['qb_id' => 'PAY999']);

        $this->assertInstanceOf(PaymentSync::class, $sync);
        $this->assertEquals('PAY999', $sync->qb_id);
    }
}

