<?php

namespace App\Domain\User\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function deactivate(User $user): User;
}