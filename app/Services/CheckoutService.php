<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Seluruh logika checkout ada di sini (bukan di controller).
 * Stok dikunci dengan lockForUpdate() di dalam DB transaction agar
 * dua kasir yang checkout bersamaan tidak membuat stok minus.
 */
class CheckoutService
{
    /**
     * @param  array<int, array{product_id:int, quantity:int}>  $items
     */
    public function checkout(User $cashier, array $items, PaymentMethod $method, int $amountPaid): Transaction
    {
        return DB::transaction(function () use ($cashier, $items, $method, $amountPaid) {
            // Gabungkan baris produk yang sama agar cek stok memakai total permintaan, bukan per baris.
            $requested = collect($items)
                ->groupBy('product_id')
                ->map(fn ($rows, $productId) => [
                    'product_id' => (int) $productId,
                    'quantity' => (int) $rows->sum('quantity'),
                ])
                ->values();

            // Kunci baris produk; urutkan id agar tidak deadlock antar transaksi.
            $products = Product::query()
                ->whereIn('id', $requested->pluck('product_id'))
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0;

            foreach ($requested as $item) {
                $product = $products->get($item['product_id']);
                $qty = $item['quantity'];

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk tidak ditemukan atau nonaktif.',
                    ]);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tersisa {$product->stock}.",
                    ]);
                }

                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ];
            }

            $total = $subtotal; // diskon bisa ditambahkan di sini sebagai satu titik perubahan

            if ($amountPaid < $total) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Nominal bayar kurang dari total.',
                ]);
            }

            $transaction = Transaction::create([
                'invoice_number' => $this->invoiceNumber(),
                'cashier_id' => $cashier->id,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change_amount' => $amountPaid - $total,
                'payment_method' => $method,
                'status' => TransactionStatus::Completed,
                'transacted_at' => now(),
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];

                $transaction->details()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'unit_cost' => $product->cost,
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);

                $product->decrement('stock', $line['quantity']);
            }

            return $transaction->load('details');
        });
    }

    /** Admin only: membatalkan transaksi dan mengembalikan stok. Data tidak dihapus. */
    public function void(Transaction $transaction, string $reason): Transaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === TransactionStatus::Voided) {
                throw ValidationException::withMessages(['status' => 'Transaksi sudah dibatalkan.']);
            }

            foreach ($transaction->details()->get() as $detail) {
                Product::withTrashed()->whereKey($detail->product_id)->increment('stock', $detail->quantity);
            }

            $transaction->update([
                'status' => TransactionStatus::Voided,
                'void_reason' => Str::limit($reason, 250),
            ]);

            return $transaction;
        });
    }

    private function invoiceNumber(): string
    {
        // Format: INV-YYYYMMDD-XXXXXX (acak, tidak berurutan agar tidak bisa ditebak)
        return 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
