<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\QuickbooksSyncMap;
use App\Enum\SyncDirection;
use PHPUnit\Framework\TestCase;

class QuickbooksSyncMapTest extends TestCase
{
    /** @test */
    public function it_initializes_with_default_values_when_no_attributes_passed()
    {
        $syncMap = new QuickbooksSyncMap();

        $this->assertInstanceOf(QuickbooksSyncMap::class, $syncMap);
        $this->assertEquals(SyncDirection::BIDIRECTIONAL, $syncMap->direction);
    }

/** @test */
public function it_sets_direction_from_array()
{
    // Replace TO_APP with any valid value from your SyncDirection enum
    $syncMap = new QuickbooksSyncMap([
        'direction' => SyncDirection::BIDIRECTIONAL->value
    ]);

    $this->assertEquals(SyncDirection::BIDIRECTIONAL, $syncMap->direction);
}


    /** @test */
    public function it_falls_back_to_bidirectional_for_invalid_direction()
    {
        $syncMap = new QuickbooksSyncMap([
            'direction' => 'invalid_direction'
        ]);

        $this->assertEquals(SyncDirection::BIDIRECTIONAL, $syncMap->direction);
    }
}

