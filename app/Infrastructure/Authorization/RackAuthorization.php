<?php

namespace App\Infrastructure\Authorization;

use Illuminate\Contracts\Auth\Authenticatable;

class RackAuthorization
{
    /**
     * Check if user can manage racks (create, update, delete)
     * Only OWNER and ADMIN allowed
     */
    public static function canManage(?Authenticatable $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole('OWNER') || $user->hasRole('ADMIN');
    }

    /**
     * Check if user can view racks
     * All authenticated users can view
     */
    public static function canView(?Authenticatable $user): bool
    {
        return $user !== null;
    }
}