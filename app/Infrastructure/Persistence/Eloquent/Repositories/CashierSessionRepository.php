<?php
namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;
use Illuminate\Support\Collection;

class CashierSessionRepository implements CashierSessionRepositoryInterface
{
    public function __construct(private readonly CashierSession $model) {}

    // Mengambil session aktif user
    public function getActiveSession(int $userId): ?CashierSession
    {
        return $this->model->where('user_id', $userId)
                           ->where('status', 'OPEN')
                           ->first();
    }

    // Membuat session baru
    public function createSession(array $data): CashierSession
    {
        return $this->model->create($data);
    }

    // Menutup session
    public function closeSession(CashierSession $session, array $data): CashierSession
    {
        $session->update($data);
        return $session;
    }

    // List semua session dengan filter
    public function listSessions(array $filters = []): Collection
    {
        $query = $this->model->query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('opened_at', 'desc')->get();
    }
}