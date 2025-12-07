<?php

namespace Tests\Imports;

use Tests\TestCase;
use App\Import\Providers\Invoicely;
use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class InvoicelyTest extends TestCase
{
    use RefreshDatabase;

    protected $account;
    protected $company;
    protected $user;
    protected $importer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required database records
        $this->account = Account::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id]);
        $this->user = User::factory()->create(['account_id' => $this->account->id]);

        // Authenticate user
        $this->actingAs($this->user);

        // Partial mock of Invoicely
        $this->importer = Mockery::mock(Invoicely::class)->makePartial();
        $this->importer->company = $this->company;
        $this->importer->shouldAllowMockingProtectedMethods();

        // Mock getCsvData for clients and invoices
        $this->importer->shouldReceive('getCsvData')
            ->with('client')
            ->andReturn([
                ['name' => 'Client One'],
                ['name' => 'Client Two'],
            ]);

        $this->importer->shouldReceive('getCsvData')
            ->with('invoice')
            ->andReturn([
                ['invoice_number' => 'INV-001'],
            ]);

        // Mock ingestion methods to return proper counts
        $this->importer->shouldReceive('ingest')
            ->andReturnUsing(fn($data, $entity) => count($data));

        $this->importer->shouldReceive('ingestInvoices')
            ->andReturnUsing(fn($data, $flag) => count($data));
    }

    /** @test */
    public function it_imports_clients_and_updates_entity_count()
    {
        $this->importer->client();

        $this->assertEquals(2, $this->importer->entity_count['clients']);
    }

    /** @test */
    public function it_imports_invoices_and_restores_company_update_products()
    {
        $initialValue = $this->company->update_products;

        $this->importer->invoice();

        $this->assertEquals(1, $this->importer->entity_count['invoices']);

        $this->company->refresh();
        $this->assertEquals($initialValue, $this->company->update_products);
    }

    /** @test */
    public function import_calls_correct_method_based_on_entity()
    {
        // Ensure client() and invoice() are called once
        $this->importer->shouldReceive('client')->once()->andReturnNull();
        $this->importer->shouldReceive('invoice')->once()->andReturnNull();

        $this->importer->import('client');
        $this->importer->import('invoice');

        $this->assertTrue(true); // avoids risky test
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

