<?php

namespace Tests\Unit\Cast;

use App\Casts\QuoteSyncCast;
use App\DataMapper\QuoteSync;
use PHPUnit\Framework\TestCase;

class QuoteSyncCastTest extends TestCase
{
    /** @test
     *  get(): should return null when value is NULL
     *  Decision: is_null($value) → TRUE
     *  MC/DC satisfied
     */
    public function test_get_returns_null_when_value_is_null()
    {
        $cast = new QuoteSyncCast();

        $result = $cast->get(null, 'quote_sync', null, []);

        $this->assertNull($result);
    }

    /** @test
     *  get(): should return null when json_decode does NOT return an array
     *  Decision: !is_array($data) → TRUE
     *  Covers invalid JSON or non-array JSON
     */
    public function test_get_returns_null_when_decoded_value_is_not_array()
    {
        $cast = new QuoteSyncCast();

        // json_decode("123") = 123 (not array)
        $result = $cast->get(null, 'quote_sync', '123', []);

        $this->assertNull($result);
    }

    /** @test
     *  get(): should return QuoteSync object for valid JSON array
     *  Decision path: is_null → FALSE AND is_array → TRUE
     *  Full path coverage
     */
    public function test_get_returns_quotesync_object_for_valid_json()
    {
        $cast = new QuoteSyncCast();

        $json = json_encode([
            'qb_id' => 'QB12345'
        ]);

        $result = $cast->get(null, 'quote_sync', $json, []);

        $this->assertInstanceOf(QuoteSync::class, $result);
        $this->assertEquals('QB12345', $result->qb_id);
    }

    /** @test
     *  set(): should return array with null when value is null
     *  Decision: is_null($value) → TRUE
     *  MC/DC satisfied
     */
    public function test_set_returns_null_array_when_value_is_null()
    {
        $cast = new QuoteSyncCast();

        $result = $cast->set(null, 'quote_sync', null, []);

        $this->assertArrayHasKey('quote_sync', $result);
        $this->assertNull($result['quote_sync']);
    }

    /** @test
     *  set(): should return encoded JSON for valid QuoteSync object
     *  Decision: is_null($value) → FALSE
     *  Covers JSON encoding path
     */
    public function test_set_encodes_valid_quotesync_object()
    {
        $cast = new QuoteSyncCast();

        $obj = new QuoteSync(['qb_id' => 'QB999']);

        $result = $cast->set(null, 'quote_sync', $obj, []);

        $this->assertArrayHasKey('quote_sync', $result);

        $decoded = json_decode($result['quote_sync'], true);

        $this->assertEquals('QB999', $decoded['qb_id']);
    }
}

