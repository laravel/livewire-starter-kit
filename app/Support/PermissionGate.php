<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class PermissionGate
{
    /**
     * Check an ability against whichever permission system is present.
     *
     * Drivers:
     * - laratrust: uses ->isAbleTo($ability)
     * - spatie:   uses ->hasPermissionTo($ability) OR ->can($ability)
     * - gate:     uses Gate::allows($ability)
     *
     * If $ability is null, we allow by default.
     */
    public static function allows(?string $ability, ?Authenticatable $user = null): bool
    {
        if ($ability === null) {
            return true;
        }

        $user ??= Auth::user();
        if (! $user) {
            return false;
        }

        // Forced driver via config if you want to pin it:
        $driver = config('ui.driver', 'auto');

        if ($driver === 'auto') {
            $driver = self::detectDriver($user);
        }

        return match ($driver) {
            'laratrust' => method_exists($user, 'isAbleTo') && $user->isAbleTo($ability),
            'spatie'    => method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($ability) ?: $user->can($ability),
            'gate'      => Gate::allows($ability),
            default     => $user->can($ability) ?: Gate::allows($ability),
        };
    }

    protected static function detectDriver(Authenticatable $user): string
    {
        if (method_exists($user, 'isAbleTo')) {
            return 'laratrust';
        }

        if (method_exists($user, 'hasPermissionTo')) {
            return 'spatie';
        }

        return 'gate';
    }
}
