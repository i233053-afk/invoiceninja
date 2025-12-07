<?php

namespace Tests\Unit\Events\Credit;

use App\Events\Credit\CreditWasEmailed;
use App\Models\Company;
use App\Models\CreditInvitation;
use Tests\TestCase;

class CreditWasEmailedTest extends TestCase
{
    /** @test */
    public function it_sets_all_properties_correctly()
    {
        // Arrange
        $invitation = new CreditInvitation();
        $company = new Company();
        $eventVars = ['key' => 'value'];
        $template = 'email_template';

        // Act
        $event = new CreditWasEmailed($invitation, $company, $eventVars, $template);

        // Assert
        $this->assertSame($invitation, $event->invitation);
        $this->assertSame($company, $event->company);
        $this->assertSame($eventVars, $event->event_vars);
        $this->assertSame($template, $event->template);
    }
}

