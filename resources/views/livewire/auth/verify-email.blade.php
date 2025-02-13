<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout("components.layouts.auth")] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(
                default: route("dashboard", absolute: false),
                navigate: true,
            );

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash("status", "verification-link-sent");
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect("/", navigate: true);
    }
};
?>

<div class="mt-4 flex flex-col gap-6">
    <div class="text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session("status") == "verification-link-sent")
        <div class="text-sm font-medium text-green-600">
            {{ __("A new verification link has been sent to the email address you provided during registration.") }}
        </div>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <flux:button
            wire:click="sendVerification"
            variant="primary"
            class="w-full"
        >
            {{ __("Resend Verification Email") }}
        </flux:button>

        <button
            wire:click="logout"
            type="submit"
            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            {{ __("Log Out") }}
        </button>
    </div>
</div>
