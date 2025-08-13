@extends('Template.admin')

@section('title', 'Kebutuhan Pesanan')

@section('content')
@php
$detailIds = $pesanan->detail->pluck('id')->values();
@endphp

<section class="flex flex-col items-center px-6 py-6">
  <div
    class="w-full max-w-screen-xl bg-white px-6 sm:px-8 py-6 rounded-lg shadow"
    x-data="needsForm(@js($detailIds), @json(old('keuntungan', $pesanan->keuntungan ?? 3)))"
  >
    <div class="mb-4">
      <h1 class="text-2xl font-bold mb-2 uppercase">Pesanan {{ $pesanan->pelanggan->name ?? '—' }}</h1>
      <a
        href="{{ route('admin.pemesanan.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 transition"
      >
        <span>←</span><span>Kembali</span>
      </a>
    </div>

    <form method="POST" action="{{ route('admin.pemesanan.kebutuhan.store', $pesanan->id) }}" @submit.prevent="beforeSubmit($event)">
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
                    <select x-model="row.kategori" class="w-full border rounded px-2 py-2 focus:outline-none focus:ring focus:border-black">
                      <option value="bahan_besi">Bahan Besi</option>
                      <option value="bahan_lainnya">Bahan Lainnya</option>
                      <option value="jasa">Jasa</option>
                    </select>
                  </td>
                  <td class="px-5 py-3 border border-gray-300">
                    <input
                      type="text"
                      x-model="row.nama"
                      placeholder="Nama bahan/jasa"
                      class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black"
                    >
                  </td>
                  <td class="px-5 py-3 border border-gray-300">
                    <input
                      type="text"
                      x-model="row.kuantitas_str"
                      @input="row.kuantitas_str = oneDecimalComma(row.kuantitas_str)"
                      inputmode="decimal"
                      class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black text-right font-mono"
                    >
                  </td>
                  <td class="px-5 py-3 border border-gray-300">
                    <input
                      type="text"
                      x-model="row.harga_str"
                      @input="formatHarga({{ $detail->id }}, i)"
                      inputmode="numeric"
                      class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black text-right font-mono"
                    >
                  </td>
                  <td class="px-5 py-3 border border-gray-300 text-right font-medium font-mono" x-text="formatRupiah(rowTotal(row))"></td>
                  <td class="px-5 py-3 border border-gray-300 text-center">
                    <button type="button" @click="removeRow({{ $detail->id }}, i)" class="text-red-600 hover:text-red-700 underline">Hapus</button>
                  </td>
                </tr>
              </template>
              <tr>
                <td colspan="6" class="px-5 py-3 border border-gray-300">
                  <button type="button" @click="addRow({{ $detail->id }})" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
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
            <input type="hidden" :name="'items[{{ $detail->id }}]['+i+'][nama]'" :value="row.nama">
            <input type="hidden" :name="'items[{{ $detail->id }}]['+i+'][kuantitas]'" :value="parseCommaDecimal(row.kuantitas_str)">
            <input type="hidden" :name="'items[{{ $detail->id }}]['+i+'][harga]'" :value="parseIDRInt(row.harga_str)">
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

      <div class="mt-4 max-w-xs">
        <label for="keuntungan" class="block text-sm font-semibold mb-1">Keuntungan</label>
        <input
          type="number"
          name="keuntungan"
          id="keuntungan"
          min="0"
          step="0.1"
          x-model.number="keuntungan"
          @input="$event.target.value = $event.target.value.replace(',', '.'); keuntungan = Number($event.target.value || 0)"
          value="{{ old('keuntungan', $pesanan->keuntungan ?? 3) }}"
          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring"
        >
        @error('keuntungan')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex items-center justify-between mt-1">
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
  window.needsForm = function(detailIds = [], keuntunganAwal = 3) {
    const ids = Array.isArray(detailIds) ? detailIds : [];
    const itemsByDetail = {};
    ids.forEach(id => {
      itemsByDetail[id] = [{
        kategori: 'bahan_besi',
        nama: '',
        kuantitas_str: '',
        harga_str: ''
      }];
    });

    return {
      itemsByDetail,
      keuntungan: Number(keuntunganAwal) || 3,

      formatIDRIntInput(raw) {
        const s = String(raw || '').replace(/\D/g, '');
        return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      },
      parseIDRInt(str) {
        const s = String(str || '').replace(/\D/g, '');
        return s ? parseInt(s, 10) : 0;
      },

      oneDecimalComma(str) {
        let s = String(str ?? '').replace(/[^\d,\.]/g, '');
        s = s.replace(/\./g, ',');
        s = s.replace(/,+/g, ',');
        if (s === '') return '';
        if (s[0] === ',') s = '0' + s;
        const parts = s.split(',');
        const intp = parts[0] ?? '';
        const decp = (parts[1] || '').slice(0, 1);
        return decp ? `${intp || '0'},${decp}` : intp;
      },
      parseCommaDecimal(str) {
        if (str == null || str === '') return 0;
        const s = String(str).replace(/\./g, '').replace(',', '.');
        const v = parseFloat(s);
        return isNaN(v) ? 0 : v;
      },

      formatHarga(did, i) {
        const raw = String(this.itemsByDetail[did][i].harga_str ?? '');
        this.itemsByDetail[did][i].harga_str = this.formatIDRIntInput(raw);
      },

      rowTotal(row) {
        const qty = this.parseCommaDecimal(row.kuantitas_str);
        const harga = this.parseIDRInt(row.harga_str);
        return qty * harga;
      },
      sumByKategori(did, kategori) {
        return (this.itemsByDetail[did] || []).reduce((sum, r) => (r.kategori === kategori ? sum + this.rowTotal(r) : sum), 0);
      },
      sumBahanBesi(did) { return this.sumByKategori(did, 'bahan_besi'); },
      sumBahanLainnya(did) { return this.sumByKategori(did, 'bahan_lainnya'); },
      sumJasa(did) { return this.sumByKategori(did, 'jasa'); },

      bahanTotal(did) { return this.sumBahanBesi(did) + this.sumBahanLainnya(did); },
      jasaTotal(did) { return this.sumJasa(did); },

      grandTotal(did) {
        const k = Number(this.keuntungan) || 0;
        return this.bahanTotal(did) * k;
      },
      grandTotalAll() {
        return Object.keys(this.itemsByDetail || {}).reduce((sum, did) => sum + this.grandTotal(did), 0);
      },

      formatRupiah(v) {
        try {
          return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
        } catch (_) {
          return `Rp ${Math.round(v || 0).toLocaleString('id-ID')}`;
        }
      },

      addRow(did) {
        (this.itemsByDetail[did] ||= []).push({ kategori: 'bahan_besi', nama: '', kuantitas_str: '', harga_str: '' });
      },
      removeRow(did, i) {
        this.itemsByDetail[did].splice(i, 1);
      },

      beforeSubmit(e) {
        for (const did of Object.keys(this.itemsByDetail)) {
          const rows = this.itemsByDetail[did] || [];
          if (!rows.length) {
            e.preventDefault();
            alert(`Minimal 1 baris kebutuhan untuk produk ID ${did}.`);
            return;
          }
          for (let i = 0; i < rows.length; i++) {
            const r = rows[i];
            if (!r.nama || !r.kategori) {
              e.preventDefault();
              alert(`Lengkapi baris kebutuhan ke-${i + 1} pada produk ID ${did}.`);
              return;
            }
            const q = this.parseCommaDecimal(r.kuantitas_str);
            if (!(q > 0)) {
              e.preventDefault();
              alert(`Kuantitas baris ke-${i + 1} pada produk ID ${did} harus > 0.`);
              return;
            }
            if (this.parseIDRInt(r.harga_str) < 0) {
              e.preventDefault();
              alert(`Harga baris ke-${i + 1} pada produk ID ${did} tidak boleh negatif.`);
              return;
            }
          }
        }
        this.$nextTick(() => e.target.submit());
      }
    };
  };
</script>
@endsection
