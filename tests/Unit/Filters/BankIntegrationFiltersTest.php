<?php

namespace Tests\Unit\Filters;
use Illuminate\Http\Request;
use App\Filters\BankIntegrationFilters;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class BankIntegrationFiltersTest extends TestCase
{
    private Builder|MockObject $builder;
    private BankIntegrationFilters $filters;

protected function setUp(): void
{
    parent::setUp();

    // Mock the Builder
    $this->builder = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['where', 'orWhere', 'getModel'])
        ->addMethods(['orderBy', 'company'])
        ->getMock();

    // Make chainable methods return $this->builder
    $this->builder->method('where')->willReturnSelf();
    $this->builder->method('orWhere')->willReturnSelf();
    $this->builder->method('orderBy')->willReturnSelf();
    $this->builder->method('company')->willReturnSelf();

    // Mock getModel to satisfy sort()
    $this->builder->method('getModel')
        ->willReturn((object)['getTable' => fn() => 'bank_integrations']);

    // Create a dummy Request
    $request = new Request();

    // Instantiate the filters
    $this->filters = new BankIntegrationFilters($request, $this->builder);
}

    public function test_name_returns_builder_if_empty(): void
    {
        $this->assertSame($this->builder, $this->filters->name(''));
    }

    public function test_name_calls_where_with_name(): void
    {
        $this->builder->expects($this->once())
            ->method('where')
            ->with('bank_account_name', 'like', '%TestName%')
            ->willReturnSelf();

        $this->filters->name('TestName');
    }

    public function test_filter_returns_builder_if_empty(): void
    {
        $this->assertSame($this->builder, $this->filters->filter(''));
    }

    public function test_status_calls_where_with_filters(): void
    {
        $this->builder->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $this->filters->status('active,archived');
    }

    public function test_sort_returns_builder_if_invalid_column(): void
    {
        $this->builder->method('getModel')->willReturn((object)['getTable' => fn() => 'bank_integrations']);
        $this->assertSame($this->builder, $this->filters->sort('invalid|asc'));
    }

    public function test_entity_filter_calls_company(): void
    {
        $this->builder->expects($this->once())
            ->method('company')
            ->willReturnSelf();

        $this->filters->entityFilter();
    }
}
