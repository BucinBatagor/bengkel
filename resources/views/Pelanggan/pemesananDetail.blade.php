@extends('Template.pelanggan')

@section('title', 'Keranjang')

@section('content')
@php
    // Urutkan terbaru dulu: pakai created_at desc, fallback ke line_id
    $sorted = collect($items)->sortByDesc(function($it){
        // handle array/object
        $created = is_array($it) ? ($it['created_at'] ?? null) : ($it->created_at ?? null);
        $line    = is_array($it) ? ($it['line_id'] ?? null)   : ($it->line_id ?? null);
        return $created ?? $line;
    })->values();

    $initialIds = $sorted->pluck('line_id')->values();

    $initialData = $sorted->mapWithKeys(function($it){
        // akses seragam as array
        $row = is_array($it) ? $it : $it->toArray();
        return [$row['line_id'] => [
            'nama'       => $row['nama'],
            'kategori'   => $row['kategori'],
            'gambar'     => $row['gambar'],
            'jumlah'     => (int)($row['jumlah'] ?? 1),
            // optional: bisa dipakai kalau mau re-sort di client
            'created_at' => isset($row['created_at']) ? (string)$row['created_at'] : null,
        ]];
    });
@endphp

<section class="py-10 bg-gray-200 min-h-screen"
  x-data='keranjangApp(@json($initialIds), @json($initialData))'
  x-init="updateCartBadges()"
>
  <div class="max-w-screen-xl mx-auto px-4">
    <div class="bg-white rounded-xl shadow p-6">
      <h1 class="text-2xl font-bold mb-6">Keranjang</h1>

      <template x-if="allIds.length === 0">
        <p class="text-gray-700">Tidak ada produk di keranjang.</p>
      </template>

      <template x-if="allIds.length > 0">
        <div>
          <div class="mb-4 flex items-center">
            <input type="checkbox" id="selectAll" @click="toggleSelectAll()" :checked="isAllSelected()" class="w-4 h-4 mr-2">
            <label for="selectAll">Pilih Semua</label>
          </div>

          <form @submit.prevent="openConfirm">
            <div class="space-y-4">
              <template x-for="id in allIds" :key="id">
                <div class="bg-white rounded-lg shadow p-4 relative">
                  <button type="button"
                          @click="confirmDelete(id)"
                          class="absolute top-2 right-2 text-red-600 hover:text-red-800 text-lg font-bold">✕</button>

                  <!-- FLEX: ceklis + gambar + detail (rapat & sejajar atas) -->
                  <div class="flex items-start gap-3">
                    <!-- ceklis -->
                    <input type="checkbox"
                           :value="id"
                           :checked="selected.includes(id)"
                           @change="toggleItemSelection(id)"
                           class="w-4 h-4 mt-1 shrink-0">

                    <!-- gambar -->
                    <img :src="itemData[id].gambar"
                         class="w-24 h-24 object-cover rounded-lg border shrink-0">

                    <!-- detail -->
                    <div class="flex-1 flex flex-col gap-1">
                      <h2 class="font-semibold text-lg leading-tight" x-text="itemData[id].nama"></h2>
                      <p class="text-sm text-gray-500 leading-snug" x-text="itemData[id].kategori"></p>

                      <!-- jumlah -->
                      <div class="mt-1 flex items-center gap-2">
                        <button type="button"
                                @click="decrement(id)"
                                class="w-9 h-9 border rounded flex items-center justify-center hover:bg-gray-100"
                                aria-label="Kurangi jumlah">−</button>

                        <input type="number"
                               min="0"
                               :value="itemData[id].jumlah"
                               @change="onQtyInput(id, $event.target.value)"
                               class="h-9 w-14 text-center border rounded"
                               aria-label="Jumlah">

                        <button type="button"
                                @click="increment(id)"
                                class="w-9 h-9 border rounded flex items-center justify-center hover:bg-gray-100"
                                aria-label="Tambah jumlah">+</button>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <div class="mt-6 flex justify-end">
              <button type="submit" :disabled="isSubmitting"
                      class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!isSubmitting">Pesan</span>
                <span x-show="isSubmitting">Memproses...</span>
              </button>
            </div>
          </form>
        </div>
      </template>
    </div>
  </div>

  <!-- Popup kecil (alert/confirm) -->
  <div x-show="popup.show" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl border-gray-300 border-2 shadow-2xl p-6 max-w-md w-full">
      <h2 class="text-lg font-semibold mb-2" x-text="popup.title"></h2>
      <p class="text-sm text-gray-700 mb-6" x-text="popup.message"></p>
      <div class="flex justify-end gap-2">
        <template x-if="popup.type==='confirm'">
          <button @click="popup.onCancel()" class="px-4 py-2 border rounded hover:bg-gray-100">Batal</button>
        </template>
        <button @click="popup.type==='confirm'? popup.onConfirm() : (popup.show=false)"
                class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
          <span x-text="popup.type==='confirm'? 'Lanjutkan' : 'Tutup'"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Modal konfirmasi checkout -->
  <div x-show="showConfirmModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl p-6 shadow-lg max-w-md w-full text-center">
      <h2 class="text-xl font-semibold mb-3">Konfirmasi Pesanan</h2>
      <p class="text-gray-700 mb-6">Pesan produk yang dipilih sekarang?</p>
      <div class="flex justify-center gap-3">
        <button @click="showConfirmModal=false" class="px-5 py-2 border rounded hover:bg-gray-100">Batal</button>
        <button @click="checkout()" :disabled="isSubmitting"
                class="px-5 py-2 bg-black text-white rounded hover:bg-gray-800 disabled:opacity-60">
          <span x-show="!isSubmitting">Ya, Pesan</span>
          <span x-show="isSubmitting">Memproses...</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Modal setelah order diterima -->
  <div x-show="showWaitingModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl p-6 shadow-lg max-w-md w-full text-center">
      <h2 class="text-xl font-semibold mb-4">Pesanan Diterima</h2>
      <p class="text-gray-700 mb-6">Terima kasih, pesanan Anda telah kami terima. Pihak bengkel akan menghubungi Anda melalui WhatsApp maksimal 1×24 jam.</p>
      <a href="{{ route('pesanan.index') }}" class="px-6 py-2 bg-black text-white rounded hover:bg-gray-800">Lihat Pesanan</a>
    </div>
  </div>
