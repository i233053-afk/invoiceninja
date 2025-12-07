<?php

namespace Tests\Unit\Rules;

use App\Rules\Subscriptions\Steps;
use App\Services\Subscription\StepService;
use Mockery;
use PHPUnit\Framework\TestCase;

class StepsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_passes_when_no_errors()
    {
        $mock = Mockery::mock('alias:' . StepService::class);
        $mock->shouldReceive('mapToClassNames')->once()->andReturn(['StepA']);
        $mock->shouldReceive('check')->once()->andReturn([]);

        $rule = new Steps();
        $failed = false;

        $rule->validate('steps', ['StepA'], function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_fails_when_single_error_returned()
    {
        $mock = Mockery::mock('alias:' . StepService::class);
        $mock->shouldReceive('mapToClassNames')->once()->andReturn(['InvalidStep']);
        $mock->shouldReceive('check')->once()->andReturn(['Step is invalid']);

        $rule = new Steps();
        $failed = false;
        $msg = '';

        $rule->validate('steps', ['InvalidStep'], function ($message) use (&$failed, &$msg) {
            $failed = true;
            $msg = $message;
        });

        $this->assertTrue($failed);
        $this->assertEquals('Step is invalid', $msg);
    }

    public function test_fails_and_uses_first_error_only()
    {
        $mock = Mockery::mock('alias:' . StepService::class);
        $mock->shouldReceive('mapToClassNames')->once()->andReturn(['StepX']);
        $mock->shouldReceive('check')->once()->andReturn([
            'Error A',
            'Error B',
            'Error C',
        ]);

        $rule = new Steps();
        $failed = false;
        $msg = '';

        $rule->validate('steps', ['StepX'], function ($message) use (&$failed, &$msg) {
            $failed = true;
            $msg = $message;
        });

        $this->assertTrue($failed);
        $this->assertEquals('Error A', $msg);
    }

    public function test_allows_empty_array()
    {
        $mock = Mockery::mock('alias:' . StepService::class);
        $mock->shouldReceive('mapToClassNames')->once()->andReturn([]);
        $mock->shouldReceive('check')->once()->andReturn([]);

        $rule = new Steps();
        $failed = false;

        $rule->validate('steps', [], function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_allows_null_value()
    {
        $mock = Mockery::mock('alias:' . StepService::class);
        $mock->shouldReceive('mapToClassNames')->once()->andReturn(null);
        $mock->shouldReceive('check')->once()->andReturn([]);

        $rule = new Steps();
        $failed = false;

        $rule->validate('steps', null, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }
}

