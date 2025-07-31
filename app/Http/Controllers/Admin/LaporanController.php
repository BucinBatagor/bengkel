<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('from');
        $end = $request->input('to');

        $query = Pemesanan::with('pelanggan', 'produk')->where('status', 'selesai');

        if ($start && $end) {
            $startDate = $start . ' 00:00:00';
            $endDate = $end . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $pemesanan = $query->latest()->paginate(10)->withQueryString();

        return view('admin.laporan', compact('pemesanan', 'start', 'end'));
    }

    public function export(Request $request)
    {
        $tanggalAwal = $request->input('from');
        $tanggalAkhir = $request->input('to');
        $format = $request->input('format');

        $startDate = $tanggalAwal . ' 00:00:00';
        $endDate = $tanggalAkhir . ' 23:59:59';

        $data = Pemesanan::with(['pelanggan', 'produk'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->get();

        if ($data->isEmpty()) {
            return redirect()->route('admin.laporan.index', [
                'from' => $tanggalAwal,
                'to' => $tanggalAkhir,
            ])->with('error', 'Tidak ada data yang bisa di-export.');
        }

        $fileName = 'laporan_' . date('Ymd', strtotime($tanggalAwal)) . '-' . date('Ymd', strtotime($tanggalAkhir)) . '.pdf';

        $pdf = Pdf::loadView('admin.laporan_pdf', ['pemesanan' => $data]);
        return $pdf->download($fileName);
    }
}
