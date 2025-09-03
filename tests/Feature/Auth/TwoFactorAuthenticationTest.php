<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Features;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Only run these tests if 2FA is enabled
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }
    }

    public function test_two_factor_settings_page_can_be_accessed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/two-factor');

        $response->assertStatus(200);
    }

    public function test_two_factor_authentication_can_be_enabled()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/user/two-factor-authentication');

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_two_factor_authentication_can_be_confirmed()
    {
        $user = User::factory()->create();

        // Enable 2FA
        $this->actingAs($user)
            ->post('/user/two-factor-authentication');

        $user->refresh();

        // Get the secret and generate a valid code
        $secret = decrypt($user->two_factor_secret);
        $google2fa = app('pragmarx.google2fa');
        $validCode = $google2fa->getCurrentOtp($secret);

        // Confirm 2FA with valid code
        $this->actingAs($user)
            ->post('/user/confirmed-two-factor-authentication', [
                'code' => $validCode,
            ]);

        $user->refresh();

        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_two_factor_authentication_can_be_disabled()
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        ]);

        $this->actingAs($user)
            ->delete('/user/two-factor-authentication');

        $user->refresh();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_recovery_codes);
    }

    public function test_recovery_codes_can_be_regenerated()
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['old-recovery-code'])),
        ]);

        $originalRecoveryCodes = $user->two_factor_recovery_codes;

        $this->actingAs($user)
            ->post('/user/two-factor-recovery-codes');

        $user->refresh();

        $this->assertNotEquals($originalRecoveryCodes, $user->two_factor_recovery_codes);
    }

    public function test_user_with_two_factor_enabled_is_redirected_to_challenge_on_login()
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/two-factor-challenge');
        $this->assertEquals($user->id, Session::get('login.id'));
    }

    public function test_user_can_login_with_two_factor_code()
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        ]);

        // Set up session as if user just entered credentials
        Session::put([
            'login.id' => $user->id,
            'login.remember' => false,
        ]);

        // Generate valid 2FA code
        $secret = decrypt($user->two_factor_secret);
        $google2fa = app('pragmarx.google2fa');
        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->post('/two-factor-challenge', [
            'code' => $validCode,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_recovery_code()
    {
        $recoveryCode = 'recovery-code-123';
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode([$recoveryCode, 'another-code'])),
        ]);

        // Set up session as if user just entered credentials
        Session::put([
            'login.id' => $user->id,
            'login.remember' => false,
        ]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Verify recovery code was consumed
        $user->refresh();
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $this->assertNotContains($recoveryCode, $recoveryCodes);
    }

    public function test_invalid_two_factor_code_fails_authentication()
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ]);

        Session::put([
            'login.id' => $user->id,
            'login.remember' => false,
        ]);

        $response = $this->post('/two-factor-challenge', [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertGuest();
    }

    public function test_user_without_two_factor_enabled_can_login_normally()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
