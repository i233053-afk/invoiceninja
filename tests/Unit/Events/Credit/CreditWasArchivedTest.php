<?php

namespace Tests\Unit\Events\Credit;

use App\Events\Credit\CreditWasArchived;
use App\Models\Credit;
use App\Models\Company;
use Tests\TestCase;

class CreditWasArchivedTest extends TestCase
{
    public function test_event_initializes_correctly()
    {
        $credit = new Credit();
        $company = new Company();
        $vars = ['action' => 'archive'];

        $event = new CreditWasArchived($credit, $company, $vars);

        $this->assertSame($credit, $event->credit);
        $this->assertSame($company, $event->company);
        $this->assertSame($vars, $event->event_vars);
    }
}

