<?php

namespace Tests\Feature\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_teams_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('teams.index'));

        $response->assertOk();
    }

    public function test_teams_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::teams.index')
            ->set('name', 'Test Team')
            ->call('createTeam')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', [
            'name' => 'Test Team',
            'is_personal' => false,
        ]);
    }

    public function test_team_slug_uses_next_available_suffix(): void
    {
        $user = User::factory()->create();

        Team::factory()->create(['name' => 'Acme', 'slug' => 'acme']);
        Team::factory()->create(['name' => 'Acme One', 'slug' => 'acme-1']);
        Team::factory()->create(['name' => 'Acme Ten', 'slug' => 'acme-10']);

        $this->actingAs($user);

        Livewire::test('pages::teams.index')
            ->set('name', 'Acme')
            ->call('createTeam')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', [
            'name' => 'Acme',
            'slug' => 'acme-11',
        ]);
    }

    public function test_team_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $response = $this
            ->actingAs($user)
            ->get(route('teams.edit', $team));

        $response->assertOk();
    }

    public function test_teams_can_be_updated_by_owners(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Original Name']);

        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        Livewire::test('pages::teams.edit', ['team' => $team])
            ->set('teamName', 'Updated Name')
            ->call('updateTeam')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_teams_cannot_be_updated_by_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member);

        Livewire::test('pages::teams.edit', ['team' => $team])
            ->set('teamName', 'Updated Name')
            ->call('updateTeam')
            ->assertForbidden();
    }

    public function test_teams_can_be_deleted_by_owners(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $team->id,
        ]);
    }

    public function test_team_deletion_requires_name_confirmation(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', 'Wrong Name')
            ->call('deleteTeam')
            ->assertHasErrors(['deleteName']);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_deleting_current_team_switches_to_alphabetically_first_remaining_team(): void
    {
        $user = User::factory()->create(['name' => 'Mike']);

        $zuluTeam = Team::factory()->create(['name' => 'Zulu Team']);
        $zuluTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $alphaTeam = Team::factory()->create(['name' => 'Alpha Team']);
        $alphaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $betaTeam = Team::factory()->create(['name' => 'Beta Team']);
        $betaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $user->update(['current_team_id' => $zuluTeam->id]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $zuluTeam])
            ->set('deleteName', $zuluTeam->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $zuluTeam->id,
        ]);

        $this->assertEquals($alphaTeam->id, $user->fresh()->current_team_id);
    }

    public function test_deleting_current_team_falls_back_to_personal_team_when_alphabetically_first(): void
    {
        $user = User::factory()->create();
        $personalTeam = $user->personalTeam();
        $team = Team::factory()->create(['name' => 'Zulu Team']);
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $user->update(['current_team_id' => $team->id]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $team->id,
        ]);

        $this->assertEquals($personalTeam->id, $user->fresh()->current_team_id);
    }

    public function test_deleting_non_current_team_leaves_current_team_unchanged(): void
    {
        $user = User::factory()->create();
        $personalTeam = $user->personalTeam();
        $team = Team::factory()->create();
        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

        $user->update(['current_team_id' => $personalTeam->id]);

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('teams', [
            'id' => $team->id,
        ]);

        $this->assertEquals($personalTeam->id, $user->fresh()->current_team_id);
    }

    public function test_deleting_team_switches_other_affected_users_to_their_personal_team(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $team = Team::factory()->create();
        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $owner->update(['current_team_id' => $team->id]);
        $member->update(['current_team_id' => $team->id]);

        $this->actingAs($owner);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertHasNoErrors();

        $this->assertEquals($member->personalTeam()->id, $member->fresh()->current_team_id);
    }

    public function test_personal_teams_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $personalTeam = $user->personalTeam();

        $this->actingAs($user);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $personalTeam])
            ->set('deleteName', $personalTeam->name)
            ->call('deleteTeam')
            ->assertForbidden();

        $this->assertDatabaseHas('teams', [
            'id' => $personalTeam->id,
            'deleted_at' => null,
        ]);
    }

    public function test_teams_cannot_be_deleted_by_non_owners(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member, ['role' => TeamRole::Member->value]);

        $this->actingAs($member);

        Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
            ->set('deleteName', $team->name)
            ->call('deleteTeam')
            ->assertForbidden();
    }

    public function test_guests_cannot_access_teams(): void
    {
        $response = $this->get(route('teams.index'));

        $response->assertRedirect(route('login'));
    }
}
