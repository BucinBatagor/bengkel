@extends('Template.admin')

@section('title', 'Laporan Pendapatan')

@section('content')
<section class="flex flex-col items-center px-4 sm:px-6 py-6"
    x-data="{
        mfrom: '{{ request('mfrom', $month) }}',
        mto:   '{{ request('mto', $month) }}',
        validateRange(e){
            if(this.mfrom && this.mto && this.mto < this.mfrom){
                e.preventDefault();
                alert('Sampai Bulan tidak boleh lebih kecil dari Dari Bulan.');
            }
        }
    }">
    <div class="w-full max-w-screen-xl bg-white px-4 sm:px-8 py-6 rounded-2xl shadow-md">

        @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif
        @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
        @endif

        <div class="rounded-xl border border-gray-800 bg-black text-white p-5 mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold tracking-wide">LAPORAN PENDAPATAN</h1>
                    <p class="text-sm text-gray-200 mt-1">
                        Periode:
                        <strong>{{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }}</strong>
                        s/d
                        <strong>{{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</strong>
                    </p>
                </div>
                <div class="w-full lg:w-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <a href="{{ route('admin.laporan.index', ['month' => $prevMonth]) }}"
                           class="w-full text-center rounded-lg border border-white/20 bg-white/10 px-3 py-2 hover:bg-white/20 transition">
                            &laquo; Bulan Sebelumnya
                        </a>
                        <a href="{{ route('admin.laporan.index', ['month' => now('Asia/Jakarta')->format('Y-m')]) }}"
                           class="w-full text-center rounded-lg border border-white/20 bg-white/10 px-3 py-2 hover:bg-white/20 transition">
                            Bulan Ini
                        </a>
                        <a href="{{ route('admin.laporan.index', ['month' => $nextMonth]) }}"
                           class="w-full text-center rounded-lg border border-white/20 bg-white/10 px-3 py-2 hover:bg-white/20 transition">
                            Bulan Berikutnya &raquo;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.laporan.index') }}"
              class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3 sm:gap-4 mb-6 items-end">
            <div class="w-full">
                <label class="block text-sm font-medium mb-1">Pilih Bulan</label>
                <input type="month" name="month" value="{{ $month }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-black">
            </div>
            <div class="w-full sm:w-auto">
                <button type="submit"
                        class="w-full sm:w-auto bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    Tampilkan
                </button>
            </div>
        </form>

        <div class="rounded-xl border border-gray-200 p-4 sm:p-5 mb-8">
            <form method="GET" action="{{ route('admin.laporan.export') }}"
                  class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 items-end"
                  @submit="validateRange($event)">
                <div class="w-full">
                    <label class="block text-sm font-medium mb-1">Dari Bulan</label>
                    <input type="month" name="mfrom" x-model="mfrom"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-black">
                </div>
                <div class="w-full">
                    <label class="block text-sm font-medium mb-1">Sampai Bulan</label>
                    <input type="month" name="mto" x-model="mto"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-black">
                </div>
                <div class="w-full md:w-auto">
                    <button type="submit"
                            class="w-full md:w-auto bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        Export PDF
                    </button>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-2">
                Kosongkan salah satu, sistem otomatis mengekspor bulan yang diisi saja.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 hover:shadow-sm transition">
                <div class="text-xs text-gray-500 mb-1">Transaksi Selesai</div>
                <div class="text-2xl sm:text-3xl font-bold break-words [overflow-wrap:anywhere] leading-tight">
                    {{ number_format($ringkasan['count'] ?? 0) }}
                </div>
            </div>
            <div class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 hover:shadow-sm transition">
                <div class="text-xs text-gray-500 mb-1">Total Harga</div>
                <div class="text-2xl sm:text-3xl font-bold break-words [overflow-wrap:anywhere] leading-tight">
                    Rp {{ number_format($ringkasan['gross'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 hover:shadow-sm transition">
                <div class="text-xs text-gray-500 mb-1">Total Bahan Besi</div>
                <div class="text-2xl sm:text-3xl font-bold break-words [overflow-wrap:anywhere] leading-tight">
                    Rp {{ number_format($ringkasan['total_bahan_besi'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 hover:shadow-sm transition">
                <div class="text-xs text-gray-500 mb-1">Total Bahan Lainnya</div>
                <div class="text-2xl sm:text-3xl font-bold break-words [overflow-wrap:anywhere] leading-tight">
                    Rp {{ number_format($ringkasan['total_bahan_lainnya'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 hover:shadow-sm transition">
                <div class="text-xs text-gray-500 mb-1">Total Jasa</div>
                <div class="text-2xl sm:text-3xl font-bold break-words [overflow-wrap:anywhere] leading-tight">
                    Rp {{ number_format($ringkasan['total_jasa'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6">
            <div class="text-xs text-gray-500">Pendapatan Bersih Bulan Ini</div>
            <div class="text-3xl sm:text-4xl font-extrabold mt-1 break-words [overflow-wrap:anywhere] leading-tight">
                Rp {{ number_format($ringkasan['net'] ?? 0, 0, ',', '.') }}
            </div>
            @if(($ringkasan['count'] ?? 0) === 0)
                <p class="mt-3 text-sm text-gray-500">Tidak ada transaksi selesai pada bulan ini.</p>
            @endif
        </div>

    </div>
</section>
@endsection
