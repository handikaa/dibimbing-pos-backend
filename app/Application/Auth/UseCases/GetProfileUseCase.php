<?php

namespace App\Application\Auth\UseCases;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Domain\Exceptions\UnauthorizedSessionAccessException;

class GetProfileUseCase
{
    /**
     * Get user profile with permissions and roles
     *
     * @param User $user
     * @return array
     * @throws UnauthorizedSessionAccessException
     */
    public function execute(User $user): array
    {
        // Check permission
        if (!$user->hasPermissionTo('profile.view')) {
            throw new UnauthorizedSessionAccessException();
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'role' => $user->getRoleNames()->first(),
            'permissions' => $user->getAllPermissions()
                ->pluck('name')
                ->values(),
        ];
    }
}