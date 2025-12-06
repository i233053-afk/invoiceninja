<?php

namespace Tests\Unit\DataMapper;

use PHPUnit\Framework\TestCase;
use App\DataMapper\ClientSettings;
use App\Models\Client;

class ClientSettingsTest extends TestCase
{
    /** @test */
    public function it_returns_defaults_with_casts()
    {
        $defaults = ClientSettings::defaults();

        // Check it returns an object
        $this->assertIsObject($defaults);

        // Check it has required properties
        $this->assertObjectHasProperty('entity', $defaults);
        $this->assertObjectHasProperty('industry_id', $defaults);
        $this->assertObjectHasProperty('size_id', $defaults);

        // Check types
        $this->assertIsString($defaults->industry_id);
        $this->assertIsString($defaults->size_id);
        $this->assertEquals(Client::class, $defaults->entity);
    }

    /** @test */
    public function buildClientSettings_returns_company_settings_if_client_is_null()
    {
        $company = (object) ['language_id' => 'en', 'currency_id' => 'USD'];
        $result = ClientSettings::buildClientSettings($company, null);

        $this->assertEquals($company, $result);
    }

    /** @test */
    public function buildClientSettings_casts_array_client_to_object_and_merges()
    {
        $company = (object) ['language_id' => 'en', 'currency_id' => 'USD', 'send_reminders' => true];

        $clientArray = [
            'language_id' => '',  // empty string triggers branch
            'currency_id' => 'EUR', // already set, no merge
        ];

        $result = ClientSettings::buildClientSettings($company, $clientArray);

        $this->assertIsObject($result);
        $this->assertEquals('en', $result->language_id); // empty string replaced
        $this->assertEquals('EUR', $result->currency_id); // existing value kept
        $this->assertEquals(true, $result->send_reminders); // missing property copied from company
    }

    /** @test */
    public function buildClientSettings_merges_object_client_with_company_settings()
    {
        $company = (object) ['language_id' => 'en', 'currency_id' => 'USD', 'send_reminders' => true];

        $clientObj = (object) [
            'language_id' => '',       // triggers branch
            'currency_id' => 'EUR',   // already set
            // send_reminders missing -> should be copied
        ];

        $result = ClientSettings::buildClientSettings($company, $clientObj);

        $this->assertEquals('en', $result->language_id);
        $this->assertEquals('EUR', $result->currency_id);
        $this->assertEquals(true, $result->send_reminders);
    }

    /** @test */
    public function buildClientSettings_keeps_non_empty_client_properties()
    {
        $company = (object) ['language_id' => 'en', 'currency_id' => 'USD'];
        $clientObj = (object) ['language_id' => 'fr', 'currency_id' => 'EUR'];

        $result = ClientSettings::buildClientSettings($company, $clientObj);

        $this->assertEquals('fr', $result->language_id);
        $this->assertEquals('EUR', $result->currency_id);
    }

    /** @test */
    public function buildClientSettings_handles_empty_string_properties()
    {
        $company = (object) ['language_id' => 'en', 'currency_id' => 'USD'];
        $clientObj = (object) ['language_id' => '', 'currency_id' => ''];

        $result = ClientSettings::buildClientSettings($company, $clientObj);

        $this->assertEquals('en', $result->language_id);
        $this->assertEquals('USD', $result->currency_id);
    }
}

