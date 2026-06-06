<?php

namespace App\Application\Dashboard\UseCases;

use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;

class DashboardOwnerUseCase
{
    public function execute(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        /**
         * Base query untuk semua data dashboard.
         * Hanya transaksi PAID yang dihitung sebagai pendapatan.
         */
        $baseQuery = Sale::query()
            ->where('status', 'PAID');

        if ($startDate) {
            $baseQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $baseQuery->whereDate('created_at', '<=', $endDate);
        }

        /**
         * 1. Total transaksi PAID
         */
        $totalTransactions = (clone $baseQuery)->count();

        /**
         * 2. Total penjualan / revenue
         */
        $totalSales = (clone $baseQuery)->sum('grand_total');

        /**
         * 3. Tambahan insight: total user aktif
         */
        $totalActiveUsers = User::query()
            ->where('is_active', true)
            ->count();

        /**
         * 4. Grafik pendapatan per hari
         */
        $incomeGraph = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as date, SUM(grand_total) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'total' => (float) $item->total,
            ])
            ->values();

        /**
         * 5. Recent transaction
         */
        $recentTransactions = (clone $baseQuery)
            ->with(['cashier', 'items', 'payment'])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_sales' => (float) $totalSales,
                'total_active_users' => $totalActiveUsers,
            ],
            'income_graph' => $incomeGraph,
            'recent_transactions' => $recentTransactions,
        ];
    }
}