<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function paginate(array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Filter search by name or email
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // Filter by role
        if (! empty($filters['role'])) {
            $role = $filters['role'];
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Laravel pagination
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id, ['*']); // Ensure all columns are selected
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function deactivate(User $user): User
    {
        $user->update(['is_active' => 0]);

        return $user;
    }

    public function findByActivationToken(string $token): ?User
    {
        return User::where('activation_token', $token)->first();
    }

    public function activate(User $user): User
    {
        $user->is_active = true;
        $user->activation_token = null;
        $user->save();

        return $user;
    }
}
