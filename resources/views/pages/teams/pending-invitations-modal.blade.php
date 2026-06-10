<?php

use App\Models\TeamInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public bool $showPendingInvitationsModal = true;

    public function mount(): void
    {
        if (session()->pull('team-invitation-accepted')) {
            Flux::toast(variant: 'success', text: __('Invitation accepted.'));
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{code: string, inviter_name: string, team_name: string}>
     */
    #[Computed]
    public function pendingInvitations(): \Illuminate\Support\Collection
    {
        $email = Str::lower(Auth::user()->email);

        return TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviter_name' => $invitation->inviter->name,
                'team_name' => $invitation->team->name,
            ]);
    }

    public function acceptInvitation(string $code): void
    {
        $invitation = $this->findPendingInvitation($code);

        $this->redirectRoute('invitations.accept', ['invitation' => $invitation->code], navigate: true);
    }

    public function declineInvitation(string $code): void
    {
        $invitation = $this->findPendingInvitation($code);

        $invitation->delete();

        Flux::toast(variant: 'success', text: __('Invitation declined.'));
    }

    private function findPendingInvitation(string $code): TeamInvitation
    {
        $invitation = TeamInvitation::query()
            ->where('code', $code)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation has expired.')],
            ]);
        }

        if (Str::lower($invitation->email) !== Str::lower(Auth::user()->email)) {
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation was sent to a different email address.')],
            ]);
        }

        return $invitation;
    }
}; ?>

<div>
    @if ($this->pendingInvitations->isNotEmpty())
        <flux:modal name="pending-invitations" wire:model="showPendingInvitationsModal" focusable class="max-w-lg">
            <div data-test="pending-invitations-modal" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Pending team invitations') }}</flux:heading>
                    <flux:subheading>{{ __('Accept or decline the teams you have been invited to join.') }}</flux:subheading>
                </div>

                <div class="grid gap-4">
                    @foreach ($this->pendingInvitations as $invitation)
                        <div data-test="pending-invitation-row" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="space-y-1">
                                <p class="font-medium">{{ $invitation['team_name'] }}</p>
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __(':inviter invited you to join this team.', ['inviter' => $invitation['inviter_name']]) }}
                                </flux:text>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <flux:button
                                    variant="filled"
                                    wire:click="declineInvitation('{{ $invitation['code'] }}')"
                                    wire:loading.attr="disabled"
                                    data-test="pending-invitation-decline"
                                >
                                    {{ __('Decline') }}
                                </flux:button>

                                <flux:button
                                    variant="primary"
                                    wire:click="acceptInvitation('{{ $invitation['code'] }}')"
                                    wire:loading.attr="disabled"
                                    data-test="pending-invitation-accept"
                                >
                                    {{ __('Accept') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </flux:modal>
    @endif
</div>
