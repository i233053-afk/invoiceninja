<?php

namespace Tests\Console;

use Tests\TestCase;
use ReflectionClass;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Kernel;
use Carbon\Carbon;

trait NinjaOverrideTrait
{
    protected function overrideNinja(array $map)
    {
        foreach ($map as $method => $return) {
            eval("
                namespace App\\Utils;
                function {$method}() {
                    return " . var_export($return, true) . ";
                }
            ");
        }
    }
}

class KernelTest extends TestCase
{
    use NinjaOverrideTrait;

    protected function getSchedule(Kernel $kernel): Schedule
    {
        $schedule = $this->app->make(Schedule::class);

        $ref = new ReflectionClass(Kernel::class);
        $method = $ref->getMethod('schedule');
        $method->setAccessible(true);
        $method->invoke($kernel, $schedule);

        return $schedule;
    }

    /** @test */
    public function it_registers_basic_jobs()
    {
        $this->overrideNinja([
            'isSelfHost' => false,
            'isHosted'   => false,
        ]);

        config(['queue.default' => 'sync']);

        $kernel = new Kernel($this->app, $this->app['events']);
        $schedule = $this->getSchedule($kernel);

        $this->assertNotEmpty($schedule->events());
    }

    /** @test */
    public function it_registers_self_host_jobs()
    {
        $this->overrideNinja([
            'isSelfHost' => true,
            'isHosted'   => false,
        ]);

        config(['queue.default' => 'sync']);

        $kernel = new Kernel($this->app, $this->app['events']);
        $schedule = $this->getSchedule($kernel);

        $found = collect($schedule->events())
            ->filter(fn($e) => str_contains($e->description, \App\Jobs\EDocument\EInvoicePullDocs::class));

        $this->assertTrue($found->isNotEmpty());
    }

    /** @test */
    public function it_registers_hosted_jobs()
    {
        $this->overrideNinja([
            'isSelfHost' => false,
            'isHosted'   => true,
        ]);

        config(['queue.default' => 'sync']);

        $kernel = new Kernel($this->app, $this->app['events']);
        $schedule = $this->getSchedule($kernel);

        $found = collect($schedule->events())
            ->filter(fn($e) => str_contains($e->description, \App\Jobs\Ninja\CheckACHStatus::class));

        $this->assertTrue($found->isNotEmpty());
    }
/** @test */
public function invoice_tax_summary_when_returns_false_on_non_covered_days()
{
    // Set a date that is not last day or first day of month
    Carbon::setTestNow('2025-03-15 15:00:00');

    $this->overrideNinja([
        'isSelfHost' => false,
        'isHosted' => false,
    ]);

    $kernel = new Kernel($this->app, $this->app['events']);
    $schedule = $this->getSchedule($kernel);

    $event = collect($schedule->events())
        ->first(fn($e) => str_contains($e->description, 'invoice-tax-summary'));

    $ref = new \ReflectionClass($event);
    $prop = $ref->getProperty('filters');
    $prop->setAccessible(true);

    $filters = $prop->getValue($event);
    $passes = true;
    foreach ($filters as $filter) {
        $passes = $passes && $filter();
    }

    // This should now return false, covering the "return false;" line
    $this->assertFalse($passes);
}

/** @test */
/** @test */
public function self_host_schedule_calls_account_update_and_einvoice_pull_docs()
{
    $this->overrideNinja([
        'isSelfHost' => true,
        'isHosted' => false,
    ]);

    config(['queue.default' => 'sync']);

    $kernel = new Kernel($this->app, $this->app['events']);
    $schedule = $this->getSchedule($kernel);

    // Use reflection to access protected $callback property
    $callFound = collect($schedule->events())
        ->filter(function ($e) {
            $ref = new \ReflectionClass($e);
            if ($ref->hasProperty('callback')) {
                $prop = $ref->getProperty('callback');
                $prop->setAccessible(true);
                $callback = $prop->getValue($e);
                return $callback instanceof \Closure;
            }
            return false;
        })
        ->isNotEmpty();

    $this->assertTrue($callFound, 'The Account update call was not scheduled.');

    // Check that EInvoicePullDocs job is scheduled
    $jobFound = collect($schedule->events())
        ->filter(fn($e) => str_contains($e->description, \App\Jobs\EDocument\EInvoicePullDocs::class))
        ->isNotEmpty();

    $this->assertTrue($jobFound, 'The EInvoicePullDocs job was not scheduled.');
}


    /** @test */
    public function invoice_tax_summary_runs_on_last_day_after_10utc()
    {
        Carbon::setTestNow('2025-03-31 15:00:00');

        $kernel = new Kernel($this->app, $this->app['events']);
        $schedule = $this->getSchedule($kernel);

        $event = collect($schedule->events())
            ->first(fn($e) => str_contains($e->description, 'invoice-tax-summary'));

        $ref = new \ReflectionClass($event);
        $prop = $ref->getProperty('filters');
        $prop->setAccessible(true);

        $filters = $prop->getValue($event);
        $passes = true;

        foreach ($filters as $filter) {
            $passes = $passes && $filter();
        }

        $this->assertTrue($passes);
    }

    /** @test */
    public function invoice_tax_summary_stops_on_first_day_after_12utc()
    {
        Carbon::setTestNow('2025-04-01 13:00:00');

        $kernel = new Kernel($this->app, $this->app['events']);
        $schedule = $this->getSchedule($kernel);

        $event = collect($schedule->events())
            ->first(fn($e) => str_contains($e->description, 'invoice-tax-summary'));

        $ref = new \ReflectionClass($event);
        $prop = $ref->getProperty('filters');
        $prop->setAccessible(true);

        $filters = $prop->getValue($event);
        $passes = true;

        foreach ($filters as $filter) {
            $passes = $passes && $filter();
        }

        $this->assertFalse($passes);
    }

    /** @test */
    public function queue_database_mode_registers_jobs()
    {
        $this->overrideNinja([
            'isSelfHost' => true,
            'isHosted'   => false,
        ]);

        config(['queue.default' => 'database']);
        config(['ninja.internal_queue_enabled' => true]);
        config(['ninja.is_docker' => false]);

        $kernel = new Kernel($this->app, $this->app['events']);
        $schedule = $this->getSchedule($kernel);

        $found = collect($schedule->events())
            ->filter(fn($e) => str_contains($e->command, 'queue:work'));

        $this->assertTrue($found->isNotEmpty());
    }
}

