<?php

namespace App\Http\Controllers\Pos;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CheckoutRequest;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function index(): View
    {
        return view('pos.index', [
            'categories' => Product::query()->where('is_active', true)->distinct()->orderBy('category')->pluck('category'),
            'paymentMethods' => collect(PaymentMethod::cases())->map(fn ($m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
        ]);
    }

    /** Pencarian produk untuk grid kasir (nama, SKU, atau barcode hasil scan). */
    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
        ]);

        $products = Product::query()
            ->where('is_active', true)
            ->search($request->string('q')->trim()->value())
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderBy('name')
            ->limit(60)
            ->get(['id', 'sku', 'barcode', 'name', 'category', 'price', 'stock', 'stock_alert_threshold', 'image_path']);

        return response()->json(['data' => $products]);
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $payload = $request->payload();

        $transaction = $this->checkout->checkout(
            $request->user(),
            $payload['items'],
            $payload['payment_method'],
            $payload['amount_paid'],
        );

        return response()->json([
            'message' => 'Transaksi berhasil.',
            'data' => [
                'invoice' => $transaction->invoice_number,
                'total' => $transaction->total,
                'paid' => $transaction->amount_paid,
                'change' => $transaction->change_amount,
            ],
        ], 201);
    }
}
