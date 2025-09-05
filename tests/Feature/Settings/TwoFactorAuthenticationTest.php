<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Artisan;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);
    }

    public function test_two_factor_settings_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('settings.two-factor'))
            ->assertOk()
            ->assertSee('Two Factor Authentication')
            ->assertSee('Disabled');
    }

    public function test_two_factor_settings_page_requires_password_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('settings.two-factor'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_two_factor_settings_page_returns_forbidden_when_two_factor_is_disabled(): void
    {
        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('settings.two-factor'));

        $response->assertForbidden();
    }

    public function test_enable_two_factor_sets_up_confirmation_flow_when_confirmation_required(): void
    {
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('settings.two-factor')
            ->call('enable');

        $component->assertSet('twoFactorEnabled', false);
        $this->assertNotEmpty($component->get('qrCodeSvg'));
        $this->assertNotEmpty($component->get('manualSetupKey'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_enable_two_factor_immediately_enables_when_confirmation_not_required(): void
    {
        Features::twoFactorAuthentication([
            'confirm' => false,
            'confirmPassword' => false,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('settings.two-factor')
            ->call('enable')
            ->assertSet('twoFactorEnabled', true);

        $this->assertNotEmpty($component->get('qrCodeSvg'));
        $this->assertNotEmpty($component->get('manualSetupKey'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_two_factor_authentication_disabled_when_confirmation_abandoned_between_requests(): void
    {
        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->actingAs($user);

        $component = Volt::test('settings.two-factor');

        $component->assertSet('twoFactorEnabled', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }
}
