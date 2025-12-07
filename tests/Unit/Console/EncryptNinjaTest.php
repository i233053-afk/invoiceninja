<?php

namespace Tests\Unit\Console;

use App\Console\Commands\EncryptNinja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EncryptNinjaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake the storage disk
        Storage::fake('base');

        // Create fake files
$command = new EncryptNinja();
foreach ($command->getFiles() as $file)
 {
            Storage::disk('base')->put($file, "Original content of {$file}");
        }
    }

    /** @test */
    public function it_encrypts_files()
    {
        $this->artisan('ninja:crypt', ['--encrypt' => true])->run();

        $command = new EncryptNinja();

        foreach ($command->files as $file) {
            // Original file should still exist
            $this->assertTrue(Storage::disk('base')->exists($file));
            // Encrypted file should exist
            $this->assertTrue(Storage::disk('base')->exists($file . '.enc'));

            $encryptedContent = Storage::disk('base')->get($file . '.enc');
            $this->assertNotEquals("Original content of {$file}", $encryptedContent);
        }
    }

    /** @test */
    public function it_decrypts_files()
    {
        $command = new EncryptNinja();

        // First encrypt files
        $this->artisan('ninja:crypt', ['--encrypt' => true])->run();

        // Then decrypt files
        $this->artisan('ninja:crypt', ['--decrypt' => true])->run();

        foreach ($command->files as $file) {
            $this->assertTrue(Storage::disk('base')->exists($file));
            $content = Storage::disk('base')->get($file);
            $this->assertEquals("Original content of {$file}", $content);
        }
    }
}

