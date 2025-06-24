<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beranda</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-700">

    <nav class="bg-white">
        <div class="flex max-w-screen-xl mx-auto py-4 items-center justify-between">

            <!-- Logo + Menu -->
            <div class="flex items-center space-x-5">
                <!-- Logo -->
                <a href="/beranda">
                    <img src="/assets/LogoBengkel.png" alt="Logo" class="h-10 w-10 rounded-full object-cover" />
                </a>

                <!-- Menu -->
                <ul class="flex items-center space-x-4 text-sm font-semibold text-black">
                    <li><a href="/beranda" class="hover:underline text-base">Beranda</a></li>
                    <li class="border h-5 border-black"></li>
                    <li><a href="#" class="hover:underline text-base">Katalog</a></li>
                </ul>
            </div>

            <!-- Login -->
            <div class="flex items-center space-x-4 text-sm font-semibold">
                <a href="#" class="border border-black px-3 py-1 rounded hover:bg-gray-100 text-base">Login</a>
            </div>

        </div>
    </nav>

    <!-- Jumbotron -->
    <section class="relative flex h-[91vh] items-center bg-cover bg-[center_72%] text-white" style="background-image: url('/assets/Jumbotron.jpg');">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 max-w-screen-xl mx-auto w-full">
            <div class="text-left px-4">
                <h1 class="text-4xl sm:text-4xl md:text-4xl font-light">
                    Selamat Datang di Bengkel Las
                </h1>
                <h2 class="text-4xl sm:text-4xl md:text-4xl font-extrabold leading-tight">
                    Usaha Mandiri
                </h2>
                <a href="/" class="inline-block bg-white text-black text-base font-bold px-6 py-2 rounded-full hover:bg-gray-200 transition mt-5">
                    Lihat Katalog Kami
                </a>
            </div>
        </div>
    </section>

    <!-- Tentang Kami -->
    <section class="bg-white text-black py-10">
        <div class="max-w-screen-xl mx-auto text-center">
            <h3 class="text-3xl font-bold mb-10">Tentang Kami</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <!-- Gambar 1 -->
                <div class="flex flex-col items-center">
                    <img src="/assets/Profesional.svg" alt="Profesional" class="w-50 h-50" />
                    <p class="text-3xl font-bold">Profesional</p>
                    <p class="text-sm text-black mt-1 font-semibold">Bekerja dengan tanggung jawab<br />dan keahlian</p>
                </div>

                <!-- Gambar 2 -->
                <div class="flex flex-col items-center">
                    <img src="/assets/Berpengalaman.svg" alt="Berpengalaman" class="w-50 h-50" />
                    <p class="text-3xl font-bold">Berpengalaman</p>
                    <p class="text-sm text-black mt-1 font-semibold">Telah dipercaya oleh banyak<br />pelanggan</p>

                </div>

                <!-- Gambar 3 -->
                <div class="flex flex-col items-center">
                    <img src="/assets/Berkualitas.svg" alt="Berkualitas" class="w-50 h-50" />
                    <p class="text-3xl font-bold">Berkualitas</p>
                    <p class="text-sm text-black mt-1 font-semibold">Setiap detail kami kerjakan<br />dengan teliti</p>

                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-white py-15">
        <div class="max-w-screen-xl mx-auto px-4 grid grid-cols-1 sm:grid-cols-3 gap-8 items-start">

            <!-- Logo -->
            <div class="flex flex-col items-center">
                <img src="/assets/LogoBengkel.png" alt="Logo Bengkel" class="w-60 h-60 object-contain rounded-full" />
            </div>

            <!-- Informasi -->
            <div class="flex flex-col items-center">
                <h4 class="font-bold text-lg">Bengkel Las Usaha Mandiri</h4>
                <p class="text-sm mt-2">
                    <span class="font-semibold">Lokasi:</span> Jl. M. Yamin Gg. Sumber Harapan No.1, Pontianak Barat.
                </p>
                <p class="text-sm mt-1">
                    <span class="font-semibold">Kontak:</span> +(62) 895 1959 9386
                </p>
            </div>

            <!-- Jadwal -->
            <div class="flex flex-col items-center">
                <h4 class="font-bold text-lg mb-2">Jadwal Buka</h4>
                <div class="text-sm space-y-4">
                    <div class="flex justify-between font-semibold">
                        <span class="pr-20">Senin – Jumat</span>
                        <span>08:00 - 17:00</span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span class="pr-20">Sabtu</span>
                        <span>Libur</span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span class="pr-20">Minggu</span>
                        <span>Libur</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>