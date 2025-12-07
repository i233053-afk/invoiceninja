<?php

namespace Tests\Unit\Events\Credit;

use App\Events\Credit\CreditWasEmailedAndFailed;
use App\Models\Credit;
use App\Models\Company;
use Tests\TestCase;

class CreditWasEmailedAndFailedTest extends TestCase
{
    public function test_event_properties_are_set()
    {
        // Create mock instances for Credit and Company
        $credit = $this->createMock(Credit::class);
        $company = $this->createMock(Company::class);
        $errors = ['error1', 'error2'];
        $event_vars = ['foo' => 'bar'];

        // Instantiate the event
        $event = new CreditWasEmailedAndFailed($credit, $company, $errors, $event_vars);

        // Assertions
        $this->assertSame($credit, $event->credit);
        $this->assertSame($company, $event->company);
        $this->assertSame($errors, $event->errors);
        $this->assertSame($event_vars, $event->event_vars);
    }
}

