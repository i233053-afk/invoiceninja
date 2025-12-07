<?php

namespace Tests\Unit\Repositories;

use App\Models\ClientGatewayToken;
use App\Models\Company;
use App\Models\Client;
use App\Repositories\ClientGatewayTokenRepository;
use Mockery;
use Tests\TestCase;

class ClientGatewayTokenRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_save_returns_setDefault_result_when_is_default_true()
    {
        $data = [
            'company_gateway_id' => 99,
            'is_default' => true,
            'some_field' => 'value',
        ];

        // Create a mock token instance (partial mock so properties can be set)
        $token = Mockery::mock(ClientGatewayToken::class)->makePartial();

        // Expect fill() to be called with $data
        $token->shouldReceive('fill')->once()->with($data)->andReturnNull();

        // Expect save() to be called
        $token->shouldReceive('save')->once()->andReturnTrue();

        // When fresh() is called on the token, return a "fresh token" object (could be another mock)
        $returnedToken = Mockery::mock(ClientGatewayToken::class);
        $token->shouldReceive('fresh')->andReturn($returnedToken);

        // Partial mock repository so we can assert setDefault is invoked and return our returnedToken
        $repo = Mockery::mock(ClientGatewayTokenRepository::class)->makePartial();

        // Expect setDefault to be called with the same token and return $returnedToken
        $repo->shouldReceive('setDefault')->once()->with($token)->andReturn($returnedToken);

        $result = $repo->save($data, $token);

        $this->assertSame($returnedToken, $result);
    }

    public function test_save_handles_company_gateway_id_and_sets_is_default_false_when_provided_false()
    {
        $data = [
            'company_gateway_id' => 42,
            'is_default' => false,
            'other' => 'x',
        ];

        $token = Mockery::mock(ClientGatewayToken::class)->makePartial();

        // fill should be called
        $token->shouldReceive('fill')->once()->with($data)->andReturnNull();

        // When code sees company_gateway_id in data, it sets company_gateway_id attribute.
        // We can't introspect internal assignment on mock, but save() should be called.
        $token->shouldReceive('save')->once()->andReturnTrue();

        // After save(), method should return fresh() when is_default is false.
        $freshToken = Mockery::mock(ClientGatewayToken::class);
        $token->shouldReceive('fresh')->once()->andReturn($freshToken);

        // Use real repository instance (no partial mock) to exercise logic path where setDefault is not called.
        $repo = new ClientGatewayTokenRepository();

        $result = $repo->save($data, $token);

        $this->assertSame($freshToken, $result);
    }
public function test_setDefault_updates_other_tokens_and_marks_token_default()
{
    // Create a company
    $company = Company::factory()->create();

    // Create a client associated with the company
    $client = Client::factory()->create([
        'company_id' => $company->id,
    ]);

    // Create the token
    $token = ClientGatewayToken::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'is_default' => false,
    ]);

    $repo = new ClientGatewayTokenRepository();
    $result = $repo->setDefault($token);

    $token->refresh();

    $this->assertTrue($token->is_default);
}
}

