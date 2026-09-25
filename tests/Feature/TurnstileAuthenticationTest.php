<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TurnstileSiteverifyClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class TurnstileAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_account_after_siteverify_accepts_register_token(): void
    {
        config(['services.turnstile.secret_key' => 'test-turnstile-secret']);
        $this->mock(TurnstileSiteverifyClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('post')
                ->once()
                ->withArgs(fn (string $secret, string $token, ?string $ipAddress): bool => $secret === 'test-turnstile-secret'
                    && $token === '0.eyJhbGciOiJIUzI1NiJ9.abc_def-ghi+jkl/mno=='
                    && $ipAddress !== null)
                ->andReturn([
                    'connected' => true,
                    'http_status' => 200,
                    'curl_errno' => 0,
                    'curl_error' => '',
                    'body' => ['success' => true, 'action' => 'register'],
                ]);
        });

        $response = $this->postJson('/api/register', [
            'name' => 'Casey Neighbor',
            'email' => 'casey@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'cf-turnstile-response' => '0.eyJhbGciOiJIUzI1NiJ9.abc_def-ghi+jkl/mno==',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Request completed successfully.');

        $user = User::query()->where('email', 'casey@example.test')->firstOrFail();
        $this->assertModelExists($user);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $user->id,
            'action' => 'user.registered',
        ]);
    }

    public function test_registration_returns_422_and_does_not_create_an_account_for_a_token_with_the_wrong_action(): void
    {
        config(['services.turnstile.secret_key' => 'test-turnstile-secret']);
        $this->mock(TurnstileSiteverifyClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('post')
                ->once()
                ->andReturn([
                    'connected' => true,
                    'http_status' => 200,
                    'curl_errno' => 0,
                    'curl_error' => '',
                    'body' => ['success' => true, 'action' => 'login'],
                ]);
        });

        $response = $this->postJson('/api/register', [
            'name' => 'Casey Neighbor',
            'email' => 'casey@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'cf-turnstile-response' => 'valid-login-token',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cf-turnstile-response'])
            ->assertJsonPath('error', true);
        $this->assertDatabaseMissing('users', ['email' => 'casey@example.test']);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_login_returns_a_token_after_siteverify_accepts_login_token(): void
    {
        $user = User::factory()->create(['password' => 'secure-password']);
        config(['services.turnstile.secret_key' => 'test-turnstile-secret']);
        $this->mock(TurnstileSiteverifyClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('post')
                ->once()
                ->andReturn([
                    'connected' => true,
                    'http_status' => 200,
                    'curl_errno' => 0,
                    'curl_error' => '',
                    'body' => ['success' => true, 'action' => 'login'],
                ]);
        });

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secure-password',
            'cf-turnstile-response' => 'valid-login-token',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email']])
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Request completed successfully.');
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_unknown_api_routes_return_consistent_error_metadata(): void
    {
        $response = $this->getJson('/api/route-that-does-not-exist');

        $response->assertNotFound()
            ->assertJsonPath('error', true)
            ->assertJsonPath('message', 'The route api/route-that-does-not-exist could not be found.');
    }
}
