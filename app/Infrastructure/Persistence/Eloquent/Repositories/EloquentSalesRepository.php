<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentSalesRepository implements SalesRepositoryInterface
{
    public function listSales(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Sale::query()->with(['items', 'payment', 'cashier']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('sale_number', 'like', "%{$filters['search']}%")
                    ->orWhere('order_code', 'like', "%{$filters['search']}%")
                    ->orWhere('customer_name', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['cashier_id'])) {
            $query->where('cashier_id', $filters['cashier_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }
    public function findById(int $id): ?Sale
    {
        return Sale::query()
            ->with([
                'items',
                'payment',
                'cashier',
                'cashierSession',
            ])
            ->find($id);
    }

    public function createSaleItem(array $data): SaleItem
    {
        return SaleItem::query()->create($data);
    }

    public function findItemById(int $saleId, int $itemId): ?SaleItem
    {
        return SaleItem::query()
            ->where('sale_id', $saleId)
            ->where('id', $itemId)
            ->first();
    }

    public function updateSaleItem(SaleItem $item, array $data): SaleItem
    {
        $item->forceFill($data)->save();

        return $item->refresh();
    }

    public function recalculateSaleTotals(Sale $sale): Sale
    {
        $items = $sale->items()->get();

        $subtotal = (float) $items->sum(function ($item) {
            return (float) $item->selling_price * (int) $item->quantity;
        });

        $discountTotal = (float) $items->sum('discount_amount');

        $sale->forceFill([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => 0,
            'grand_total' => $subtotal - $discountTotal,
        ])->save();

        return $sale->refresh();
    }
}
