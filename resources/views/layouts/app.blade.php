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

    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/be832a042f.js" crossorigin="anonymous"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Style untuk Nav Link Aktif */
        .nav-link-active {
            border-bottom-color: #3b82f6; /* blue-500 */
            color: #1f2937; /* gray-900 */
            font-weight: 600; /* semi-bold */
        }
        .nav-link-inactive {
            border-color: transparent;
            color: #6b7280; /* gray-500 */
            font-weight: 500; /* medium */
        }
        .nav-link-inactive:hover {
            border-color: #d1d5db; /* gray-300 */
            color: #374151; /* gray-700 */
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigasi Utama -->
        <nav class="bg-white border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">

                    <!-- Bagian Kiri: Logo dan Link Navigasi Utama -->
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                <img class="h-10 w-auto" src="{{ asset('images/KMI.png') }}" alt="KMI Logo">
                            </a>
                        </div>

                        <!-- Link Navigasi -->
                        <div class="hidden sm:flex space-x-8 -my-px ml-10">
                            <!-- Link Dashboard -->
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm transition {{ request()->routeIs('dashboard') ? 'nav-link-active' : 'nav-link-inactive' }}">
                                Dashboard
                            </a>

                            <!-- Link Plant Semarang (Default) -->
                            <a href="{{ route('calendar.index', ['plant' => '3000']) }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm transition {{ request()->routeIs('calendar.index') && (request()->route('plant') == '3000' || request()->route('plant') == null) ? 'nav-link-active' : 'nav-link-inactive' }}">
                                Plant Semarang
                            </a>

                            <!-- Link Plant Surabaya -->
                            <a href="{{ route('calendar.index', ['plant' => '2000']) }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm transition {{ request()->routeIs('calendar.index') && request()->route('plant') == '2000' ? 'nav-link-active' : 'nav-link-inactive' }}">
                                Plant Surabaya
                            </a>
                        </div>
                    </div>

                    <!-- Bagian Kanan: Info Pengguna dan Logout -->
                    <div class="hidden sm:flex items-center ml-6">
                        <div class="font-medium text-sm text-gray-800 flex items-center">
                            <i class="fa-solid fa-user mr-2 text-gray-500"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="ml-4">
                            @csrf
                            <button type="submit" class="text-sm bg-red-500 rounded-md px-3 py-2 text-white hover:bg-red-600 transition">
                                Logout
                            </button>
                        </form>
                    </div>

                    <!-- Navigasi Mobile (Tampilan Layar Kecil) -->
                    <div class="sm:hidden flex items-center">
                         <div class="font-medium text-sm text-gray-800 flex items-center">
                            <i class="fa-solid fa-user mr-2 text-gray-500"></i>
                         </div>
                         <form method="POST" action="{{ route('logout') }}" class="ml-2">
                            @csrf
                            <button type="submit" class="text-sm bg-red-500 rounded-md px-3 py-2 text-white hover:bg-red-600 transition">
                                Logout
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            <!-- Navigasi Bawah untuk Mobile -->
            <div class="sm:hidden border-t border-gray-200">
                <div class="flex justify-around py-2">
                     <a href="{{ route('dashboard') }}" class="flex flex-col items-center text-xs transition {{ request()->routeIs('dashboard') ? 'text-blue-600 font-bold' : 'text-gray-500 font-medium' }}">
                        <i class="fa-solid fa-chart-line mb-1"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('calendar.index', ['plant' => '3000']) }}" class="flex flex-col items-center text-xs transition {{ request()->route('plant') == '3000' ? 'text-blue-600 font-bold' : 'text-gray-500 font-medium' }}">
                        <i class="fa-solid fa-calendar-days mb-1"></i>
                        Plant 3000
                    </a>
                    <a href="{{ route('calendar.index', ['plant' => '2000']) }}" class="flex flex-col items-center text-xs transition {{ request()->route('plant') == '2000' ? 'text-blue-600 font-bold' : 'text-gray-500 font-medium' }}">
                        <i class="fa-solid fa-calendar-days mb-1"></i>
                        Plant 2000
                    </a>
                </div>
            </div>
        </nav>

        <!-- Konten Halaman -->
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
