<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;

class PemesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pemesanan::with(['pelanggan', 'produk']);

        if ($request->filled('search')) {
            $query->whereHas('pelanggan', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'pending');
        }

        $pemesanan = $query->orderByDesc('created_at')->paginate(10);

        return view('admin.pemesanan', compact('pemesanan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,dikerjakan,selesai,menunggu_refund,refund_diterima',
        ]);

        $pesanan = Pemesanan::findOrFail($id);

        if (in_array($pesanan->status, ['dibatalkan', 'gagal', 'selesai']) && $request->status !== 'refund_diterima') {
            return redirect()->back()->with('error', 'Status sudah final dan tidak bisa diubah.');
        }

        $newStatus = $request->status === 'diproses' ? 'menunggu' : $request->status;
        $pesanan->status = $newStatus;

        $pesanan->save();

        return redirect()->route('admin.pemesanan.index')->with('success', 'Status berhasil diperbarui.');
    }
}
