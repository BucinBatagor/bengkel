<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-p1CmOHOt+TVzpNmYKn6Nv3ja6VXe3N7Az3zF2xT2L9hwH+oLq3F+vIfNslNleq5N70JIDjz2W8DRYlsbxV9QJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-black text-white min-h-screen flex flex-col">
            <!-- Header Admin Menu -->
            <div class="px-6 py-4 font-bold text-xl border-b border-gray-800">
                Panel <span class="text-gray-400">Admin</span>
            </div>

            <!-- Sidebar Menu -->
            <nav class="flex-1 text-sm pt-4 space-y-1">
                <a href="#"
                    class="block px-6 py-3 bg-blue-500 text-white font-medium">
                    Katalog Produk
                </a>

                <a href="#"
                    class="block px-6 py-3 text-white hover:bg-gray-800 transition">
                    Akun Pelanggan
                </a>

                <a href="#"
                    class="block px-6 py-3 text-white hover:bg-gray-800 transition">
                    Status Pesanan
                </a>

                <a href="#"
                    class="block px-6 py-3 text-white hover:bg-gray-800 transition">
                    Laporan Pesanan
                </a>
            </nav>

            <!-- Logout -->
            <form method="POST" action="#" class="px-6 mt-4 mb-6">
                @csrf
                <button type="submit"
                    class="w-full text-left px-3 py-2 rounded-md text-red-400 hover:bg-red-600 hover:text-white transition">
                    Logout
                </button>
            </form>
        </aside>


        <!-- Konten Utama -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

</body>

</html>