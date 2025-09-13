<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

test('profile_page_is_displayed', function (): void {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile_information_can_be_updated', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    $this->assertEquals('Test User', $user->name);
    $this->assertEquals('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email_verification_status_is_unchanged_when_email_address_is_unchanged', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user_can_delete_their_account', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertNull($user->fresh());
    $this->assertFalse(auth()->check());
});

test('correct_password_must_be_provided_to_delete_account', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    $this->assertNotNull($user->fresh());
});
