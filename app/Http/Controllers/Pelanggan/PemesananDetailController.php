<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\PemesananDetail;
use App\Models\Produk;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PemesananDetailController extends Controller
{
  public function index()
  {
    $items = PemesananDetail::with('produk.gambar')
      ->where('pelanggan_id', auth()->id())
      ->whereNull('pemesanan_id')
      ->latest()
      ->get()
      ->map(fn ($item) => [
        'id' => $item->id,
        'produk_id' => $item->produk->id,
        'nama' => $item->produk->nama,
        'kategori' => $item->produk->kategori,
        'gambar' => $item->produk->gambar->first()
          ? asset('storage/' . $item->produk->gambar->first()->gambar)
          : asset('assets/default.jpg'),
      ]);

    return view('pelanggan.pemesananDetail', compact('items'));
  }

  public function tambah(Request $request)
  {
    $request->validate([
      'produk_id' => 'required|exists:produk,id',
    ]);

    $produk = Produk::findOrFail($request->produk_id);

    PemesananDetail::create([
      'pelanggan_id' => auth()->id(),
      'produk_id' => $produk->id,
      'nama_produk' => $produk->nama,
    ]);

    return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
  }

  public function hapus($id)
  {
    PemesananDetail::where('id', $id)
      ->where('pelanggan_id', auth()->id())
      ->whereNull('pemesanan_id')
      ->delete();

    return back()->with('success', 'Produk dihapus dari keranjang.');
  }

  public function checkout(Request $request)
  {
    $items = $request->input('items', []);
    if (!is_array($items) || empty($items)) {
      return response()->json(['error' => 'Payload "items" kosong atau bukan array'], 422);
    }

    $kirimEmail = $request->boolean('kirim_email');

    DB::beginTransaction();
    try {
      $pemesanan = Pemesanan::create([
        'order_id' => 'ORDER-' . strtoupper(uniqid()),
        'pelanggan_id' => auth()->id(),
        'status' => 'butuh_cek_ukuran',
        'total_harga' => 0,
      ]);

      foreach ($items as $id) {
        $detail = PemesananDetail::where('id', $id)
          ->whereNull('pemesanan_id')
          ->where('pelanggan_id', auth()->id())
          ->first();

        if ($detail) {
          $detail->update(['pemesanan_id' => $pemesanan->id]);
        } else {
          $produk = Produk::findOrFail($id);
          PemesananDetail::create([
            'pemesanan_id' => $pemesanan->id,
            'pelanggan_id' => auth()->id(),
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
          ]);
        }
      }

      DB::commit();

      $emailedAdmin = false;
      if ($kirimEmail) {
        try {
          $emails = Admin::whereNotNull('email')->pluck('email')->filter()->unique()->values();
          if ($emails->isNotEmpty()) {
            $pemesanan->load(['pelanggan', 'detail.produk']);

            $custName = optional($pemesanan->pelanggan)->name ?? 'Pelanggan';
            $custEmail = optional($pemesanan->pelanggan)->email ?? '-';
            $custPhone = optional($pemesanan->pelanggan)->telepon
              ?? optional($pemesanan->pelanggan)->phone
              ?? '-';

            $itemsList = $pemesanan->detail->map(function ($d) {
              return '• ' . ($d->nama_produk ?? $d->produk->nama ?? 'Produk');
            })->implode("\n");

            $manageUrl = route('admin.pemesanan.kebutuhan.edit', $pemesanan->id);

            $body = "Ada pesanan baru.\n\n"
              . "Nomor Pesanan : {$pemesanan->order_id}\n"
              . "Pelanggan     : {$custName}\n"
              . "Email/Telepon : {$custEmail} / {$custPhone}\n\n"
              . "Item:\n{$itemsList}\n\n"
              . "Kelola pesanan: {$manageUrl}\n";

            $to = $emails->shift();
            Mail::raw($body, function ($m) use ($to, $emails, $pemesanan) {
              $m->to($to)->subject('Pesanan Baru - ' . $pemesanan->order_id);
              if ($emails->isNotEmpty()) {
                $m->bcc($emails->all());
              }
            });

            $emailedAdmin = true;
          }
        } catch (\Throwable $e) {
          Log::error('Notifikasi email admin gagal: ' . $e->getMessage());
        }
      }

      return response()->json([
        'success' => true,
        'email_sent_admin' => $emailedAdmin,
        'order_id' => $pemesanan->order_id,
        'pemesanan_id' => $pemesanan->id,
      ], 200);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Checkout error: ' . $e->getMessage());
      return response()->json([
        'error' => 'Gagal membuat pesanan: ' . $e->getMessage(),
      ], 500);
    }
  }
}
