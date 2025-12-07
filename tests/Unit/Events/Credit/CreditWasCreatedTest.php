<?php

namespace Tests\Unit\Events\Credit;

use App\Events\Credit\CreditWasCreated;
use App\Models\Credit;
use App\Models\Company;
use Tests\TestCase;

class CreditWasCreatedTest extends TestCase
{
    public function test_event_properties_are_set()
    {
        $credit = $this->createMock(Credit::class);
        $company = $this->createMock(Company::class);
        $vars = ['action' => 'create'];

        $event = new CreditWasCreated($credit, $company, $vars);

        $this->assertSame($credit, $event->credit);
        $this->assertSame($company, $event->company);
        $this->assertSame($vars, $event->event_vars);
    }

    public function test_broadcast_model_returns_credit()
    {
        $credit = $this->createMock(Credit::class);
        $event = new CreditWasCreated($credit, $this->createMock(Company::class), []);

        $this->assertSame($credit, $event->broadcastModel());
    }

    public function test_broadcast_includes()
    {
        $event = new CreditWasCreated(
            $this->createMock(Credit::class),
            $this->createMock(Company::class),
            []
        );

        $this->assertEquals(['client'], $event->broadcastIncludes());
    }
}

