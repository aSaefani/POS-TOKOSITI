@extends('layouts.app')

@section('title', 'Transaksi')
@section('heading', 'Riwayat Transaksi')
@section('subheading', $canViewAll ? 'Seluruh transaksi toko.' : 'Transaksi milik Anda.')

@section('content')
<section class="space-y-4">
    <form method="GET" class="card grid gap-3 p-4 sm:grid-cols-[1fr_1fr_auto]">
        <div>
            <label for="date" class="label">Tanggal</label>
            <input id="date" type="date" name="date" value="{{ request('date') }}" class="input">
        </div>
        <div>
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">Semua</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ ucfirst($status->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button class="btn-primary">Terapkan</button>
            <a href="{{ route('transactions.index') }}" class="btn-ghost">Reset</a>
        </div>
    </form>

    <div class="card overflow-hidden p-0">
        <div class="table-glass overflow-x-auto">
            <table class="w-full min-w-[40rem]">
                <thead>
                    <tr>
                        <th scope="col">Invoice</th>
                        <th scope="col">Waktu</th>
                        @if ($canViewAll)<th scope="col">Kasir</th>@endif
                        <th scope="col">Metode</th>
                        <th scope="col" class="text-right">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr class="animate-fade-up">
                            <td class="font-mono text-xs">{{ $trx->invoice_number }}</td>
                            <td class="whitespace-nowrap">{{ $trx->transacted_at->translatedFormat('d M Y, H:i') }}</td>
                            @if ($canViewAll)<td>{{ $trx->cashier?->name ?? '—' }}</td>@endif
                            <td>{{ $trx->payment_method->label() }}</td>
                            <td class="text-right font-semibold tabular-nums">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                            <td>
                                @if ($trx->status->value === 'voided')
                                    <x-badge tone="danger">Dibatalkan</x-badge>
                                @else
                                    <x-badge tone="success">Selesai</x-badge>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('transactions.show', $trx) }}" class="font-semibold text-brand-700 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-ink-500">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/60 px-4 py-3">{{ $transactions->links() }}</div>
    </div>
</section>
@endsection
