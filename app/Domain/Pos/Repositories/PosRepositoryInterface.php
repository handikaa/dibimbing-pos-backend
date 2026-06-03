<?php

namespace App\Domain\Pos\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItem;
use App\Infrastructure\Persistence\Eloquent\Models\Payment;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovement;

interface PosRepositoryInterface
{
    public function findActiveSessionByCashierId(int $cashierId): ?CashierSession;

    public function findActiveProductById(int $productId): ?Product;

    public function getTodaySalesCount(): int;

    public function generateSaleNumber(): string;

    public function createSale(array $data): Sale;

    public function createSaleItem(array $data): SaleItem;

    public function createPayment(array $data): Payment;

    public function updateProductStock(Product $product, int $stockAfter): Product;

    public function createStockMovement(array $data): StockMovement;

    public function updateCashierSessionSummary(CashierSession $session, int $grandTotal): CashierSession;
}