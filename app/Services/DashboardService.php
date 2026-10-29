<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Agregasi dashboard. Hasil di-cache 60 detik: cukup segar untuk monitoring,
 * dan mengurangi beban query saat banyak kasir membuka dashboard bersamaan.
 */
class DashboardService
{
    public function summary(): array
    {
        return Cache::remember('dashboard.summary.'.now()->format('YmdHi'), 60, function () {
            $today = Transaction::query()->completed()->whereDate('transacted_at', today());

            $monthly = Transaction::query()
                ->completed()
                ->whereBetween('transacted_at', [now()->startOfMonth(), now()->endOfMonth()]);

            return [
                'today_sales' => (int) (clone $today)->sum('total'),
                'today_transactions' => (int) (clone $today)->count(),
                'today_items_sold' => (int) TransactionDetail::query()
                    ->whereHas('transaction', fn ($q) => $q->completed()->whereDate('transacted_at', today()))
                    ->sum('quantity'),
                'monthly_revenue' => (int) (clone $monthly)->sum('total'),
            ];
        });
    }

    /** Tren penjualan untuk minggu, bulan, atau tahun berjalan, termasuk periode kosong. */
    public function salesTrend(string $period = 'week'): array
    {
        $today = now()->startOfDay();
        [$start, $count, $step, $format] = match ($period) {
            'month' => [$today->copy()->startOfMonth(), $today->daysInMonth, 'day', 'j'],
            'year' => [$today->copy()->startOfYear(), 12, 'month', 'M'],
            default => [$today->copy()->startOfWeek(), 7, 'day', 'D'],
        };
        $end = match ($period) {
            'month' => $start->copy()->endOfMonth()->endOfDay(),
            'year' => $start->copy()->endOfYear()->endOfDay(),
            default => $start->copy()->endOfWeek()->endOfDay(),
        };

        $rows = Transaction::query()
            ->completed()
            ->whereBetween('transacted_at', [$start, $end])
            ->selectRaw('DATE(transacted_at) as period_date, SUM(total) as revenue, COUNT(*) as trx')
            ->groupBy('period_date')
            ->get()
            ->keyBy('period_date');

        $daily = collect(range(0, $period === 'year' ? $start->daysInYear - 1 : $count - 1))
            ->map(function (int $i) use ($start, $rows, $period) {
                $date = $start->copy()->addDays($i);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'revenue' => (int) ($rows[$key]->revenue ?? 0),
                    'transactions' => (int) ($rows[$key]->trx ?? 0),
                ];
            });

        if ($period === 'year') {
            return collect(range(0, 11))->map(function (int $i) use ($start, $daily) {
                $date = $start->copy()->addMonths($i);
                $key = $date->format('Y-m');
                $month = $daily->filter(fn (array $day) => str_starts_with($day['date'], $key));

                return [
                    'label' => $date->translatedFormat('M'),
                    'date' => $key,
                    'revenue' => (int) $month->sum('revenue'),
                    'transactions' => (int) $month->sum('transactions'),
                ];
            })->all();
        }

        return $daily->map(function (array $day) use ($format) {
            $day['label'] = Carbon::parse($day['date'])->translatedFormat($format);

            return $day;
        })->all();
    }

    public function lowStock(int $limit = 8)
    {
        return Product::query()
            ->where('is_active', true)
            ->lowStock()
            ->orderBy('stock')
            ->limit($limit)
            ->get(['id', 'name', 'sku', 'stock', 'stock_alert_threshold']);
    }

    public function recentTransactions(int $limit = 5)
    {
        return Transaction::query()
            ->with('cashier:id,name')
            ->latest('transacted_at')
            ->limit($limit)
            ->get();
    }
}
