<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\PemesananKebutuhan;
use App\Models\ProdukGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pemesanan::with(['pelanggan', 'detail.produk.gambar', 'kebutuhan']);

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->whereHas('pelanggan', fn ($qq) => $qq->where('name', 'like', "%{$q}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pemesanan = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->query());

        return view('Admin.pemesanan', compact('pemesanan'));
    }

    public function show($id)
    {
        return redirect()->route('admin.pemesanan.index');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'butuh_cek_ukuran',
                    'batal',
                    'belum_bayar',
                    'gagal',
                    'di_proses',
                    'dikerjakan',
                    'selesai',
                    'pengembalian_dana',
                    'pengembalian_selesai',
                ]),
            ],
        ]);

        $pesanan = Pemesanan::findOrFail($id);
        $current = $pesanan->status;
        $target  = (string) $request->input('status');

        $locked = ['butuh_cek_ukuran', 'belum_bayar', 'batal', 'gagal'];
        if (in_array($current, $locked, true)) {
            return back()->with('error', 'Status ini tidak bisa diubah oleh admin.');
        }

        $allowed = [
            'di_proses'            => ['dikerjakan', 'selesai'],
            'dikerjakan'           => ['di_proses', 'selesai'],
            'selesai'              => ['di_proses', 'dikerjakan'],
            'pengembalian_dana'    => ['pengembalian_selesai'],
            'pengembalian_selesai' => ['pengembalian_dana'],
        ];

        if (!in_array($target, $allowed[$current] ?? [], true)) {
            return back()->with('error', "Transisi status dari '{$current}' ke '{$target}' tidak diizinkan.");
        }

        $pesanan->status = $target;
        $pesanan->save();

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function editKebutuhan($id)
    {
        $pesanan = Pemesanan::with([
            'pelanggan',
            'produk.kategoriRel',
            'kebutuhan',
            'detail.produk.kategoriRel',
        ])->findOrFail($id);

        return view('Admin.pemesanan_kebutuhan', [
            'pesanan'        => $pesanan,
            'fieldsByDetail' => [],
        ]);
    }

    public function storeKebutuhan(Request $request, $id)
    {
        $pesanan = Pemesanan::with(['detail.produk.kategoriRel'])->findOrFail($id);

        $rules = [
            'keuntungan'                          => ['required', 'numeric', 'min:0', 'max:99'],
            'items'                               => ['required', 'array', 'min:1'],
            'items.*'                             => ['array', 'min:1'],
            'items.*.*.pemesanan_detail_id'       => ['required', 'integer'],
            'items.*.*.kategori'                  => ['required', Rule::in(['bahan_besi', 'bahan_lainnya', 'jasa'])],
            'items.*.*.nama'                      => ['required', 'string', 'max:255'],
            'items.*.*.kuantitas'                 => ['required', 'numeric', 'min:0.01'],
            'items.*.*.harga'                     => ['required', 'integer', 'min:0'],
        ];

        $messages = [
            'keuntungan.required'                 => 'Keuntungan wajib diisi.',
            'items.required'                      => 'Minimal satu baris kebutuhan harus diisi.',
            'items.*.*.pemesanan_detail_id.required' => 'Baris kebutuhan tidak valid.',
            'items.*.*.kategori.required'         => 'Kategori kebutuhan harus dipilih.',
            'items.*.*.kategori.in'               => 'Kategori kebutuhan tidak valid.',
            'items.*.*.nama.required'             => 'Nama kebutuhan harus diisi.',
            'items.*.*.kuantitas.required'        => 'Kuantitas harus diisi.',
            'items.*.*.kuantitas.numeric'         => 'Kuantitas harus berupa angka.',
            'items.*.*.kuantitas.min'             => 'Kuantitas minimal 0.01.',
            'items.*.*.harga.required'            => 'Harga harus diisi.',
            'items.*.*.harga.integer'             => 'Harga harus berupa bilangan bulat.',
            'items.*.*.harga.min'                 => 'Harga minimal 0.',
        ];

        $validated = $request->validate($rules, $messages);

        $k = max(0, (float) ($validated['keuntungan'] ?? 3));
        $detailMap = $pesanan->detail->keyBy('id');

        $itemsByDetail = [];
        foreach (($validated['items'] ?? []) as $detailId => $rows) {
            $detailId = (int) $detailId;
            if (!$detailId || !$detailMap->has($detailId)) {
                continue;
            }
            $cleanRows = [];
            foreach (($rows ?? []) as $r) {
                if ((int)($r['pemesanan_detail_id'] ?? 0) !== $detailId) {
                    return back()
                        ->withErrors(['items' => "Baris kebutuhan memiliki pemesanan_detail_id tidak cocok untuk detail {$detailId}."])
                        ->withInput();
                }
                $cleanRows[] = [
                    'kategori'  => $r['kategori'],
                    'nama'      => $r['nama'],
                    'kuantitas' => (float) $r['kuantitas'],
                    'harga'     => (int) $r['harga'],
                ];
            }
            if (!empty($cleanRows)) {
                $itemsByDetail[$detailId] = $cleanRows;
            }
        }

        if (empty($itemsByDetail)) {
            return back()->withErrors(['items' => 'Tidak ada kebutuhan yang valid untuk disimpan.'])->withInput();
        }

        DB::transaction(function () use ($pesanan, $itemsByDetail, $detailMap, $k) {
            $pesanan->kebutuhan()->delete();

            $sumBesi = 0; $sumLain = 0; $sumJasa = 0;

            foreach ($itemsByDetail as $detailId => $rows) {
                $produkId = optional($detailMap[$detailId]->produk)->id;

                foreach ($rows as $row) {
                    $qty      = (float) $row['kuantitas'];
                    $harga    = (int)   $row['harga'];
                    $subtotal = (int)   round($qty * $harga);

                    PemesananKebutuhan::create([
                        'pemesanan_id'        => $pesanan->id,
                        'pemesanan_detail_id' => $detailId,
                        'produk_id'           => $produkId,
                        'kategori'            => $row['kategori'],
                        'nama'                => $row['nama'],
                        'kuantitas'           => $qty,
                        'harga'               => $harga,
                        'subtotal'            => $subtotal,
                    ]);

                    if ($row['kategori'] === 'bahan_besi')      $sumBesi += $subtotal;
                    elseif ($row['kategori'] === 'bahan_lainnya') $sumLain += $subtotal;
                    elseif ($row['kategori'] === 'jasa')          $sumJasa += $subtotal;
                }
            }

            $totalBahan = $sumBesi + $sumLain;
            $grandTotal = (int) round($totalBahan * $k);
            $bersih     = (int) ($grandTotal - ($totalBahan + $sumJasa));

            $pesanan->status = 'belum_bayar';
            if (Schema::hasColumn($pesanan->getTable(), 'total_bahan_besi'))     $pesanan->total_bahan_besi     = $sumBesi;
            if (Schema::hasColumn($pesanan->getTable(), 'total_bahan_lainnya'))  $pesanan->total_bahan_lainnya  = $sumLain;
            if (Schema::hasColumn($pesanan->getTable(), 'total_jasa'))           $pesanan->total_jasa           = $sumJasa;
            if (Schema::hasColumn($pesanan->getTable(), 'pendapatan_bersih'))    $pesanan->pendapatan_bersih    = $bersih;
            if (Schema::hasColumn($pesanan->getTable(), 'keuntungan'))           $pesanan->keuntungan           = $k;

            $pesanan->total_harga = $grandTotal;
            $pesanan->save();
        });

        return redirect()->route('admin.pemesanan.index')->with('success', 'Kebutuhan berhasil disimpan.');
    }

    public function destroyKebutuhan($id)
    {
        $pesanan = Pemesanan::with(['kebutuhan'])->findOrFail($id);

        DB::transaction(function () use ($pesanan) {
            $pesanan->kebutuhan()->delete();
            $pesanan->total_harga = 0;

            if (Schema::hasColumn($pesanan->getTable(), 'total_bahan_besi')) {
                $pesanan->total_bahan_besi = 0;
            }
            if (Schema::hasColumn($pesanan->getTable(), 'total_bahan_lainnya')) {
                $pesanan->total_bahan_lainnya = 0;
            }
            if (Schema::hasColumn($pesanan->getTable(), 'total_jasa')) {
                $pesanan->total_jasa = 0;
            }
            if (Schema::hasColumn($pesanan->getTable(), 'pendapatan_bersih')) {
                $pesanan->pendapatan_bersih = 0;
            }

            $pesanan->status = 'butuh_cek_ukuran';
            $pesanan->save();
        });

        return back()->with('success', 'Kebutuhan berhasil dihapus (pesanan tetap ada).');
    }

    public function uploadGambar(Request $request, $id)
    {
        $pesanan = Pemesanan::with('detail.produk')->findOrFail($id);

        if ($pesanan->status !== 'selesai') {
            return back()->with('error', 'Upload gambar hanya dapat dilakukan saat pesanan berstatus selesai.');
        }

        $request->validate([
            'produk_id' => ['required','integer'],
            'gambar' => ['required','array','min:1'],
            'gambar.*' => ['file','image','mimes:jpeg,jpg,png,webp','max:2048'],
        ]);

        $produkId = (int) $request->input('produk_id');
        $produkValid = $pesanan->detail->contains(function($d) use ($produkId) {
            $pid = $d->produk->id ?? $d->produk_id ?? null;
            return $pid === $produkId;
        });

        if (!$produkValid) {
            return back()->with('error', 'Produk tidak valid untuk pesanan ini.');
        }

        DB::transaction(function () use ($request, $produkId) {
            foreach ($request->file('gambar', []) as $file) {
                $path = $file->store('produk', 'public');
                ProdukGambar::create([
                    'produk_id' => $produkId,
                    'gambar' => $path,
                ]);
            }
        });

        return back()->with('success', 'Gambar berhasil diupload.');
    }
}
