<?php

namespace Tests\Unit\Cast;

use Tests\TestCase;
use App\Casts\EncryptedCast;
use Illuminate\Support\Facades\Crypt;

class EncryptedCastTest extends TestCase
{
    /** @test */
    public function it_returns_null_when_value_is_null()
    {
        $cast = new EncryptedCast();

        $result = $cast->get(null, 'secret', null, []);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_value_is_not_a_string_or_too_short()
    {
        $cast = new EncryptedCast();

        $result1 = $cast->get(null, 'secret', 123, []);
        $result2 = $cast->get(null, 'secret', '', []);
        $result3 = $cast->get(null, 'secret', 'a', []); // length 1

        $this->assertNull($result1);
        $this->assertNull($result2);
        $this->assertNull($result3);
    }

    /** @test */
    public function it_decrypts_a_valid_encrypted_string()
    {
        $cast = new EncryptedCast();

        $encrypted = encrypt('my-secret');

        $result = $cast->get(null, 'secret', $encrypted, []);

        $this->assertEquals('my-secret', $result);
    }

    /** @test */
    public function it_encrypts_value_when_setting()
    {
        $cast = new EncryptedCast();

        $result = $cast->set(null, 'secret', 'hello', []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('secret', $result);

        // Validate decrypts to original value
        $this->assertEquals('hello', decrypt($result['secret']));
    }

    /** @test */
    public function it_returns_null_when_setting_null_value()
    {
        $cast = new EncryptedCast();

        $result = $cast->set(null, 'secret', null, []);

        $this->assertIsArray($result);
        $this->assertNull($result['secret']);
    }
}

