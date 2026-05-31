<?php
namespace App\Domain\CashierSession\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;
use Illuminate\Support\Collection;

interface CashierSessionRepositoryInterface
{
    public function getActiveSession(int $userId): ?CashierSession;
    public function createSession(array $data): CashierSession;
    public function closeSession(CashierSession $session, array $data): CashierSession;
    public function listSessions(array $filters = []): Collection;
}