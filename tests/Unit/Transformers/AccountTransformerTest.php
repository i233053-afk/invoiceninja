<?php

namespace Tests\Unit\Transformers;

use App\Models\Account;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Transformers\AccountTransformer;
use Tests\TestCase;
use Mockery;

class AccountTransformerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_transforms_account_correctly()
    {
        $account = Mockery::mock(Account::class)->makePartial();

        // Mock properties and methods used in transform
        $account->id = 1;
        $account->key = 'account_key';
        $account->plan_term = 'monthly';
        $account->plan_started = now();
        $account->plan_paid = now();
        $account->plan_expires = now();
        $account->user_agent = 'phpunit';
        $account->payment_id = 2;
        $account->trial_started = now();
        $account->trial_plan = 'trial_plan';
        $account->plan_price = 100.0;
        $account->num_users = 5;
        $account->utm_source = 'utm_source';
        $account->utm_medium = 'utm_medium';
        $account->utm_content = 'utm_content';
        $account->utm_term = 'utm_term';
        $account->referral_code = 'ref123';
        $account->latest_version = '1.0.0';
        $account->updated_at = time();
        $account->deleted_at = null;
        $account->report_errors = true;
        $account->is_scheduler_running = true;
        $account->default_company_id = 1;
        $account->set_react_as_default_ap = true;
        $account->account_sms_verified = true;
        $account->inapp_transaction_id = 'iap123';
        $account->e_invoice_quota = 10;
        $account->docuninja_num_users = 2;

        // Mock methods
        $account->shouldReceive('getPlan')->andReturn('pro');
        $account->shouldReceive('emailsSent')->andReturn(3);
        $account->shouldReceive('getDailyEmailLimit')->andReturn(50);
        $account->shouldReceive('is_migrated')->andReturn(false);
        $account->shouldReceive('canTrial')->andReturn(true);
        $account->shouldReceive('getTrialDays')->andReturn(7);

        $transformer = new AccountTransformer();

        $result = $transformer->transform($account);

        $this->assertIsArray($result);
        $this->assertEquals('pro', $result['plan']);
        $this->assertEquals(3, $result['emails_sent']);
        $this->assertEquals(50, $result['email_quota']);
        $this->assertEquals(true, $result['can_trial']);
    }

    /** @test */
    public function it_includes_company_users()
    {
        $account = Mockery::mock(Account::class)->makePartial();
        $companyUser = Mockery::mock(CompanyUser::class);

        $account->company_users = collect([$companyUser]);

        $transformer = new AccountTransformer();

        $result = $transformer->includeCompanyUsers($account);

        $this->assertNotNull($result);
    }

    /** @test */
    public function it_includes_default_company()
    {
        $account = Mockery::mock(Account::class)->makePartial();
        $company = Mockery::mock(Company::class);

        $account->default_company = $company;

        $transformer = new AccountTransformer();

        $result = $transformer->includeDefaultCompany($account);

        $this->assertNotNull($result);
    }

    /** @test */
    public function it_includes_user()
    {
        $account = Mockery::mock(Account::class)->makePartial();
        $user = Mockery::mock(User::class);

        // Mock the auth helper
        auth()->shouldReceive('user')->andReturn($user);

        $transformer = new AccountTransformer();

        $result = $transformer->includeUser($account);

        $this->assertNotNull($result);
    }
}

