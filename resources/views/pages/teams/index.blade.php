<?php

use App\Actions\Teams\CreateTeam;
use App\Data\UserTeam;
use App\Models\Team;
use App\Rules\TeamName;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Teams')] class extends Component {
    public string $name = '';

    public function createTeam(CreateTeam $createTeam): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', new TeamName],
        ]);

        $team = $createTeam->handle(Auth::user(), $validated['name']);

        $this->dispatch('close-modal', name: 'create-team');

        $this->reset('name');

        Flux::toast(variant: 'success', text: __('Team created.'));

        $this->redirectRoute('teams.edit', ['team' => $team->slug], navigate: true);
    }

    public function leaveTeam(int $teamId): void
    {
        $team = Team::findOrFail($teamId);
        $user = Auth::user();

        Gate::authorize('leave', $team);

        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        $this->dispatch('close-modal', name: "leave-team-{$teamId}");

        Flux::toast(variant: 'success', text: __('You left the team ":name"', ['name' => $team->name]));

        $this->redirectRoute('teams.index', navigate: true);
    }

    /**
     * @return Collection<int, UserTeam>
     */
    #[Computed]
    public function teams(): Collection
    {
        return Auth::user()->toUserTeams(includeCurrent: true);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Teams') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Teams')" :subheading="__('Manage your teams and team memberships')">
        <div class="flex items-center justify-end">
            <flux:modal.trigger name="create-team">
                <flux:button variant="primary" icon="plus" x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-team')" data-test="teams-new-team-button">
                    {{ __('New team') }}
                </flux:button>
            </flux:modal.trigger>
        </div>

        <div class="mt-6 space-y-3">
            @forelse ($this->teams as $team)
                <div class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900" data-test="team-row">
                    <div class="flex items-center gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $team->name }}</span>
                                @if ($team->isPersonal)
                                    <flux:badge color="zinc">{{ __('Personal') }}</flux:badge>
                                @endif
                            </div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $team->roleLabel }}</flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        @if (! $team->isPersonal && $team->role !== 'owner')
                            <flux:modal.trigger :name="'leave-team-'.$team->id">
                                <flux:tooltip :content="__('Leave team')">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-right-start-on-rectangle"
                                        x-data=""
                                        x-on:click.prevent="$dispatch('open-modal', 'leave-team-{{ $team->id }}')"
                                        data-test="team-leave-button"
                                    />
                                </flux:tooltip>
                            </flux:modal.trigger>
                        @endif

                        <flux:tooltip :content="$team->role === 'member' ? __('View team') : __('Edit team')">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                :icon="$team->role === 'member' ? 'eye' : 'pencil'"
                                :href="route('teams.edit', $team->slug)"
                                wire:navigate
                                :data-test="$team->role === 'member' ? 'team-view-button' : 'team-edit-button'"
                            />
                        </flux:tooltip>
                    </div>
                </div>

                @if (! $team->isPersonal && $team->role !== 'owner')
                    <flux:modal :name="'leave-team-'.$team->id" focusable class="max-w-lg">
                        <form wire:submit="leaveTeam({{ $team->id }})" class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('Leave team') }}</flux:heading>
                                <flux:subheading>
                                    {{ __('Are you sure you want to leave :name?', ['name' => $team->name]) }}
                                </flux:subheading>
                            </div>

                            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                                <flux:modal.close>
                                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                                </flux:modal.close>

                                <flux:button variant="danger" type="submit" data-test="leave-team-confirm">
                                    {{ __('Leave team') }}
                                </flux:button>
                            </div>
                        </form>
                    </flux:modal>
                @endif
            @empty
                <flux:text class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                    {{ __('You don\'t belong to any teams yet.') }}
                </flux:text>
            @endforelse
        </div>
    </x-pages::settings.layout>

    <flux:modal name="create-team" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="createTeam" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create a new team') }}</flux:heading>
                <flux:subheading>{{ __('Give your team a name to get started.') }}</flux:subheading>
            </div>

            <flux:input wire:model="name" :label="__('Team name')" type="text" required autofocus data-test="create-team-name" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit" data-test="create-team-submit">
                    {{ __('Create team') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
