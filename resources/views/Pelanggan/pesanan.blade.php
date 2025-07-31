@extends('Template.pelanggan')

@section('title', 'Pesanan Saya')

@section('content')
<section class=" bg-gray-200 py-10 px-5 min-h-screen">
    <div class="max-w-screen-xl mx-auto">
        <div class="bg-white rounded-lg shadow px-6 py-6 min-h-[500px]">
            <h1 class="text-2xl font-bold mb-6">Daftar Pesanan</h1>

            @if (session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                {{ session('error') }}
            </div>
            @endif

            @if ($pemesanan->isEmpty())
            <p class="text-gray-600">Belum ada pesanan.</p>
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
                        <div class="text-sm text-gray-600">Tanggal: <strong>{{ $pesanan->created_at->format('d-m-Y') }}</strong></div>
                        <div class="text-sm px-2 py-1 rounded {{ $statusColor }}">
                            {{ $statusText }}
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($pesanan->details as $d)
                        @php
                        $gambar = $d->produk->gambar->first()?->gambar;
                        $harga = ($d->subtotal && $d->subtotal > 0)
                        ? $d->subtotal
                        : ($d->produk->harga * $d->panjang * $d->lebar * $d->tinggi);
                        @endphp
                        <div class="flex gap-4 border p-3 rounded items-start">
                            {{-- Gambar Produk --}}
                            <div class="w-40 h-40 shrink-0 bg-gray-100 border rounded overflow-hidden">
                                @if ($gambar)
                                <img src="{{ asset('storage/' . $gambar) }}" alt="Gambar Produk" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs text-center px-2">
                                    Tidak ada gambar
                                </div>
                                @endif
                            </div>

                            {{-- Detail Produk --}}
                            <div class="flex-1 space-y-1 text-sm text-gray-700">
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

                    {{-- Total Harga & Aksi --}}
                    <div class="mt-4">
                        <p class="font-semibold text-gray-800">Total Harga:</p>
                        <p class="text-gray-700">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                    </div>

                    @if ($pesanan->status === 'pending')
                    <div class="mt-3">
                        <button onclick="pay('{{ $pesanan->id }}')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                            Bayar Sekarang
                        </button>
                    </div>
                    @elseif ($pesanan->status === 'menunggu')
                    <div class="mt-3">
                        <form action="{{ route('pesanan.batal', $pesanan->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Batalkan pesanan ini?')"
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                Batalkan Pesanan
                            </button>
                        </form>
                    </div>
                    @elseif ($pesanan->status === 'menunggu_refund')
                    <div class="mt-3">
                        <form action="{{ route('pesanan.batalkan_refund', $pesanan->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Batalkan pengajuan refund ini dan kembali ke status menunggu?')"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                Batalkan Refund
                            </button>
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
                    <li>
                        <a href="{{ $pemesanan->appends(request()->except('page'))->url(1) }}"
                            class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a>
                    </li>
                    <li>
                        <a href="{{ $pemesanan->appends(request()->except('page'))->previousPageUrl() }}"
                            class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a>
                    </li>
                    @endif
                </div>
                <div class="inline-flex space-x-1 mx-2">
                    @php
                    $current = $pemesanan->currentPage();
                    $last = $pemesanan->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $start + 4);
                    if ($end - $start < 4) $start=max(1, $end - 4);
                        @endphp
                        @for ($i=$start; $i <=$end; $i++)
                        <li>
                        <a href="{{ $pemesanan->appends(request()->except('page'))->url($i) }}"
                            class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">
                            {{ $i }}
                        </a>
                        </li>
                        @endfor
                </div>
                <div class="inline-flex space-x-1 ml-2">
                    @if ($pemesanan->hasMorePages())
                    <li>
                        <a href="{{ $pemesanan->appends(request()->except('page'))->nextPageUrl() }}"
                            class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a>
                    </li>
                    <li>
                        <a href="{{ $pemesanan->appends(request()->except('page'))->url($pemesanan->lastPage()) }}"
                            class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a>
                    </li>
                    @else
                    <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                    <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                    @endif
                </div>
            </ul>
        </div>
    </div>
</section>

<div id="loadingBackdrop" style="display:none; position:fixed; inset:0; background:rgba(255,255,255,0.6); z-index:9999;" class="flex items-center justify-center">
    <span class="text-black font-semibold text-lg">Memuat pembayaran...</span>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    function pay(id) {
        document.getElementById('loadingBackdrop').style.display = 'flex';

        fetch(`/pesanan/${id}/bayar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingBackdrop').style.display = 'none';
                if (data.snap_token) {
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            alert('Pembayaran berhasil!');
                            window.location.reload();
                        },
                        onPending: function(result) {
                            alert('Transaksi belum selesai.');
                            window.location.reload();
                        },
                        onError: function(result) {
                            alert('Pembayaran gagal: ' + result.status_message);
                        },
                        onClose: function() {
                            alert('Kamu menutup popup sebelum membayar.');
                        }
                    });
                } else {
                    alert('Gagal mendapatkan token pembayaran.');
                }
            })
            .catch(error => {
                document.getElementById('loadingBackdrop').style.display = 'none';
                console.error(error);
                alert('Terjadi kesalahan.');
            });
    }
</script>
@endsection