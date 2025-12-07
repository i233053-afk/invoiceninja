<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\ClientSync;
use PHPUnit\Framework\TestCase;

class ClientSyncTest extends TestCase
{
    /** @test */
    public function it_sets_qb_id_from_constructor()
    {
        $data = ['qb_id' => 'QB-001'];
        $clientSync = new ClientSync($data);

        $this->assertSame('QB-001', $clientSync->qb_id);
    }

    /** @test */
    public function it_defaults_qb_id_to_empty_string_if_not_provided()
    {
        $clientSync = new ClientSync([]);

        $this->assertSame('', $clientSync->qb_id);
    }

    /** @test */
    public function from_array_creates_instance_correctly()
    {
        $data = ['qb_id' => 'QB-002'];
        $clientSync = ClientSync::fromArray($data);

        $this->assertInstanceOf(ClientSync::class, $clientSync);
        $this->assertSame('QB-002', $clientSync->qb_id);
    }

    /** @test */
    public function cast_using_returns_client_sync_cast_class()
    {
        $result = ClientSync::castUsing([]);
        $this->assertSame('App\Casts\ClientSyncCast', $result);
    }
}

