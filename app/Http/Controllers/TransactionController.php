<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    /**
     * Kasir hanya melihat transaksi shift/miliknya sendiri.
     * Admin & Owner melihat seluruh transaksi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewAll = $user->hasPermission('transactions.view_all');

        $transactions = Transaction::query()
            ->with('cashier:id,name')
            ->when(! $canViewAll, fn ($q) => $q->forCashier($user->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('transacted_at', $request->date('date')))
            ->latest('transacted_at')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'canViewAll' => $canViewAll,
            'statuses' => TransactionStatus::cases(),
        ]);
    }

    public function show(Request $request, Transaction $transaction): View
    {
        $this->authorizeView($request, $transaction);

        return view('transactions.show', [
            'transaction' => $transaction->load(['details', 'cashier:id,name']),
        ]);
    }

    /** Pembatalan hanya oleh Admin. Data transaksi tidak dihapus permanen (soft status). */
    public function void(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'min:5', 'max:250'],
        ]);

        $this->checkout->void($transaction, $validated['void_reason']);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('status', 'Transaksi dibatalkan dan stok dikembalikan.');
    }

    private function authorizeView(Request $request, Transaction $transaction): void
    {
        $user = $request->user();

        abort_unless(
            $user->hasPermission('transactions.view_all') || $transaction->cashier_id === $user->id,
            403
        );
    }
}
