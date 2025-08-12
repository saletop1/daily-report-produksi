<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header Halaman dan Filter Tanggal -->
            <header class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard Analisis Produksi</h1>
                        <p class="text-gray-600 mt-1">Ringkasan data produksi untuk periode yang dipilih.</p>
                    </div>
                    <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto rounded-md p-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="hidden sm:inline text-gray-500">to</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto rounded-md p-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Filter</button>
                    </form>
                </div>
            </header>

            <!-- Bagian Utama -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- KOLOM KIRI: GRAFIK GARIS --}}
                <div class="lg:col-span-2 bg-white p-4 sm:p-6 rounded-2xl shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Tren Produksi Harian</h3>
                    <div class="relative h-64 sm:h-96">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>

                {{-- KOLOM KANAN: KARTU RINGKASAN & DIAGRAM PAI --}}
                <div class="lg:col-span-1 space-y-8">
                    <!-- Kartu Ringkasan -->
                    <div class="grid grid-cols-2 gap-4 sm:gap-6">
                        <div class="bg-white p-4 rounded-2xl shadow-sm text-center">
                            <h3 class="text-sm font-medium text-gray-500">Total Goods Receipt</h3>
                            <p class="mt-1 text-xl sm:text-2xl font-bold text-green-600">{{ number_format($totalGr, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl shadow-sm text-center">
                            <h3 class="text-sm font-medium text-gray-500">Total Sold Value</h3>
                            <p class="mt-1 text-xl sm:text-2xl font-bold text-amber-600">$ {{ number_format($totalSoldValue, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl shadow-sm text-center">
                            <h3 class="text-sm font-medium text-gray-500">Total Transfer WHFG</h3>
                            <p class="mt-1 text-xl sm:text-2xl font-bold text-indigo-600">{{ number_format($totalWhfg, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl shadow-sm text-center">
                            <h3 class="text-sm font-medium text-gray-500">Total Transfer Value</h3>
                            <p class="mt-1 text-xl sm:text-2xl font-bold text-blue-600">$ {{ number_format($totalTransferValue, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <!-- Diagram Pai -->
                    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm flex flex-col">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontribusi Harian (Filter)</h3>
                        <div class="relative flex-grow h-64">
                            <canvas id="dailyPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- ============================================= --}}
    {{--      SKRIP JAVASCRIPT (BAGIAN DIPERBAIKI)     --}}
    {{-- ============================================= --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            // --- LOGIKA GRAFIK GARIS ---
            const lineCtx = document.getElementById('productionChart').getContext('2d');
            const chartLabels = {!! $chartLabels !!};
            const chartGrData = {!! $chartGrData !!};
            const chartWhfgData = {!! $chartWhfgData !!};

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { label: 'Goods Receipt (PRO)', data: chartGrData, borderColor: 'rgba(22, 163, 74, 1)', backgroundColor: 'rgba(22, 163, 74, 0.2)', fill: true, tension: 0.3 },
                        { label: 'Transfer to WHFG', data: chartWhfgData, borderColor: 'rgba(79, 70, 229, 1)', backgroundColor: 'rgba(79, 70, 229, 0.2)', fill: true, tension: 0.3 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true }, x: { grid: { display: false } } },
                    plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } }
                }
            });

            // --- LOGIKA DIAGRAM PAI ---
            const pieCtx = document.getElementById('dailyPieChart').getContext('2d');
            const dailyPieData = {!! $dailyPieData !!};

            // Hanya tampilkan chart jika ada data
            if (dailyPieData.data && dailyPieData.data.length > 0) {
                const colorPalette = ['#3b82f6', '#10b981', '#f97316', '#ef4444', '#8b5cf6', '#ec4899', '#64748b', '#f59e0b', '#14b8a6', '#84cc16'];
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: dailyPieData.labels,
                        datasets: [{
                            label: 'Kontribusi Sold Value',
                            data: dailyPieData.data,
                            backgroundColor: colorPalette,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: dailyPieData.labels.length <= 20, // Sembunyikan legenda jika terlalu banyak
                                position: 'bottom',
                                labels: { padding: 10, boxWidth: 12 }
                            },
                            title: { display: false }
                        }
                    }
                });
            } else {
                // Tampilkan pesan jika tidak ada data untuk pie chart
                pieCtx.canvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Tidak ada data untuk ditampilkan.</div>';
            }

        } catch (e) {
            console.error("Gagal merender grafik:", e);
        }
    });
    </script>
    @endpush
</x-app-layout>
