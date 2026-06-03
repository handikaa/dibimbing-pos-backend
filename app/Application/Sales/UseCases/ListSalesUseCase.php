<?php

namespace App\Application\Sales\UseCases;

use App\Application\Sales\DTOs\ListSalesDTO;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListSalesUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $repository
    ) {}

    public function execute(ListSalesDTO $dto): LengthAwarePaginator
    {
        return $this->repository->listSales([
            'search' => $dto->search,
            'status' => $dto->status,
            'payment_method' => $dto->paymentMethod,
            'cashier_id' => $dto->cashierId,
            'date_from' => $dto->dateFrom,
            'date_to' => $dto->dateTo,
        ], $dto->perPage);
    }
}