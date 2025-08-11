@extends('Template.admin')

@section('title', 'Isi Kebutuhan Pesanan')

@section('content')
@php
  $detailIds = $pesanan->detail->pluck('id')->values();
@endphp

<section class="flex flex-col items-center px-6 py-6">
  <div class="w-full max-w-screen-xl bg-white px-6 sm:px-8 py-6 rounded-lg shadow"
       x-data="needsForm(@js($detailIds))">

    <div class="mb-4">
      <h1 class="text-2xl font-bold mb-2 uppercase">Pesanan {{ $pesanan->pelanggan->name ?? '—' }}</h1>
      <a href="{{ route('admin.pemesanan.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 transition">
        <span>←</span><span>Kembali</span>
      </a>
    </div>

    <form method="POST" action="{{ route('admin.pemesanan.kebutuhan.store', $pesanan->id) }}" @submit="beforeSubmit">
      @csrf

      @foreach($pesanan->detail as $detail)
      <div class="border rounded-lg mb-8">
        <div class="px-5 py-4 border-b flex items-center justify-start bg-gray-50">
          <div class="flex items-center gap-4">
            @php $gambar = $detail->produk?->gambar->first()?->gambar; @endphp
            <div class="w-14 h-14 rounded overflow-hidden bg-gray-200">
              @if($gambar)
                <img src="{{ asset('storage/'.$gambar) }}" alt="Produk" class="w-full h-full object-cover">
              @endif
            </div>
            <div>
              <div class="font-semibold">{{ $detail->nama_produk }}</div>
            </div>
          </div>
        </div>

        <div class="p-5 overflow-x-auto">
          <table class="min-w-[980px] w-full border border-gray-300 border-collapse text-sm">
            <thead class="bg-black text-white uppercase text-xs tracking-wider sticky top-0 z-10">
              <tr>
                <th class="px-5 py-3 border border-gray-300 w-48 text-center">Kategori</th>
                <th class="px-5 py-3 border border-gray-300 text-center">Nama Kebutuhan</th>
                <th class="px-5 py-3 border border-gray-300 w-40 text-center">Kuantitas</th>
                <th class="px-5 py-3 border border-gray-300 w-44 text-center">Harga</th>
                <th class="px-5 py-3 border border-gray-300 w-44 text-center">Total</th>
                <th class="px-5 py-3 border border-gray-300 w-24 text-center">Aksi</th>
              </tr>
            </thead>

            <tbody class="text-gray-700">
              <template x-for="(row, i) in itemsByDetail[{{ $detail->id }}]" :key="'row-'+{{ $detail->id }}+'-'+i">
                <tr class="hover:bg-gray-100/60">
                  <td class="px-5 py-3 border border-gray-300">
                    <select x-model="row.kategori"
                            class="w-full border rounded px-2 py-2 focus:outline-none focus:ring focus:border-black">
                      <option value="bahan_besi">Bahan Besi</option>
                      <option value="bahan_lainnya">Bahan Lainnya</option>
                      <option value="jasa">Jasa</option>
                    </select>
                  </td>
                  <td class="px-5 py-3 border border-gray-300">
                    <input type="text" x-model="row.nama"
                           placeholder="Nama bahan/jasa"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black">
                  </td>
                  <td class="px-5 py-3 border border-gray-300">
                    <input type="number" step="0.01" min="0.01" x-model.number="row.kuantitas"
                           placeholder="0"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black text-right font-mono">
                  </td>
                  <td class="px-5 py-3 border border-gray-300">
                    <input type="text" x-model="row.harga_str" @input="formatHarga({{ $detail->id }}, i)" inputmode="numeric"
                           placeholder="0"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black text-right font-mono">
                  </td>
                  <td class="px-5 py-3 border border-gray-300 text-right font-medium font-mono"
                      x-text="formatRupiah(rowTotal(row))"></td>
                  <td class="px-5 py-3 border border-gray-300 text-center">
                    <button type="button" @click="removeRow({{ $detail->id }}, i)" class="text-red-600 hover:text-red-700 underline">Hapus</button>
                  </td>
                </tr>
              </template>

              <tr>
                <td colspan="6" class="px-5 py-3 border border-gray-300">
                  <button type="button" @click="addRow({{ $detail->id }})"
                          class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                    + Tambah Baris
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <template x-for="(row, i) in itemsByDetail[{{ $detail->id }}]" :key="'hidden-{{ $detail->id }}-'+i">
          <div class="hidden">
            <input type="hidden" :name="'items[{{ $detail->id }}]['+i+'][kategori]'" :value="row.kategori">
            <input type="hidden" :name="'items[{{ $detail->id }}]['+i+'][nama]'"     :value="row.nama">
            <input type="hidden" :name="'items[{{ $detail->id }}]['+i+'][kuantitas]'" :value="row.kuantitas">
            <input type="hidden" :name="'items[{{ $detail->id }}]['+i+'][harga]'"    :value="parseIDRInt(row.harga_str)">
          </div>
        </template>
      </div>
      @endforeach

      @if ($errors->any())
        <div class="mt-4 text-red-600 text-sm">
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-600">
          <span class="mr-2">Total Semua Produk:</span>
          <span class="font-bold" x-text="formatRupiah(grandTotalAll())"></span>
        </div>
        <button type="submit" class="bg-black text-white px-5 py-2 rounded hover:bg-gray-800">
          Simpan Kebutuhan
        </button>
      </div>
    </form>
  </div>
