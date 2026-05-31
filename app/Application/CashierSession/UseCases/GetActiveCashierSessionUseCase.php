<?php
namespace App\Application\CashierSession\UseCases;

use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;

class GetActiveCashierSessionUseCase
{
    public function __construct(
        private readonly CashierSessionRepositoryInterface $repository
    ) {}

    public function execute(int $userId): ?CashierSession
    {
        return $this->repository->getActiveSession($userId);
    }
}