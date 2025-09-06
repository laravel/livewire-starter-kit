<?php

namespace App\Support;

use Illuminate\Support\Str;

final class Sidebar
{
    public static function isFluxIcon(array $item): bool
    {
        $icon = (string) ($item['icon'] ?? '');
        return $icon !== '' && ! Str::startsWith($icon, ['fa ', 'fa-', 'fa-solid', 'fa-regular', 'fa-light', 'fa-thin', 'fa-duotone', 'fas ', 'far ']);
    }

    public static function href(array $item): string
    {
        $url = $item['url'] ?? '#';

        // If it's a route name (no scheme, no slash, no hash), try route():
        if ($url && ! Str::startsWith($url, ['http://', 'https://', '/', '#'])) {
            try {
                return route($url);
            } catch (\Throwable) {
                // Fall through to raw value if route doesn't exist
            }
        }

        return $url;
    }

    public static function currentPattern(array $item): string
    {
        return (string) ($item['current'] ?? ($item['url'] ?? ''));
    }

    public static function shouldRender(array $item): bool
    {
        return PermissionGate::allows($item['permission'] ?? null);
    }
}
