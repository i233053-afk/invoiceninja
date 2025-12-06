<?php

namespace Tests\Events;

use PHPUnit\Framework\TestCase;
use App\Events\Account\AccountCreated;
use App\Events\Account\AccountDeleted;
use App\Events\Account\StripeConnectFailure;
use App\Models\Company;

class AccountTest extends TestCase
{
    /** @test */
    public function it_creates_account_created_event()
    {
        $user = (object)['id' => 1];
        $company = (object)['id' => 10];
        $eventVars = ['foo' => 'bar'];

        $event = new AccountCreated($user, $company, $eventVars);

        $this->assertSame($user, $event->user);
        $this->assertSame($company, $event->company);
        $this->assertSame($eventVars, $event->event_vars);

        $this->assertEquals([], $event->broadcastOn());
    }

    /** @test */
    public function it_creates_account_deleted_event()
    {
        $event = new AccountDeleted(
            account_key: 'acc_123',
            email: 'test@example.com',
            ip: '127.0.0.1'
        );

        $this->assertEquals('acc_123', $event->account_key);
        $this->assertEquals('test@example.com', $event->email);
        $this->assertEquals('127.0.0.1', $event->ip);

        $this->assertEquals([], $event->broadcastOn());
    }

    /** @test */
    public function it_creates_stripe_connect_failure_event()
    {
        $company = new Company();
        $company->id = 5;

        $event = new StripeConnectFailure($company, 'database1');

        $this->assertSame($company, $event->company);
        $this->assertEquals('database1', $event->db);

        $this->assertEquals([], $event->broadcastOn());
    }
}

