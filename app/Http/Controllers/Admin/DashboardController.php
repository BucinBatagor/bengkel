<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Kelompok status untuk ringkasan utama
        $groups = [
            'perlu_diukur' => ['butuh_cek_ukuran'],
            'proses'       => ['di_proses', 'dikerjakan'],
            'selesai'      => ['selesai'],
            'refund'       => ['pengembalian_dana', 'pengembalian_selesai'],
            'batal'        => ['batal', 'gagal'],
        ];

        // Hitung jumlah pesanan per group status
        $orderCounts = [];
        foreach ($groups as $key => $statuses) {
            $orderCounts[$key] = Pemesanan::whereIn('status', $statuses)->count();
        }

        // Hitung jumlah item (sum pemesanan_detail.jumlah) per group status
        $itemCounts = [];
        foreach ($groups as $key => $statuses) {
            $itemCounts[$key] = DB::table('pemesanan')
                ->join('pemesanan_detail', 'pemesanan_detail.pemesanan_id', '=', 'pemesanan.id')
                ->whereIn('pemesanan.status', $statuses)
                ->sum(DB::raw('COALESCE(pemesanan_detail.jumlah, 1)'));
        }

        // Produk terjual per kategori (hanya status selesai)
        $kategoriCounts = DB::table('pemesanan')
            ->join('pemesanan_detail', 'pemesanan_detail.pemesanan_id', '=', 'pemesanan.id')
            ->leftJoin('produk', 'produk.id', '=', 'pemesanan_detail.produk_id')
            ->where('pemesanan.status', '=', 'selesai')
            ->selectRaw('COALESCE(produk.kategori, "Lainnya") AS kategori, SUM(COALESCE(pemesanan_detail.jumlah,1)) AS total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        $maxKategori = ($kategoriCounts->max('total') ?? 0) ?: 1;

        // ===== Tambahan: Statistik Pelanggan & Belum Bayar =====
        $totalCustomers = Pelanggan::count();

        $unpaidOrderCount = Pemesanan::where('status', 'belum_bayar')->count();

        $unpaidCustomerCount = Pemesanan::where('status', 'belum_bayar')
            ->distinct('pelanggan_id')
            ->count('pelanggan_id');

        return view('Admin.dashboard', [
            'orderCounts'        => $orderCounts,
            'itemCounts'         => $itemCounts,
            'kategori'           => $kategoriCounts,
            'maxKategori'        => $maxKategori,
            // tambahan
            'totalCustomers'     => $totalCustomers,
            'unpaidOrderCount'   => $unpaidOrderCount,
            'unpaidCustomerCount'=> $unpaidCustomerCount,
        ]);
    }
}
