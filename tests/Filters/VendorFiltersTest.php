<?php

namespace Tests\Filters;

use Tests\TestCase;
use Mockery;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Filters\VendorFilters;
use ReflectionClass;

class VendorFiltersTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeBuilder()
    {
        $builder = Mockery::mock(Builder::class)->makePartial();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('orWhere')->andReturnSelf();
        $builder->shouldReceive('orWhereHas')->andReturnSelf();
        $builder->shouldReceive('orderBy')->andReturnSelf();
        $builder->shouldReceive('orderByRaw')->andReturnSelf();
        $builder->shouldReceive('company')->andReturnSelf();
        $builder->shouldReceive('getModel->getTable')->andReturn('vendors');
        return $builder;
    }

    private function setProtectedProperty($object, string $property, $value)
    {
        $ref = new ReflectionClass($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    /** @test */
    public function test_filter_with_empty_string()
    {
        $request = new Request(['filter' => '']);
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->filter('');
        $this->assertSame($builder, $result);
    }

    /** @test */
    public function test_filter_with_non_empty_string()
    {
        $request = new Request(['filter' => 'Acme']);
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->filter('Acme');
        $this->assertSame($builder, $result);
    }

    /** @test */
    public function test_number_with_empty_string()
    {
        $request = new Request(['number' => '']);
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->number('');
        $this->assertSame($builder, $result);
    }

    /** @test */
    public function test_number_with_value()
    {
        $request = new Request(['number' => 'INV-001']);
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->number('INV-001');
        $this->assertSame($builder, $result);
    }

    /** @test */
    public function test_sort_with_invalid_sort_string()
    {
        $request = new Request(['sort' => 'invalid|asc']);
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->sort('invalid|asc');
        $this->assertSame($builder, $result);
    }

    /** @test */
    public function test_sort_with_valid_sort_string_number_column()
    {
        $request = new Request(['sort' => 'number|asc']);
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->sort('number|asc');
        $this->assertSame($builder, $result);
    }

    /** @test */
    public function test_sort_with_valid_sort_string_other_column()
    {
        $request = new Request(['sort' => 'name|desc']);
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->sort('name|desc');
        $this->assertSame($builder, $result);
    }

    /** @test */
   /** @test */
public function test_filter_with_non_empty_string_full_coverage()
{
    $request = new Request(['filter' => 'Acme']);

    // Main builder mock
    $builder = Mockery::mock(Builder::class)->makePartial();

    // Closure builder inside the main where()
    $closureBuilder = Mockery::mock(Builder::class)->makePartial();
    $closureBuilder->shouldReceive('where')->andReturnSelf();
    $closureBuilder->shouldReceive('orWhere')->andReturnSelf();

    // Mock orWhereHas to actually execute the inner closure
    $closureBuilder->shouldReceive('orWhereHas')->andReturnUsing(function ($relation, $callback) {
        $innerBuilder = Mockery::mock(Builder::class)->makePartial();
        $innerBuilder->shouldReceive('where')->andReturnSelf();
        $innerBuilder->shouldReceive('orWhere')->andReturnSelf();

        // Execute the closure so coverage sees these lines
        $callback($innerBuilder);

        return $innerBuilder; // return innerBuilder to continue chaining
    });

    $closureBuilder->shouldReceive('orWhere')->andReturnSelf();

    // Main builder executes the closure passed to where()
    $builder->shouldReceive('where')->andReturnUsing(function ($callback) use ($closureBuilder) {
        $callback($closureBuilder);
        return $closureBuilder;
    });

    $filters = new VendorFilters($request);

    // Set protected builder property
    $ref = new ReflectionClass($filters);
    $prop = $ref->getProperty('builder');
    $prop->setAccessible(true);
    $prop->setValue($filters, $builder);

    $result = $filters->filter('Acme');

    $this->assertSame($closureBuilder, $result);
}

    /** @test */
    public function test_entityFilter_returns_builder()
    {
        $request = new Request();
        $builder = $this->makeBuilder();
        $filters = new VendorFilters($request);
        $this->setProtectedProperty($filters, 'builder', $builder);

        $result = $filters->entityFilter();
        $this->assertSame($builder, $result);
    }
}

