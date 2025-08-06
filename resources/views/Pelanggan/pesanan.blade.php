@extends('Template.pelanggan')

@section('title', 'Pesanan Saya')

@section('content')
<section class="bg-gray-200 py-10 px-5 min-h-screen" x-data="pesananApp()" x-init="
    @if (session('success')) showAlert('{{ session('success') }}', 'success') @endif
    @if (session('error')) showAlert('{{ session('error') }}', 'error') @endif
">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow px-6 py-6 min-h-[540px]">
            <h1 class="text-2xl font-bold mb-6">Status Pesanan</h1>

            @if ($pemesanan->isEmpty())
                <div class="col-span-full flex items-center justify-center text-gray-500 min-h-[400px]">
                    <p class=" text-center text-gray-600">Belum ada pesanan.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($pemesanan as $pesanan)
                        @php
                            $statusText = match($pesanan->status) {
                                'pending' => 'Belum Dibayar',
                                'menunggu' => 'Diproses',
                                'dikerjakan' => 'Dikerjakan',
                                'selesai' => 'Selesai',
                                'gagal' => 'Gagal',
                                'menunggu_refund' => 'Menunggu Refund',
                                'refund_diterima' => 'Refund Diterima',
                                default => ucfirst($pesanan->status),
                            };
                            $statusColor = match($pesanan->status) {
                                'pending', 'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'dikerjakan' => 'bg-blue-100 text-blue-800',
                                'selesai' => 'bg-green-100 text-green-800',
                                'gagal' => 'bg-red-100 text-red-800',
                                'menunggu_refund' => 'bg-yellow-50 text-yellow-800',
                                'refund_diterima' => 'bg-green-50 text-green-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp

                        <div class="border rounded-lg shadow-sm p-4 bg-white">
                            <div class="flex justify-between items-center mb-3">
                                <div class="text-sm text-gray-600"><strong>{{ $pesanan->created_at->format('d-m-Y') }}</strong></div>
                                <div class="text-sm px-2 py-1 rounded {{ $statusColor }}">{{ $statusText }}</div>
                            </div>

                            <div class="space-y-4">
                                @foreach ($pesanan->details as $d)
                                    @php
                                        $gambar = $d->produk->gambar->first()?->gambar;
                                        $harga = ($d->subtotal && $d->subtotal > 0) ? $d->subtotal : ($d->produk->harga * $d->panjang * $d->lebar * $d->tinggi);
                                    @endphp
                                    <div class="flex flex-col sm:flex-row gap-4 border p-3 rounded items-start">
                                        <div class="w-full sm:w-40 h-40 shrink-0 bg-gray-100 border rounded overflow-hidden">
                                            @if ($gambar)
                                                <img src="{{ asset('storage/' . $gambar) }}" alt="Gambar Produk" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs text-center px-2">Tidak ada gambar</div>
                                            @endif
                                        </div>
                                        <div class="flex-1 space-y-1 text-sm text-gray-700 w-full">
                                            <div><span class="font-medium">Nama Produk:</span> {{ $d->produk->nama ?? '-' }}</div>
                                            <div><span class="font-medium">Kategori:</span> {{ $d->produk->kategori ?? '-' }}</div>
                                            <div class="mt-2 font-medium text-gray-800">Ukuran:</div>
                                            <ul class="ml-4 list-disc text-gray-600">
                                                <li>Panjang: {{ (float)$d->panjang }} m</li>
                                                <li>Lebar: {{ (float)$d->lebar }} m</li>
                                                <li>Tinggi: {{ (float)$d->tinggi }} m</li>
                                            </ul>
                                            <div class="mt-2"><span class="font-medium">Harga:</span> Rp {{ number_format($harga, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                <p class="font-semibold text-gray-800">Total Harga:</p>
                                <p class="text-gray-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                            </div>

                            @if ($pesanan->status === 'pending')
                                <div class="mt-3">
                                    <button @click="pay('{{ $pesanan->id }}')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Bayar Sekarang</button>
                                </div>
                            @elseif ($pesanan->status === 'menunggu')
                                <div class="mt-3">
                                    <form action="{{ route('pesanan.batal', $pesanan->id) }}" method="POST" @submit.prevent="confirmAction('Batalkan Pesanan', 'Yakin ingin membatalkan pesanan ini?', () => $el.submit())">
                                        @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Batalkan Pesanan</button>
                                    </form>
                                </div>
                            @elseif ($pesanan->status === 'menunggu_refund')
                                <div class="mt-3">
                                    <form action="{{ route('pesanan.batalkan_refund', $pesanan->id) }}" method="POST" @submit.prevent="confirmAction('Batalkan Refund', 'Yakin ingin membatalkan pengajuan refund ini?', () => $el.submit())">
                                        @csrf
                                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">Batalkan Refund</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex justify-center mt-6">
            <ul class="inline-flex items-center text-sm">
                <div class="inline-flex space-x-1 mr-2">
                    @if ($pemesanan->onFirstPage())
                        <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
                        <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                    @else
                        <li><a href="{{ $pemesanan->appends(request()->except('page'))->url(1) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a></li>
                        <li><a href="{{ $pemesanan->appends(request()->except('page'))->previousPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a></li>
                    @endif
                </div>
                <div class="inline-flex space-x-1 mx-2">
                    @php
                        $current = $pemesanan->currentPage();
                        $last = $pemesanan->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $start + 4);
                        if ($end - $start < 4) $start = max(1, $end - 4);
                    @endphp
                    @for ($i = $start; $i <= $end; $i++)
                        <li><a href="{{ $pemesanan->appends(request()->except('page'))->url($i) }}" class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">{{ $i }}</a></li>
                    @endfor
                </div>
                <div class="inline-flex space-x-1 ml-2">
                    @if ($pemesanan->hasMorePages())
                        <li><a href="{{ $pemesanan->appends(request()->except('page'))->nextPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a></li>
                        <li><a href="{{ $pemesanan->appends(request()->except('page'))->url($pemesanan->lastPage()) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a></li>
                    @else
                        <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                        <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                    @endif
                </div>
            </ul>
        </div>
    </div>

    <div x-show="popup.show" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-transparent" style="pointer-events: auto;">
        <div class="bg-white rounded-xl border shadow-xl p-6 max-w-md w-full" @click.away="popup.show = false">
            <h2 class="text-lg font-semibold mb-2" x-text="popup.title"></h2>
            <p class="text-sm text-gray-700 mb-6" x-text="popup.message"></p>
            <div class="flex justify-end gap-2">
                <template x-if="popup.type === 'confirm'">
                    <button @click="popup.onCancel()" class="px-4 py-2 rounded border text-black bg-white hover:bg-gray-100">Batal</button>
                </template>
                <button :class="'px-4 py-2 rounded text-white ' + (popup.type === 'alert' ? 'bg-black hover:bg-gray-800' : 'bg-black hover:bg-gray-800')" @click="popup.type === 'confirm' ? popup.onConfirm() : popup.show = false">
                    <span x-text="popup.type === 'confirm' ? 'Lanjutkan' : 'Tutup'"></span>
                </button>
            </div>
        </div>
    </div>
</section>

<div id="loadingBackdrop" style="display:none; position:fixed; inset:0; background:rgba(255,255,255,0.6); z-index:9999;" class="flex items-center justify-center">
    <span class="text-black font-semibold text-lg">Memuat pembayaran...</span>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    function pesananApp() {
        return {
            popup: {
                show: false,
                title: '',
                message: '',
                type: 'alert',
                onConfirm: () => {},
                onCancel: () => {}
            },
            showAlert(message, type = 'alert') {
                this.popup = {
                    show: true,
                    title: type === 'success' ? 'Berhasil' : 'Pemberitahuan',
                    message: message,
                    type: 'alert'
                };
            },
            confirmAction(title, message, onConfirm) {
                this.popup = {
                    show: true,
                    title,
                    message,
                    type: 'confirm',
                    onConfirm: onConfirm,
                    onCancel: () => this.popup.show = false
                };
            },
            pay(id) {
                document.getElementById('loadingBackdrop').style.display = 'flex';
                fetch(`/pesanan/${id}/bayar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('loadingBackdrop').style.display = 'none';
                    if (data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: () => window.location.reload(),
                            onPending: () => window.location.reload(),
                            onError: result => this.showAlert('Pembayaran gagal: ' + result.status_message, 'alert'),
                            onClose: () => this.showAlert('Kamu menutup popup sebelum membayar.', 'alert')
                        });
                    } else {
                        this.showAlert('Gagal mendapatkan token pembayaran.', 'alert');
                    }
                })
                .catch(() => {
                    document.getElementById('loadingBackdrop').style.display = 'none';
                    this.showAlert('Terjadi kesalahan saat memproses pembayaran.', 'alert');
                });
            }
        }
    }
</script>
@endsection
