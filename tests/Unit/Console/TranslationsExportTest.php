<?php

namespace Tests\Unit\Console;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Lang;

class TranslationsExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fake the storage disk to prevent actual file writes
        Storage::fake('local');

        // Fake language loader for export
        Lang::shouldReceive('getLoader')
            ->andReturnSelf()
            ->byDefault();

        Lang::shouldReceive('load')
            ->andReturn([
                'hello' => 'Hello',
                'welcome' => 'Welcome',
            ])
            ->byDefault();
    }
    
    /** @test */
public function it_runs_export_type_without_crashing()
{
    // Fake the storage disk
    Storage::fake('local');

    // Mock the language loader properly
    $loaderMock = \Mockery::mock('overload:Illuminate\Translation\FileLoader');
    $loaderMock->shouldReceive('load')
        ->withArgs(function ($locale, $group, $namespace = null) {
            return true; // accept any args
        })
        ->andReturn([
            'hello' => 'Hello',
            'welcome' => 'Welcome',
        ])
        ->byDefault();

    // Call the Artisan command
    $exitCode = Artisan::call('ninja:translations', ['--type' => 'export']);

    $this->assertTrue(in_array($exitCode, [0, null]), "Command crashed.");

    // Assert storage directories and files were created
    Storage::disk('local')->assertExists('lang/en/en.json');
}


    /** @test */
    public function it_runs_import_type_without_crashing()
    {
        $tempPath = storage_path('lang_import/');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        // Create a dummy import file
        file_put_contents($tempPath . 'textsphp_en.php', '<?php return ["hello" => "Hello"];');

        $exitCode = Artisan::call('ninja:translations', ['--type' => 'import', '--path' => $tempPath]);

        $this->assertTrue(in_array($exitCode, [0, null]), "Command crashed.");

        // Assert the imported file exists in lang_path
        $this->assertFileExists(lang_path('en/texts.php'));

        // Cleanup
        unlink($tempPath . 'textsphp_en.php');
        if (file_exists(lang_path('en/texts.php'))) {
            unlink(lang_path('en/texts.php'));
        }
    }

    /** @test */
    public function it_runs_without_type_option_and_defaults_to_export()
    {
        $exitCode = Artisan::call('ninja:translations');

        $this->assertTrue(in_array($exitCode, [0, null]), "Command crashed.");

        Storage::disk('local')->assertExists('lang/en/en.json');
    }
}

