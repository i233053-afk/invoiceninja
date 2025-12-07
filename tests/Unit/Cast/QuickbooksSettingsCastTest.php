<?php

namespace Tests\Unit\Cast;

use App\Casts\QuickbooksSettingsCast;
use App\DataMapper\QuickbooksSettings;
use PHPUnit\Framework\TestCase;

class QuickbooksSettingsCastTest extends TestCase
{
    /** @test
     *  get(): should return new QuickbooksSettings when value is null
     *  Decision: is_null($value) → TRUE
     *  MC/DC satisfied
     */
    public function test_get_returns_new_object_when_value_is_null()
    {
        $cast = new QuickbooksSettingsCast();

        $result = $cast->get(null, 'settings', null, []);

        $this->assertInstanceOf(QuickbooksSettings::class, $result);
    }

    /** @test
     *  get(): should return QuickbooksSettings created from JSON array
     *  Decision: is_null($value) → FALSE
     *  Statement coverage: json_decode + fromArray
     */
    public function test_get_returns_object_from_valid_json()
    {
        $cast = new QuickbooksSettingsCast();

        $jsonValue = json_encode([
            'enabled' => true,
            'client_id' => 'ABC123',
            'client_secret' => 'XYZ789'
        ]);

        // Mock fromArray if needed, but usually real call works
        $result = $cast->get(null, 'settings', $jsonValue, []);

        $this->assertInstanceOf(QuickbooksSettings::class, $result);
    }

    /** @test
     *  set(): should json_encode object properties when value is QuickbooksSettings
     *  Tests full happy path
     */
    public function test_set_encodes_quickbooks_settings_object()
    {
        $cast = new QuickbooksSettingsCast();

        $obj = new QuickbooksSettings();
        $obj->enabled = true;
        $obj->client_id = 'ID123';
        $obj->client_secret = 'SECRET456';

        $result = $cast->set(null, 'settings', $obj, []);

        $decoded = json_decode($result, true);

        $this->assertEquals('ID123', $decoded['client_id']);
        $this->assertEquals('SECRET456', $decoded['client_secret']);
        $this->assertTrue($decoded['enabled']);
    }

    /** @test
     *  set(): should return null when value is NOT QuickbooksSettings object
     *  Decision: instanceof → FALSE
     *  MC/DC satisfied
     */
    public function test_set_returns_null_when_invalid_type_passed()
    {
        $cast = new QuickbooksSettingsCast();

        $result = $cast->set(null, 'settings', ['invalid' => true], []);

        $this->assertNull($result);
    }
}

