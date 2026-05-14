<?php

use App\Actions\Teams\CreateTeam;
use App\Rules\TeamName;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $teamName = '';

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
}; ?>

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
