<?php

namespace Tests\Unit\Cast;

use Tests\TestCase;
use App\Casts\InvoiceSyncCast;
use App\DataMapper\InvoiceSync;

class InvoiceSyncCastTest extends TestCase
{
    /** @test */
    public function it_returns_null_when_value_is_null()
    {
        $cast = new InvoiceSyncCast();

        $result = $cast->get(null, 'invoice_sync', null, []);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_value_is_not_valid_json()
    {
        $cast = new InvoiceSyncCast();

        $result = $cast->get(null, 'invoice_sync', 'invalid-json', []);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_decoded_value_is_not_array()
    {
        $cast = new InvoiceSyncCast();

        $result = $cast->get(null, 'invoice_sync', json_encode("string"), []);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_invoice_sync_object_for_valid_json()
    {
        $cast = new InvoiceSyncCast();

        $json = json_encode([
            'qb_id' => 'QB123'
        ]);

        $result = $cast->get(null, 'invoice_sync', $json, []);

        $this->assertInstanceOf(InvoiceSync::class, $result);
        $this->assertEquals('QB123', $result->qb_id);
    }

    /** @test */
    public function it_returns_null_array_when_setting_null()
    {
        $cast = new InvoiceSyncCast();

        $result = $cast->set(null, 'invoice_sync', null, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('invoice_sync', $result);
        $this->assertNull($result['invoice_sync']);
    }

    /** @test */
    public function it_encodes_invoice_sync_object_to_json_when_setting()
    {
        $cast = new InvoiceSyncCast();

        $sync = new InvoiceSync();
        $sync->qb_id = 'QB999';

        $result = $cast->set(null, 'invoice_sync', $sync, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('invoice_sync', $result);

        $decoded = json_decode($result['invoice_sync'], true);

        $this->assertEquals('QB999', $decoded['qb_id']);
    }
}

