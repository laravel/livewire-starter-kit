<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Laravel\Fortify\Features;
use Tests\TestCase;

class TwoFactorSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        // Enable two-factor authentication with confirmation
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);
    }

    public function test_two_factor_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('settings.two-factor');

        $component->assertOk()
                  ->assertSee('Two Factor Authentication')
                  ->assertSee('Disabled');
    }

    public function test_two_factor_settings_page_returns_forbidden_when_feature_disabled(): void
    {
        $this->markTestSkipped('Feature disabling test requires app restart to take effect properly.');
    }

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable');

        $component->assertSet('twoFactorEnabled', false); // Still false until confirmed
        
        // Check that QR code and manual setup key are populated
        $this->assertNotEmpty($component->get('qrCodeSvg'));
        $this->assertNotEmpty($component->get('manualSetupKey'));
        
        $component->assertDispatched('show-two-factor-modal');

        // Verify two-factor data is stored in database
        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at); // Not confirmed yet
    }

    public function test_user_can_enable_two_factor_without_confirmation_when_disabled(): void
    {
        $this->markTestSkipped('Configuration changes need app restart to take effect in component.');
    }

    public function test_user_can_proceed_to_verification_step(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->call('proceedToVerification');

        $component->assertSet('showVerificationStep', true)
                  ->assertSet('authCode', '');
    }

    public function test_user_can_go_back_to_setup_from_verification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->call('proceedToVerification')
            ->set('authCode', '123456')
            ->call('backToSetup');

        $component->assertSet('showVerificationStep', false)
                  ->assertSet('authCode', '');
    }

    public function test_user_can_confirm_two_factor_with_valid_code(): void
    {
        $user = User::factory()->create();

        // First enable 2FA to generate secret
        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable');

        // Get the secret to generate a valid TOTP code
        $user->refresh();
        $secret = decrypt($user->two_factor_secret);
        
        // Generate a valid TOTP code using the secret
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $validCode = $google2fa->getCurrentOtp($secret);

        $component->call('proceedToVerification')
                  ->set('authCode', $validCode)
                  ->call('confirmTwoFactor');

        $component->assertSet('twoFactorEnabled', true)
                  ->assertSet('showVerificationStep', false)
                  ->assertDispatched('two-factor-enabled')
                  ->assertDispatched('hide-two-factor-modal');

        // Verify user is now fully enabled with 2FA
        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    public function test_user_cannot_confirm_two_factor_with_invalid_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->call('proceedToVerification')
            ->set('authCode', '000000')
            ->call('confirmTwoFactor');

        // Should have validation or authentication error
        $this->assertTrue($component->errors()->has('authCode') || $component->errors()->isNotEmpty());

        // Verify user is not confirmed
        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_auth_code_validation_rules(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->call('proceedToVerification');

        // Test required validation
        $component->set('authCode', '')
                  ->call('confirmTwoFactor')
                  ->assertHasErrors(['authCode' => 'required']);

        // Test minimum length validation
        $component->set('authCode', '123')
                  ->call('confirmTwoFactor')
                  ->assertHasErrors(['authCode' => 'min']);

        // Test maximum length validation  
        $component->set('authCode', '1234567')
                  ->call('confirmTwoFactor')
                  ->assertHasErrors(['authCode' => 'max']);
    }

    public function test_user_can_view_recovery_codes_when_two_factor_enabled(): void
    {
        $user = $this->createUserWithTwoFactorEnabled();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('fetchRecoveryCodes');

        $recoveryCodes = $component->get('recoveryCodes');
        $this->assertNotEmpty($recoveryCodes);
        $this->assertCount(8, $recoveryCodes); // Default recovery codes count
    }

    public function test_user_can_regenerate_recovery_codes(): void
    {
        $user = $this->createUserWithTwoFactorEnabled();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('fetchRecoveryCodes');

        $originalCodes = $component->get('recoveryCodes');

        $component->call('regenerateRecoveryCodes')
                  ->call('fetchRecoveryCodes');

        $newCodes = $component->get('recoveryCodes');

        $this->assertNotEquals($originalCodes, $newCodes);
        $this->assertCount(8, $newCodes);
    }

    public function test_user_can_toggle_recovery_codes_visibility(): void
    {
        $user = $this->createUserWithTwoFactorEnabled();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor');

        $component->assertSet('showRecoveryCodes', false);

        $component->call('toggleRecoveryCodes');
        $component->assertSet('showRecoveryCodes', true);
        $this->assertNotEmpty($component->get('recoveryCodes'));

        $component->call('toggleRecoveryCodes');
        $component->assertSet('showRecoveryCodes', false);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = $this->createUserWithTwoFactorEnabled();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('disable');

        $component->assertSet('twoFactorEnabled', false)
                  ->assertSet('qrCodeSvg', '')
                  ->assertSet('manualSetupKey', '')
                  ->assertSet('recoveryCodes', []);

        // Verify database cleanup
        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_modal_config_property_returns_correct_data_for_disabled_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor');

        $modalConfig = $component->get('modalConfig');

        $this->assertEquals('Enable Two-Factor Authentication', $modalConfig['title']);
        $this->assertStringContainsString('To finish enabling', $modalConfig['description']);
        $this->assertEquals('Continue', $modalConfig['buttonText']);
    }

    public function test_modal_config_property_returns_correct_data_for_enabled_state(): void
    {
        $user = $this->createUserWithTwoFactorEnabled();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor');

        $modalConfig = $component->get('modalConfig');

        $this->assertEquals('Two-Factor Authentication Enabled', $modalConfig['title']);
        $this->assertStringContainsString('Two-factor authentication is now enabled', $modalConfig['description']);
        $this->assertEquals('Close', $modalConfig['buttonText']);
    }

    public function test_modal_config_property_returns_correct_data_for_verification_step(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->call('proceedToVerification');

        $modalConfig = $component->get('modalConfig');

        $this->assertEquals('Verify Authentication Code', $modalConfig['title']);
        $this->assertStringContainsString('Enter the 6-digit code', $modalConfig['description']);
        $this->assertEquals('Continue', $modalConfig['buttonText']);
    }

    public function test_user_can_close_modal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->call('proceedToVerification')
            ->set('authCode', '123456')
            ->call('closeModal');

        $component->assertSet('showVerificationStep', false)
                  ->assertSet('authCode', '')
                  ->assertDispatched('hide-two-factor-modal');
    }

    public function test_close_modal_clears_setup_data_when_two_factor_enabled(): void
    {
        $user = $this->createUserWithTwoFactorEnabled();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable') // This populates setup data
            ->call('closeModal');

        $component->assertSet('qrCodeSvg', '')
                  ->assertSet('manualSetupKey', '')
                  ->assertSet('recoveryCodes', []);
    }

    public function test_setup_data_is_cleared_properly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable');

        // Verify setup data is populated
        $this->assertNotEmpty($component->get('qrCodeSvg'));
        $this->assertNotEmpty($component->get('manualSetupKey'));

        $component->call('clearSetupData');

        $component->assertSet('qrCodeSvg', '')
                  ->assertSet('manualSetupKey', '')
                  ->assertSet('recoveryCodes', []);
    }

    public function test_component_properly_mounts_with_two_factor_disabled(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor');

        $component->assertSet('twoFactorEnabled', false)
                  ->assertSet('requiresConfirmation', true)
                  ->assertSet('showVerificationStep', false)
                  ->assertSet('showRecoveryCodes', false);
    }

    public function test_component_properly_mounts_with_two_factor_enabled(): void
    {
        $user = $this->createUserWithTwoFactorEnabled();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor');

        $component->assertSet('twoFactorEnabled', true)
                  ->assertSet('requiresConfirmation', true);
        $this->assertNotEmpty($component->get('recoveryCodes'));
    }

    public function test_component_cleans_up_unconfirmed_two_factor_on_mount(): void
    {
        $user = User::factory()->create();

        // Simulate user who started 2FA setup but never confirmed
        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => null,
        ])->save();

        // Mount component (should trigger cleanup)
        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor');

        $component->assertSet('twoFactorEnabled', false);

        // Verify cleanup happened
        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_fetch_setup_data_populates_qr_code_and_manual_key(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->call('fetchSetupData');

        $this->assertNotEmpty($component->get('qrCodeSvg'));
        $this->assertNotEmpty($component->get('manualSetupKey'));

        // Verify QR code is actually SVG
        $qrCode = $component->get('qrCodeSvg');
        $this->assertStringContainsString('<svg', $qrCode);
        $this->assertStringContainsString('</svg>', $qrCode);
    }

    protected function createUserWithTwoFactorEnabled(): User
    {
        $user = User::factory()->create();

        // Enable two-factor authentication for the user
        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret-key-for-2fa-auth'),
            'two_factor_recovery_codes' => encrypt(json_encode([
                'recovery-code-1',
                'recovery-code-2', 
                'recovery-code-3',
                'recovery-code-4',
                'recovery-code-5',
                'recovery-code-6',
                'recovery-code-7',
                'recovery-code-8'
            ])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user;
    }
}