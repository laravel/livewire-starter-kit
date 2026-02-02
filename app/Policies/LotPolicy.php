<?php

namespace App\Policies;

use App\Models\Lot;
use App\Models\User;

class LotPolicy
{
    /**
     * Determine whether the user can view any lots.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_materials_area') || $user->hasPermissionTo('manage_lots');
    }

    /**
     * Determine whether the user can view the lot.
     */
    public function view(User $user, Lot $lot): bool
    {
        return $user->hasPermissionTo('view_materials_area') || $user->hasPermissionTo('manage_lots');
    }

    /**
     * Determine whether the user can create lots.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_lots');
    }

    /**
     * Determine whether the user can update the lot.
     */
    public function update(User $user, Lot $lot): bool
    {
        return $user->hasPermissionTo('manage_lots');
    }

    /**
     * Determine whether the user can delete the lot.
     */
    public function delete(User $user, Lot $lot): bool
    {
        // Can only delete if user has permission and lot can be deleted
        return $user->hasPermissionTo('manage_lots') && $lot->canBeDeleted();
    }
}
