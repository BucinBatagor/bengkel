<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\PemesananDetail;
use App\Models\Produk;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PemesananDetailController extends Controller
{
    public function index(Request $request)
    {
        $items = collect();

        if (auth()->check()) {
            $details = PemesananDetail::with(['produk.gambar'])
                ->where('pelanggan_id', auth()->id())
                ->whereNull('pemesanan_id')
                ->get();

            $items = $details->map(function ($row) {
                $firstImg = optional($row->produk?->gambar?->first())->gambar;
                return [
                    'line_id'  => $row->id,
                    'nama'     => $row->nama_produk ?? optional($row->produk)->nama ?? 'Produk',
                    'kategori' => optional($row->produk)->kategori ?? '-',
                    'gambar'   => $firstImg ? asset('storage/' . $firstImg) : asset('assets/default.jpg'),
                    'jumlah'   => (int)($row->jumlah ?? 1),
                ];
            });
        }

        return view('Pelanggan.pemesananDetail', compact('items'));
    }

    public function tambah(Request $request)
    {
        $request->validate(['produk_id' => 'required|exists:produk,id']);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $pelangganId = auth()->id();
        $produk      = Produk::with('gambar')->findOrFail($request->produk_id);

        $existing = PemesananDetail::where('pelanggan_id', $pelangganId)
            ->whereNull('pemesanan_id')
            ->where('produk_id', $produk->id)
            ->first();

        if ($existing) {
            $existing->increment('jumlah');
        } else {
            PemesananDetail::create([
                'pelanggan_id' => $pelangganId,
                'pemesanan_id' => null,
                'produk_id'    => $produk->id,
                'nama_produk'  => $produk->nama,
                'jumlah'       => 1,
            ]);
        }

        $count = PemesananDetail::where('pelanggan_id', $pelangganId)
            ->whereNull('pemesanan_id')
            ->count();

        return response()->json([
            'success'    => true,
            'cart_count' => (int)$count,
        ]);
    }

    public function hapus(string $detailId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        PemesananDetail::where('id', $detailId)
            ->where('pelanggan_id', auth()->id())
            ->whereNull('pemesanan_id')
            ->delete();

        $cartCount = PemesananDetail::where('pelanggan_id', auth()->id())
            ->whereNull('pemesanan_id')
            ->count();

        return response()->json(['success' => true, 'cart_count' => (int)$cartCount]);
    }

    public function ubahJumlah(Request $request, string $detailId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:0|max:999',
        ]);

        $detail = PemesananDetail::where('id', $detailId)
            ->where('pelanggan_id', auth()->id())
            ->whereNull('pemesanan_id')
            ->first();

        if (!$detail) {
            return response()->json(['error' => 'Item tidak ditemukan.'], 404);
        }

        if ($validated['jumlah'] === 0) {
            $detail->delete();
            $newQty = 0;
        } else {
            $detail->jumlah = $validated['jumlah'];
            $detail->save();
            $newQty = (int)$detail->jumlah;
        }

        $cartCount = PemesananDetail::where('pelanggan_id', auth()->id())
            ->whereNull('pemesanan_id')
            ->count();

        return response()->json([
            'success'     => true,
            'new_qty'     => $newQty,
            'cart_count'  => (int)$cartCount,
            'line_id'     => (int)$detailId,
        ]);
    }

    public function pesan(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $pelangganId = auth()->id();
        $rawItems    = $request->input('items', []);
        $buyNow      = (bool) $request->boolean('buy_now', false);
        $kirimEmail  = (bool) $request->boolean('kirim_email', false);

        $ids = array_values(array_unique(array_map('intval', is_array($rawItems) ? $rawItems : [])));
        if (empty($ids)) {
            return response()->json(['error' => 'Items tidak boleh kosong'], 422);
        }

        $STATUS_AWAL = 'butuh_cek_ukuran';

        if ($buyNow) {
            try {
                [$order, $emailed] = DB::transaction(function () use ($pelangganId, $ids, $STATUS_AWAL, $kirimEmail) {
                    $order = Pemesanan::create([
                        'pelanggan_id'      => $pelangganId,
                        'order_id'          => 'ORDER-' . strtoupper(uniqid()),
                        'snap_token'        => null,
                        'total_harga'       => 0,
                        'status'            => $STATUS_AWAL,
                        'midtrans_response' => null,
                    ]);

                    $produkIds = Produk::whereIn('id', $ids)->pluck('id')->all();
                    foreach ($produkIds as $pid) {
                        $p = Produk::find($pid);
                        if (!$p) continue;

                        PemesananDetail::create([
                            'pelanggan_id' => $pelangganId,
                            'pemesanan_id' => $order->id,
                            'produk_id'    => $p->id,
                            'nama_produk'  => $p->nama,
                            'jumlah'       => 1,
                        ]);
                    }

                    $emailed = $kirimEmail ? $this->notifyAdmins($order) : false;
                    return [$order, $emailed];
                });

                return response()->json([
                    'success'          => true,
                    'email_sent_admin' => $emailed,
                    'pemesanan_id'     => $order->id,
                    'order_id'         => $order->order_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Buy now error: ' . $e->getMessage());
                return response()->json(['error' => 'Gagal membuat pesanan.'], 500);
            }
        }

        $selectedDetails = PemesananDetail::where('pelanggan_id', $pelangganId)
            ->whereNull('pemesanan_id')
            ->whereIn('id', $ids)
            ->get();

        if ($selectedDetails->isEmpty()) {
            return response()->json(['error' => 'Item terpilih tidak valid atau keranjang kosong.'], 422);
        }

        try {
            [$order, $emailed] = DB::transaction(function () use ($pelangganId, $selectedDetails, $STATUS_AWAL, $kirimEmail) {
                $order = Pemesanan::create([
                    'pelanggan_id'      => $pelangganId,
                    'order_id'          => 'ORDER-' . strtoupper(uniqid()),
                    'snap_token'        => null,
                    'total_harga'       => 0,
                    'status'            => $STATUS_AWAL,
                    'midtrans_response' => null,
                ]);

                PemesananDetail::whereIn('id', $selectedDetails->pluck('id'))
                    ->update(['pemesanan_id' => $order->id]);

                $emailed = $kirimEmail ? $this->notifyAdmins($order) : false;

                return [$order, $emailed];
            });

            $cartCount = PemesananDetail::where('pelanggan_id', $pelangganId)
                ->whereNull('pemesanan_id')
                ->count();

            return response()->json([
                'success'          => true,
                'email_sent_admin' => $emailed,
                'order_id'         => $order->order_id,
                'pemesanan_id'     => $order->id,
                'cart_count'       => (int)$cartCount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat pesanan.'], 500);
        }
    }

    private function notifyAdmins(Pemesanan $order): bool
    {
        try {
            $emails = Admin::whereNotNull('email')->pluck('email')->filter()->unique()->values();
            if ($emails->isEmpty()) return false;

            $order->load(['pelanggan', 'detail.produk']);

            $custName  = optional($order->pelanggan)->name ?? 'Pelanggan';
            $custEmail = optional($order->pelanggan)->email ?? '-';
            $custPhone = optional($order->pelanggan)->telepon ?? optional($order->pelanggan)->phone ?? '-';

            $itemsList = $order->detail->map(function ($d) {
                $nm = $d->nama_produk ?? optional($d->produk)->nama ?? 'Produk';
                $qty = (int)($d->jumlah ?? 1);
                return '• ' . $nm . ' (x' . $qty . ')';
            })->implode("\n");

            $manageUrl = route('admin.pemesanan.kebutuhan.edit', $order->id);

            $body = "Ada pesanan baru.\n\n"
                . "Nomor Pesanan : {$order->order_id}\n"
                . "Pelanggan     : {$custName}\n"
                . "Email/Telepon : {$custEmail} / {$custPhone}\n\n"
                . "Item:\n{$itemsList}\n\n"
                . "Kelola pesanan: {$manageUrl}\n";

            $to = $emails->shift();
            Mail::raw($body, function ($m) use ($to, $emails, $order) {
                $m->to($to)->subject('Pesanan Baru - ' . $order->order_id);
                if ($emails->isNotEmpty()) $m->bcc($emails->all());
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Notifikasi email admin gagal: ' . $e->getMessage());
            return false;
        }
    }
}
