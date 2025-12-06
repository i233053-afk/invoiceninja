<?php

namespace Tests\Listeners\Activity;

use Tests\TestCase;
use Mockery;
use App\Listeners\Activity\CreatedCreditActivity;
use App\Libraries\MultiDB;
use App\Repositories\ActivityRepository;
use App\Models\Activity;

class CreatedCreditActivityTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_saves_activity_with_user_id_from_event_vars()
    {
        $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
        $multiDbMock->shouldReceive('setDb')->once()->with('test_db');

        $activityRepoMock = Mockery::mock(ActivityRepository::class);
        $activityRepoMock->shouldReceive('save')
            ->once()
            ->withArgs(function ($fields, $credit, $event_vars) {
                $this->assertEquals(1, $fields->credit_id);
                $this->assertEquals(123, $fields->user_id);
                $this->assertEquals(10, $fields->client_id);
                $this->assertEquals(10, $fields->company_id);
                $this->assertEquals(Activity::CREATE_CREDIT, $fields->activity_type_id);
                return true;
            });

        $listener = new CreatedCreditActivity($activityRepoMock);

        $event = (object)[
            'company' => (object)['db' => 'test_db'],
            'credit' => (object)[
                'id' => 1,
                'user_id' => 999,
                'client_id' => 10,
                'company_id' => 10
            ],
            'event_vars' => [
                'user_id' => 123
            ]
        ];

        $listener->handle($event);
    }

    /** @test */
    public function it_saves_activity_with_user_id_from_credit_when_not_in_event_vars()
    {
        $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
        $multiDbMock->shouldReceive('setDb')->once()->with('test_db');

        $activityRepoMock = Mockery::mock(ActivityRepository::class);
        $activityRepoMock->shouldReceive('save')
            ->once()
            ->withArgs(function ($fields, $credit, $event_vars) {
                $this->assertEquals(1, $fields->credit_id);
                $this->assertEquals(999, $fields->user_id); // fallback
                $this->assertEquals(10, $fields->client_id);
                $this->assertEquals(10, $fields->company_id);
                $this->assertEquals(Activity::CREATE_CREDIT, $fields->activity_type_id);
                return true;
            });

        $listener = new CreatedCreditActivity($activityRepoMock);

        $event = (object)[
            'company' => (object)['db' => 'test_db'],
            'credit' => (object)[
                'id' => 1,
                'user_id' => 999,
                'client_id' => 10,
                'company_id' => 10
            ],
            'event_vars' => [] // no user_id
        ];

        $listener->handle($event);
    }
}

