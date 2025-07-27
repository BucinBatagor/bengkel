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

    // Jika status difilter, gunakan status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    } else {
        // Jika tidak difilter, tampilkan semua kecuali 'pending'
        $query->where('status', '!=', 'pending');
    }

    $pemesanan = $query->orderByDesc('created_at')->paginate(10);

    return view('admin.pemesanan', compact('pemesanan'));
}



    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,dikerjakan,selesai',
        ]);

        $pemesanan = Pemesanan::findOrFail($id);
        $pemesanan->status = $request->status;
        $pemesanan->save();

        return redirect()->route('admin.pemesanan.index')->with('success', 'Status pemesanan berhasil diperbarui.');
    }
}
