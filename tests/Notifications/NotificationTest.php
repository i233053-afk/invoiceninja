<?php

namespace Tests\Notifications;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Company;
use Illuminate\Notifications\Messages\SlackMessage;
use App\Notifications\Ninja\UserQualityNotification;
use App\Notifications\Ninja\GenericNinjaAdminNotification;
use App\Notifications\Ninja\GmailCredentialNotification;
use App\Notifications\Ninja\NewAccountCreated;
use App\Notifications\Ninja\PayPalUnlinkedTransaction;
use App\Notifications\Ninja\RenewalFailureNotification;
use App\Notifications\Ninja\ClientAccountNotFound;
use App\Notifications\Ninja\ClientBillingFlag;
use App\Notifications\Ninja\DomainFailureNotification;
use App\Notifications\Ninja\DomainRenewalFailureNotification;
use App\Notifications\Ninja\EmailBounceNotification;
use App\Notifications\Ninja\DomainRenewedNotification;
use App\Notifications\Ninja\EmailQualityNotification;
use App\Notifications\Ninja\EmailQuotaNotification;

class NotificationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function getFromProperty($slack)
    {
        $ref = new \ReflectionClass($slack);
        if ($ref->hasProperty('from')) {
            $prop = $ref->getProperty('from');
            $prop->setAccessible(true);
            return $prop->getValue($slack);
        }
        return null;
    }

  
  /** @test */
    public function it_builds_user_quality_notification()
    {
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('present->name')->andReturn('Jane Doe');

        $accountKey = 'ACCT_123';
        $notification = new UserQualityNotification($userMock, $accountKey);

        $this->assertEquals(['slack'], $notification->via(null));

        $slack = $notification->toSlack(null);
        $this->assertStringContainsString('User Quality notification Jane Doe', $slack->content);
        $this->assertStringContainsString("Account: ACCT_123", $slack->content);
    }

    /** @test */
    public function it_builds_generic_admin_notification()
    {
        $messages = ['Line One', 'Line Two', 'Final Line'];
        $notification = new GenericNinjaAdminNotification($messages);
        $slack = $notification->toSlack(null);

        foreach ($messages as $line) {
            $this->assertStringContainsString($line, $slack->content);
        }
    }

    /** @test */
    public function it_builds_gmail_credential_notification()
    {
        $owner = new class {
            public $email = 'owner@example.com';
            public function present() {
                return new class {
                    public function name() { return 'Owner Name'; }
                };
            }
        };

        $account = new class($owner) {
            public $key = 'ACCOUNT_XYZ';
            private $owner;
            public function __construct($owner) { $this->owner = $owner; }
            public function companies() {
                $owner = $this->owner;
                return new class($owner) {
                    private $owner;
                    public function __construct($owner){ $this->owner = $owner; }
                    public function first() {
                        $owner = $this->owner;
                        return new class($owner) {
                            private $owner;
                            public function __construct($owner){ $this->owner = $owner; }
                            public function owner() { return $this->owner; }
                        };
                    }
                };
            }
        };

        $notification = new GmailCredentialNotification($account);
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString("GMail credentials invalid for Account ACCOUNT_XYZ", $slack->content);
        $this->assertStringContainsString("Owner Owner Name", $slack->content);
        $this->assertStringContainsString("owner@example.com", $slack->content);
    }

    /** @test */
    public function it_builds_new_account_created_notification()
    {
        $user = new class {
            public $first_name = 'Alice';
            public $last_name = 'Smith';
            public $email = 'alice@example.com';
            public $ip = '127.0.0.1';
            public $company_set;
            public function setCompany($company){ $this->company_set = $company; }
        };

        $company = (object) ['company_key' => 'C_KEY'];
        $notification = new NewAccountCreated($user, $company, true);

        $slack = $notification->toSlack(null);

        $this->assertSame($company, $user->company_set);
        $this->assertStringContainsString('Alice Smith', $slack->content);
        $this->assertStringContainsString('alice@example.com', $slack->content);
        $this->assertStringContainsString('127.0.0.1', $slack->content);
    }

    /** @test */
    public function it_builds_paypal_unlinked_transaction_notification()
    {
        $notification = new PayPalUnlinkedTransaction('ORDER-123', 'TX-ABC-987');
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString('PayPal Order Not Found', $slack->content);
        $this->assertStringContainsString('ORDER-123', $slack->content);
        $this->assertStringContainsString('TX-ABC-987', $slack->content);
    }

    /** @test */
    public function it_builds_renewal_failure_notification()
    {
        $notification = new RenewalFailureNotification('Some Error Message');
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString('Plan paid, account not updated', $slack->content);
        $this->assertStringContainsString('Some Error Message', $slack->content);
    }

    /** @test */
    public function it_builds_client_account_not_found_notification()
    {
        $notification = new ClientAccountNotFound('ACC123', 'john@example.com');
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString('Client not found', $slack->content);
        $this->assertStringContainsString('ACC123', $slack->content);
        $this->assertStringContainsString('john@example.com', $slack->content);
    }

    /** @test */
    public function it_builds_client_billing_flag_notification()
    {
        $notification = new ClientBillingFlag('ACC555', 'Billing issue');
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString('Client has inapp purchase AND regular plan', $slack->content);
        $this->assertStringContainsString('ACC555', $slack->content);
        $this->assertStringContainsString('Billing issue', $slack->content);
    }

    /** @test */
    public function it_builds_domain_failure_notification()
    {
        $notification = new DomainFailureNotification('example.com');
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString('Domain Certificate failure:', $slack->content);
        $this->assertStringContainsString('example.com', $slack->content);
    }

    /** @test */
    public function it_builds_domain_renewal_failure_notification()
    {
        $notification = new DomainRenewalFailureNotification('renewal-example.com');
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString('Domain Certificate _renewal_ failure:', $slack->content);
        $this->assertStringContainsString('renewal-example.com', $slack->content);
    }

    /** @test */
    public function it_builds_email_bounce_notification()
    {
        $notification = new EmailBounceNotification('test@example.com');
        $slack = $notification->toSlack(null);

        $expected = "Email bounce notification for test@example.com \n";

        $this->assertEquals($expected, $slack->content);
        $this->assertEquals('success', $slack->level);
    }

   
    /** @test */
    public function it_builds_email_quota_notification()
    {
        $account = Mockery::mock();
        $account->key = 'ACC-01';

        $company = Mockery::mock();
        $owner = Mockery::mock();
        $owner->email = 'owner@example.com';

        $presenter = Mockery::mock();
        $presenter->shouldReceive('name')->andReturn('Account Owner');

        $owner->shouldReceive('present')->andReturn($presenter);
        $company->shouldReceive('owner')->andReturn($owner);

        $companiesRelation = Mockery::mock();
        $companiesRelation->shouldReceive('first')->andReturn($company);

        $account->shouldReceive('companies')->andReturn($companiesRelation);

        $notification = new EmailQuotaNotification($account);
        $slack = $notification->toSlack(null);

        $this->assertStringContainsString('ACC-01', $slack->content);
        $this->assertStringContainsString('Account Owner', $slack->content);
        $this->assertStringContainsString('owner@example.com', $slack->content);
    }
}

