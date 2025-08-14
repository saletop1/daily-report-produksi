<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header Halaman dan Filter Tanggal -->
            <header class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard Analisis Produksi</h1>
                        <p class="text-gray-600 mt-1">Perbandingan performa antar plant untuk periode yang dipilih.</p>
                    </div>
                    <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-lg shadow-sm">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto rounded-md p-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="hidden sm:inline text-gray-500">to</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto rounded-md p-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Filter</button>
                    </form>
                </div>
            </header>

            <!-- Ringkasan Performa Plant -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                {{-- Kartu Plant 3000 --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-blue-500">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center"><i class="fa-solid fa-industry mr-3 text-blue-500"></i>Plant Semarang</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- MODIFIED: Added icons to each metric --}}
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-box-open w-4 text-center mr-2 text-green-500"></i>
                                <span>Total Goods Receipt PRO</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($plant3000['totals']['totalGr'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-truck-fast w-4 text-center mr-2 text-indigo-500"></i>
                                <span>Total Transfer to WHFG</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($plant3000['totals']['totalWhfg'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-dollar-sign w-4 text-center mr-2 text-blue-500"></i>
                                <span>Total Value GR</span>
                            </div>
                            <p class="text-2xl font-bold text-blue-600 mt-1">$ {{ number_format($plant3000['totals']['totalValue'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-hand-holding-dollar w-4 text-center mr-2 text-amber-500"></i>
                                <span>Total Transfer Value</span>
                            </div>
                            <p class="text-2xl font-bold text-amber-600 mt-1">$ {{ number_format($plant3000['totals']['totalSoldValue'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Kartu Plant 2000 --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-green-500">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center"><i class="fa-solid fa-industry mr-3 text-green-500"></i>Plant Surabaya</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- MODIFIED: Added icons to each metric --}}
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-box-open w-4 text-center mr-2 text-green-500"></i>
                                <span>Total Goods Receipt PRO </span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($plant2000['totals']['totalGr'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-truck-fast w-4 text-center mr-2 text-indigo-500"></i>
                                <span>Total Transfer to WHFG</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($plant2000['totals']['totalWhfg'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-dollar-sign w-4 text-center mr-2 text-green-500"></i>
                                <span>Total Value GR</span>
                            </div>
                            <p class="text-2xl font-bold text-green-600 mt-1">$ {{ number_format($plant2000['totals']['totalValue'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-solid fa-hand-holding-dollar w-4 text-center mr-2 text-amber-500"></i>
                                <span>Total Transfer Value</span>
                            </div>
                            <p class="text-2xl font-bold text-amber-600 mt-1">$ {{ number_format($plant2000['totals']['totalSoldValue'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik dan Diagram -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Grafik Tren Garis --}}
                <div class="lg:col-span-2 bg-white p-4 sm:p-6 rounded-2xl shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Tren Produksi (Qty Goods Receipt)</h3>
                    <div class="relative h-96">
                        <canvas id="productionTrendChart"></canvas>
                    </div>
                </div>

                {{-- Diagram Pai Kontribusi --}}
                <div class="lg:col-span-1 bg-white p-4 sm:p-6 rounded-2xl shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontribusi Total Value GR</h3>
                    <div class="relative h-96 flex items-center justify-center">
                        <canvas id="contributionPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            // --- GRAFIK TREN GARIS ---
            const trendCtx = document.getElementById('productionTrendChart').getContext('2d');
            const trendData = {!! $trendChartData !!};
            new Chart(trendCtx, {
                type: 'line',
                data: trendData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true }, x: { grid: { display: false } } },
                    plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } }
                }
            });

            // --- DIAGRAM PAI ---
            const pieCtx = document.getElementById('contributionPieChart').getContext('2d');
            const pieData = {!! $pieChartData !!};
            if (pieData.data && pieData.data.reduce((a, b) => a + b, 0) > 0) {
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: pieData.labels,
                        datasets: [{
                            label: 'Kontribusi Total Value',
                            data: pieData.data,
                            backgroundColor: ['#3b82f6', '#10b981'],
                            borderColor: '#ffffff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                pieCtx.canvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Tidak ada data nilai untuk ditampilkan.</div>';
            }
        } catch (e) {
            console.error("Gagal merender grafik:", e);
        }
    });
    </script>
    @endpush
</x-app-layout>
