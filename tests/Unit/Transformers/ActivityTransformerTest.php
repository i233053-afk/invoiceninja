<?php

namespace Tests\Unit\Transformers;

use App\Models\Activity;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Credit;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\RecurringInvoice;
use App\Models\Task;
use App\Models\Backup;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorContact;
use App\Transformers\ActivityTransformer;
use Tests\TestCase;

class ActivityTransformerTest extends TestCase
{
    protected ActivityTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new ActivityTransformer();
    }

protected function assertIncludeReturnsTransformerOrNull(string $method, Activity $activity, $relatedModel)
{
    // Map include methods → correct Activity model properties
    $propertyMap = [
        'includeClient' => 'client',
        'includeVendor' => 'vendor',
        'includeContact' => 'contact', // Activity uses $activity->contact
        'includeVendorContact' => 'vendor_contact',
        'includeRecurringInvoice' => 'recurring_invoice',
        'includePurchaseOrder' => 'purchase_order',
        'includeQuote' => 'quote',
        'includeInvoice' => 'invoice',
        'includeCredit' => 'credit',
        'includePayment' => 'payment',
        'includeUser' => 'user',
        'includeExpense' => 'expense',
        'includeTask' => 'task',
        'includeHistory' => 'backup',
    ];

    $property = $propertyMap[$method];

    // Expect null first
    $this->assertNull($this->transformer->$method($activity));

    // Assign the related model
    $activity->$property = $relatedModel;

    // Now it should NOT return null
    $response = $this->transformer->$method($activity);
    $this->assertNotNull($response, "$method returned null even after assigning $property");
}

    public function testTransformReturnsAllFields()
    {
        $activity = new Activity();
	$activity->forceFill([
    'id' => 1,
    'activity_type_id' => 2,
    'client_id' => 3,
    'recurring_invoice_id' => 4,
    'recurring_expense_id' => 5,
    'purchase_order_id' => 6,
    'vendor_id' => 7,
    'vendor_contact_id' => 8,
    'company_id' => 9,
    'user_id' => 10,
    'invoice_id' => 11,
    'quote_id' => 12,
    'payment_id' => 13,
    'credit_id' => 14,
    'updated_at' => 1670000000,
    'created_at' => 1670000000,
    'expense_id' => 15,
    'is_system' => true,
    'client_contact_id' => 16,
    'task_id' => 17,
    'token_id' => 18,
    'notes' => 'Test note',
    'ip' => '127.0.0.1',
]);


        $result = $this->transformer->transform($activity);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('activity_type_id', $result);
        $this->assertArrayHasKey('client_id', $result);
        $this->assertArrayHasKey('recurring_invoice_id', $result);
        $this->assertArrayHasKey('recurring_expense_id', $result);
        $this->assertArrayHasKey('purchase_order_id', $result);
        $this->assertArrayHasKey('vendor_id', $result);
        $this->assertArrayHasKey('vendor_contact_id', $result);
        $this->assertArrayHasKey('company_id', $result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('invoice_id', $result);
        $this->assertArrayHasKey('quote_id', $result);
        $this->assertArrayHasKey('payment_id', $result);
        $this->assertArrayHasKey('credit_id', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('expense_id', $result);
        $this->assertArrayHasKey('is_system', $result);
        $this->assertArrayHasKey('contact_id', $result);
        $this->assertArrayHasKey('task_id', $result);
        $this->assertArrayHasKey('token_id', $result);
        $this->assertArrayHasKey('notes', $result);
        $this->assertArrayHasKey('ip', $result);
    }

    public function testIncludeClientReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeClient($activity));

        $activity->client = new Client();
        $response = $this->transformer->includeClient($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeVendorReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeVendor($activity));

        $activity->vendor = new Vendor();
        $response = $this->transformer->includeVendor($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeContactReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeContact($activity));

        $activity->contact = new ClientContact();
        $response = $this->transformer->includeContact($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeVendorContactReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeVendorContact($activity));

        $activity->vendor_contact = new VendorContact();
        $response = $this->transformer->includeVendorContact($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeRecurringInvoiceReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeRecurringInvoice($activity));

        $activity->recurring_invoice = new RecurringInvoice();
        $response = $this->transformer->includeRecurringInvoice($activity);
        $this->assertNotNull($response);
    }

    public function testIncludePurchaseOrderReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includePurchaseOrder($activity));

        $activity->purchase_order = new PurchaseOrder();
        $response = $this->transformer->includePurchaseOrder($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeQuoteReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeQuote($activity));

        $activity->quote = new Quote();
        $response = $this->transformer->includeQuote($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeInvoiceReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeInvoice($activity));

        $activity->invoice = new Invoice();
        $response = $this->transformer->includeInvoice($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeCreditReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeCredit($activity));

        $activity->credit = new Credit();
        $response = $this->transformer->includeCredit($activity);
        $this->assertNotNull($response);
    }

    public function testIncludePaymentReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includePayment($activity));

        $activity->payment = new Payment();
        $response = $this->transformer->includePayment($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeUserReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeUser($activity));

        $activity->user = new User();
        $response = $this->transformer->includeUser($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeExpenseReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeExpense($activity));

        $activity->expense = new Expense();
        $response = $this->transformer->includeExpense($activity);
        $this->assertNotNull($response);
    }

    public function testIncludeTaskReturnsTransformerOrNull()
    {
        $activity = new Activity();
        $this->assertNull($this->transformer->includeTask($activity));

        $activity->task = new Task();
        $response = $this->transformer->includeTask($activity);
        $this->assertNotNull($response);
    }
    
    public function testIncludeMethods()
{
    $activity = new Activity();

    $this->assertIncludeReturnsTransformerOrNull('includeClient', $activity, new Client());
    $this->assertIncludeReturnsTransformerOrNull('includeVendor', $activity, new Vendor());
    $this->assertIncludeReturnsTransformerOrNull('includeContact', $activity, new ClientContact());
    $this->assertIncludeReturnsTransformerOrNull('includeVendorContact', $activity, new VendorContact());
    $this->assertIncludeReturnsTransformerOrNull('includeRecurringInvoice', $activity, new RecurringInvoice());
    $this->assertIncludeReturnsTransformerOrNull('includePurchaseOrder', $activity, new PurchaseOrder());
    $this->assertIncludeReturnsTransformerOrNull('includeQuote', $activity, new Quote());
    $this->assertIncludeReturnsTransformerOrNull('includeInvoice', $activity, new Invoice());
    $this->assertIncludeReturnsTransformerOrNull('includeCredit', $activity, new Credit());
    $this->assertIncludeReturnsTransformerOrNull('includePayment', $activity, new Payment());
    $this->assertIncludeReturnsTransformerOrNull('includeUser', $activity, new User());
    $this->assertIncludeReturnsTransformerOrNull('includeExpense', $activity, new Expense());
    $this->assertIncludeReturnsTransformerOrNull('includeTask', $activity, new Task());

    // ✅ Cover includeHistory
    $backup = new Backup();
    $activity->backup = $backup;
    $response = $this->transformer->includeHistory($activity);
    $this->assertNotNull($response);
}

}

