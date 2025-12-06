<?php

namespace Tests\Export;

use Tests\TestCase;
use Mockery;
use ReflectionClass;
use App\Models\Company;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Currency;
use App\Models\PurchaseOrder;
use App\Export\CSV\PurchaseOrderExport;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderExportTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function setProtected($object, string $prop, $value)
    {
        $ref = new ReflectionClass($object);
        $property = $ref->getProperty($prop);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    private function callPrivateMethod($object, string $method, array $args = [])
    {
        $ref = new ReflectionClass($object);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($object, $args);
    }

    private function makeCompany()
    {
        $company = Mockery::mock(Company::class)->makePartial();
        $company->id = 1;
        $company->db = 'testdb';
        $company->settings = (object)['translations' => []];

        $currency = new Currency();
        $currency->code = 'USD';
        $company->shouldReceive('currency')->andReturn($currency);

        return $company;
    }

    private function makePO()
{
    // Currency
    $currency = new Currency();
    $currency->code = 'USD';

    // Vendor
    $vendor = Mockery::mock(Vendor::class)->makePartial();
    $vendor->shouldReceive('currency')->andReturn($currency); // method must return Currency object
    $vendor->shouldReceive('present')->andReturn(
        Mockery::mock()->shouldReceive('name')->andReturn('Vendor Name')->getMock()
    );

    // User
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('present')->andReturn(
        Mockery::mock()->shouldReceive('name')->andReturn('UserName')->getMock()
    );

    // Assigned User
    $assigned = Mockery::mock(User::class)->makePartial();
    $assigned->shouldReceive('present')->andReturn(
        Mockery::mock()->shouldReceive('name')->andReturn('AssignedName')->getMock()
    );

    // Purchase Order
    $po = Mockery::mock(PurchaseOrder::class)->makePartial();
    $po->setRelation('vendor', $vendor);
    $po->setRelation('company', $this->makeCompany());
    $po->setRelation('user', $user);
    $po->setRelation('assigned_user', $assigned);
    $po->status_id = 2;
    $po->stringStatus = fn() => 'Sent';

    return $po;
}

    private function mockBuilder($items)
    {
        $qb = Mockery::mock(Builder::class);
        $qb->shouldReceive('withTrashed')->andReturnSelf();
        $qb->shouldReceive('with')->andReturnSelf();
        $qb->shouldReceive('whereHas')->andReturnSelf();
        $qb->shouldReceive('where')->andReturnSelf();
        $qb->shouldReceive('cursor')->andReturn(collect($items));
        return $qb;
    }

    /** @test */
    public function test_init_all_branches()
    {
        $company = $this->makeCompany();
        $input = [
            'report_keys' => [],
            'include_deleted' => false,
            'client_id' => [1],
            'status' => 'paid',
            'document_email_attachment' => true,
            'pdf_email_attachment' => true,
            'date_range' => [] // required for BaseExport
        ];

        $export = new PurchaseOrderExport($company, $input);
        $this->setProtected($export, 'purchase_order_report_keys', ['purchase_order.id']);
        $this->setProtected($export, 'forced_vendor_fields', ['purchase_order.vendor_id']);

        // prevent actual DB call
        Mockery::mock('alias:App\Libraries\MultiDB')->shouldReceive('setDb')->andReturnNull();

        $qb = $this->mockBuilder([]);

        $exportMock = Mockery::mock($export)->makePartial()->shouldAllowMockingProtectedMethods();
        $exportMock->shouldReceive('addDateRange')->andReturn($qb);
        $exportMock->shouldReceive('addClientFilter')->andReturn($qb);
        $exportMock->shouldReceive('addPurchaseOrderStatusFilter')->andReturn($qb);
        // allow zero calls to queueDocuments/queuePdfs
        $exportMock->shouldReceive('queueDocuments')->andReturnNull();
        $exportMock->shouldReceive('queuePdfs')->andReturnNull();

        $this->assertInstanceOf(Builder::class, $exportMock->init());
    }

    /** @test */
    public function test_returnJson()
    {
        $company = $this->makeCompany();
        $po = $this->makePO();

        $input = [
            'report_keys' => ['purchase_order.id'],
            'include_deleted' => false,
            'client_id' => [],
            'status' => '',
            'document_email_attachment' => false,
            'pdf_email_attachment' => false,
            'date_range' => []
        ];

        $export = new PurchaseOrderExport($company, $input);
        Mockery::mock('alias:App\Libraries\MultiDB')->shouldReceive('setDb')->andReturnNull();

        $qb = $this->mockBuilder([$po]);
        $spy = Mockery::mock($export)->makePartial();
        $spy->shouldReceive('init')->andReturn($qb);
        $spy->shouldReceive('buildHeader')->andReturn(['h1']);
        $spy->shouldReceive('processMetaData')->andReturn(['meta']);

        $result = $spy->returnJson();
        $this->assertArrayHasKey('columns', $result);
    }

    /** @test */
/** @test */
public function test_run_generates_csv()
{
    $company = $this->makeCompany();
    $po = $this->makePO();

    $input = [
        'report_keys' => [
            'purchase_order.id',
            'purchase_order.currency_id',
            'purchase_order.vendor_id',
            'purchase_order.status',
            'purchase_order.user_id',
            'purchase_order.assigned_user_id',
        ],
        'include_deleted' => false,
        'client_id' => [],
        'status' => '',
        'document_email_attachment' => false,
        'pdf_email_attachment' => false,
        'date_range' => []
    ];

    $export = new PurchaseOrderExport($company, $input);
    Mockery::mock('alias:App\Libraries\MultiDB')->shouldReceive('setDb')->andReturnNull();

    $qb = $this->mockBuilder([$po]);

    // Partial mock to override init() and headers
    $spy = Mockery::mock($export)->makePartial();
    $spy->shouldReceive('init')->andReturn($qb);
    $spy->shouldReceive('buildHeader')->andReturn([
        'purchase_order.id',
        'purchase_order.currency_id',
        'purchase_order.vendor_id',
        'purchase_order.status',
        'purchase_order.user_id',
        'purchase_order.assigned_user_id',
    ]);

    // Run the export normally without trying to mock private buildRow
    $csv = $spy->run();

    $this->assertStringContainsString('Vendor Name', $csv);
    $this->assertStringContainsString('USD', $csv);
}


    /** @test */
    public function test_buildRow_and_decorateAdvancedFields()
    {
        $company = $this->makeCompany();
        $po = $this->makePO();

        $input = [
            'report_keys' => [
                'purchase_order.id',
                'purchase_order.currency_id',
                'purchase_order.vendor_id',
                'purchase_order.status',
                'purchase_order.user_id',
                'purchase_order.assigned_user_id',
            ],
            'include_deleted' => false,
            'client_id' => [],
            'status' => '',
            'document_email_attachment' => false,
            'pdf_email_attachment' => false,
            'date_range' => []
        ];

        $export = new PurchaseOrderExport($company, $input);
        Mockery::mock('alias:App\Libraries\MultiDB')->shouldReceive('setDb')->andReturnNull();

        // Inject private transformer
        $this->setProtected($export, 'purchase_order_transformer',
            new class {
                public function transform() { return ['id' => 10]; }
            }
        );

        $row = $this->callPrivateMethod($export, 'buildRow', [$po]);

        $this->assertSame('USD', $row['purchase_order.currency_id']);
        $this->assertSame('Vendor Name', $row['purchase_order.vendor_id']);
        $this->assertSame('Sent', $row['purchase_order.status']); // matches real logic
        $this->assertSame('UserName', $row['purchase_order.user_id']);
        $this->assertSame('AssignedName', $row['purchase_order.assigned_user_id']);
    }
}

