<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Pemesanan;
use App\Models\PemesananDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PemesananDetailController extends Controller
{
    public function index(Request $request)
    {
        $rawCart = collect(session('cart', []));

        $cart = $rawCart->map(function ($row) {
            $r = is_array($row) ? $row : (array) $row;

            if (empty($r['line_id'])) {
                $r['line_id'] = (string) Str::uuid();
            }

            if (!empty($r['produk_id']) && (empty($r['nama']) || empty($r['kategori']) || empty($r['gambar']))) {
                $p = Produk::with('gambar')->find($r['produk_id']);
                if ($p) {
                    $r['nama']     = $r['nama']     ?? $p->nama;
                    $r['kategori'] = $r['kategori'] ?? $p->kategori;
                    $r['gambar']   = $r['gambar']   ?? (optional($p->gambar->first())->gambar
                        ? asset('storage/' . $p->gambar->first()->gambar)
                        : asset('assets/default.jpg'));
                }
            }

            return $r;
        })->values();

        if ($cart->toJson() !== $rawCart->toJson()) {
            session(['cart' => $cart->all()]);
        }

        $items = $cart->map(function ($r) {
            return [
                'line_id'   => $r['line_id'],
                'produk_id' => $r['produk_id'] ?? null,
                'nama'      => $r['nama'] ?? 'Produk',
                'kategori'  => $r['kategori'] ?? '-',
                'gambar'    => $r['gambar'] ?? asset('assets/default.jpg'),
            ];
        });

        return view('pelanggan.pemesananDetail', compact('items'));
    }

    public function tambah(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
        ]);

        $produk = Produk::with('gambar')->findOrFail($request->produk_id);

        $cart = session('cart', []);

        $cart[] = [
            'line_id'   => (string) Str::uuid(),
            'produk_id' => $produk->id,
            'nama'      => $produk->nama,
            'kategori'  => $produk->kategori,
            'gambar'    => optional($produk->gambar->first())->gambar
                ? asset('storage/' . $produk->gambar->first()->gambar)
                : asset('assets/default.jpg'),
        ];

        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true], 200);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function hapus(Request $request, $lineId)
    {
        $cart = collect(session('cart', []))
            ->reject(fn ($row) => (is_array($row) ? ($row['line_id'] ?? null) : ((array) $row)['line_id'] ?? null) === $lineId)
            ->values()
            ->all();

        session(['cart' => $cart]);

        return response()->json(['ok' => true]);
    }

    public function checkout(Request $request)
    {
        $items = $request->input('items', []);
        if (!is_array($items) || empty($items)) {
            return response()->json(['error' => 'Payload "items" kosong atau bukan array'], 422);
        }

        $buyNow     = $request->boolean('buy_now');
        $kirimEmail = $request->boolean('kirim_email');

        DB::beginTransaction();
        try {
            $order = Pemesanan::create([
                'order_id'     => 'ORDER-' . strtoupper(uniqid()),
                'pelanggan_id' => auth()->id(),
                'status'       => 'butuh_cek_ukuran',
                'keuntungan'   => 3,
                'total_harga'  => 0,
            ]);

            if ($buyNow) {
                foreach ($items as $produkId) {
                    $produk = Produk::find($produkId);
                    if ($produk) {
                        PemesananDetail::create([
                            'pemesanan_id' => $order->id,
                            'produk_id'    => $produk->id,
                            'nama_produk'  => $produk->nama,
                        ]);
                    }
                }
            } else {
                $cart = collect(session('cart', []));
                $selected = $cart->whereIn('line_id', $items);

                if ($selected->isEmpty()) {
                    DB::rollBack();
                    return response()->json(['error' => 'Item yang dipilih tidak ditemukan di keranjang.'], 422);
                }

                foreach ($selected as $row) {
                    PemesananDetail::create([
                        'pemesanan_id' => $order->id,
                        'produk_id'    => $row['produk_id'],
                        'nama_produk'  => $row['nama'],
                    ]);
                }

                $remain = $cart->reject(fn ($r) => in_array($r['line_id'], $items))->values()->all();
                session(['cart' => $remain]);
            }

            DB::commit();

            $emailedAdmin = false;
            if ($kirimEmail) {
                $emailedAdmin = $this->notifyAdmins($order);
            }

            return response()->json([
                'success'          => true,
                'email_sent_admin' => $emailedAdmin,
                'order_id'         => $order->order_id,
                'pemesanan_id'     => $order->id,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function notifyAdmins(Pemesanan $order): bool
    {
        try {
            $emails = Admin::whereNotNull('email')->pluck('email')->filter()->unique()->values();
            if ($emails->isEmpty()) {
                return false;
            }

            $order->load(['pelanggan', 'detail.produk']);

            $custName  = optional($order->pelanggan)->name ?? 'Pelanggan';
            $custEmail = optional($order->pelanggan)->email ?? '-';
            $custPhone = optional($order->pelanggan)->telepon
                ?? optional($order->pelanggan)->phone
                ?? '-';

            $itemsList = $order->detail->map(function ($d) {
                return '• ' . ($d->nama_produk ?? optional($d->produk)->nama ?? 'Produk');
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
                if ($emails->isNotEmpty()) {
                    $m->bcc($emails->all());
                }
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Notifikasi email admin gagal: ' . $e->getMessage());
            return false;
        }
    }
}
