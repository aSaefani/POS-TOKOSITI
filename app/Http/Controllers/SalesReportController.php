<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($validated['from'])->startOfDay();
        $to = Carbon::parse($validated['to'])->endOfDay();
        $filename = sprintf('laporan-penjualan_%s_sampai_%s.csv', $from->toDateString(), $to->toDateString());

        return response()->streamDownload(function () use ($from, $to) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM supaya Excel mengenali teks Indonesia.

            fputcsv($output, [
                'Nomor invoice', 'Waktu transaksi', 'Kasir', 'Metode pembayaran',
                'Nama produk', 'Jumlah', 'Modal per unit (Rp)', 'Harga per unit (Rp)',
                'Total item (Rp)', 'Subtotal transaksi (Rp)', 'Diskon transaksi (Rp)',
                'Total transaksi (Rp)', 'Dibayar (Rp)', 'Kembalian (Rp)', 'Status',
            ], ';', '"', '');

            $safeText = static function (?string $value): string {
                $value ??= '';

                return preg_match('/^[\t\r =+\-@]/u', $value) ? "'{$value}" : $value;
            };

            Transaction::query()
                ->completed()
                ->whereBetween('transacted_at', [$from, $to])
                ->with(['cashier:id,name', 'details' => fn ($query) => $query->orderBy('id')])
                ->chunkById(500, function ($transactions) use ($output, $safeText) {
                    foreach ($transactions as $transaction) {
                        $firstLine = true;

                        foreach ($transaction->details as $detail) {
                            fputcsv($output, [
                                $safeText($transaction->invoice_number),
                                $transaction->transacted_at->format('Y-m-d H:i:s'),
                                $safeText($transaction->cashier?->name),
                                $transaction->payment_method->value,
                                $safeText($detail->product_name),
                                $detail->quantity,
                                $detail->unit_cost,
                                $detail->unit_price,
                                $detail->line_total,
                                $firstLine ? $transaction->subtotal : '',
                                $firstLine ? $transaction->discount : '',
                                $firstLine ? $transaction->total : '',
                                $firstLine ? $transaction->amount_paid : '',
                                $firstLine ? $transaction->change_amount : '',
                                $transaction->status->value,
                            ], ';', '"', '');
                            $firstLine = false;
                        }
                    }
                });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
