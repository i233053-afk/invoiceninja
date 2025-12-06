<?php

namespace Tests\listeners;

use Tests\TestCase;
use Mockery;
use App\Listeners\SendVerificationNotification;
use App\Jobs\Mail\NinjaMailerJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\App;

class SendVerificationNotificationTest extends TestCase
{
    /** @test */
    public function it_handles_event_and_executes_invite_and_skips_mailer_when_company_is_new()
    {
        Queue::fake();

        // --- Mock static MultiDB class ---
        $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
        $multiDbMock->shouldReceive('setDB')->once()->with('testdb');

        // --- Mock company (new, so branch skipped) ---
        $company = (object)[
            'db' => 'testdb',
            'created_at' => now(),
            'settings' => ['test' => 'value']
        ];

        // --- Mock user service chain ---
        $serviceMock = Mockery::mock();
        $serviceMock->shouldReceive('invite')
                    ->once()
                    ->with($company, true);

        $userMock = Mockery::mock();
        $userMock->shouldReceive('service')->andReturn($serviceMock);

        $event = (object)[
            'company' => $company,
            'user' => $userMock,
            'creating_user' => (object)[],
            'is_react' => true,
        ];

        $listener = new SendVerificationNotification();
        $listener->handle($event);

        // No mail should be dispatched
        Queue::assertNothingPushed();
    }

    /** @test */
   /** @test */
public function it_dispatches_mailer_when_company_is_older_than_one_day()
{
    Queue::fake();

    // --- Mock static MultiDB ---
    $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
    $multiDbMock->shouldReceive('setDB')->once()->with('testdb');

    // --- Correct settings structure to avoid Ninja::transformTranslations crash ---
    $settings = (object)[
        'translations' => (object)[
            'en' => (object)[
                'whatever' => 'Test'
            ]
        ]
    ];

    $company = (object)[
        'db' => 'testdb',
        'created_at' => Carbon::now()->subDays(2),
        'settings' => $settings,
    ];

    // --- Mock invite() chain ---
    $serviceMock = Mockery::mock();
    $serviceMock->shouldReceive('invite')
                ->once()
                ->with($company, false);

    $userMock = Mockery::mock();
    $userMock->shouldReceive('service')->andReturn($serviceMock);

    $creatingUser = (object)['id' => 99];

    $event = (object)[
        'company' => $company,
        'user' => $userMock,
        'creating_user' => $creatingUser,
        'is_react' => false
    ];

    App::forgetInstance('translator');

    $listener = new SendVerificationNotification();
    $listener->handle($event);

    Queue::assertPushed(NinjaMailerJob::class);
}

}

