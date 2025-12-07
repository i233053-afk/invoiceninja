<?php

namespace Tests\Unit\Elastic;

use Tests\TestCase;
use App\Elastic\Index\EntityIndex;
use Elastic\Migrations\Facades\Index;
use Mockery;

class EntityIndexTest extends TestCase
{
    /** @test */
    public function it_calls_createRaw_with_correct_parameters()
    {
        $indexName = 'test_entity_index';

        $entityIndex = new EntityIndex();

        // Mock the Facade
        Index::shouldReceive('createRaw')
            ->once()
            ->with($indexName, $entityIndex->mapping);

        // Execute the method
        $entityIndex->create($indexName);
    }
}

