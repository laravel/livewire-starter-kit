<?php

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Teams')] class extends Component {
    public TeamInvitation $invitation;

    public function mount(TeamInvitation $invitation): void
    {
        $this->invitation = $invitation;

        $this->acceptInvitation();
    }

    public function acceptInvitation(): void
    {
        $user = Auth::user();

        $this->validateInvitation($user, $this->invitation);

        DB::transaction(function () use ($user) {
            $team = $this->invitation->team;

            $membership = $team->memberships()->firstOrCreate(
                ['user_id' => $user->id],
                ['role' => $this->invitation->role]
            );

            $this->invitation->update(['accepted_at' => now()]);

            $user->switchTeam($team);
        });

        $this->redirectRoute('dashboard');
    }

    private function validateInvitation(User $user, TeamInvitation $invitation): void
    {
        if ($invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation has already been accepted.')],
            ]);
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation has expired.')],
            ]);
        }

        if (Str::lower($invitation->email) !== Str::lower($user->email)) {
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation was sent to a different email address.')],
            ]);
        }
    }
}; ?>

<div></div>
