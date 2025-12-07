<?php

namespace Tests\Unit\Factory;

use Tests\TestCase;
use App\Factory\RecurringExpenseToExpenseFactory;
use App\Models\RecurringExpense;
use App\Models\Expense;
use App\Models\Client;
use App\Models\Company;
use Illuminate\Support\Carbon;

class RecurringExpenseToExpenseFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 12, 6)); // fix current date for testing
    }

    public function testCreateCopiesFieldsCorrectly()
    {
        $client = $this->createMock(Client::class);
        $client->method('locale')->willReturn('en');
        $client->method('date_format')->willReturn('Y-m-d');

        $company = $this->createMock(Company::class);
        $company->settings = (object)['date_format_id' => 1];
        $company->locale = 'en';

        $recurringExpense = new RecurringExpense();
        $recurringExpense->id = 123;
        $recurringExpense->user_id = 1;
        $recurringExpense->assigned_user_id = 2;
        $recurringExpense->client_id = 5;
        $recurringExpense->vendor_id = 3;
        $recurringExpense->invoice_id = 10;
        $recurringExpense->currency_id = 1;
        $recurringExpense->company_id = 7;
        $recurringExpense->bank_id = 8;
        $recurringExpense->exchange_rate = 1.5;
        $recurringExpense->should_be_invoiced = true;
        $recurringExpense->amount = 200.00;
        $recurringExpense->foreign_amount = null;
        $recurringExpense->public_notes = 'Test :MONTH :YEAR';
        $recurringExpense->private_notes = '';
        $recurringExpense->transaction_reference = 'REF123';
        $recurringExpense->custom_value1 = 'CV1';
        $recurringExpense->client = $client;
        $recurringExpense->company = $company;

        $expense = RecurringExpenseToExpenseFactory::create($recurringExpense);

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertEquals(123, $expense->recurring_expense_id);
        $this->assertEquals(1, $expense->user_id);
        $this->assertEquals(2, $expense->assigned_user_id);
        $this->assertEquals(5, $expense->client_id);
        $this->assertEquals(200.00, $expense->amount);
        $this->assertEquals(0, $expense->foreign_amount); // default
        $this->assertStringContainsString('December', $expense->public_notes);
        $this->assertEquals('', $expense->private_notes); // empty string
    }

    public function testTransformObjectWithNullValue()
    {
        $recurringExpense = new RecurringExpense();
        $this->assertEquals('', RecurringExpenseToExpenseFactory::transformObject(null, $recurringExpense));
    }

    public function testTransformObjectWithMonthYearPlaceholder()
    {
        $recurringExpense = new RecurringExpense();
        $recurringExpense->client = $this->createMock(Client::class);
        $recurringExpense->client->method('locale')->willReturn('en');
        $recurringExpense->client->method('date_format')->willReturn('Y-m-d');

        $result = RecurringExpenseToExpenseFactory::transformObject('Report for :MONTHYEAR', $recurringExpense);
        $this->assertStringContainsString('December 2025', $result);
    }
public function test_transform_object_with_range_placeholder()
{
    $recurringExpense = new RecurringExpense();
    
    // Mock Client
    $client = $this->createMock(Client::class);
    $client->method('locale')->willReturn('en');
    $client->method('date_format')->willReturn('Y-m-d');
    $recurringExpense->client = $client;

    // Mock Company
    $company = $this->createMock(Company::class);
    $company->settings = (object)['date_format_id' => 1];
    $company->locale = 'en';
    $recurringExpense->company = $company;

    // Mock date_formats collection for app() helper
    $dateFormatMock = new class {
        public $id = 1;
        public $format = 'Y-m-d';
    };
    app()->instance('date_formats', collect([$dateFormatMock]));

    // Example value with a range
    $value = '[MONTH|MONTH+2]';

    $transformed = RecurringExpenseToExpenseFactory::transformObject($value, $recurringExpense);

    // With Carbon::setTestNow(Carbon::create(2025, 12, 6)), MONTH=December 2025
    $this->assertStringContainsString('December 2025 to February 2026', $transformed);
}

}

