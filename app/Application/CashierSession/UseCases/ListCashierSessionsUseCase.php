<?php

namespace App\Application\CashierSession\UseCases;

use App\Application\CashierSession\DTOs\ListCashierSessionsDTO;
use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListCashierSessionsUseCase
{
    public function __construct(
        private readonly CashierSessionRepositoryInterface $repository
    ) {}

    public function execute(ListCashierSessionsDTO $dto): LengthAwarePaginator
    {
        // Bisa ditambahkan guard logic tambahan jika perlu
        return $this->repository->paginate($dto->perPage, [
            'user_id' => $dto->userId,
            'status' => $dto->status,
            'date_from' => $dto->dateFrom,
            'date_to' => $dto->dateTo,
        ]);
    }
}