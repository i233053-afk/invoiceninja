<?php

namespace Tests\Unit\Events\Contact;

use Tests\TestCase;
use App\Models\Company;
use App\Models\ClientContact;
use App\Events\Contact\ContactLoggedIn;

class ContactLoggedInTest extends TestCase
{
    public function test_event_properties_are_assigned_correctly()
    {
        $contact = new ClientContact();
        $company = new Company();
        $vars = ['ip' => '127.0.0.1'];

        $event = new ContactLoggedIn($contact, $company, $vars);

        $this->assertSame($contact, $event->client_contact);
        $this->assertSame($company, $event->company);
        $this->assertSame($vars, $event->event_vars);
    }

    public function test_broadcast_on_returns_empty_array()
    {
        $event = new ContactLoggedIn(
            new ClientContact(),
            new Company(),
            []
        );

        $this->assertIsArray($event->broadcastOn());
        $this->assertEmpty($event->broadcastOn());
    }
}

