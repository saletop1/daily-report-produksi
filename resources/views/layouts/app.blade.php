<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    @vite('resources/css/app.css') {{-- Hapus ini kalau belum pakai Vite --}}
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="min-h-screen">
        @yield('content')
    </div>
</body>
</html>
