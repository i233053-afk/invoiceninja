<?php

namespace Tests\Unit\Events\Credit;

use App\Events\Credit\CreditWasViewed;
use App\Models\Company;
use App\Models\CreditInvitation;
use Tests\TestCase;

class CreditWasViewedTest extends TestCase
{
    public function test_event_properties_are_set()
    {
        // Create mock instances for CreditInvitation and Company
        $invitation = $this->createMock(CreditInvitation::class);
        $company = $this->createMock(Company::class);
        $event_vars = ['foo' => 'bar'];

        // Instantiate the event
        $event = new CreditWasViewed($invitation, $company, $event_vars);

        // Assertions for properties
        $this->assertSame($invitation, $event->invitation);
        $this->assertSame($company, $event->company);
        $this->assertSame($event_vars, $event->event_vars);
    }
}

