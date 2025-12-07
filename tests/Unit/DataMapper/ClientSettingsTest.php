<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\ClientSettings;
use App\Models\Client;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClientSettingsTest extends TestCase
{
    /** @test */
    public function defaults_returns_stdclass_with_correct_properties()
    {
        $defaults = ClientSettings::defaults();

        $this->assertInstanceOf(stdClass::class, $defaults);
        $this->assertTrue(property_exists($defaults, 'entity'));
        $this->assertTrue(property_exists($defaults, 'industry_id'));
        $this->assertTrue(property_exists($defaults, 'size_id'));
        $this->assertSame(Client::class, $defaults->entity);
        $this->assertSame('', $defaults->industry_id);
        $this->assertSame('', $defaults->size_id);
    }

    /** @test */
    public function build_client_settings_returns_company_settings_when_client_settings_is_null()
    {
        $company_settings = (object)[
            'language_id' => 'en',
            'currency_id' => 'USD'
        ];

        $result = ClientSettings::buildClientSettings($company_settings, null);

        $this->assertSame($company_settings, $result);
    }

    /** @test */
    public function build_client_settings_merges_empty_client_settings()
    {
        $company_settings = (object)[
            'language_id' => 'en',
            'currency_id' => 'USD'
        ];

        $client_settings = (object)[
            'language_id' => '',
        ];

        $merged = ClientSettings::buildClientSettings($company_settings, $client_settings);

        $this->assertSame('en', $merged->language_id); // inherited from company
        $this->assertSame('USD', $merged->currency_id); // inherited from company
    }

    /** @test */
    public function build_client_settings_preserves_non_empty_client_settings()
    {
        $company_settings = (object)[
            'language_id' => 'en',
            'currency_id' => 'USD'
        ];

        $client_settings = (object)[
            'language_id' => 'fr',
            'currency_id' => 'EUR'
        ];

        $merged = ClientSettings::buildClientSettings($company_settings, $client_settings);

        $this->assertSame('fr', $merged->language_id);
        $this->assertSame('EUR', $merged->currency_id);
    }

    /** @test */
    public function build_client_settings_handles_array_input()
    {
        $company_settings = (object)[
            'language_id' => 'en',
            'currency_id' => 'USD'
        ];

        $client_settings_array = [
            'language_id' => '',
            'currency_id' => ''
        ];

        $merged = ClientSettings::buildClientSettings($company_settings, $client_settings_array);

        $this->assertSame('en', $merged->language_id);
        $this->assertSame('USD', $merged->currency_id);
    }
}

