<?php

namespace App\Domain\Sales\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItem;

interface SalesRepositoryInterface
{
    public function listSales(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): ?Sale;

    public function createSaleItem(array $data): SaleItem;

    public function findItemById(int $saleId, int $itemId): ?SaleItem;

    public function updateSaleItem(SaleItem $item, array $data): SaleItem;

    public function recalculateSaleTotals(Sale $sale): Sale;

    public function findBySaleNumber(string $saleNumber): ?Sale;
    /**
     * Ambil semua sale yang status PENDING_PAYMENT
     */
    public function listPendingPaymentSales(): iterable;
}
