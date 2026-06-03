<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;
use App\Infrastructure\Persistence\Eloquent\Models\Payment;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItem;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovement;

class EloquentPosRepository implements PosRepositoryInterface
{
    public function findActiveSessionByCashierId(int $cashierId): ?CashierSession
    {
        return CashierSession::query()
            ->where('user_id', $cashierId)
            ->where('status', 'OPEN')
            ->latest('opened_at')
            ->first();
    }

    public function findActiveProductById(int $productId): ?Product
    {
        return Product::query()
            ->with(['category', 'rack'])
            ->where('is_active', true)
            ->find($productId);
    }

    public function getTodaySalesCount(): int
    {
        return Sale::query()
            ->whereDate('created_at', today())
            ->count();
    }

    public function generateSaleNumber(): string
    {
        return 'SALE-' . now()->format('Ymd-His') . '-' . random_int(1000, 9999);
    }

    public function createSale(array $data): Sale
    {
        return Sale::query()->create($data);
    }

    public function createSaleItem(array $data): SaleItem
    {
        return SaleItem::query()->create($data);
    }

    public function createPayment(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    public function updateProductStock(Product $product, int $stockAfter): Product
    {
        $product->forceFill([
            'current_stock' => $stockAfter,
        ])->save();

        return $product->refresh();
    }

    public function createStockMovement(array $data): StockMovement
    {
        return StockMovement::query()->create($data);
    }

    public function updateCashierSessionSummary(CashierSession $session, int $grandTotal): CashierSession
    {
        $session->forceFill([
            'cash_sales_total' => $session->cash_sales_total + $grandTotal,
            'transaction_count' => $session->transaction_count + 1,
            'expected_cash' => $session->expected_cash + $grandTotal,
        ])->save();

        return $session->refresh();
    }
}