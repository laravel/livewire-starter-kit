<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div x-data="{
            showRecoveryInput: {{ $errors->has('recovery_code') ? 'true' : 'false' }},
            code: '',
            recovery_code: ''
        }" class="relative w-full h-auto">
            <div x-show="!showRecoveryInput">
                <x-auth-header :title="__('Authentication Code')" :description="__('Enter the authentication code provided by your authenticator application.')" />
            </div>
            <div x-show="showRecoveryInput">
                <x-auth-header :title="__('Recovery Code')" :description="__('Please confirm access to your account by entering one of your emergency recovery codes.')" />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}">
                @csrf

                <div class="space-y-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center my-5">
                            <x-input-otp name="code" digits="6" autocomplete="one-time-code"
                                @input="code = $event.target.value" />
                        </div>
                        @error('code')
                            <p class="my-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-show="showRecoveryInput" x-cloak>
                        <div class="my-5">
                            <flux:input type="text" name="recovery_code" x-ref="recovery_code"
                                x-bind:required="showRecoveryInput" autocomplete="one-time-code"
                                x-model="recovery_code" />
                        </div>
                        @error('recovery_code')
                            <p class="my-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <flux:button variant="primary" type="submit" class="w-full"
                        x-bind:class="{ 'opacity-50 cursor-default pointer-events-none': showRecoveryInput ? recovery_code.length === 0 : code.length < 6 }">
                        {{ __('Continue') }}
                    </flux:button>
                </div>

                <div class="mt-5 space-x-0.5 text-sm leading-5 text-center">
                    <span class="opacity-50">or you can </span>
                    <div class="font-medium underline opacity-80 cursor-pointer inline">
                        <span x-show="!showRecoveryInput"
                            @click="showRecoveryInput = true; code = ''; recovery_code = ''; $dispatch('clear-auth-2fa-auth-code'); $nextTick(() => $refs.recovery_code?.focus())">login
                            using a recovery code</span>
                        <span x-show="showRecoveryInput" x-cloak
                            @click="showRecoveryInput = false; code = ''; recovery_code = ''; $dispatch('clear-auth-2fa-auth-code'); $nextTick(() => $dispatch('focus-auth-2fa-auth-code'))">login
                            using an authentication code</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.auth>
