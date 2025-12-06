<?php

namespace Tests\http;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Product;
use App\Models\Company;
use App\Models\Account;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $repo;
    protected $account;
    protected $company;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF for tests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Disable policies using Gate::before
        \Illuminate\Support\Facades\Gate::before(fn($user, $ability) => true);

        // Create account + company
        $this->account = Account::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id]);

        // Create user
        $this->user = User::factory()->create(['account_id' => $this->account->id]);

        // Attach user to company as owner
        $this->user->companies()->attach($this->company->id, [
            'account_id' => $this->account->id,
            'is_owner' => true,
        ]);

        // Mock company-related checks directly if controller uses them
        $this->user = Mockery::mock($this->user)->makePartial();
        $this->user->shouldReceive('belongsToCompany')->andReturn(true);
        $this->user->shouldReceive('isOwner')->andReturn(true);

        // Authenticate user
        $this->actingAs($this->user);

        // Mock repository
        $this->repo = Mockery::mock(ProductRepository::class);
        $this->app->instance(ProductRepository::class, $this->repo);
    }

    /** @test */
    public function it_lists_products()
    {
        Product::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data'); // optional, check JSON data
    }

    /** @test */
    public function it_creates_product()
    {
        $response = $this->get('/api/v1/products/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_stores_product()
    {
        $payload = ['name' => 'Test Product'];

        // Mock repository save to return a product
        $this->repo->shouldReceive('save')->once()->andReturn(
            Product::factory()->make([
                'company_id' => $this->company->id,
                'user_id' => $this->user->id,
            ])
        );

        $response = $this->post('/api/v1/products', $payload);

        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}



    /** @test 
    public function it_shows_a_product()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/api/v1/products/{$product->hashed_id}");

        $response->assertStatus(200);
    }

   
    public function it_edits_a_product()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/api/v1/products/{$product->hashed_id}/edit");

        $response->assertStatus(200);
    }

    
    public function it_updates_a_product()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $this->repo->shouldReceive('save')->once()->andReturn($product);

        $response = $this->put("/api/v1/products/{$product->hashed_id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200);
    }

   
    public function it_cannot_update_deleted_product()
    {
        $product = Product::factory()->create([
            'is_deleted' => true,
            'account_id' => $this->account->id,
            'company_id' => $this->company->id,
        ]);

        $response = $this->put("/api/v1/products/{$product->hashed_id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(400);
    }

    public function it_destroys_a_product()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $this->repo->shouldReceive('delete')->once();

        $response = $this->delete("/api/v1/products/{$product->hashed_id}");

        $response->assertStatus(200);
    }

    
    public function bulk_updates_tax_id_branch()
    {
        $p1 = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $p2 = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->post('/api/v1/products/bulk', [
            'action' => 'set_tax_id',
            'tax_id' => '123',
            'ids'    => [$p1->hashed_id, $p2->hashed_id],
        ]);

        $response->assertStatus(200);
    }

    public function bulk_executes_actions_when_user_can_edit()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $this->repo->shouldReceive('archive')->once();

        $response = $this->post('/api/v1/products/bulk', [
            'action' => 'archive',
            'ids'    => [$product->hashed_id],
        ]);

        $response->assertStatus(200);
    }

    
    public function bulk_skips_action_when_user_cannot_edit()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $mockUser = Mockery::mock($this->user)->makePartial();
        $mockUser->shouldReceive('can')->andReturn(false);
        Auth::shouldReceive('user')->andReturn($mockUser);

        $this->repo->shouldNotReceive('delete');

        $response = $this->post('/api/v1/products/bulk', [
            'action' => 'delete',
            'ids'    => [$product->hashed_id],
        ]);

        $response->assertStatus(200);
    }

    
    public function upload_rejects_when_feature_disabled()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $account = Account::factory()->create(['document_upload_enabled' => false]);

        $response = $this->post("/api/v1/products/{$product->hashed_id}/upload");

        $response->assertStatus(400);
    }

   
    public function upload_saves_documents()
    {
        Storage::fake('local');

        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->create('doc1.pdf', 200);

        $response = $this->post("/api/v1/products/{$product->hashed_id}/upload", [
            'documents' => [$file],
            'is_public' => true,
        ]);

        $response->assertStatus(200);
    }

    
    public function upload_without_documents_still_passes()
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->post("/api/v1/products/{$product->hashed_id}/upload");

        $response->assertStatus(200);
    } */


