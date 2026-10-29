@extends('layouts.app')

@section('title', 'Detail '.$transaction->invoice_number)
@section('heading', 'Detail Transaksi')
@section('subheading', $transaction->invoice_number)

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <section class="card lg:col-span-2 p-0 overflow-hidden">
        <div class="table-glass overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th scope="col">Produk</th>
                        <th scope="col" class="text-right">Harga</th>
                        <th scope="col" class="text-right">Qty</th>
                        <th scope="col" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->details as $line)
                        <tr>
                            <td class="font-medium text-ink-900">{{ $line->product_name }}</td>
                            <td class="text-right tabular-nums">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right tabular-nums">{{ $line->quantity }}</td>
                            <td class="text-right font-semibold tabular-nums">Rp {{ number_format($line->line_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="card space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-ink-500">Status</span>
                <x-badge :tone="$transaction->status->value === 'voided' ? 'danger' : 'success'">{{ $transaction->status->value === 'voided' ? 'Dibatalkan' : 'Selesai' }}</x-badge>
            </div>
            <div class="flex justify-between"><span class="text-ink-500">Kasir</span><span class="font-medium">{{ $transaction->cashier?->name }}</span></div>
            <div class="flex justify-between"><span class="text-ink-500">Waktu</span><span>{{ $transaction->transacted_at->translatedFormat('d M Y, H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-ink-500">Metode</span><span>{{ $transaction->payment_method->label() }}</span></div>
            <hr class="border-white/70">
            <div class="flex justify-between"><span class="text-ink-500">Subtotal</span><span class="tabular-nums">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-base font-extrabold"><span>Total</span><span class="tabular-nums">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-ink-500">Dibayar</span><span class="tabular-nums">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-ink-500">Kembali</span><span class="tabular-nums">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>

            @if ($transaction->void_reason)
                <p class="rounded-xl bg-rose-50 px-3 py-2 text-xs text-rose-800">Alasan pembatalan: {{ $transaction->void_reason }}</p>
            @endif
        </section>

        @if (auth()->user()->hasPermission('transactions.void') && $transaction->status->value === 'completed')
            <section class="card">
                <h2 class="mb-3 font-bold">Batalkan transaksi</h2>
                <p class="mb-3 text-xs text-ink-500">Stok produk akan dikembalikan. Data tetap tersimpan untuk audit.</p>
                <form method="POST" action="{{ route('transactions.void', $transaction) }}" class="space-y-3"
                      x-data="{ confirming: false }">
                    @csrf
                    <label for="void_reason" class="label">Alasan</label>
                    <input id="void_reason" name="void_reason" required minlength="5" maxlength="250" class="input" placeholder="Contoh: salah input item">
                    @error('void_reason')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
                    <button type="button" x-show="!confirming" @click="confirming = true" class="btn-danger w-full">Batalkan transaksi</button>
                    <button type="submit" x-show="confirming" x-cloak class="btn-danger w-full">Ya, batalkan sekarang</button>
                </form>
            </section>
        @endif
    </aside>
</div>
@endsection
