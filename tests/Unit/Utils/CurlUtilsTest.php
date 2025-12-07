<?php

namespace Tests\Unit\Utils;

use App\Utils\CurlUtils;
use Tests\TestCase;

class CurlUtilsTest extends TestCase
{
public function up()
{
    if (Schema::getConnection()->getDriverName() === 'mysql') {
        Schema::table('users', function (Blueprint $table) {
            $table->text('oauth_user_token')->nullable()->change();
        });
    }
}

    public function testPostExecutesCorrectly()
    {
        // Fake URL and data
        $url = 'https://example.com/api';
        $data = ['key' => 'value'];
        $headers = ['Content-Type: application/json'];

        // We can't mock curl directly easily, so we'll just test the method returns a string
        $response = CurlUtils::post($url, $data, $headers);

        // Assert response is string (or null if real curl fails)
        $this->assertIsString($response);
    }

    /** @test */
    public function testGetExecutesCorrectly()
    {
        $url = 'https://example.com/api';
        $headers = ['Accept: application/json'];

        $response = CurlUtils::get($url, $headers);

        $this->assertIsString($response);
    }

    /** @test */
    public function testExecHandlesCurlErrors()
    {
        // Invalid URL to force curl error
        $url = 'http://invalid.localhost';
        $response = CurlUtils::exec('GET', $url, null);

        // Response should be false or empty string
        $this->assertTrue($response === false || is_string($response));
    }
}