</section>

<script>
window.needsForm = function(detailIds = []) {
  const ids = Array.isArray(detailIds) ? detailIds : [];

  const itemsByDetail = {};
  ids.forEach(id => {
    itemsByDetail[id] = [{ kategori:'bahan_besi', nama:'', kuantitas:'', harga_str:'' }];
  });

  return {
    itemsByDetail,

    addRow(did) { (this.itemsByDetail[did] ||= []).push({ kategori:'bahan_besi', nama:'', kuantitas:'', harga_str:'' }); },
    removeRow(did, i) { this.itemsByDetail[did].splice(i, 1); },

    formatHarga(did, i) {
      const raw = String(this.itemsByDetail[did][i].harga_str ?? '');
      this.itemsByDetail[did][i].harga_str = this.formatIDRIntInput(raw);
    },
    parseIDRInt(str) {
      const s = String(str || '').replace(/\D/g, '');
      return s ? parseInt(s, 10) : 0;
    },
    formatIDRIntInput(raw) {
      const s = String(raw || '').replace(/\D/g, '');
      return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    rowTotal(row) {
      const qty = Number(row.kuantitas) || 0;
      const harga = this.parseIDRInt(row.harga_str);
      return qty * harga;
    },

    sumBahanBesi(did) {
      return (this.itemsByDetail[did] || []).reduce((sum, r) => (r.kategori === 'bahan_besi') ? sum + this.rowTotal(r) : sum, 0);
    },
    sumBahanLainnya(did) {
      return (this.itemsByDetail[did] || []).reduce((sum, r) => (r.kategori === 'bahan_lainnya') ? sum + this.rowTotal(r) : sum, 0);
    },
    sumJasa(did) {
      return (this.itemsByDetail[did] || []).reduce((sum, r) => (r.kategori === 'jasa') ? sum + this.rowTotal(r) : sum, 0);
    },

    grandTotal(did) {
      return (this.sumBahanBesi(did) + this.sumBahanLainnya(did)) * 3;
    },
    grandTotalAll() {
      return (Object.keys(this.itemsByDetail || {})).reduce((sum, did) => sum + this.grandTotal(did), 0);
    },

    formatRupiah(v) {
      try {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
      } catch (_) {
        return `Rp ${Math.round(v || 0).toLocaleString('id-ID')}`;
      }
    },

    beforeSubmit(e) {
      for (const did of Object.keys(this.itemsByDetail)) {
        const rows = this.itemsByDetail[did] || [];
        if (!rows.length) { e.preventDefault(); alert(`Minimal 1 baris kebutuhan untuk produk ID ${did}.`); return; }
        for (let i=0;i<rows.length;i++) {
          const r = rows[i];
          if (!r.nama || !r.kategori || !(Number(r.kuantitas) > 0)) {
            e.preventDefault(); alert(`Lengkapi baris kebutuhan ke-${i+1} pada produk ID ${did}.`); return;
          }
          if (this.parseIDRInt(r.harga_str) < 0) {
            e.preventDefault(); alert(`Harga baris ke-${i+1} pada produk ID ${did} tidak boleh negatif.`); return;
          }
        }
      }
    }
  }
}
</script>
@endsection
