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
        $gross = 0.0;

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
            $gross += (float) (($besi + $lain) * 3);
        }

        $net = $gross - $totalBesi - $totalLain - $totalJasa;

        return [
            'gross' => $gross,
            'total_bahan_besi' => $totalBesi,
            'total_bahan_lainnya' => $totalLain,
            'total_jasa' => $totalJasa,
            'net' => $net,
            'count' => $orders->count(),
        ];
    }

    public function index(Request $request)
    {
        $monthParam = $request->query('month') ?: Carbon::now('Asia/Jakarta')->format('Y-m');

        try {
            $bulanAktif = Carbon::createFromFormat('Y-m', $monthParam, 'Asia/Jakarta')->startOfMonth();
        } catch (\Throwable $e) {
            $bulanAktif = Carbon::now('Asia/Jakarta')->startOfMonth();
            $monthParam = $bulanAktif->format('Y-m');
        }

        $start = (clone $bulanAktif)->startOfMonth();
        $end = (clone $bulanAktif)->endOfMonth();

        $orders = Pemesanan::with(['pelanggan', 'detail.produk', 'kebutuhan'])
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $ringkasan = $this->ringkasan($orders);

        $prevMonth = (clone $start)->subMonth()->format('Y-m');
        $nextMonth = (clone $start)->addMonth()->format('Y-m');

        return view('Admin.laporan', [
            'month' => $monthParam,
            'start' => $start,
            'end' => $end,
            'ringkasan' => $ringkasan,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function export(Request $request)
    {
        $month = $request->query('month');
        $mfrom = $request->query('mfrom');
        $mto = $request->query('mto');
        $from = $request->query('from');
        $to = $request->query('to');

        if ($mfrom || $mto) {
            $mfrom = $mfrom ?: $mto;
            $mto = $mto ?: $mfrom;

            try {
                $start = Carbon::createFromFormat('Y-m', $mfrom, 'Asia/Jakarta')->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $mto, 'Asia/Jakarta')->endOfMonth();
            } catch (\Throwable $e) {
                return redirect()->route('admin.laporan.index', ['month' => Carbon::now('Asia/Jakarta')->format('Y-m')])
                    ->with('error', 'Format bulan tidak valid untuk ekspor.');
            }

            if ($end->lt($start)) {
                return redirect()->route('admin.laporan.index', ['month' => $mfrom])
                    ->with('error', 'Bulan sampai tidak boleh lebih awal dari bulan dari.');
            }

            $fileName = 'laporan_' . $start->format('Ym') . '-' . $end->format('Ym') . '.pdf';
        } elseif ($month) {
            try {
                $bulan = Carbon::createFromFormat('Y-m', $month, 'Asia/Jakarta')->startOfMonth();
            } catch (\Throwable $e) {
                return redirect()->route('admin.laporan.index', ['month' => Carbon::now('Asia/Jakarta')->format('Y-m')])
                    ->with('error', 'Format bulan tidak valid.');
            }

            $start = (clone $bulan)->startOfMonth();
            $end = (clone $bulan)->endOfMonth();
            $fileName = 'laporan_' . $bulan->format('Ym') . '.pdf';
        } else {
            $request->validate([
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
            ]);

            $start = Carbon::parse($from, 'Asia/Jakarta')->startOfDay();
            $end = Carbon::parse($to, 'Asia/Jakarta')->endOfDay();
            $fileName = 'laporan_' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.pdf';
        }

        $data = Pemesanan::with(['pelanggan', 'detail.produk', 'kebutuhan'])
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($data->isEmpty()) {
            return redirect()->route('admin.laporan.index', [
                'month' => ($month ?: $start->format('Y-m')),
            ])->with('error', 'Tidak ada data yang bisa diekspor.');
        }

        $ringkasan = $this->ringkasan($data);

        $pdf = Pdf::loadView('Admin.laporan_pdf', [
            'pemesanan' => $data,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'ringkasan' => $ringkasan,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}
