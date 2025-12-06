<?php

namespace Tests\Listeners\Activity;

use Tests\TestCase;
use Mockery;
use App\Listeners\Activity\ClientPurgedActivity;
use App\Libraries\MultiDB;
use App\Repositories\ActivityRepository;
use App\Models\Activity;
use stdClass;

class ClientPurgedActivityTest extends TestCase
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
            ->withArgs(function ($fields, $user, $event_vars) {
                $this->assertEquals(123, $fields->user_id);
                $this->assertEquals(10, $fields->company_id);
                $this->assertEquals(Activity::PURGE_CLIENT, $fields->activity_type_id);
                $this->assertEquals('PurgedClientData', $fields->notes);
                return true;
            });

        $listener = new ClientPurgedActivity($activityRepoMock);

        $event = (object)[
            'company' => (object)[ 'db'=>'test_db', 'id'=>10 ],
            'user' => (object)[ 'id'=>999 ],
            'purged_client' => 'PurgedClientData',
            'event_vars' => [ 'user_id'=>123 ]
        ];

        $listener->handle($event);
    }

    /** @test */
    public function it_saves_activity_with_user_id_from_user_when_not_in_event_vars()
    {
        // Mock MultiDB static
        $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
        $multiDbMock->shouldReceive('setDb')->once()->with('test_db');

        // Mock ActivityRepository
        $activityRepoMock = Mockery::mock(ActivityRepository::class);
        $activityRepoMock->shouldReceive('save')
            ->once()
            ->withArgs(function ($fields, $user, $event_vars) {
                $this->assertEquals(999, $fields->user_id); // fallback
                $this->assertEquals(10, $fields->company_id);
                $this->assertEquals(Activity::PURGE_CLIENT, $fields->activity_type_id);
                $this->assertEquals('PurgedClientData', $fields->notes);
                return true;
            });

        $listener = new ClientPurgedActivity($activityRepoMock);

        $event = (object)[
            'company' => (object)[ 'db'=>'test_db', 'id'=>10 ],
            'user' => (object)[ 'id'=>999 ],
            'purged_client' => 'PurgedClientData',
            'event_vars' => [] // no user_id
        ];

        $listener->handle($event);
    }
}

