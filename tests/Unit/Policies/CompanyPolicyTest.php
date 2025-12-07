<?php

namespace Tests\Unit\Policies;

use App\Models\Company;
use App\Models\User;
use App\Policies\CompanyPolicy;
use Mockery;
use Tests\TestCase;

class CompanyPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function create_returns_true_if_user_is_admin_or_has_permission()
    {
        $policy = new CompanyPolicy();

        // Admin user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->once()->andReturn(true);
        $this->assertTrue($policy->create($user));

        // User with 'create_company' permission
        $user2 = Mockery::mock(User::class);
        $user2->shouldReceive('isAdmin')->once()->andReturn(false);
        $user2->shouldReceive('hasPermission')->once()->with('create_company')->andReturn(true);
        $this->assertTrue($policy->create($user2));

        // User with 'create_all' permission
        $user3 = Mockery::mock(User::class);
        $user3->shouldReceive('isAdmin')->once()->andReturn(false);
        $user3->shouldReceive('hasPermission')->with('create_company')->andReturn(false);
        $user3->shouldReceive('hasPermission')->with('create_all')->andReturn(true);
        $this->assertTrue($policy->create($user3));

        // User with no permissions
        $user4 = Mockery::mock(User::class);
        $user4->shouldReceive('isAdmin')->once()->andReturn(false);
        $user4->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $this->assertFalse($policy->create($user4));
    }

    /** @test */
    public function view_returns_true_for_admin_owner_or_company_match()
    {
        $policy = new CompanyPolicy();

        // Use real Company instance
        $company = new Company();
        $company->id = 1;

        // Admin + company match
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->andReturn(true);
        $user->shouldReceive('companyId')->andReturn(1);
        $user->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $user->shouldReceive('owns')->withAnyArgs()->andReturn(false);
        $this->assertTrue($policy->view($user, $company));

        // owns() returns true
        $user2 = Mockery::mock(User::class);
        $user2->shouldReceive('isAdmin')->andReturn(false);
        $user2->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $user2->shouldReceive('owns')->once()->with($company)->andReturn(true);
        $user2->shouldReceive('companyId')->andReturn(2);
        $this->assertTrue($policy->view($user2, $company));

        // companyId() matches entity
        $user3 = Mockery::mock(User::class);
        $user3->shouldReceive('isAdmin')->andReturn(false);
        $user3->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $user3->shouldReceive('owns')->withAnyArgs()->andReturn(false);
        $user3->shouldReceive('companyId')->andReturn(1);
        $this->assertTrue($policy->view($user3, $company));

        // No match
        $user4 = Mockery::mock(User::class);
        $user4->shouldReceive('isAdmin')->andReturn(false);
        $user4->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $user4->shouldReceive('owns')->withAnyArgs()->andReturn(false);
        $user4->shouldReceive('companyId')->andReturn(2);
        $this->assertFalse($policy->view($user4, $company));
    }

    /** @test */
    public function edit_returns_true_for_admin_owner_or_permission()
    {
        $policy = new CompanyPolicy();

        // Use real Company instance
        $company = new Company();
        $company->id = 1;

        // Admin + company match
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->andReturn(true);
        $user->shouldReceive('companyId')->andReturn(1);
        $user->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $user->shouldReceive('owns')->withAnyArgs()->andReturn(false);
        $this->assertTrue($policy->edit($user, $company));

        // hasPermission('edit_company') + company match
        $user2 = Mockery::mock(User::class);
        $user2->shouldReceive('isAdmin')->andReturn(false);
        $user2->shouldReceive('hasPermission')->once()->with('edit_company')->andReturn(true);
        $user2->shouldReceive('companyId')->andReturn(1);
        $user2->shouldReceive('owns')->withAnyArgs()->andReturn(false);
        $this->assertTrue($policy->edit($user2, $company));

        // owns() returns true
        $user3 = Mockery::mock(User::class);
        $user3->shouldReceive('isAdmin')->andReturn(false);
        $user3->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $user3->shouldReceive('owns')->once()->with($company)->andReturn(true);
        $user3->shouldReceive('companyId')->andReturn(2);
        $this->assertTrue($policy->edit($user3, $company));

        // No match
        $user4 = Mockery::mock(User::class);
        $user4->shouldReceive('isAdmin')->andReturn(false);
        $user4->shouldReceive('hasPermission')->withAnyArgs()->andReturn(false);
        $user4->shouldReceive('owns')->withAnyArgs()->andReturn(false);
        $user4->shouldReceive('companyId')->andReturn(2);
        $this->assertFalse($policy->edit($user4, $company));
    }
}

