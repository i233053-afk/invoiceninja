<?php

namespace Tests\Unit\Cast;

use Tests\TestCase;
use App\Casts\AsTaxEntityCollection;
use App\DataMapper\EInvoice\TaxEntity;

class AsTaxEntityCollectionTest extends TestCase
{
    /** @test */
    public function it_returns_empty_array_when_value_is_null()
    {
        $cast = new AsTaxEntityCollection();

        $result = $cast->get(null, 'taxes', null, []);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_empty_array_when_value_is_string_null()
    {
        $cast = new AsTaxEntityCollection();

        $result = $cast->get(null, 'taxes', "null", []);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_decodes_json_into_tax_entity_objects()
    {
        $cast = new AsTaxEntityCollection();

        $json = json_encode([
            ['rate' => 10, 'name' => 'GST'],
            ['rate' => 5, 'name' => 'VAT']
        ]);

        $result = $cast->get(null, 'taxes', $json, []);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(TaxEntity::class, $result[0]);
        $this->assertInstanceOf(TaxEntity::class, $result[1]);
    }

    /** @test */
    public function it_sets_and_returns_empty_array_json_when_value_is_null()
    {
        $cast = new AsTaxEntityCollection();

        $result = $cast->set(null, 'taxes', null, []);

        $this->assertEquals('[]', $result);
    }

    /** @test */
    public function it_converts_single_tax_entity_to_json()
    {
        $cast = new AsTaxEntityCollection();

        $tax = new TaxEntity(['name' => 'GST', 'rate' => 10]);

        $result = $cast->set(null, 'taxes', $tax, []);

        $decoded = json_decode($result, true);

        $this->assertCount(1, $decoded);
        $this->assertEquals('GST', $decoded[0]['name']);
        $this->assertEquals(10, $decoded[0]['rate']);
    }

    /** @test */
    public function it_converts_array_of_tax_entities_to_json()
    {
        $cast = new AsTaxEntityCollection();

        $value = [
            new TaxEntity(['name' => 'GST', 'rate' => 10]),
            new TaxEntity(['name' => 'VAT', 'rate' => 5])
        ];

        $result = json_decode($cast->set(null, 'taxes', $value, []), true);

        $this->assertCount(2, $result);
        $this->assertEquals('GST', $result[0]['name']);
        $this->assertEquals('VAT', $result[1]['name']);
    }
}

