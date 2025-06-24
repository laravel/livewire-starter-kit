<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

readonly class VerifyUserEmail
{
    public function __construct(
        private User $user
    ) {
    }

    public function handle(): void
    {
        if (! $this->user instanceof MustVerifyEmail) {
            return;
        }

        DB::transaction(function (): void {
            $this->purgeConflicts();
            $this->applyVerification();
        });
    }

    /**
     * Remove conflicting unverified users and reservations.
     */
    private function purgeConflicts(): void
    {
        $email = $this->user->unverified_email ?? $this->user->email;

        User::query()
            ->where('email', $email)
            ->whereNot('id', $this->user->id)
            ->whereNull('email_verified_at')
            ->delete();

        User::query()
            ->where('unverified_email', $email)
            ->whereNot('id', $this->user->id)
            ->whereNull('email_verified_at')
            ->update(['unverified_email' => null]);
    }

    /**
     * Apply email change or mark as verified.
     */
    private function applyVerification(): void
    {
        if ($this->user->unverified_email) {
            $this->user->forceFill([
                'email' => $this->user->unverified_email,
                'unverified_email' => null,
                'email_verified_at' => now(),
            ])->save();

            return;
        }

        $this->user->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }
}
