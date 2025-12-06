<?php

namespace Tests\Events;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Company;

use App\Events\Client\ClientWasCreated;
use App\Events\Client\ClientWasUpdated;
use App\Events\Client\ClientWasDeleted;
use App\Events\Client\ClientWasRestored;
use App\Events\Client\ClientWasArchived;
use App\Events\Client\ClientWasMerged;
use App\Events\Client\ClientWasPurged;

use Illuminate\Broadcasting\PrivateChannel;

class ClientTest extends TestCase
{
    private Client $client;
    private Company $company;
    private array $vars;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->client->id = 1;
        $this->client->company_id = 1;

        // entity type needed for Fractal transformation in ClientWasArchived
        $this->client->setRelation('company', new Company());
        $this->client->entity_type = 'client';

        $this->company = new Company();
        $this->company->company_key = 'abc123';

        $this->vars = ['test' => true];
    }

    /** @test */
    public function it_creates_client_created_event()
    {
        $event = new ClientWasCreated($this->client, $this->company, $this->vars);

        $this->assertSame($this->client, $event->client);
        $this->assertSame($this->company, $event->company);
        $this->assertSame($this->vars, $event->event_vars);
    }

    /** @test */
    public function it_creates_client_updated_event()
    {
        $event = new ClientWasUpdated($this->client, $this->company, $this->vars);

        $this->assertSame($this->client, $event->client);
        $this->assertSame($this->company, $event->company);
        $this->assertSame($this->vars, $event->event_vars);
    }

    /** @test */
    public function it_creates_client_deleted_event()
    {
        $event = new ClientWasDeleted($this->client, $this->company, $this->vars);

        $this->assertSame($this->client, $event->client);
        $this->assertSame($this->company, $event->company);
        $this->assertSame($this->vars, $event->event_vars);
    }

    /** @test */
    public function it_creates_client_restored_event()
    {
        $event = new ClientWasRestored($this->client, true, $this->company, $this->vars);

        $this->assertSame($this->client, $event->client);
        $this->assertTrue($event->fromDeleted);
        $this->assertSame($this->company, $event->company);
        $this->assertSame($this->vars, $event->event_vars);
    }

    /** @test */
    public function it_creates_client_archived_event_and_tests_broadcast()
    {
        $event = new ClientWasArchived($this->client, $this->company, $this->vars);

        // broadcastOn()
        $channels = $event->broadcastOn();
        $this->assertIsArray($channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-company-abc123', $channels[0]->name);


        // broadcastWith()
        $payload = $event->broadcastWith();
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('id', $payload);
    }

    /** @test */
    public function it_creates_client_merged_event()
    {
        $event = new ClientWasMerged('merge_123', $this->client, $this->company, $this->vars);

        $this->assertEquals('merge_123', $event->mergeable_client);
        $this->assertSame($this->client, $event->client);
        $this->assertSame($this->company, $event->company);
        $this->assertSame($this->vars, $event->event_vars);
    }

    /** @test */
    public function it_creates_client_purged_event()
    {
        $user = new User();
        $event = new ClientWasPurged('purged_123', $user, $this->company, $this->vars);

        $this->assertEquals('purged_123', $event->purged_client);
        $this->assertSame($user, $event->user);
        $this->assertSame($this->company, $event->company);
        $this->assertSame($this->vars, $event->event_vars);
    }
}

