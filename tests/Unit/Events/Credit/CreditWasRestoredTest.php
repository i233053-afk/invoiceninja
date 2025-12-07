<?php

namespace Tests\Unit\Events\Credit;

use App\Events\Credit\CreditWasRestored;
use App\Models\Credit;
use App\Models\Company;
use Tests\TestCase;

class CreditWasRestoredTest extends TestCase
{
    public function test_event_properties_are_set()
    {
        // Create mock instances for Credit and Company
        $credit = $this->createMock(Credit::class);
        $company = $this->createMock(Company::class);
        $event_vars = ['foo' => 'bar'];
        $fromDeleted = true;

        // Instantiate the event
        $event = new CreditWasRestored($credit, $fromDeleted, $company, $event_vars);

        // Assertions
        $this->assertSame($credit, $event->credit);
        $this->assertSame($fromDeleted, $event->fromDeleted);
        $this->assertSame($company, $event->company);
        $this->assertSame($event_vars, $event->event_vars);
    }
}

