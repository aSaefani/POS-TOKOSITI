@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Ringkasan Hari Ini')
@section('subheading', 'Pantau penjualan, stok, dan aktivitas toko secara langsung.')

@section('content')
<div x-data="dashboard" class="space-y-6">

    {{-- Kartu ringkasan --}}
    <section class="stagger grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan">
        <x-stat-card label="Penjualan hari ini" :value="'Rp '.number_format($summary['today_sales'], 0, ',', '.')" style="--i:0">
            <x-slot:icon><svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M11 17.9V19h2v-1.1c1.7-.3 3-1.4 3-3.1 0-2-1.7-2.7-3.4-3.2C11.4 12.3 11 12 11 11.1c0-.9.8-1.6 2-1.6s1.8.7 1.9 1.5h1.8c-.1-1.5-1.1-2.7-2.7-3V6h-2v1.2c-1.6.3-2.9 1.3-2.9 3 0 1.9 1.6 2.6 3.2 3 1.5.4 2.1.9 2.1 1.7 0 .9-.7 1.6-2.1 1.6-1.5 0-2.1-.7-2.2-1.5H8c.1 1.7 1.3 2.7 3 3.1Z"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Total transaksi" :value="number_format($summary['today_transactions'])" hint="Transaksi berstatus selesai" style="--i:1">
            <x-slot:icon><svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2Zm8 1.5V8h4.5L14 3.5Z"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Produk terjual" :value="number_format($summary['today_items_sold']).' pcs'" style="--i:2">
            <x-slot:icon><svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M12 2 3 7v10l9 5 9-5V7l-9-5Z"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Pendapatan bulan ini" :value="'Rp '.number_format($summary['monthly_revenue'], 0, ',', '.')" style="--i:3">
            <x-slot:icon><svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M3 17h2v4H3v-4Zm4-4h2v8H7v-8Zm4-4h2v12h-2V9Zm4-4h2v16h-2V5Zm4-2h2v18h-2V3Z"/></svg></x-slot:icon>
        </x-stat-card>
    </section>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Grafik tren --}}
        <section class="card xl:col-span-2 animate-fade-up" aria-labelledby="trend-title">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="trend-title" class="font-bold">Tren penjualan</h2>
                    <p class="text-xs text-ink-500">Pendapatan untuk periode yang dipilih</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label for="sales-period" class="sr-only">Periode grafik penjualan</label>
                    <select id="sales-period" class="input sm:w-36" :value="period" @change="changePeriod($event.target.value)">
                        <option value="week">Minggu ini</option>
                        <option value="month">Bulan ini</option>
                        <option value="year">Tahun ini</option>
                    </select>
                    <span class="text-xs text-ink-500" x-show="lastUpdated" x-cloak>Diperbarui <span x-text="lastUpdated"></span></span>
                </div>
            </div>
            <div class="relative h-64">
                <div x-show="loading" class="absolute inset-0 skeleton"></div>
                <canvas x-ref="trend" x-show="!loading" role="img" aria-label="Grafik tren penjualan 7 hari terakhir"></canvas>
            </div>
            <p x-show="error" x-cloak class="mt-3 text-xs font-medium text-amber-700" x-text="error"></p>
            @if (auth()->user()->hasPermission('reports.view'))
                <form method="GET" action="{{ route('reports.sales.export') }}" class="mt-5 border-t border-white/60 pt-4">
                    <p class="mb-3 text-sm font-semibold">Unduh laporan penjualan untuk audit</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <label for="report-from" class="label">Dari tanggal</label>
                            <input id="report-from" type="date" name="from" value="{{ now()->startOfMonth()->toDateString() }}" required class="input">
                        </div>
                        <div class="flex-1">
                            <label for="report-to" class="label">Sampai tanggal</label>
                            <input id="report-to" type="date" name="to" value="{{ today()->toDateString() }}" required class="input">
                        </div>
                        <button type="submit" class="btn-primary whitespace-nowrap">Unduh Excel (CSV)</button>
                    </div>
                    <p class="mt-2 text-xs text-ink-500">Berisi rincian transaksi selesai dan item; file CSV dapat dibuka di Excel.</p>
                </form>
            @endif
        </section>

        {{-- Peringatan stok --}}
        <section class="card animate-fade-up" aria-labelledby="stock-title" style="animation-delay:80ms">
            <div class="mb-4 flex items-center justify-between">
                <h2 id="stock-title" class="font-bold">Peringatan stok</h2>
                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="text-xs font-semibold text-brand-700 hover:underline">Lihat semua</a>
            </div>

            <div x-show="loading" class="space-y-3">
                @for ($i = 0; $i < 4; $i++)
                    <div class="skeleton h-10"></div>
                @endfor
            </div>

            <ul x-show="!loading" x-cloak class="thin-scroll max-h-72 space-y-2 overflow-y-auto pr-1">
                <template x-for="item in lowStock" :key="item.id">
                    <li class="flex items-center justify-between gap-3 rounded-xl bg-white/50 px-3 py-2.5 transition duration-200 hover:bg-white/80">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold" x-text="item.name"></p>
                            <p class="font-mono text-xs text-ink-500" x-text="item.sku"></p>
                        </div>
                        <span class="badge shrink-0"
                              :class="item.stock === 0 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'"
                              x-text="item.stock === 0 ? 'Habis' : `Sisa ${item.stock}`"></span>
                    </li>
                </template>
                <li x-show="lowStock.length === 0" class="py-8 text-center text-sm text-ink-500">Semua stok aman.</li>
            </ul>
        </section>
    </div>

    {{-- Aktivitas terbaru --}}
    <section class="card animate-fade-up" aria-labelledby="recent-title" style="animation-delay:140ms">
        <div class="mb-4 flex items-center justify-between">
            <h2 id="recent-title" class="font-bold">Aktivitas terbaru</h2>
            <span class="badge bg-emerald-100 text-emerald-800"><span class="size-1.5 animate-pulse rounded-full bg-emerald-600"></span> Langsung</span>
        </div>

        <div class="table-glass overflow-x-auto">
            <table class="w-full min-w-[32rem]">
                <thead>
                    <tr>
                        <th scope="col">Invoice</th>
                        <th scope="col">Kasir</th>
                        <th scope="col" class="text-right">Total</th>
                        <th scope="col">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="trx in recent" :key="trx.invoice">
                        <tr>
                            <td class="font-mono text-xs" x-text="trx.invoice"></td>
                            <td x-text="trx.cashier ?? '—'"></td>
                            <td class="text-right font-semibold tabular-nums" x-text="window.rupiah(trx.total)"></td>
                            <td x-text="trx.at"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="!loading && recent.length === 0" x-cloak class="py-8 text-center text-sm text-ink-500">Belum ada transaksi hari ini.</p>
        </div>
    </section>
</div>
@endsection
