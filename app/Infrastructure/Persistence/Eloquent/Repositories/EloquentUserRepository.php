<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function ($query, string $role) {
                $query->role($role);
            })
            ->when(isset($filters['is_active']), function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::query()
            ->with('roles')
            ->find($id);
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh()->load('roles');
    }

    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);

        return $user->refresh()->load('roles');
    }
}