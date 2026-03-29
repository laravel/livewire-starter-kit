<?php
namespace Tests\Feature\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcceptInvitationOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeamWithInvitation(string $email): array
    {
        $owner = User::factory()->create();
        $team  = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

        $invitation = TeamInvitation::factory()->create([
            'team_id'    => $team->id,
            'email'      => $email,
            'role'       => TeamRole::Member,
            'invited_by' => $owner->id,
        ]);

        return [$team, $invitation, $owner];
    }

    public function test_unauthenticated_guest_with_no_account_is_redirected_to_register(): void
    {
        [, $invitation] = $this->makeTeamWithInvitation('newuser@example.com');

        Livewire::test('pages::teams.accept-invitation', ['invitation' => $invitation])
            ->assertRedirect(route('register'));

        $this->assertEquals($invitation->code, session('pending_invitation_code'));
    }

    public function test_unauthenticated_guest_with_existing_account_is_redirected_to_login(): void
    {
        User::factory()->create(['email' => 'known@example.com']);

        [, $invitation] = $this->makeTeamWithInvitation('known@example.com');

        Livewire::test('pages::teams.accept-invitation', ['invitation' => $invitation])
            ->assertRedirect(route('login'));

        $this->assertEquals($invitation->code, session('pending_invitation_code'));
    }

    public function test_already_accepted_invitation_is_rejected_before_guest_redirect(): void
    {
        [, $invitation] = $this->makeTeamWithInvitation('newuser@example.com');
        $invitation->update(['accepted_at' => now()]);

        Livewire::test('pages::teams.accept-invitation', ['invitation' => $invitation])
            ->assertHasErrors(['invitation']);

        $this->assertFalse(session()->has('pending_invitation_code'));
    }

    public function test_expired_invitation_is_rejected_before_guest_redirect(): void
    {
        [, $invitation] = $this->makeTeamWithInvitation('newuser@example.com');

        $invitation = TeamInvitation::factory()->expired()->create([
            'team_id'    => $invitation->team_id,
            'email'      => 'newuser@example.com',
            'invited_by' => $invitation->invited_by,
        ]);

        Livewire::test('pages::teams.accept-invitation', ['invitation' => $invitation])
            ->assertHasErrors(['invitation']);

        $this->assertFalse(session()->has('pending_invitation_code'));
    }

    public function test_pending_invitation_is_accepted_after_login(): void
    {
        [$team, $invitation] = $this->makeTeamWithInvitation('member@example.com');
        $user                = User::factory()->create(['email' => 'member@example.com']);

        $this->actingAs($user)
            ->withSession(['pending_invitation_code' => $invitation->code])
            ->get(route('dashboard'))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertTrue($user->fresh()->belongsToTeam($team));
    }

    public function test_pending_invitation_session_token_is_consumed_after_first_use(): void
    {
        [$team, $invitation] = $this->makeTeamWithInvitation('member@example.com');
        $user                = User::factory()->create(['email' => 'member@example.com']);

        $this->actingAs($user)
            ->withSession(['pending_invitation_code' => $invitation->code])
            ->get(route('dashboard'));

        $this->actingAs($user)
            ->get(route('dashboard'));

        $this->assertEquals(
            1,
            $team->fresh()->members()->where('users.id', $user->id)->count()
        );
    }

    public function test_no_pending_invitation_in_session_does_not_affect_normal_requests(): void
    {
        $owner = User::factory()->create();
        $team  = Team::factory()->create();
        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $owner->update(['current_team_id' => $team->id]);

        $this->withoutVite()
            ->actingAs($owner)
            ->get(route('dashboard', ['current_team' => $team->slug]))
            ->assertOk();
    }

    public function test_mismatched_email_flashes_error_and_skips_acceptance(): void
    {
        [$team, $invitation] = $this->makeTeamWithInvitation('someone-else@example.com');
        $user                = User::factory()->create(['email' => 'intruder@example.com']);

        $this->actingAs($user)
            ->withSession(['pending_invitation_code' => $invitation->code])
            ->get(route('dashboard'))
            ->assertSessionHas('error');

        $this->assertFalse($user->fresh()->belongsToTeam($team));
    }

    public function test_already_accepted_invitation_in_session_is_skipped_gracefully(): void
    {
        [$team, $invitation] = $this->makeTeamWithInvitation('member@example.com');
        $user                = User::factory()->create(['email' => 'member@example.com']);
        $invitation->update(['accepted_at' => now()]);

        $this->actingAs($user)
            ->withSession(['pending_invitation_code' => $invitation->code])
            ->get(route('dashboard'))
            ->assertSessionHas('error');

        $this->assertFalse($user->fresh()->belongsToTeam($team));
    }

    public function test_expired_invitation_in_session_is_skipped_gracefully(): void
    {
        [, $expiredInvitation] = $this->makeTeamWithInvitation('member@example.com');
        $user                  = User::factory()->create(['email' => 'member@example.com']);

        $expired = TeamInvitation::factory()->expired()->create([
            'team_id'    => $expiredInvitation->team_id,
            'email'      => 'member@example.com',
            'invited_by' => $expiredInvitation->invited_by,
        ]);

        $this->actingAs($user)
            ->withSession(['pending_invitation_code' => $expired->code])
            ->get(route('dashboard'))
            ->assertSessionHas('error');
    }
}
