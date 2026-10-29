<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(): View
    {
        return view('dashboard.index', [
            'summary' => $this->dashboard->summary(),
        ]);
    }

    /** Endpoint JSON untuk polling aktivitas & grafik (dipanggil Alpine.js). */
    public function feed(Request $request): JsonResponse
    {
        $validated = $request->validate(['period' => ['nullable', 'in:week,month,year']]);

        return response()->json([
            'trend' => $this->dashboard->salesTrend($validated['period'] ?? 'week'),
            'recent' => $this->dashboard->recentTransactions(5)->map(fn ($t) => [
                'invoice' => $t->invoice_number,
                'cashier' => $t->cashier?->name,
                'total' => $t->total,
                'status' => $t->status->value,
                'at' => $t->transacted_at->diffForHumans(),
            ]),
            'low_stock' => $this->dashboard->lowStock(8),
        ]);
    }
}
