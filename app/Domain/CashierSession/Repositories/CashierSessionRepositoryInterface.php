<?php

namespace App\Domain\CashierSession\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


interface CashierSessionRepositoryInterface
{
    /**
     * Create new session
     *
     * @return CashierSession
     */
    public function create(array $data): CashierSession;

    /**
     * Get active session by user ID
     *
     * @return CashierSession|null
     */
    public function getActiveSessionByUserId(int $userId): ?CashierSession;

    /**
     * Find session by ID
     *
     * @return CashierSession|null
     */
    public function findById(int $id): ?CashierSession;

    /**
     * Find session by session code
     *
     * @return CashierSession|null
     */
    public function findBySessionCode(string $code): ?CashierSession;

    /**
     * Update session
     *
     * @return CashierSession
     */
    public function update(CashierSession $session, array $data): CashierSession;

    /**
     * Close session
     *
     * @return CashierSession
     */
    public function closeSession(CashierSession $session, array $data): CashierSession;

    /**
     * Get all sessions with pagination
     *
     * @return Paginator
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
}