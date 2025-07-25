<?php

namespace App\Actions\TwoFactorAuth;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class GenerateQrCodeAndSecretKey
{
    /**
     * The length of the secret key to generate.
     */
    private static int $SECRET_KEY_LENGTH = 16;
    
    public string $companyName;

    /**
     * Generate a QR code image and secret key for the user.
     *
     * @return array{string, string}
     */
    public function __invoke($user): array
    {
        $google2fa = new Google2FA;
        $secret_key = $google2fa->generateSecretKey(self::$SECRET_KEY_LENGTH);

        $this->companyName = 'Auth';

        if (is_string(config('app.name'))) {
            $this->companyName = config('app.name');
        }

        $g2faUrl = $google2fa->getQRCodeUrl(
            $this->companyName,
            (string) $user->email,
            $secret_key
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd()
            )
        );

        $qrcode_image = base64_encode($writer->writeString($g2faUrl));

        return [$qrcode_image, $secret_key];
    }
}
