<?php

namespace App\Infrastructure\Authorization;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Service untuk mengecek permissions
 * Digunakan di UseCase untuk memastikan user punya permission sebelum execute business logic
 */
class PermissionGuard
{
    /**
     * Check if user has permission, throw exception if not
     *
     * @param Authenticatable|null $user
     * @param string|array $permissions - single permission atau array of permissions
     * @param string $message - custom error message
     *
     * @throws AuthorizationException
     */
    public static function check(
        ?Authenticatable $user,
        string|array $permissions,
        string|null $message = null
    ): void {
        if (!$user) {
            throw new AuthorizationException('Unauthenticated');
        }

        // Convert single permission to array
        $permissionsToCheck = is_string($permissions) ? [$permissions] : $permissions;

        // Check if user has ANY of the permissions
        $hasPermission = false;
        foreach ($permissionsToCheck as $permission) {
            if ($user->hasPermissionTo($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            $permissionText = is_string($permissions)
                ? $permissions
                : implode(' or ', $permissions);

            throw new AuthorizationException(
                $message ?? "You do not have permission to access: {$permissionText}"
            );
        }
    }

    /**
     * Check if user has all permissions
     *
     * @throws AuthorizationException
     */
    public static function checkAll(
        ?Authenticatable $user,
        array $permissions,
        string $message = null
    ): void {
        if (!$user) {
            throw new AuthorizationException('Unauthenticated');
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermissionTo($permission)) {
                throw new AuthorizationException(
                    $message ?? "You do not have permission to access: {$permission}"
                );
            }
        }
    }

    /**
     * Check permission and return boolean (untuk use case yang ingin handle sendiri)
     */
    public static function has(?Authenticatable $user, string|array $permissions): bool
    {
        if (!$user) {
            return false;
        }

        $permissionsToCheck = is_string($permissions) ? [$permissions] : $permissions;

        foreach ($permissionsToCheck as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }
}