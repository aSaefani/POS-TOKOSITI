@extends('layouts.app')

@section('title', 'Produk')
@section('heading', 'Produk')
@section('subheading', 'Kelola katalog, harga, dan stok.')

@section('actions')
    <a href="{{ route('products.create') }}" class="btn-primary">+ Tambah produk</a>
@endsection

@section('content')
<section class="space-y-4">
    <form method="GET" class="card flex flex-col gap-3 p-3 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, SKU, atau barcode…" class="input sm:flex-1" aria-label="Cari produk">
        <label class="flex items-center gap-2 whitespace-nowrap text-sm text-ink-700">
            <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock')) class="rounded border-white text-brand-600 focus:ring-brand-500">
            Stok menipis saja
        </label>
        <button class="btn-ghost">Cari</button>
    </form>

    <div class="card overflow-hidden p-0">
        <div class="table-glass overflow-x-auto">
            <table class="w-full min-w-[46rem]">
                <thead>
                    <tr>
                        <th scope="col">Produk</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Kategori</th>
                        <th scope="col" class="text-right">Harga</th>
                        <th scope="col" class="text-right">Stok</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><div class="flex items-center gap-3"><img src="{{ $product->image_url ?: asset('images/product-placeholder.svg') }}" alt="{{ $product->name }}" loading="lazy" class="size-12 rounded-xl bg-white/70 object-contain p-1"><span class="font-semibold text-ink-900">{{ $product->name }}</span></div></td>
                            <td class="font-mono text-xs">{{ $product->sku }}</td>
                            <td>{{ $product->category }}</td>
                            <td class="text-right tabular-nums">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="text-right font-semibold tabular-nums">{{ $product->stock }}</td>
                            <td>
                                @if ($product->stock === 0)
                                    <x-badge tone="danger">Habis</x-badge>
                                @elseif ($product->is_low_stock)
                                    <x-badge tone="warning">Menipis</x-badge>
                                @else
                                    <x-badge tone="success">Aman</x-badge>
                                @endif
                            </td>
                            <td class="space-x-3 whitespace-nowrap text-right">
                                <a href="{{ route('products.edit', $product) }}" class="font-semibold text-brand-700 hover:underline">Ubah</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline" x-data
                                      x-on:submit="if (!window.confirm('Hapus produk ini dari katalog? Riwayat transaksi tetap aman.')) $event.preventDefault()">
                                    @csrf @method('DELETE')
                                    <button class="font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-ink-500">Produk tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/60 px-4 py-3">{{ $products->links() }}</div>
    </div>
</section>
@endsection
