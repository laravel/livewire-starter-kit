<?php

namespace Tests\Feature\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_member_role_can_be_updated_by_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($owner);

        Livewire::test('pages::teams.edit', ['team' => $team])
            ->call('updateMember', $member->id, TeamRole::Admin->value)
            ->assertHasNoErrors();

        $this->assertEquals(
            TeamRole::Admin->value,
            $team->members()->where('user_id', $member->id)->first()->pivot->role->value,
        );
    }

    public function test_team_member_role_cannot_be_updated_by_non_owner(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($admin);

        Livewire::test('pages::teams.edit', ['team' => $team])
            ->call('updateMember', $member->id, TeamRole::Admin->value)
            ->assertForbidden();
    }

    public function test_team_member_can_be_removed_by_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($owner);

        Livewire::test('pages::teams.remove-member-modal', ['team' => $team])
            ->set('memberId', $member->id)
            ->call('removeMember')
            ->assertHasNoErrors();

        $this->assertFalse($member->fresh()->belongsToTeam($team));
    }

    public function test_team_member_cannot_be_removed_by_non_owners(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($admin);

        Livewire::test('pages::teams.remove-member-modal', ['team' => $team])
            ->set('memberId', $member->id)
            ->call('removeMember')
            ->assertForbidden();
    }

    public function test_removed_members_current_team_is_set_to_personal_team(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $personalTeam = $member->personalTeam();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $member->update(['current_team_id' => $team->id]);

        $this->actingAs($owner);

        Livewire::test('pages::teams.remove-member-modal', ['team' => $team])
            ->set('memberId', $member->id)
            ->call('removeMember')
            ->assertHasNoErrors();

        $this->assertEquals($personalTeam->id, $member->fresh()->current_team_id);
    }
}
