<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Dasboard Produksi</title>
    <link rel="icon" href="{{ asset('images/KMI.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/be832a042f.js" crossorigin="anonymous"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigasi Utama -->
        <nav class="bg-white border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                               <img class="h-10 w-auto" src="{{ asset('images/KMI.png') }}" alt="KMI Logo">
                            </a>
                        </div>

                        <!-- Link Navigasi -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out">
                                Dashboard
                            </a>
                            <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.index') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out">
                                Calendar
                            </a>
                        </div>
                    </div>

                    <!-- Pengaturan Pengguna -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="font-medium text-sm text-gray-800">
                            <i class="fa-solid fa-user me-3"></i>
                            {{ Auth::user()->name }}</div>
                        <!-- Tombol Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="ml-4">
                            @csrf
                            <button type="submit" class="text-sm bg-red-500 rounded-md p-2 text-white hover:bg-white hover:border-2 hover:border-red-500 hover:text-gray-700">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Konten Halaman -->
        <main>
            {{ $slot }}
        </main>
    </div>

    {{-- [FIXED] Menambahkan @stack untuk menerima skrip dari halaman lain --}}
    @stack('scripts')
</body>
</html>
