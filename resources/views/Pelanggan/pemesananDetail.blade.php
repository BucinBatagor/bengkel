@extends('Template.pelanggan')

@section('title', 'Keranjang Saya')

@section('content')
<section class="py-10 bg-gray-200 min-h-screen" x-data="keranjangApp({{ $items->pluck('id')->toJson() }})">
  <div class="max-w-screen-xl mx-auto px-4">
    <div class="bg-white rounded-xl shadow p-6">
      <h1 class="text-2xl font-bold mb-6">Keranjang Saya</h1>

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
            <div class="space-y-6">
              <template x-for="id in allIds" :key="id">
                <div class="bg-white rounded-lg shadow p-4 relative">
                  <button type="button" @click="confirmDelete(id)" class="absolute top-2 right-2 text-red-600 hover:text-red-800 text-lg font-bold">✕</button>
                  <div class="flex items-center gap-4">
                    <input type="checkbox" :value="id" :checked="selected.includes(id)" @change="toggleItemSelection(id)" class="w-4 h-4">
                    <img :src="itemData[id].gambar" class="w-24 h-24 object-cover rounded-lg border">
                    <div class="flex-1">
                      <h2 class="font-semibold text-lg" x-text="itemData[id].nama"></h2>
                      <p class="text-sm text-gray-500" x-text="itemData[id].kategori"></p>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <div class="mt-6 flex justify-end">
              <button type="submit" :disabled="isSubmitting" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!isSubmitting">Pesan</span>
                <span x-show="isSubmitting">Memproses...</span>
              </button>
            </div>
          </form>
        </div>
      </template>
    </div>
  </div>

  <div x-show="popup.show" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl border-gray-300 border-2 shadow-2xl p-6 max-w-md w-full">
      <h2 class="text-lg font-semibold mb-2" x-text="popup.title"></h2>
      <p class="text-sm text-gray-700 mb-6" x-text="popup.message"></p>
      <div class="flex justify-end gap-2">
        <template x-if="popup.type==='confirm'">
          <button @click="popup.onCancel()" class="px-4 py-2 border rounded hover:bg-gray-100">Batal</button>
        </template>
        <button @click="popup.type==='confirm'? popup.onConfirm() : (popup.show=false)" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
          <span x-text="popup.type==='confirm'? 'Lanjutkan' : 'Tutup'"></span>
        </button>
      </div>
    </div>
  </div>

  <div x-show="showConfirmModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl p-6 shadow-lg max-w-md w-full text-center">
      <h2 class="text-xl font-semibold mb-3">Konfirmasi Pesanan</h2>
      <p class="text-gray-700 mb-6">Pesan produk yang dipilih sekarang?</p>
      <div class="flex justify-center gap-3">
        <button @click="showConfirmModal=false" class="px-5 py-2 border rounded hover:bg-gray-100">Batal</button>
        <button @click="checkout()" :disabled="isSubmitting" class="px-5 py-2 bg-black text-white rounded hover:bg-gray-800 disabled:opacity-60">
          <span x-show="!isSubmitting">Ya, Pesan</span>
          <span x-show="isSubmitting">Memproses...</span>
        </button>
      </div>
    </div>
  </div>

  <div x-show="showWaitingModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl p-6 shadow-lg max-w-md w-full text-center">
      <h2 class="text-xl font-semibold mb-4">Pesanan Diterima</h2>
      <p class="text-gray-700 mb-6">Terima kasih, pesanan Anda telah kami terima. Silakan tunggu 1×24 jam, pihak bengkel akan menghubungi Anda melalui WhatsApp.</p>
      <a href="{{ route('pesanan.index') }}" class="px-6 py-2 bg-black text-white rounded hover:bg-gray-800">Lihat Pesanan</a>
    </div>
  </div>
</section>

<script>
function keranjangApp(initialIds) {
  return {
    allIds: initialIds,
    selected: [],
    isSubmitting: false,
    showConfirmModal: false,
    popup: { show: false, title: '', message: '', type: 'alert', onConfirm: () => {}, onCancel: () => {} },
    showWaitingModal: false,
    itemData: {!! json_encode(
      $items->mapWithKeys(function($item){
        return [$item['id'] => [
          'nama' => $item['nama'],
          'kategori' => $item['kategori'],
          'gambar' => $item['gambar']
        ]];
      })
    ) !!},
    isAllSelected() {
      return this.selected.length === this.allIds.length && this.allIds.length > 0;
    },
    toggleSelectAll() {
      this.selected = this.isAllSelected() ? [] : [...this.allIds];
    },
    toggleItemSelection(id) {
      this.selected.includes(id) ? this.selected = this.selected.filter(i => i !== id) : this.selected.push(id);
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
    deleteItem(id) {
      this.popup.show = false;
      fetch(`/keranjang/hapus/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      }).then(() => location.reload());
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
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ items: this.selected, kirim_email: true })
        });
        const d = await res.json();
        if (d.success) {
          this.showConfirmModal = false;
          this.showWaitingModal = true;
          this.allIds = [];
          this.selected = [];
        } else {
          alert(d.error || 'Gagal membuat pesanan.');
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
