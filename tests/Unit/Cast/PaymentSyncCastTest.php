<?php

namespace Tests\Unit\Cast;

use App\Casts\PaymentSyncCast;
use App\DataMapper\PaymentSync;
use PHPUnit\Framework\TestCase;

class PaymentSyncCastTest extends TestCase
{
    /** @test
     *  Test: get() should return null when value is null
     *  Coverage: 
     *   - Decision 1: is_null($value) → TRUE branch
     */
    public function test_get_returns_null_when_value_is_null()
    {
        $cast = new PaymentSyncCast();

        $result = $cast->get(null, 'sync', null, []);

        $this->assertNull($result);
    }

    /** @test
     *  Test: get() should return null when json_decode() does not return an array 
     *  (MC/DC → ensures second decision independently)
     *  Example: invalid JSON string
     */
    public function test_get_returns_null_when_value_is_not_array()
    {
        $cast = new PaymentSyncCast();

        // invalid JSON that returns NULL
        $result = $cast->get(null, 'sync', 'invalid-json', []);

        $this->assertNull($result);
    }

    /** @test
     *  Test: get() should return PaymentSync object when valid JSON is provided
     *  Coverage:
     *   - is_null($value) → FALSE
     *   - is_array($data) → TRUE
     *   - All statements executed
     */
    public function test_get_returns_payment_sync_object_on_valid_json()
    {
        $cast = new PaymentSyncCast();

        $jsonValue = json_encode([
            'qb_id' => 'QB12345'
        ]);

        $result = $cast->get(null, 'sync', $jsonValue, []);

        $this->assertInstanceOf(PaymentSync::class, $result);
        $this->assertEquals('QB12345', $result->qb_id);
    }


    /** @test
     *  Test: set() should return null value when input is null
     *  MC/DC → ensures independent effect of is_null($value)
     */
    public function test_set_returns_null_array_when_value_is_null()
    {
        $cast = new PaymentSyncCast();

        $result = $cast->set(null, 'sync', null, []);

        $this->assertEquals(['sync' => null], $result);
    }

    /** @test
     *  Test: set() should encode the qb_id properly
     *  Tests full statement execution in set()
     */
    public function test_set_returns_encoded_json_for_valid_object()
    {
        $cast = new PaymentSyncCast();

        $obj = new PaymentSync();
        $obj->qb_id = 'QB999';

        $result = $cast->set(null, 'sync', $obj, []);

        // result should contain JSON containing qb_id
        $this->assertArrayHasKey('sync', $result);

        $decoded = json_decode($result['sync'], true);

        $this->assertEquals('QB999', $decoded['qb_id']);
    }
}

