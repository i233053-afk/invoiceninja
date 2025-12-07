<?php

namespace Tests\Notifications;

use Tests\TestCase;
use App\Notifications\Ninja\SpamNotification;
use Illuminate\Notifications\Messages\SlackMessage;

class SpamNotificationTest extends TestCase
{
    /** @test */
    public function it_returns_slack_as_the_via_channel()
    {
        $notification = new SpamNotification([]);

        $this->assertEquals(['slack'], $notification->via(null));
    }

    /** @test */
    public function it_builds_slack_message_for_companies_only()
    {
        $spamList = [
            'companies' => [
                [
                    'name' => 'Company A',
                    'company_key' => 'C123',
                    'account_key' => 'A123',
                    'owner' => 'John Doe'
                ]
            ]
        ];

        $notification = new SpamNotification($spamList);
        $slack = $notification->toSlack(null);

        $this->assertInstanceOf(SlackMessage::class, $slack);

        // Only content is publicly accessible
        $this->assertStringContainsString("Companies", $slack->content);
        $this->assertStringContainsString(
            "Company A - c_key=C123 - a_key=A123 - John Doe",
            $slack->content
        );
    }

    /** @test */
    public function it_builds_slack_message_for_templates_only()
    {
        $spamList = [
            'templates' => [
                [
                    'name' => 'Template X',
                    'company_key' => 'TC1',
                    'account_key' => 'TA1',
                    'owner' => 'Alice'
                ]
            ]
        ];

        $notification = new SpamNotification($spamList);
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString("Templates", $slack->content);
        $this->assertStringContainsString("Template X - c_key=TC1 - a_key=TA1 - Alice", $slack->content);
    }

    /** @test */
    public function it_builds_slack_message_for_users_only()
    {
        $spamList = [
            'users' => [
                [
                    'email' => 'user@example.com',
                    'account_key' => 'A987',
                    'created' => '2025-01-05'
                ]
            ]
        ];

        $notification = new SpamNotification($spamList);
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString("Users", $slack->content);
        $this->assertStringContainsString(
            "user@example.com - a_key=A987 - created=2025-01-05",
            $slack->content
        );
    }

    /** @test */
    public function it_builds_slack_message_with_all_sections()
    {
        $spamList = [
            'companies' => [
                [
                    'name' => 'Comp1',
                    'company_key' => 'C1',
                    'account_key' => 'A1',
                    'owner' => 'Owner1'
                ]
            ],
            'templates' => [
                [
                    'name' => 'Temp1',
                    'company_key' => 'C2',
                    'account_key' => 'A2',
                    'owner' => 'Owner2'
                ]
            ],
            'users' => [
                [
                    'email' => 'u1@test.com',
                    'account_key' => 'A3',
                    'created' => '2025-01-03'
                ]
            ]
        ];

        $notification = new SpamNotification($spamList);
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString("Companies", $slack->content);
        $this->assertStringContainsString("Templates", $slack->content);
        $this->assertStringContainsString("Users", $slack->content);

        $this->assertStringContainsString("Comp1 - c_key=C1 - a_key=A1 - Owner1", $slack->content);
        $this->assertStringContainsString("Temp1 - c_key=C2 - a_key=A2 - Owner2", $slack->content);
        $this->assertStringContainsString("u1@test.com - a_key=A3 - created=2025-01-03", $slack->content);
    }
}

