<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('email_verification_screen_can_be_rendered', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertStatus(200);
});

test('email_can_be_verified', function (): void {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);

    $this->assertTrue($user->fresh()->hasVerifiedEmail());
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email_is_not_verified_with_invalid_hash', function (): void {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    $this->assertFalse($user->fresh()->hasVerifiedEmail());
});

test('already_verified_user_visiting_verification_link_is_redirected_without_firing_event_again', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    $this->assertTrue($user->fresh()->hasVerifiedEmail());
    Event::assertNotDispatched(Verified::class);
});
