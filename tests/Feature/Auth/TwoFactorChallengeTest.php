<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_challenge_redirects_when_not_authenticated(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $response = $this->get(route('two-factor.login'));

        $response->assertRedirect(route('login'));
    }

    public function test_two_factor_challenge_renders_correct_livewire_component(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        Volt::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('two-factor.login'))
            ->assertOk();
    }

    public function test_two_factor_authentication_is_rate_limited(): void
    {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt(implode(range('A', 'P'))),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        collect(range(1, 5))->each(function () {
            $this->post(route('two-factor.login.store'), ['code' => '21212'])
                ->assertRedirect(route('two-factor.login'))
                ->assertSessionHasErrors('code');
        });

        $this->post(route('two-factor.login.store'), ['code' => '000000'])
            ->assertTooManyRequests();
    }
}
