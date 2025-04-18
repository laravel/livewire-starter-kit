<?php

namespace App\Actions\TwoFactorAuth;

use PragmaRX\Google2FA\Google2FA;

class VerifyTwoFactorCode
{
    /**
     * Verify a two-factor authentication code.
     *
     * @param  string  $secret The decrypted secret key
     * @param  string  $code The code to verify
     * @return bool
     */
    public function __invoke(string $secret, string $code): bool
    {
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($secret, $code);
    }
}
