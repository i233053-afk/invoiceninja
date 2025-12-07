<?php

namespace Tests\Unit\Cast;

use Tests\TestCase;
use App\Casts\ClientSyncCast;
use App\DataMapper\ClientSync;

class ClientSyncCastTest extends TestCase
{
    /** @test */
    public function it_returns_null_when_get_value_is_null()
    {
        $cast = new ClientSyncCast();

        $result = $cast->get(null, 'client_sync', null, []);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_get_value_is_invalid_json()
    {
        $cast = new ClientSyncCast();

        $result = $cast->get(null, 'client_sync', 'not valid json', []);

        $this->assertNull($result);
    }

    /** @test */
    public function it_decodes_valid_json_into_client_sync_object()
    {
        $cast = new ClientSyncCast();

        $json = json_encode(['qb_id' => 'QB123']);

        $result = $cast->get(null, 'client_sync', $json, []);

        $this->assertInstanceOf(ClientSync::class, $result);
        $this->assertEquals('QB123', $result->qb_id);
    }

    /** @test */
    public function it_returns_null_array_when_set_value_is_null()
    {
        $cast = new ClientSyncCast();

        $result = $cast->set(null, 'client_sync', null, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('client_sync', $result);
        $this->assertNull($result['client_sync']);
    }

    /** @test */
    public function it_encodes_client_sync_object_to_json()
    {
        $cast = new ClientSyncCast();

        $obj = new ClientSync();
        $obj->qb_id = 'QB999';

        $result = $cast->set(null, 'client_sync', $obj, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('client_sync', $result);

        $decoded = json_decode($result['client_sync'], true);

        $this->assertEquals('QB999', $decoded['qb_id']);
    }
}

