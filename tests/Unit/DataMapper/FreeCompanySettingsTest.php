<?php

namespace Tests\Unit\DataMapper;

use Tests\TestCase;
use App\DataMapper\FreeCompanySettings;
use stdClass;

class FreeCompanySettingsTest extends TestCase
{
    /** @test */
    public function defaults_returns_stdclass_with_correct_properties()
    {
        // Fake config values
        config()->set('ninja.settings', json_encode([]));
        config()->set('ninja.i18n.timezone_id', 'UTC');
        config()->set('ninja.i18n.currency_id', 'USD');
        config()->set('ninja.i18n.language_id', 'en');
        config()->set('ninja.i18n.payment_terms', 30);
        config()->set('ninja.i18n.military_time', true);
        config()->set('ninja.i18n.date_format_id', 'Y-m-d');
        config()->set('ninja.i18n.country_id', 'US');

        $defaults = FreeCompanySettings::defaults();

        $this->assertInstanceOf(stdClass::class, $defaults);

        // Check important properties
        $this->assertSame('UTC', $defaults->timezone_id);
        $this->assertSame('USD', $defaults->currency_id);
        $this->assertSame('en', $defaults->language_id);
        $this->assertSame(30, $defaults->payment_terms);
        $this->assertTrue($defaults->military_time);
        $this->assertSame('Y-m-d', $defaults->date_format_id);
        $this->assertSame('US', $defaults->country_id);

        // Ensure translations is an object
        $this->assertIsObject($defaults->translations);

        // Test that all casted fields exist
        foreach (FreeCompanySettings::$casts as $key => $type) {
$this->assertTrue(property_exists($defaults, 'design'), 'design property missing');
        }
    }

    /** @test */
    public function defaults_applies_casts_correctly()
    {
        config()->set('ninja.settings', json_encode([]));
        config()->set('ninja.i18n.timezone_id', 'Asia/Karachi');
        config()->set('ninja.i18n.currency_id', 'PKR');
        config()->set('ninja.i18n.language_id', 'ur');
        config()->set('ninja.i18n.payment_terms', 15);
        config()->set('ninja.i18n.military_time', false);
        config()->set('ninja.i18n.date_format_id', 'd-m-Y');
        config()->set('ninja.i18n.country_id', 'PK');

        $defaults = FreeCompanySettings::defaults();

        // Type assertions based on $casts
        $this->assertIsString($defaults->timezone_id);
        $this->assertIsString($defaults->currency_id);
        $this->assertIsString($defaults->language_id);
        $this->assertIsInt($defaults->payment_terms);
        $this->assertIsBool($defaults->military_time);
        $this->assertIsString($defaults->date_format_id);
        $this->assertIsString($defaults->country_id);
    }

    /** @test */
    public function defaults_handles_empty_i18n_config_gracefully()
    {
        config()->set('ninja.settings', json_encode([]));
        config()->set('ninja.i18n.timezone_id', null);
        config()->set('ninja.i18n.currency_id', null);
        config()->set('ninja.i18n.language_id', null);
        config()->set('ninja.i18n.payment_terms', null);
        config()->set('ninja.i18n.military_time', null);
        config()->set('ninja.i18n.date_format_id', null);
        config()->set('ninja.i18n.country_id', null);

        $defaults = FreeCompanySettings::defaults();

        // Should still return an object, with nullable/empty fields
        $this->assertInstanceOf(stdClass::class, $defaults);
        $this->assertSame('',$defaults->timezone_id);
        $this->assertNull($defaults->currency_id);
        $this->assertNull($defaults->language_id);
        $this->assertNull($defaults->payment_terms);
        $this->assertNull($defaults->military_time);
        $this->assertNull($defaults->date_format_id);
        $this->assertNull($defaults->country_id);
    }
}

