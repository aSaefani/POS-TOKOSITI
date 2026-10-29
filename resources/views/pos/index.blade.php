@extends('layouts.app')

@section('title', 'Kasir')
@section('heading', 'Kasir')
@section('subheading', 'Tekan / untuk cari · Enter untuk scan · F9 untuk bayar')

@section('content')
<div x-data="pos" class="grid gap-6 xl:grid-cols-[1fr_400px] xl:items-start">

    {{-- Katalog produk --}}
    <section class="min-w-0 space-y-4" aria-label="Katalog produk">
        <div class="card flex flex-col gap-3 p-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 fill-ink-500" viewBox="0 0 24 24"><path d="M10 2a8 8 0 0 1 6.3 12.9l5.4 5.4-1.4 1.4-5.4-5.4A8 8 0 1 1 10 2Zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z"/></svg>
                <input x-ref="search" x-model="query" @input="onSearch()" @keydown.enter.prevent="scanEnter()"
                       type="search" placeholder="Cari nama, SKU, atau scan barcode…" aria-label="Cari produk"
                       class="input pl-10">
            </div>
            <select x-model="category" @change="fetchProducts()" class="input sm:w-44" aria-label="Filter kategori">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>

        <p x-show="error" x-cloak role="alert" class="animate-fade-up rounded-xl bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-800" x-text="error"></p>

        {{-- Skeleton saat memuat --}}
        <div x-show="loading && products.length === 0" class="grid grid-cols-2 gap-3 md:grid-cols-3 2xl:grid-cols-4">
            @for ($i = 0; $i < 8; $i++)
                <div class="card space-y-3">
                    <div class="skeleton h-4 w-3/4"></div>
                    <div class="skeleton h-3 w-1/2"></div>
                    <div class="skeleton h-6 w-2/3"></div>
                </div>
            @endfor
        </div>

        <div class="stagger grid grid-cols-2 gap-3 md:grid-cols-3 2xl:grid-cols-4" x-show="!(loading && products.length === 0)">
            <template x-for="(product, idx) in products" :key="product.id">
                <button type="button" @click="add(product)" :disabled="product.stock <= 0"
                        :style="`--i:${Math.min(idx, 12)}`"
                        class="card-interactive group flex flex-col items-start gap-2 p-4 text-left disabled:opacity-50 disabled:hover:translate-y-0">
                    <span class="flex w-full items-start justify-between gap-2">
                        <span class="badge bg-white/80 text-ink-700" x-text="product.category"></span>
                        <span class="grid size-7 place-items-center rounded-lg bg-brand-600 text-white opacity-0 shadow-md transition duration-200 group-hover:opacity-100 group-focus-visible:opacity-100">+</span>
                    </span>
                    <img :src="product.image_url || '{{ asset('images/product-placeholder.svg') }}'" :alt="product.name" loading="lazy" class="aspect-square w-full rounded-xl bg-white/70 object-contain p-2">
                    <span class="line-clamp-2 min-h-[2.5rem] text-sm font-bold leading-snug" x-text="product.name"></span>
                    <span class="mt-auto flex w-full items-end justify-between gap-2">
                        <span class="text-base font-extrabold text-brand-700 tabular-nums" x-text="window.rupiah(product.price)"></span>
                        <span class="badge text-[11px]"
                              :class="product.stock <= product.stock_alert_threshold ? 'bg-amber-100 text-amber-800' : 'bg-white/70 text-ink-500'"
                              x-text="product.stock > 0 ? `Stok ${product.stock}` : 'Habis'"></span>
                    </span>
                </button>
            </template>
        </div>

        <div x-show="!loading && products.length === 0" x-cloak class="card py-12 text-center text-sm text-ink-500">
            Produk tidak ditemukan untuk pencarian ini.
        </div>
    </section>

    {{-- Keranjang & pembayaran --}}
    <aside class="glass-strong flex flex-col rounded-3xl p-5 xl:sticky xl:top-6 xl:max-h-[calc(100dvh-3rem)]" aria-label="Keranjang belanja">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-bold">Keranjang <span class="text-ink-500" x-text="`(${itemCount})`"></span></h2>
            <button type="button" x-show="cart.length" x-cloak @click="clear()" class="text-xs font-semibold text-rose-600 hover:underline">Kosongkan</button>
        </div>

        <div class="thin-scroll min-h-40 flex-1 space-y-2 overflow-y-auto pr-1">
            <template x-for="item in cart" :key="item.id">
                <div class="animate-fade-up flex items-center gap-3 rounded-2xl bg-white/60 p-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold" x-text="item.name"></p>
                        <p class="text-xs text-ink-500 tabular-nums" x-text="window.rupiah(item.price)"></p>
                    </div>
                    <div class="flex items-center rounded-xl bg-white/80 shadow-sm">
                        <button type="button" @click="setQty(item, item.quantity - 1)" class="size-8 rounded-l-xl text-lg transition hover:bg-brand-50" aria-label="Kurangi">−</button>
                        <input type="number" min="0" :max="item.stock" x-model.number="item.quantity" @change="setQty(item, $event.target.value)"
                               class="w-10 border-0 bg-transparent p-0 text-center text-sm font-bold tabular-nums focus:ring-0" aria-label="Jumlah">
                        <button type="button" @click="setQty(item, item.quantity + 1)" class="size-8 rounded-r-xl text-lg transition hover:bg-brand-50" aria-label="Tambah">+</button>
                    </div>
                </div>
            </template>
            <p x-show="cart.length === 0" class="grid place-items-center py-10 text-center text-sm text-ink-500">
                Keranjang kosong.<br>Pilih produk di sebelah kiri.
            </p>
        </div>

        <div class="mt-4 space-y-4 border-t border-white/60 pt-4">
            <div class="flex items-end justify-between">
                <span class="text-sm font-semibold text-ink-500">Total</span>
                <span class="text-3xl font-extrabold tracking-tight tabular-nums" x-text="window.rupiah(total)"></span>
            </div>

            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Metode pembayaran">
                @foreach ($paymentMethods as $method)
                    <label class="cursor-pointer">
                        <input type="radio" name="payment" value="{{ $method['value'] }}" x-model="paymentMethod" class="peer sr-only">
                        <span class="block rounded-xl border border-white/80 bg-white/60 py-2.5 text-center text-xs font-semibold transition duration-200 peer-checked:border-brand-500 peer-checked:bg-brand-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500">
                            {{ $method['label'] }}
                        </span>
                    </label>
                @endforeach
            </div>

            <div>
                <label for="paid" class="label">Nominal bayar</label>
                <input id="paid" type="text" inputmode="numeric" x-model="paidInput" placeholder="0" class="input text-right text-lg font-bold tabular-nums">
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="amount in quickAmounts()" :key="amount">
                        <button type="button" @click="paidInput = String(amount)" class="btn-ghost px-3 py-1.5 text-xs" x-text="window.rupiah(amount)"></button>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between rounded-2xl bg-emerald-50/80 px-4 py-3">
                <span class="text-sm font-semibold text-emerald-800">Kembalian</span>
                <span class="text-lg font-extrabold text-emerald-700 tabular-nums" x-text="window.rupiah(change)"></span>
            </div>

            <p x-show="error" x-cloak role="alert" class="text-xs font-medium text-rose-700" x-text="error"></p>

            <button type="button" @click="checkout()" :disabled="!canCheckout" class="btn-primary w-full py-3.5 text-base">
                <span x-show="!processing">Bayar (F9)</span>
                <span x-show="processing" x-cloak>Memproses…</span>
            </button>
        </div>
    </aside>

    {{-- Struk berhasil --}}
    <div x-show="receipt" x-cloak x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-slate-900/30 p-4 backdrop-blur-sm">
        <div x-show="receipt" x-transition:enter="transition duration-300 ease-glass" x-transition:enter-start="opacity-0 scale-95"
             class="glass-strong animate-pop w-full max-w-sm rounded-3xl p-7 text-center" role="dialog" aria-modal="true" aria-labelledby="receipt-title">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-100 text-2xl text-emerald-700">✓</span>
            <h2 id="receipt-title" class="mt-4 text-lg font-extrabold">Transaksi berhasil</h2>
            <p class="font-mono text-xs text-ink-500" x-text="receipt?.invoice"></p>

            <dl class="mt-6 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-500">Total</dt><dd class="font-semibold tabular-nums" x-text="window.rupiah(receipt?.total)"></dd></div>
                <div class="flex justify-between"><dt class="text-ink-500">Dibayar</dt><dd class="font-semibold tabular-nums" x-text="window.rupiah(receipt?.paid)"></dd></div>
                <div class="flex justify-between text-base"><dt class="font-bold">Kembali</dt><dd class="font-extrabold text-emerald-700 tabular-nums" x-text="window.rupiah(receipt?.change)"></dd></div>
            </dl>

            <button type="button" @click="closeReceipt()" class="btn-primary mt-7 w-full">Transaksi baru</button>
        </div>
    </div>
</div>
@endsection