</section>

<script>
function keranjangApp(initialIds, initialData) {
  return {
    allIds: initialIds || [],
    selected: [],
    isSubmitting: false,
    showConfirmModal: false,
    popup: { show: false, title: '', message: '', type: 'alert', onConfirm: () => {}, onCancel: () => {} },
    showWaitingModal: false,
    itemData: initialData || {},

    setCartCount(n) {
      if (window.Alpine && Alpine.store && Alpine.store('cart')) {
        Alpine.store('cart').count = n;
      } else {
        const ids = ['cartBadge', 'cartBadgeMobile'];
        ids.forEach((elId) => {
          const el = document.getElementById(elId);
          if (!el) return;
          el.textContent = n > 0 ? String(n) : '';
          el.style.display = n > 0 ? '' : 'none';
        });
      }
    },

    updateCartBadges() {
      this.setCartCount(this.allIds.length);
    },

    isAllSelected() {
      return this.allIds.length > 0 && this.selected.length === this.allIds.length;
    },
    toggleSelectAll() {
      this.selected = this.isAllSelected() ? [] : [...this.allIds];
    },
    toggleItemSelection(id) {
      this.selected = this.selected.includes(id)
        ? this.selected.filter(i => i !== id)
        : [...this.selected, id];
    },

    confirmDelete(id) {
      this.popup = {
        show: true,
        title: 'Hapus Produk',
        message: 'Apakah kamu yakin ingin menghapus produk ini dari keranjang?',
        type: 'confirm',
        onConfirm: () => this.deleteItem(id),
        onCancel: () => this.popup.show = false
      };
    },

    async deleteItem(id) {
      this.popup.show = false;
      try {
        const res = await fetch(`/keranjang/hapus/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        });

        const d = await res.json().catch(() => null);

        if (!res.ok) {
          if (res.status === 401) { alert('Silakan login terlebih dahulu.'); return; }
          if (res.status === 419) { alert('Sesi kedaluwarsa. Muat ulang halaman.'); return; }
          alert((d && (d.message || d.error)) || 'Gagal menghapus item.');
          return;
        }

        this.allIds = this.allIds.filter(i => i !== id);
        this.selected = this.selected.filter(i => i !== id);
        delete this.itemData[id];
        this.setCartCount(typeof d?.cart_count !== 'undefined' ? d.cart_count : this.allIds.length);
      } catch (e) {
        alert('Terjadi kesalahan jaringan.');
      }
    },

    increment(id) {
      const now = parseInt(this.itemData[id]?.jumlah || 1, 10);
      this.changeQty(id, now + 1);
    },
    decrement(id) {
      const now = parseInt(this.itemData[id]?.jumlah || 1, 10);
      const next = now - 1;
      this.changeQty(id, Math.max(0, next)); // 0 = hapus
    },
    onQtyInput(id, val) {
      const qty = parseInt(val, 10);
      if (isNaN(qty)) return;
      this.changeQty(id, Math.max(0, Math.min(999, qty)));
    },

    async changeQty(id, qty) {
      try {
        const res = await fetch(`/keranjang/${id}/jumlah`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify({ jumlah: qty })
        });

        const d = await res.json().catch(() => null);

        if (!res.ok) {
          if (res.status === 401) { alert('Silakan login terlebih dahulu.'); return; }
          if (res.status === 419) { alert('Sesi kedaluwarsa. Muat ulang halaman.'); return; }
          alert((d && (d.message || d.error)) || 'Gagal mengubah jumlah.');
          return;
        }

        if (qty === 0 || d?.new_qty === 0) {
          this.allIds = this.allIds.filter(i => i !== id);
          this.selected = this.selected.filter(i => i !== id);
          delete this.itemData[id];
        } else {
          if (!this.itemData[id]) this.itemData[id] = {};
          this.itemData[id].jumlah = d?.new_qty ?? qty;

          // (opsional) kalau ingin ketika qty berubah item naik ke atas:
          // pindahkan item ke urutan pertama
          // this.allIds = [id, ...this.allIds.filter(i => i !== id)];
        }

        this.setCartCount(typeof d?.cart_count !== 'undefined' ? d.cart_count : this.allIds.length);
      } catch (e) {
        alert('Terjadi kesalahan jaringan.');
      }
    },

    openConfirm() {
      if (!this.selected.length) {
        this.popup = { show: true, title: 'Tidak Ada Produk Dipilih', message: 'Pilih setidaknya satu produk sebelum memesan.', type: 'alert', onConfirm: () => {}, onCancel: () => {} };
        return;
      }
      this.showConfirmModal = true;
    },

    async checkout() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;
      try {
        const res = await fetch('{{ route('keranjang.pesan') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify({ items: this.selected, kirim_email: true })
        });

        const d = await res.json().catch(() => null);

        if (!res.ok) {
          if (res.status === 401) { this.showConfirmModal = false; alert('Silakan login terlebih dahulu.'); return; }
          if (res.status === 419) { this.showConfirmModal = false; alert('Sesi kedaluwarsa. Muat ulang halaman.'); return; }
          alert((d && (d.message || d.error)) || 'Gagal membuat pesanan.');
          return;
        }

        if (d && d.success) {
          const removed = new Set(this.selected);
          this.allIds = this.allIds.filter(id => !removed.has(id));
          this.selected = [];
          for (const rid of removed) { delete this.itemData[rid]; }
          this.showConfirmModal = false;
          this.showWaitingModal = true;

          if (typeof d.cart_count !== 'undefined') {
            this.setCartCount(parseInt(d.cart_count, 10) || 0);
          } else {
            this.updateCartBadges();
          }
        } else {
          alert((d && (d.error || d.message)) || 'Gagal membuat pesanan.');
        }
      } catch (e) {
        alert('Terjadi kesalahan jaringan.');
      } finally {
        this.isSubmitting = false;
      }
    }
  };
}
</script>
@endsection
