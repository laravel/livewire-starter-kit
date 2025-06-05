<?php

use App\Actions\TwoFactorAuth\VerifyTwoFactorCode;
use App\Actions\TwoFactorAuth\ProcessRecoveryCode;
use App\Actions\TwoFactorAuth\CompleteTwoFactorAuthentication;
use App\Actions\TwoFactorAuth\GetTwoFactorAuthenticatableUser;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    public $recovery = false;

    #[Validate('required|min:6')] 
    public $auth_code;
    public $recovery_code;

    public function mount()
    {
        if ( !session()->has('login.id')) {
            return redirect()->route('login');
        }
        $this->recovery = false;
    }

    #[On('submitCode')] 
    public function submitCode($code)
    {
        $this->auth_code = $code;
        $this->validate();

        // Get the user that is in the process of 2FA
        $user = app(GetTwoFactorAuthenticatableUser::class)();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $this->ensureIsNotRateLimited($user);
        
        // Verify the authentication code
        $valid = app(VerifyTwoFactorCode::class)(decrypt($user->two_factor_secret), $code);

        if ($valid) {
            // Complete the authentication process
            app(CompleteTwoFactorAuthentication::class)($user);
            
            // Clear rate limiter on successful authentication
            RateLimiter::clear($this->throttleKey($user));
            
            // Redirect to the intended page
            return $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } else {
            RateLimiter::hit($this->throttleKey($user));
            $this->addError('auth_code', 'Invalid authentication code. Please try again.');
        }
    }

    public function submit_recovery_code()
    {
        // Get the user that is in the process of 2FA
        $user = app(GetTwoFactorAuthenticatableUser::class)();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $this->ensureIsNotRateLimited($user);
        
        // Process the recovery code
        $updatedCodes = app(ProcessRecoveryCode::class)(json_decode(decrypt($user->two_factor_recovery_codes), true), $this->recovery_code);

        if ($updatedCodes !== false) {
            // Update the user's recovery codes in the database
            $user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode($updatedCodes))
            ])->save();
            
            // Complete the authentication process
            app(CompleteTwoFactorAuthentication::class)($user);

            // Clear rate limiter on successful authentication
            RateLimiter::clear($this->throttleKey($user));
            
            // Redirect to the intended page
            return $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } else {
            RateLimiter::hit($this->throttleKey($user));
            $this->addError('recovery_code', 'This is an invalid recovery code. Please try again.');
        }
    }

    /**
     * Ensure the two-factor authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(User $user): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($user), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey($user));

        throw ValidationException::withMessages([
            'auth_code' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the two-factor challenge.
     */
    protected function throttleKey(User $user): string
    {
        return Str::transliterate($user->id . '|2fa');
    }
}

?>

<div class="flex flex-col gap-6">
    <div x-data x-on:code-input-complete.window="console.log(event); $dispatch('submitCode', [event.detail.code])" class="relative w-full h-auto">
        @if(!$recovery)
            <x-auth-header :title="__('Authentication Code')" :description="__('Enter the authentication code provided by your authenticator application.')" />
        @else
            <x-auth-header :title="__('Recovery Code')" :description="__('Please confirm access to your account by entering one of your emergency recovery codes.')" />
        @endif

        <div class="space-y-5 text-center mt-5">
            @if(!$recovery)
                <div class="relative">
                    <!-- Authentication Code -->
                    <x-input-otp wire:model="auth_code" id="auth-input-code" digits="6" eventCallback="code-input-complete" type="text" label="Code" />
                </div>
                @error('auth_code')
                    <p class="my-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <flux:button variant="primary" type="submit" class="w-full" wire:click="submitCode(document.getElementById('auth-input-code').value)">{{ __('Continue') }}</flux:button>
            @else
                <div class="relative">
                    <flux:input type="text" x-init="$el.focus();" wire:keydown.enter="submit_recovery_code" wire:model="recovery_code" :label="__('Recovery Code')" required autofocus autocomplete="recovery-code" />
                </div>
                <flux:button variant="primary" type="submit" class="w-full" wire:click="submit_recovery_code">{{ __('Continue') }}</flux:button>
            @endif
        </div>

        <div class="mt-5 space-x-0.5 text-sm leading-5 text-center text-black dark:text-white">
            <span class="opacity-[47%]">or you can </span>
            <div class="font-medium underline opacity-60 cursor-pointer inline">
                @if(!$recovery)
                    <span wire:click="$set('recovery', true)">login using a recovery code</span>
                @else
                    <span wire:click="$set('recovery', false)">login using an authentication code</span>
                @endif
            </div>
        </div>
    </div>
</div>