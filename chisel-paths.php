<?php

return [
    'welcome' => 'resources/views/welcome.blade.php',
    'login' => 'resources/views/pages/auth/login.blade.php',
    'register' => 'resources/views/pages/auth/register.blade.php',
    'confirm_password' => 'resources/views/pages/auth/confirm-password.blade.php',
    'verify_email' => 'resources/views/pages/auth/verify-email.blade.php',
    'two_factor_challenge' => 'resources/views/pages/auth/two-factor-challenge.blade.php',

    'profile_files' => [
        'resources/views/pages/settings/profile.blade.php',
    ],

    'security_files' => [
        'resources/views/pages/settings/security.blade.php',
    ],

    'two_factor_files' => [
        'resources/views/pages/settings/two-factor-setup-modal.blade.php',
        'resources/views/pages/settings/two-factor/recovery-codes.blade.php',
    ],
];
