<?php

namespace Tests\Unit\Cast;

use App\Casts\TransactionEventMetadataCast;
use App\DataMapper\TransactionEventMetadata;
use PHPUnit\Framework\TestCase;

class TransactionEventMetadataCastTest extends TestCase
{
    /** @test */
    public function test_get_returns_null_when_value_is_null()
    {
        $cast = new TransactionEventMetadataCast();

        $result = $cast->get(null, 'event_meta', null, []);

        $this->assertNull($result);
    }

    /** @test */
    public function test_get_returns_null_when_decoded_value_is_not_array()
    {
        $cast = new TransactionEventMetadataCast();

        $result = $cast->get(null, 'event_meta', '123', []);

        $this->assertNull($result);
    }

    /** @test */
    public function test_get_returns_transaction_event_metadata_object()
    {
        $cast = new TransactionEventMetadataCast();

        $json = json_encode([
            'source' => 'gateway',
            'event_id' => 'EVT123'
        ]);

        $result = $cast->get(null, 'event_meta', $json, []);

        $this->assertInstanceOf(TransactionEventMetadata::class, $result);

        $arrayData = $result->toArray(); // <- use toArray() instead of direct property access

        $this->assertEquals('gateway', $arrayData['source']);
        $this->assertEquals('EVT123', $arrayData['event_id']);
    }

    /** @test */
    public function test_set_returns_null_array_when_value_is_null()
    {
        $cast = new TransactionEventMetadataCast();

        $result = $cast->set(null, 'event_meta', null, []);

        $this->assertArrayHasKey('event_meta', $result);
        $this->assertNull($result['event_meta']);
    }

    /** @test */
    public function test_set_encodes_valid_transaction_event_metadata_object()
    {
        $cast = new TransactionEventMetadataCast();

        $metadata = new TransactionEventMetadata([
            'source' => 'bank',
            'event_id' => 'EVT999'
        ]);

        $result = $cast->set(null, 'event_meta', $metadata, []);

        $this->assertArrayHasKey('event_meta', $result);

        $decoded = json_decode($result['event_meta'], true);

        $this->assertEquals('bank', $decoded['source']);
        $this->assertEquals('EVT999', $decoded['event_id']);
    }
}

