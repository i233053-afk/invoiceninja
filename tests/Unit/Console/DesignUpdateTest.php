<?php

namespace Tests\Unit\Console;

use App\Console\Commands\DesignUpdate;
use App\Libraries\MultiDB;
use App\Models\Design;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use stdClass;

class DesignUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure multi_db_enabled is false by default
        Config::set('ninja.db.multi_db_enabled', false);
    }

    /** @test */
    public function it_updates_designs_in_single_db_mode()
    {
    
    $design = Design::factory()->create([
        'name' => 'default',
        'is_custom' => false,
        'design' => null,
        'template_html' => '<html><head></head><body></body></html>', // <- ADD THIS
    ]);

        // Run the command
        $command = new DesignUpdate();
        $command->handle();

        $design->refresh();

        $this->assertInstanceOf(stdClass::class, $design->design);
        $this->assertNotEmpty($design->design->includes);
        $this->assertNotEmpty($design->design->header);
        $this->assertNotEmpty($design->design->body);
        $this->assertNotEmpty($design->design->footer);
        $this->assertEquals('', $design->design->product);
        $this->assertEquals('', $design->design->task);
    }

    /** @test */
    public function it_updates_designs_in_multi_db_mode()
    {
        Config::set('ninja.db.multi_db_enabled', true);

        // Add fake databases
        MultiDB::$dbs = ['db1', 'db2'];

        $design = Design::factory()->create([
            'name' => 'default',
            'is_custom' => false,
            'design' => null,
        ]);

        $command = new DesignUpdate();
        $command->handle();

        $design->refresh();

        $this->assertInstanceOf(stdClass::class, $design->design);
        $this->assertNotEmpty($design->design->includes);
        $this->assertNotEmpty($design->design->header);
        $this->assertNotEmpty($design->design->body);
        $this->assertNotEmpty($design->design->footer);
    }

    /** @test */
    public function it_skips_custom_designs()
    {
        $customDesign = Design::factory()->create([
            'name' => 'custom',
            'is_custom' => true,
            'design' => null,
        ]);

        $command = new DesignUpdate();
        $command->handle();

        $customDesign->refresh();

        // Custom designs should remain null
        $this->assertNull($customDesign->design);
    }
}

