<?php

namespace Tests\Livewire;

use Tests\TestCase;
use Mockery;
use App\Livewire\BillingPortalPurchase;
use App\Models\Subscription;
use App\Models\ClientContact;
use Illuminate\Support\Facades\Auth;
use App\Jobs\Mail\NinjaMailerJob;
use App\Libraries\MultiDB;

class BillingPortalPurchaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createComponent(array $overrides = [])
    {
        // Mock subscription service
        $subscriptionService = Mockery::mock();
        $subscriptionService->shouldReceive('isEligible')->andReturn(['status_code'=>200,'message'=>'Success']);
        $subscriptionService->shouldReceive('createInvoice')->andReturnSelf();
        $subscriptionService->shouldReceive('markSent')->andReturnSelf();
        $subscriptionService->shouldReceive('fillDefaults')->andReturnSelf();
        $subscriptionService->shouldReceive('adjustInventory')->andReturnSelf();
        $subscriptionService->shouldReceive('save')->andReturnSelf();
        $subscriptionService->shouldReceive('products')->andReturn(collect([(object)['product_key'=>'peppol_500']]));
        $subscriptionService->shouldReceive('recurring_products')->andReturn(collect([(object)['product_key'=>'whitelabel']]));
        $subscriptionService->shouldReceive('startTrial')->andReturn('trial_started');
        $subscriptionService->shouldReceive('handleNoPaymentRequired')->andReturn('no_payment');

        // Partial mock of subscription
        $subscription = Mockery::mock(Subscription::class)->makePartial();
        $subscription->price = 100;
        $subscription->promo_price = 80;
        $subscription->promo_discount = 20;
        $subscription->trial_enabled = false;
        $subscription->company_id = 1;
        $subscription->service = $subscriptionService;
        $subscription->hashed_id = 'subhash';
        $subscription->company = (object)['id'=>1, 'settings'=>[]];

        // Component
        $component = new BillingPortalPurchase();
        $component->subscription = $subscription;
        $component->db = 'test'; // must be a string to satisfy MultiDB
        $component->request_data = $overrides['request_data'] ?? [];

        // Mock MultiDB to avoid actual DB calls
        $multiDBMock = Mockery::mock('alias:' . MultiDB::class);
        $multiDBMock->shouldReceive('setDB')->withAnyArgs()->andReturnNull();

        return $component;
    }

    /** @test */
    public function mount_sets_price_and_quantity()
    {
        $component = $this->createComponent();
        $component->mount();
        $this->assertEquals(1, $component->quantity);
        $this->assertEquals(100, $component->price);
    }

    /** @test */
    public function authenticate_handles_new_and_existing_user_branches()
    {
        $component = $this->createComponent();

        // Mock ClientContact alias for static calls
        $contact = Mockery::mock(ClientContact::class)->makePartial();
        $contact->company_id = 1;
        $contact->shouldReceive('pushQuietly')->andReturnNull();

        $contactAlias = Mockery::mock('alias:App\Models\ClientContact');
        $contactAlias->shouldReceive('where->first')->andReturn($contact);

        // Test existing_user = false
        $component->steps['existing_user'] = false;
        $component->email = 'test@test.com';
        $component->password = 'password';
        $component->authenticate();
        $this->assertTrue($component->steps['existing_user']);

        // Test existing_user = true (failed login)
        Auth::shouldReceive('guard->attempt')->andReturn(false);
        $component->steps['existing_user'] = true;
        $component->authenticate();

        // Test existing_user = true (successful login)
        Auth::shouldReceive('guard->attempt')->andReturn(true);
        $component->authenticate();
    }

    /** @test */
    public function handleRff_validates_and_sets_payment_methods()
    {
        $component = $this->createComponent();

        $contact = Mockery::mock(ClientContact::class)->makePartial();
        $contact->client = Mockery::mock();
        $contact->client->shouldReceive('service->getPaymentMethods')->andReturn([]);
        $contact->shouldReceive('pushQuietly')->once();

        $component->contact = $contact;
        $component->contact_first_name = 'John';
        $component->contact_last_name = 'Doe';
        $component->contact_email = 'john@test.com';
        $component->client_city = 'NY';
        $component->client_postal_code = '10001';

        $component->handleRff();
        $this->assertTrue($component->steps['fetched_payment_methods']);
    }

    /** @test */
    public function updateQuantity_branches()
    {
        $component = $this->createComponent();
        $component->subscription->max_seats_limit = 5;
        $component->quantity = 1;
        $component->price = 100;

        $component->updateQuantity('decrement');
        $this->assertEquals(1, $component->quantity);

        $component->updateQuantity('increment');
        $this->assertEquals(2, $component->quantity);

        $component->quantity = 6;
        $component->updateQuantity('increment');
        $this->assertEquals(6, $component->quantity);

        $component->quantity = 3;
        $component->updateQuantity('decrement');
        $this->assertEquals(2, $component->quantity);
    }

    /** @test */
    public function handleCoupon_applies_discount()
    {
        $component = $this->createComponent();
        $component->coupon = 'PROMO';
        $component->steps['discount_applied'] = false;
        $component->handleCoupon();

        $this->assertTrue($component->steps['discount_applied']);
        $this->assertEquals(80, $component->price);
    }

    /** @test */
    public function passwordlessLogin_dispatches_job()
    {
        $component = $this->createComponent();

        $contact = Mockery::mock(ClientContact::class)->makePartial();
        $contact->id = 1;
        $contact->email = 'john@test.com';

        $contactAlias = Mockery::mock('alias:App\Models\ClientContact');
        $contactAlias->shouldReceive('where->first')->andReturn($contact);

        NinjaMailerJob::shouldReceive('dispatch')->once();

        $component->subscription->company = (object)['settings'=>[]];
        $component->email = 'john@test.com';
        $component->coupon = null;

        $component->passwordlessLogin();

        $this->assertFalse($component->passwordless_login_btn);
        $this->assertTrue($component->steps['passwordless_login_sent']);
    }
}

