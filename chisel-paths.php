<?php

return [
    'welcome' => 'resources/views/welcome.blade.php',
    'login' => 'resources/views/livewire/auth/login.blade.php',
    'register' => 'resources/views/livewire/auth/register.blade.php',
    'confirm_password' => 'resources/views/livewire/auth/confirm-password.blade.php',
    'verify_email' => 'resources/views/livewire/auth/verify-email.blade.php',
    'two_factor_challenge' => 'resources/views/livewire/auth/two-factor-challenge.blade.php',

    'profile_files' => [
        'app/Livewire/Settings/Profile.php',
        'resources/views/livewire/settings/profile.blade.php',
    ],

    'security_files' => [
        'app/Livewire/Settings/Security.php',
        'resources/views/livewire/settings/security.blade.php',
    ],

    'two_factor_files' => [
        'app/Livewire/Settings/TwoFactor/RecoveryCodes.php',
        'resources/views/livewire/settings/two-factor/recovery-codes.blade.php',
    ],
];
