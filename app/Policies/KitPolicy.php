<?php

namespace App\Policies;

use App\Models\Kit;
use App\Models\User;

class KitPolicy
{
    /**
     * Determine whether the user can view any kits.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_materials_area') 
            || $user->hasPermissionTo('manage_kits')
            || $user->hasPermissionTo('view_quality_area');
    }

    /**
     * Determine whether the user can view the kit.
     */
    public function view(User $user, Kit $kit): bool
    {
        return $user->hasPermissionTo('view_materials_area') 
            || $user->hasPermissionTo('manage_kits')
            || $user->hasPermissionTo('view_quality_area');
    }

    /**
     * Determine whether the user can create kits.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_kits');
    }

    /**
     * Determine whether the user can update the kit.
     */
    public function update(User $user, Kit $kit): bool
    {
        // Can only update if user has permission and kit can be edited
        return $user->hasPermissionTo('manage_kits') && $kit->canBeEdited();
    }

    /**
     * Determine whether the user can delete the kit.
     */
    public function delete(User $user, Kit $kit): bool
    {
        // Can only delete if user has permission and kit can be deleted
        return $user->hasPermissionTo('manage_kits') && $kit->canBeDeleted();
    }

    /**
     * Determine whether the user can submit the kit to quality.
     */
    public function submitToQuality(User $user, Kit $kit): bool
    {
        return $user->hasPermissionTo('submit_to_quality') 
            && $kit->status === Kit::STATUS_PREPARING;
    }

    /**
     * Determine whether the user can approve the kit.
     */
    public function approve(User $user, Kit $kit): bool
    {
        return $user->hasPermissionTo('approve_kits') 
            && $kit->status === Kit::STATUS_READY;
    }

    /**
     * Determine whether the user can reject the kit.
     */
    public function reject(User $user, Kit $kit): bool
    {
        return $user->hasPermissionTo('reject_kits') 
            && $kit->status === Kit::STATUS_READY;
    }
}
