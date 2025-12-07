<?php

namespace Tests\Listeners\Activity;

use Tests\TestCase;
use Mockery;
use App\Listeners\Activity\ArchivedClientActivity;
use App\Libraries\MultiDB;
use App\Repositories\ActivityRepository;
use App\Models\Activity;
use stdClass;

class ArchivedClientActivityTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_saves_activity_with_user_id_from_event_vars()
    {
        // Mock MultiDB static
        $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
        $multiDbMock->shouldReceive('setDb')->once()->with('test_db');

        // Mock ActivityRepository
        $activityRepoMock = Mockery::mock(ActivityRepository::class);
        $activityRepoMock->shouldReceive('save')
            ->once()
            ->withArgs(function ($fields, $client, $event_vars) {
                // Assertions on fields
                $this->assertEquals(1, $fields->client_id);
                $this->assertEquals(123, $fields->user_id);
                $this->assertEquals(10, $fields->company_id);
                $this->assertEquals(Activity::ARCHIVE_CLIENT, $fields->activity_type_id);
                return true;
            });

        $listener = new ArchivedClientActivity($activityRepoMock);

        $event = (object)[
            'company' => (object)['db' => 'test_db'],
            'client'  => (object)[
                'id' => 1,
                'user_id' => 999,
                'company_id' => 10,
            ],
            'event_vars' => [
                'user_id' => 123,
            ]
        ];

        $listener->handle($event);
    }

    /** @test */
    public function it_saves_activity_with_user_id_from_client_when_not_in_event_vars()
    {
        // Mock MultiDB static
        $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
        $multiDbMock->shouldReceive('setDb')->once()->with('test_db');

        // Mock ActivityRepository
        $activityRepoMock = Mockery::mock(ActivityRepository::class);
        $activityRepoMock->shouldReceive('save')
            ->once()
            ->withArgs(function ($fields, $client, $event_vars) {
                // Assertions on fields
                $this->assertEquals(1, $fields->client_id);
                $this->assertEquals(999, $fields->user_id); // falls back to client->user_id
                $this->assertEquals(10, $fields->company_id);
                $this->assertEquals(Activity::ARCHIVE_CLIENT, $fields->activity_type_id);
                return true;
            });

        $listener = new ArchivedClientActivity($activityRepoMock);

        $event = (object)[
            'company' => (object)['db' => 'test_db'],
            'client'  => (object)[
                'id' => 1,
                'user_id' => 999,
                'company_id' => 10,
            ],
            'event_vars' => [] // user_id not set
        ];

        $listener->handle($event);
    }
}

