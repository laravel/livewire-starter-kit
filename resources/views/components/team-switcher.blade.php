<?php

use App\Actions\Teams\CreateTeam;
use App\Models\Team;
use App\Rules\TeamName;
use App\Support\UserTeam;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $teamName = '';

    public function currentTeam(): ?array
    {
        $team = Auth::user()->currentTeam;

        return $team ? [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
        ] : null;
    }

    /**
     * @return Collection<int, UserTeam>
     */
    public function teams(): Collection
    {
        return Auth::user()->toUserTeams(includeCurrent: true);
    }

    public function createTeam(CreateTeam $createTeam): void
    {
        $validated = $this->validate([
            'teamName' => ['required', 'string', 'max:255', new TeamName],
        ]);

        $team = $createTeam->handle(Auth::user(), $validated['teamName']);

        $this->dispatch('close-modal', name: 'create-team-switcher');

        $this->reset('teamName');

        Flux::toast(variant: 'success', text: __('Team created.'));

        $this->redirectRoute('teams.edit', ['team' => $team->slug], navigate: true);
    }

    public function switchTeam(string $slug): void
    {
        $user = Auth::user();

        abort_unless(
            $user->belongsToTeam($team = Team::where('slug', $slug)->firstOrFail()),
            403
        );

        $currentTeamSlug = $user->currentTeam?->slug;

        $user->switchTeam($team);

        if (! request()->header('Referer')) {
            $this->redirectRoute('dashboard', ['current_team' => $team->slug], navigate: true);

            return;
        }

        if (! $currentTeamSlug) {
            $this->redirect(request()->header('Referer'), navigate: true);

            return;
        }

        $redirectTo = $this->replaceCurrentTeamInReferer(
            request()->header('Referer'),
            $currentTeamSlug,
            $team->slug,
        );

        $this->redirect($redirectTo ?? request()->header('Referer'), navigate: true);
    }

    protected function replaceCurrentTeamInReferer(string $referer, string $currentTeamSlug, string $newTeamSlug): ?string
    {
        $redirectTo = preg_replace(
            '#/'.preg_quote($currentTeamSlug, '#').'(?=/|\?|$)#',
            '/'.$newTeamSlug,
            $referer,
            1,
        );

        return preg_replace(
            '#([?&]current_team=)'.preg_quote($currentTeamSlug, '#').'(?=&|$)#',
            '$1'.$newTeamSlug,
            $redirectTo ?? $referer,
            1,
        );
    }
}; ?>

<div>
    <flux:dropdown position="bottom" align="start">
        <flux:button variant="ghost" class="group w-full justify-start in-data-flux-sidebar-collapsed-desktop:justify-center" data-test="team-switcher-trigger">
            <flux:icon name="users" class="hidden size-4 in-data-flux-sidebar-collapsed-desktop:block" />
            <span class="truncate font-semibold in-data-flux-sidebar-collapsed-desktop:hidden">{{ $this->currentTeam()['name'] ?? __('Select team') }}</span>
            <flux:icon
                name="chevrons-up-down"
                variant="micro"
                class="ms-auto size-4 in-data-flux-sidebar-collapsed-desktop:hidden"
            />
        </flux:button>

        <flux:menu class="min-w-56">
            <flux:menu.heading>{{ __('Teams') }}</flux:menu.heading>

            @foreach ($this->teams() as $team)
                <flux:menu.item
                    wire:click="switchTeam('{{ $team->slug }}')"
                    class="cursor-pointer"
                    data-test="team-switcher-item"
                >
                    <div class="flex w-full items-center justify-between">
                        <span>{{ $team->name }}</span>
                        @if ($team->isCurrent)
                            <flux:icon name="check" class="size-4" />
                        @endif
                    </div>
                </flux:menu.item>
            @endforeach

            <flux:menu.separator />

            <flux:modal.trigger name="create-team-switcher">
                <flux:menu.item icon="plus" class="cursor-pointer" data-test="team-switcher-new-team">
                    {{ __('New team') }}
                </flux:menu.item>
            </flux:modal.trigger>
        </flux:menu>
    </flux:dropdown>

    <flux:modal name="create-team-switcher" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="createTeam" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create a new team') }}</flux:heading>
                <flux:subheading>{{ __('Give your team a name to get started.') }}</flux:subheading>
            </div>

            <flux:input wire:model="teamName" :label="__('Team name')" type="text" required autofocus data-test="switcher-create-team-name" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit" data-test="switcher-create-team-submit">
                    {{ __('Create team') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
