<?php

namespace Tests\Unit\DataMapper;

use PHPUnit\Framework\TestCase;
use App\DataMapper\BaseSettings;

class BaseSettingsTest extends TestCase
{
/** @test */
public function it_casts_attributes_correctly()
{
    // Integer casting
    $this->assertSame(123, BaseSettings::castAttribute('int', '123'));
    $this->assertSame(123, BaseSettings::castAttribute('integer', '123'));

    // Float/double/real casting
    $this->assertSame(123.45, BaseSettings::castAttribute('float', '123.45'));
    $this->assertSame(123.45, BaseSettings::castAttribute('double', '123.45'));
    $this->assertSame(123.45, BaseSettings::castAttribute('real', '123.45'));

    // String casting
    $this->assertSame('123', BaseSettings::castAttribute('string', 123));
    $this->assertSame('1', BaseSettings::castAttribute('string', true));
    $this->assertSame('', BaseSettings::castAttribute('string', null));

    // Boolean casting
    $this->assertTrue(BaseSettings::castAttribute('bool', 1));
    $this->assertFalse(BaseSettings::castAttribute('boolean', 0));

    // Object casting
    $jsonObject = '{"key":"value"}';
    $decodedObject = BaseSettings::castAttribute('object', $jsonObject);
    $this->assertIsObject($decodedObject);
    $this->assertSame('value', $decodedObject->key);

    // Array casting
    $jsonArray = '{"a":1,"b":2}';
    $decodedArray = BaseSettings::castAttribute('array', $jsonArray);
    $this->assertIsArray($decodedArray);
    $this->assertSame(1, $decodedArray['a']);
    $this->assertSame(2, $decodedArray['b']);

    $decodedJson = BaseSettings::castAttribute('json', $jsonArray);
    $this->assertIsArray($decodedJson);
    $this->assertSame(1, $decodedJson['a']);

    // Default: unknown type returns value unchanged
    $this->assertSame('unchanged', BaseSettings::castAttribute('unknown', 'unchanged'));
}

    /** @test */
    public function it_sets_casts_on_object_correctly()
    {
        $obj = new class {
            public $foo = '123';
            public $bar = 'true';
            public $baz = '{"key":"value"}';
            public $qux = 'unchanged';
        };

        $casts = [
            'foo' => 'int',
            'bar' => 'bool',
            'baz' => 'object',
            'qux' => 'unknown', // default
        ];

        $castedObj = BaseSettings::setCasts($obj, $casts);

        $this->assertSame(123, $castedObj->foo);
        $this->assertTrue($castedObj->bar);
        $this->assertIsObject($castedObj->baz);
        $this->assertSame('value', $castedObj->baz->key);
        $this->assertSame('unchanged', $castedObj->qux);
    }

    /** @test */
    public function it_handles_non_scalar_values_gracefully()
    {
        // Non-scalar values should fall back to defaults
        $this->assertSame(0, BaseSettings::castAttribute('int', ['not','scalar']));
        $this->assertSame(0.0, BaseSettings::castAttribute('float', ['not','scalar']));
        $this->assertSame('', BaseSettings::castAttribute('string', ['not','scalar']));
        $this->assertFalse(BaseSettings::castAttribute('bool', ['not','scalar']));
    }
}

