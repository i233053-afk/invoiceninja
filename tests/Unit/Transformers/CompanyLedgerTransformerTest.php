<?php

namespace Tests\Unit\Transformers;

use Tests\TestCase;
use App\Models\CompanyLedger;
use App\Transformers\CompanyLedgerTransformer;

class CompanyLedgerTransformerTest extends TestCase
{
    public function testCompanyLedgerTransformer()
    {
        // Prepare a fake ledger model
        $ledger = new CompanyLedger();

        // Example polymorphic relation: Client / Invoice / Payment etc.
        $ledger->company_ledgerable_type = 'Client'; // class_basename → 'Client' → lcfirst → 'client_id'
        $ledger->company_ledgerable_id = 55;

        $ledger->notes = 'Adjustment Note';
        $ledger->balance = 1200.50;
        $ledger->adjustment = -200.75;
        $ledger->activity_id = 10;

        $ledger->created_at = now()->timestamp;
        $ledger->updated_at = now()->timestamp;
        $ledger->deleted_at = null;

        $transformer = new CompanyLedgerTransformer();
        $transformed = $transformer->transform($ledger);

        // Dynamic key based on polymorphic type
        $expected_key = 'client_id';

        $this->assertArrayHasKey($expected_key, $transformed);
        $this->assertEquals(
            $transformer->encodePrimaryKey(55),
            $transformed[$expected_key]
        );

        // Regular fields
        $this->assertEquals('Adjustment Note', $transformed['notes']);
        $this->assertEquals(1200.50, $transformed['balance']);
        $this->assertEquals(-200.75, $transformed['adjustment']);
        $this->assertEquals(10, $transformed['activity_id']);

        // Timestamp fields
        $this->assertIsInt($transformed['created_at']);
        $this->assertIsInt($transformed['updated_at']);
        $this->assertEquals(0, $transformed['archived_at']);
    }
}

