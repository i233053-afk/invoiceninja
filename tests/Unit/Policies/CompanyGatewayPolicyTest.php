<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\CompanyGatewayPolicy;
use Mockery;
use Tests\TestCase;

class CompanyGatewayPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_returns_true_for_admin_user()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->once()->andReturn(true);

        $policy = new CompanyGatewayPolicy();

        $this->assertTrue($policy->create($user));
    }

    public function test_create_returns_false_for_non_admin_user()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->once()->andReturn(false);

        $policy = new CompanyGatewayPolicy();

        $this->assertFalse($policy->create($user));
    }
}

