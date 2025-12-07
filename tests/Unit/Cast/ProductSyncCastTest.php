<?php

namespace Tests\Unit\Cast;

use App\Casts\ProductSyncCast;
use App\DataMapper\ProductSync;
use PHPUnit\Framework\TestCase;

class ProductSyncCastTest extends TestCase
{
    /** @test
     *  get(): should return null when value is null
     *  Decision: is_null($value) → TRUE branch
     */
    public function test_get_returns_null_when_value_is_null()
    {
        $cast = new ProductSyncCast();

        $result = $cast->get(null, 'sync', null, []);

        $this->assertNull($result);
    }

    /** @test
     *  get(): should return null when json_decode() does NOT produce an array
     *  (MC/DC → independent FALSE branch of is_array)
     */
    public function test_get_returns_null_when_value_is_not_valid_json_array()
    {
        $cast = new ProductSyncCast();

        $result = $cast->get(null, 'sync', 'invalid-json', []);

        $this->assertNull($result);
    }

    /** @test
     *  get(): should return ProductSync object on valid JSON
     *  Covers:
     *   - is_null($value) = FALSE
     *   - is_array($data) = TRUE
     *   - All assignment statements
     */
    public function test_get_returns_product_sync_object_on_valid_json()
    {
        $cast = new ProductSyncCast();

        $jsonValue = json_encode([
            'qb_id' => 'PRD777'
        ]);

        $result = $cast->get(null, 'sync', $jsonValue, []);

        $this->assertInstanceOf(ProductSync::class, $result);
        $this->assertEquals('PRD777', $result->qb_id);
    }


    /** @test
     *  set(): should return null when value is null
     *  MC/DC → TRUE branch of is_null($value)
     */
    public function test_set_returns_null_array_when_value_is_null()
    {
        $cast = new ProductSyncCast();

        $result = $cast->set(null, 'sync', null, []);

        $this->assertEquals(['sync' => null], $result);
    }

    /** @test
     *  set(): should encode qb_id correctly for valid ProductSync object
     *  Tests full statement execution
     */
    public function test_set_encodes_qb_id_correctly()
    {
        $cast = new ProductSyncCast();

        $obj = new ProductSync();
        $obj->qb_id = 'PRD999';

        $result = $cast->set(null, 'sync', $obj, []);

        $this->assertArrayHasKey('sync', $result);

        $decoded = json_decode($result['sync'], true);

        $this->assertEquals('PRD999', $decoded['qb_id']);
    }
}

