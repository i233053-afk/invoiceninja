<?php

namespace Tests\Unit\Events\Credit;

use App\Events\Credit\CreditWasUpdated;
use App\Models\Credit;
use App\Models\Company;
use Tests\TestCase;

class CreditWasUpdatedTest extends TestCase
{
    public function test_event_properties_are_set()
    {
        // Create mock instances for Credit and Company
        $credit = $this->createMock(Credit::class);
        $company = $this->createMock(Company::class);
        $event_vars = ['foo' => 'bar'];

        // Instantiate the event
        $event = new CreditWasUpdated($credit, $company, $event_vars);

        // Assertions for properties
        $this->assertSame($credit, $event->credit);
        $this->assertSame($company, $event->company);
        $this->assertSame($event_vars, $event->event_vars);
    }

    public function test_broadcast_model_and_includes()
    {
        $credit = $this->createMock(Credit::class);
        $company = $this->createMock(Company::class);
        $event_vars = [];

        $event = new CreditWasUpdated($credit, $company, $event_vars);

        // broadcastModel should return the credit model
        $this->assertSame($credit, $event->broadcastModel());

        // broadcastIncludes should return array with 'client'
        $this->assertSame(['client'], $event->broadcastIncludes());
    }
}

