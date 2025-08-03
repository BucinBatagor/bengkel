@extends('Template.pelanggan')

@section('title', 'Keranjang Saya')

@section('content')
<section class="py-10 bg-gray-200 min-h-screen" x-data="keranjangApp({{ $items->toJson() }})">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="bg-white rounded-xl shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Keranjang Saya</h1>

            <template x-if="items.length === 0">
                <p class="text-gray-700">Tidak ada produk yang dimasukkan ke keranjang.</p>
            </template>

            <template x-if="items.length > 0">
                <div>
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" id="selectAll" @click="toggleSelectAll()" :checked="isAllSelected()" class="w-4 h-4 mr-2">
                        <label for="selectAll">Pilih Semua</label>
                    </div>

                    <form @submit.prevent="checkout">
                        <div class="space-y-6">
                            <template x-for="item in items" :key="item.id">
                                <div class="bg-white rounded-lg shadow p-4 relative">
                                    <button type="button" @click="confirmDelete(item.id)" class="absolute top-2 right-2 text-red-600 hover:text-red-800 text-lg font-bold">✕</button>

                                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                        <div class="flex items-start sm:items-center gap-4 w-full sm:w-auto">
                                            <input type="checkbox" :value="item.id" :checked="selected.includes(item.id)" @change="toggleItemSelection(item.id)" class="mt-1 sm:mt-0 w-4 h-4">
                                            <img :src="item.gambar" class="w-20 h-20 object-cover rounded">
                                        </div>

                                        <div class="flex-1 space-y-1">
                                            <h2 class="font-semibold text-lg" x-text="item.nama"></h2>
                                            <p class="text-sm text-gray-600" x-text="formatRp(item.harga) + ' / m²'"></p>
                                            <div class="flex gap-2 flex-wrap mt-2">
                                                <div class="flex flex-col"><label class="text-xs text-gray-500">Panjang</label><input type="number" min="0" step="1" class="border rounded p-2 w-24" x-model.number="item.panjang" @input="checkAutoSelect(item)"></div>
                                                <div class="flex flex-col"><label class="text-xs text-gray-500">Lebar</label><input type="number" min="0" step="1" class="border rounded p-2 w-24" x-model.number="item.lebar" @input="checkAutoSelect(item)"></div>
                                                <div class="flex flex-col"><label class="text-xs text-gray-500">Tinggi</label><input type="number" min="0" step="1" class="border rounded p-2 w-24" x-model.number="item.tinggi" @input="checkAutoSelect(item)"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-6 flex justify-between items-center flex-wrap gap-4">
                            <p class="text-lg font-bold">Total Harga: <span x-text="formatRp(totalHarga())"></span></p>
                            <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">Checkout</button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

    <!-- Popup -->
    <div x-show="popup.show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        class="fixed inset-0 z-50 flex items-center justify-center px-4">

        <div class="bg-white rounded-xl border-2 border-gray-300 shadow-2xl p-6 max-w-md w-full">

            <h2 class="text-lg font-semibold mb-2" x-text="popup.title"></h2>
            <p class="text-sm text-gray-700 mb-6" x-text="popup.message"></p>
            <div class="flex justify-end gap-2">
                <template x-if="popup.type === 'confirm'">
                    <button @click="popup.onCancel()" class="px-4 py-2 rounded border text-black bg-white hover:bg-gray-100">Batal</button>
                </template>
                <button
                    :class="popup.type === 'alert' ? 'bg-black text-white hover:bg-gray-800' : 'bg-black text-white hover:bg-gray-800'"
                    class="px-4 py-2 rounded"
                    @click="popup.type === 'confirm' ? popup.onConfirm() : popup.show = false">
                    <span x-text="popup.type === 'confirm' ? 'Lanjutkan' : 'Tutup'"></span>
                </button>
            </div>
        </div>
    </div>
</section>

<script>
    function keranjangApp(data) {
        return {
            items: data.map(i => ({
                ...i,
                panjang: Number(i.panjang) || 0,
                lebar: Number(i.lebar) || 0,
                tinggi: Number(i.tinggi) || 0
            })),
            selected: [],
            popup: {
                show: false,
                title: '',
                message: '',
                type: 'alert',
                onConfirm: () => {},
                onCancel: () => {}
            },

            isAllSelected() {
                return this.selected.length === this.items.length;
            },
            toggleSelectAll() {
                this.selected = this.isAllSelected() ? [] : this.items.map(i => i.id);
            },
            toggleItemSelection(id) {
                this.selected.includes(id) ?
                    this.selected = this.selected.filter(i => i !== id) :
                    this.selected.push(id);
            },
            itemSubtotal(item) {
                return (item.panjang > 0 && item.lebar > 0 && item.tinggi > 0) ?
                    (item.panjang + item.lebar + item.tinggi) * item.harga :
                    0;
            },
            totalHarga() {
                return this.items
                    .filter(i => this.selected.includes(i.id))
                    .reduce((sum, i) => sum + this.itemSubtotal(i), 0);
            },
            formatRp(val) {
                return 'Rp ' + Number(val).toLocaleString('id-ID');
            },
            checkAutoSelect(item) {
                const filled = item.panjang > 0 && item.lebar > 0 && item.tinggi > 0;
                if (filled && !this.selected.includes(item.id)) this.selected.push(item.id);
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
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                }).then(() => location.reload());
            },
            checkout() {
                const selectedItems = this.items.filter(i => this.selected.includes(i.id));
                const invalidItems = selectedItems.filter(i => !(i.panjang > 0 && i.lebar > 0 && i.tinggi > 0));

                if (selectedItems.length === 0) {
                    this.popup = {
                        show: true,
                        title: 'Tidak Ada Produk Dipilih',
                        message: 'Pilih setidaknya satu produk sebelum melanjutkan ke pembayaran.',
                        type: 'alert'
                    };
                    return;
                }

                if (invalidItems.length > 0) {
                    this.popup = {
                        show: true,
                        title: 'Ukuran Belum Lengkap',
                        message: 'Isi panjang, lebar, dan tinggi untuk semua produk yang dipilih sebelum checkout.',
                        type: 'alert'
                    };
                    return;
                }

                fetch('{{ route("keranjang.checkout") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            selected_items: selectedItems
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            snap.pay(data.token, {
                                onSuccess: () => window.location.href = '/pesanan?sukses=true',
                                onPending: () => window.location.href = '/pesanan?status=pending',
                                onError: () => {
                                    console.error(result);
                                    this.popup = {
                                        show: true,
                                        title: 'Pembayaran Gagal',
                                        message: 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.',
                                        type: 'alert'
                                    };
                                },
                                onClose: () => {
                                    this.popup = {
                                        show: true,
                                        title: 'Pembayaran Belum Selesai',
                                        message: 'Kamu menutup halaman pembayaran. Ingin tetap di halaman keranjang atau lanjut ke pesanan?',
                                        type: 'confirm',
                                        onConfirm: () => window.location.href = '/pesanan?status=closed',
                                        onCancel: () => location.reload()
                                    };
                                }
                            });
                        } else {
                            this.popup = {
                                show: true,
                                title: 'Kesalahan',
                                message: data.error || 'Terjadi kesalahan saat memproses checkout.',
                                type: 'alert'
                            };
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        this.popup = {
                            show: true,
                            title: 'Kesalahan Jaringan',
                            message: 'Tidak dapat terhubung ke server. Silakan coba beberapa saat lagi.',
                            type: 'alert'
                        };
                    });
            }
        }
    }
</script>


<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
@endsection