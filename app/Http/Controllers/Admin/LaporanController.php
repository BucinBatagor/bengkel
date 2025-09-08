<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LaporanController extends Controller
{
    private function ringkasan(Collection $orders): array
    {
        $totalBesi = 0.0;
        $totalLain = 0.0;
        $totalJasa = 0.0;

        foreach ($orders as $o) {
            $besi = 0.0;
            $lain = 0.0;
            $jasa = 0.0;

            foreach ($o->kebutuhan as $k) {
                $subtotal = isset($k->subtotal)
                    ? (float) $k->subtotal
                    : ((float) ($k->kuantitas ?? 0) * (float) ($k->harga ?? 0));

                if ($k->kategori === 'bahan_besi') {
                    $besi += $subtotal;
                } elseif ($k->kategori === 'bahan_lainnya') {
                    $lain += $subtotal;
                } elseif ($k->kategori === 'jasa') {
                    $jasa += $subtotal;
                }
            }

            $totalBesi += $besi;
            $totalLain += $lain;
            $totalJasa += $jasa;
        }

        $gross = (float) $orders->sum(function ($o) {
            if (isset($o->total_harga) && is_numeric($o->total_harga)) {
                return (float) $o->total_harga;
            }

            $besi = 0.0;
            $lain = 0.0;
            foreach ($o->kebutuhan as $k) {
                $sub = isset($k->subtotal)
                    ? (float) $k->subtotal
                    : ((float) ($k->kuantitas ?? 0) * (float) ($k->harga ?? 0));

                if ($k->kategori === 'bahan_besi') {
                    $besi += $sub;
                } elseif ($k->kategori === 'bahan_lainnya') {
                    $lain += $sub;
                }
            }
            $k = (int) ($o->keuntungan ?? 3);
            if ($k < 1) {
                $k = 1;
            }
            return ($besi + $lain) * $k;
        });

        $net = $gross - $totalBesi - $totalLain - $totalJasa;

        return [
            'gross'               => $gross,
            'total_bahan_besi'    => $totalBesi,
            'total_bahan_lainnya' => $totalLain,
            'total_jasa'          => $totalJasa,
            'net'                 => $net,
            'count'               => $orders->count(),
        ];
    }

    public function index(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $hasRange = $from && $to;
        $orders = collect();
        $ringkasan = null;
        $start = null;
        $end = null;

        if ($hasRange) {
            try {
                $start = Carbon::parse($from, 'Asia/Jakarta')->startOfDay();
                $end = Carbon::parse($to, 'Asia/Jakarta')->endOfDay();
            } catch (\Throwable $e) {
                return redirect()->route('admin.laporan.index')
                    ->with('error', 'Format tanggal tidak valid.');
            }

            if ($end->lt($start)) {
                return redirect()->route('admin.laporan.index', [
                    'from' => $from,
                    'to' => $to,
                ])->with('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
            }

            $orders = Pemesanan::with(['pelanggan', 'detail.produk', 'kebutuhan'])
                ->where('status', 'selesai')
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $ringkasan = $this->ringkasan($orders);
        }

        return view('Admin.laporan', [
            'from'      => $from,
            'to'        => $to,
            'start'     => $start,
            'end'       => $end,
            'orders'    => $orders,
            'ringkasan' => $ringkasan,
            'hasRange'  => $hasRange,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $start = Carbon::parse($request->query('from'), 'Asia/Jakarta')->startOfDay();
        $end = Carbon::parse($request->query('to'), 'Asia/Jakarta')->endOfDay();

        $data = Pemesanan::with(['pelanggan', 'detail.produk', 'kebutuhan'])
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($data->isEmpty()) {
            return redirect()->route('admin.laporan.index', [
                'from' => $start->toDateString(),
                'to'   => $end->toDateString(),
            ])->with('error', 'Tidak ada data yang bisa diekspor.');
        }

        $ringkasan = $this->ringkasan($data);

        $pdf = Pdf::loadView('Admin.laporan_pdf', [
            'pemesanan' => $data,
            'start'     => $start->toDateString(),
            'end'       => $end->toDateString(),
            'ringkasan' => $ringkasan,
        ])->setPaper('a4', 'landscape');

        $fileName = 'laporan_' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.pdf';
        return $pdf->stream($fileName);
    }
}
