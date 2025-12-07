<?php

namespace Tests\Unit\Console;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use App\Libraries\MultiDB;

class TypeCheckTest extends TestCase
{
    /** @test */
    public function it_runs_all_option_without_crashing()
    {
        // Force multi-db OFF to test the first branch
        config(['ninja.db.multi_db_enabled' => false]);

        $exitCode = Artisan::call('ninja:type-check --all=1');

        $this->assertContains($exitCode, [0, null]);
    }

    /** @test */
    public function it_runs_all_option_with_multi_db_enabled()
    {
        // Force multi-db ON to cover foreach(MultiDB::$dbs)
        config(['ninja.db.multi_db_enabled' => true]);

        // Provide fake DB list so foreach loop runs
        MultiDB::$dbs = ['db1', 'db2'];

        $exitCode = Artisan::call('ninja:type-check --all=1');

        $this->assertContains($exitCode, [0, null]);
    }

    /** @test */
    public function it_runs_client_id_option_without_crashing()
    {
        $exitCode = Artisan::call('ninja:type-check --client_id=1');

        $this->assertContains($exitCode, [0, null]);
    }

    /** @test */
    public function it_runs_company_id_option_without_crashing()
    {
        $exitCode = Artisan::call('ninja:type-check --company_id=1');

        $this->assertContains($exitCode, [0, null]);
    }
}

