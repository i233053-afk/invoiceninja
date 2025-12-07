<?php

namespace Tests\Unit\Events\Credit;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Credit;
use App\Events\Credit\CreditWasDeleted;

class CreditWasDeletedTest extends TestCase
{
    /** @test */
    public function it_sets_the_event_properties_correctly()
    {
        $credit = new Credit();
        $company = new Company();
        $vars = ['foo' => 'bar'];

        $event = new CreditWasDeleted($credit, $company, $vars);

        $this->assertSame($credit, $event->credit);
        $this->assertSame($company, $event->company);
        $this->assertSame($vars, $event->event_vars);
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $event = new CreditWasDeleted(
            new Credit(),
            new Company(),
            []
        );

        $this->assertInstanceOf(CreditWasDeleted::class, $event);
    }
}

