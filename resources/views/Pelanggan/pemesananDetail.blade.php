@extends('Template.pelanggan')

@section('title', 'Keranjang Saya')

@section('content')
<section class="py-10 px-5 min-h-screen" x-data="keranjangApp({{ $items->toJson() }})">
    <div class="max-w-screen-xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Keranjang Saya</h1>

        <div class="mb-4 flex items-center">
            <input type="checkbox" id="selectAll"
                @click="toggleSelectAll()"
                :checked="isAllSelected()"
                class="w-4 h-4 mr-2">
            <label for="selectAll">Pilih Semua</label>
        </div>

        <form @submit.prevent="checkout">
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="w-full table-auto">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 text-left"></th>
                            <th class="p-3 text-left">Produk</th>
                            <th class="p-3 text-left">Panjang</th>
                            <th class="p-3 text-left">Lebar</th>
                            <th class="p-3 text-left">Tinggi</th>
                            <th class="p-3 text-left">Harga / m²</th>
                            <th class="p-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in items" :key="item.id">
                            <tr class="border-b">
                                <td class="p-3">
                                    <input type="checkbox"
                                        :value="item.id"
                                        :checked="selected.includes(item.id)"
                                        @change="toggleItemSelection(item.id)">
                                </td>
                                <td class="p-3 flex items-center gap-3">
                                    <img :src="item.gambar" class="w-16 h-16 object-cover rounded">
                                    <span x-text="item.nama"></span>
                                </td>
                                <td class="p-3">
                                    <input type="number" placeholder="Panjang" min="0" step="1"
                                        class="border rounded p-1 w-20 text-right"
                                        x-model.number="item.panjang"
                                        @input="checkAutoSelect(item)">
                                </td>
                                <td class="p-3">
                                    <input type="number" placeholder="Lebar" min="0" step="1"
                                        class="border rounded p-1 w-20 text-right"
                                        x-model.number="item.lebar"
                                        @input="checkAutoSelect(item)">
                                </td>
                                <td class="p-3">
                                    <input type="number" placeholder="Tinggi" min="0" step="1"
                                        class="border rounded p-1 w-20 text-right"
                                        x-model.number="item.tinggi"
                                        @input="checkAutoSelect(item)">
                                </td>
                                <td class="p-3">
                                    <span x-text="formatRp(item.harga)"></span>
                                </td>
                                <td class="p-3">
                                    <form :action="`/keranjang/hapus/${item.id}`" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between items-center">
                <p class="text-lg font-bold">
                    Total Harga: <span x-text="formatRp(totalHarga())"></span>
                </p>
                <button type="submit"
                        class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
                    Checkout
                </button>
            </div>
        </form>
    </div>
</section>

<script>
function keranjangApp(data) {
    return {
        items: data.map(i => ({
            ...i,
            panjang: Number(i.panjang) || 0,
            lebar: Number(i.lebar) || 0,
            tinggi: Number(i.tinggi) || 0,
        })),
        selected: [],

        isAllSelected() {
            return this.selected.length === this.items.length;
        },
        toggleSelectAll() {
            if (this.isAllSelected()) {
                this.selected = [];
            } else {
                this.selected = this.items.map(i => i.id);
            }
        },
        toggleItemSelection(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
            }
        },
        itemSubtotal(item) {
            if (item.panjang > 0 && item.lebar > 0 && item.tinggi > 0) {
                return (item.panjang + item.lebar + item.tinggi) * item.harga;
            }
            return 0;
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
            const isFilled = item.panjang > 0 && item.lebar > 0 && item.tinggi > 0;
            if (isFilled && !this.selected.includes(item.id)) {
                this.selected.push(item.id);
            }
        },
        checkout() {
            const selectedItems = this.items.filter(i => this.selected.includes(i.id));

            const invalidItems = selectedItems.filter(i =>
                !(i.panjang > 0 && i.lebar > 0 && i.tinggi > 0)
            );

            if (selectedItems.length === 0) {
                alert('Tidak ada produk yang dipilih.');
                return;
            }

            if (invalidItems.length > 0) {
                alert('Semua produk yang dipilih harus memiliki ukuran panjang, lebar, dan tinggi yang valid.');
                return;
            }

            fetch('{{ route("keranjang.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ selected_items: selectedItems })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            window.location.href = '/pesanan?sukses=true';
                        },
                        onPending: function(result) {
                            window.location.href = '/pesanan?status=pending';
                        },
                        onError: function(result) {
                            alert("Pembayaran gagal.");
                            console.error(result);
                        },
                        onClose: function() {
                            alert('Kamu menutup popup pembayaran.');
                        }
                    });
                } else {
                    alert(data.error || 'Terjadi kesalahan saat checkout.');
                }
            })
            .catch(error => {
                console.error(error);
                alert('Terjadi kesalahan jaringan.');
            });
        }
    }
}
</script>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

@endsection
