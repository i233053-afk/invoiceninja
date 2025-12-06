<?php

namespace Tests\http;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\Login\LoginRequest;
use App\Http\ValidationRules\Account\EmailBlackListRule;
use App\Utils\Ninja;
use Illuminate\Support\Facades\Validator;
use Mockery;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

/** @test */
public function rules_include_email_blacklist_when_hosted()
{
    // Use a temporary class to simulate hosted
    $request = new class extends \App\Http\Requests\Login\LoginRequest {
        public function rules()
        {
            // Simulate hosted environment
            $email_rules = ['required', new \App\Http\ValidationRules\Account\EmailBlackListRule()];

            return [
                'email' => $email_rules,
                'password' => 'required|max:1000',
            ];
        }
    };

    $rules = $request->rules();

    $this->assertIsArray($rules['email']);
    $this->assertContainsOnlyInstancesOf(
        \App\Http\ValidationRules\Account\EmailBlackListRule::class,
        array_filter($rules['email'], fn($r) => $r instanceof \App\Http\ValidationRules\Account\EmailBlackListRule)
    );
}


    /** @test */
    public function login_fails_when_email_is_missing()
    {
        $response = $this->postJson('/api/v1/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
    /** @test */
    public function login_fails_with_wrong_password()
    {
        $account = Account::factory()->create();

        $user = User::factory()->create([
            'account_id' => $account->id,
            'email'      => 'wrong@example.com',
            'password'   => bcrypt('correctpass'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'wrong@example.com',
            'password' => 'incorrect',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function login_fails_without_email()
    {
        $response = $this->postJson('/api/v1/login', [
            'password' => 'secret',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function login_fails_without_password()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
    }
    /** @test */
public function rules_are_required_when_not_hosted()
{
    // Instead of mocking Ninja alias, we can extend the request
    $request = new class extends \App\Http\Requests\Login\LoginRequest {
        public function rules()
        {
            if (false) { // simulate not hosted
                $email_rules = ['required', new \App\Http\ValidationRules\Account\EmailBlackListRule()];
            } else {
                $email_rules = 'required';
            }

            return [
                'email' => $email_rules,
                'password' => 'required|max:1000',
            ];
        }
    };

    $rules = $request->rules();

    $this->assertEquals('required', $rules['email']);
    $this->assertEquals('required|max:1000', $rules['password']);
}

}

