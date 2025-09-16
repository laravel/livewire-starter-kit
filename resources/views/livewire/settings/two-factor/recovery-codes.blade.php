<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());
        $this->loadRecoveryCodes();
    }

    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();
        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        }
    }
}; ?>

<div class="rounded-xl border border-zinc-200 dark:border-white/10 py-6 shadow-sm space-y-6" wire:cloak
     x-data="{ showRecoveryCodes: false }">
    @if (filled($recoveryCodes))
        <div class="px-6 space-y-2">
            <div class="flex items-center gap-2">
                <flux:icon.lock-closed variant="outline" class="size-4"/>
                <flux:heading size="lg" level="3">{{ __('2FA Recovery Codes') }}</flux:heading>
            </div>
            <flux:text variant="subtle">
                {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
            </flux:text>
        </div>
        <div class="px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <flux:button
                    x-show="!showRecoveryCodes"
                    icon="eye"
                    icon:variant="outline"
                    variant="primary"
                    @click="showRecoveryCodes = true;"
                    aria-expanded="false"
                    aria-controls="recovery-codes-section"
                >
                    {{ __('View Recovery Codes') }}
                </flux:button>
                <flux:button
                    x-show="showRecoveryCodes"
                    icon="eye-slash"
                    icon:variant="outline"
                    variant="primary"
                    @click="showRecoveryCodes = false"
                    aria-expanded="true"
                    aria-controls="recovery-codes-section"
                >
                    {{ __('Hide Recovery Codes') }}
                </flux:button>
                <flux:button
                    x-show="showRecoveryCodes"
                    icon="arrow-path"
                    variant="filled"
                    wire:click="regenerateRecoveryCodes"
                >
                    {{ __('Regenerate Codes') }}
                </flux:button>
            </div>
            <div
                x-show="showRecoveryCodes"
                x-transition
                id="recovery-codes-section"
                class="relative overflow-hidden"
                x-bind:aria-hidden="!showRecoveryCodes"
            >
                <div class="mt-3 space-y-3">
                    <div class="grid gap-1 rounded-lg p-4 bg-zinc-100 dark:bg-white/5 font-mono text-sm"
                         role="list"
                         aria-label="Recovery codes">
                        @foreach($recoveryCodes as $code)
                            <div role="listitem" class="select-text"
                                 wire:loading.class="opacity-50 animate-pulse">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <flux:text variant="subtle" class="text-xs">
                        {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.') }}
                    </flux:text>
                </div>
            </div>
        </div>
    @endif
</div>

