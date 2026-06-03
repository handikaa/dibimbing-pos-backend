<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;
use Illuminate\Pagination\Paginator;
 use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCashierSessionRepository implements CashierSessionRepositoryInterface
{
    public function __construct(
        private readonly CashierSession $model
    ) {}

    /**
     * Create new session
     */
    public function create(array $data): CashierSession
    {
        return $this->model->create($data);
    }

    /**
     * Get active session by user ID
     */
    public function getActiveSessionByUserId(int $userId): ?CashierSession
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', 'OPEN')
            ->first();
    }

    /**
     * Find session by ID
     */
    public function findById(int $id): ?CashierSession
    {
        return $this->model->find($id);
    }

    /**
     * Find session by session code
     */
    public function findBySessionCode(string $code): ?CashierSession
    {
        return $this->model
            ->where('session_code', $code)
            ->first();
    }

    /**
     * Update session
     */
    public function update(CashierSession $session, array $data): CashierSession
    {
        $session->update($data);
        return $session->fresh();
    }

    /**
     * Close session
     */
    public function closeSession(CashierSession $session, array $data): CashierSession
    {
        $session->update($data);
        return $session->fresh();
    }

    /**
     * Get all sessions with pagination
     */
   

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model;

        // filter by user_id
        if (isset($filters['user_id'])) {
            $query = $query->where('user_id', $filters['user_id']);
        }

        // filter by status
        if (isset($filters['status'])) {
            $query = $query->where('status', $filters['status']);
        }

        // filter by date range
        if (isset($filters['date_from'])) {
            $query = $query->whereDate('opened_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query = $query->whereDate('opened_at', '<=', $filters['date_to']);
        }

        // Eloquent paginate() sudah mengembalikan LengthAwarePaginator
        return $query->orderBy('opened_at', 'desc')->paginate($perPage);
    }
}
