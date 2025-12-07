<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\QuickbooksSettings;
use App\DataMapper\QuickbooksSync;
use PHPUnit\Framework\TestCase;

class QuickbooksSettingsTest extends TestCase
{
    /** @test */
    public function it_initializes_with_default_values()
    {
        $settings = new QuickbooksSettings();

        $this->assertSame('', $settings->accessTokenKey);
        $this->assertSame('', $settings->refresh_token);
        $this->assertSame('', $settings->realmID);
        $this->assertSame(0, $settings->accessTokenExpiresAt);
        $this->assertSame(0, $settings->refreshTokenExpiresAt);
        $this->assertSame('', $settings->baseURL);
        $this->assertInstanceOf(QuickbooksSync::class, $settings->settings);
    }

    /** @test */
    public function it_sets_values_correctly_from_array()
    {
        $data = [
            'accessTokenKey' => 'abc123',
            'refresh_token' => 'xyz789',
            'realmID' => '12345',
            'accessTokenExpiresAt' => 111111,
            'refreshTokenExpiresAt' => 222222,
            'baseURL' => 'https://example.com',
            'settings' => [
                'qb_id' => 'QB-001'
            ],
        ];

        $settings = QuickbooksSettings::fromArray($data);

        $this->assertSame('abc123', $settings->accessTokenKey);
        $this->assertSame('xyz789', $settings->refresh_token);
        $this->assertSame('12345', $settings->realmID);
        $this->assertSame(111111, $settings->accessTokenExpiresAt);
        $this->assertSame(222222, $settings->refreshTokenExpiresAt);
        $this->assertSame('https://example.com', $settings->baseURL);

        // settings must be converted to QuickbooksSync object
        $this->assertInstanceOf(QuickbooksSync::class, $settings->settings);
        $this->assertSame('QB-001', $settings->settings->qb_id);
    }

    /** @test */
    public function it_returns_cast_using_correct_class()
    {
        $this->assertSame(
            \App\Casts\QuickbooksSettingsCast::class,
            QuickbooksSettings::castUsing([])
        );
    }
}

