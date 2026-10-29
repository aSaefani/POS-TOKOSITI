<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ProductRequest;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->search($request->string('q')->trim()->value())
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', ['products' => $products]);
    }

    public function create(): View
    {
        return view('products.form', ['product' => new Product]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $this->payload($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', ['product' => $product]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->payload($request);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Produk diperbarui.');
    }

    /** Soft delete: riwayat transaksi tetap utuh karena snapshot nama & harga disimpan. */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Produk dihapus.');
    }

    /**
     * Nilai checkbox & field opsional dinormalisasi eksplisit:
     * checkbox yang tidak dicentang tidak ikut dikirim browser.
     *
     * @return array<string, mixed>
     */
    private function payload(ProductRequest $request): array
    {
        return $request->safe()->except('image') + [
            'cost' => $request->integer('cost'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
