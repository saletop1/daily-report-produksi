<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Dasbor Analisis Produksi
                </h1>
                <p class="text-gray-600 mt-1">Ringkasan data produksi untuk bulan ini.</p>
            </header>

            <!-- Grid Kartu KPI -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kartu Total GR -->
                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Goods Receipt (GR)</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($totalGr, 0, ',', '.') }}</p>
                </div>
                <!-- Kartu Total WHFG -->
                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Transfer ke WHFG</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($totalWhfg, 0, ',', '.') }}</p>
                </div>
                <!-- Kartu Total Transfer Value -->
                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Transfer Value</h3>
                    <p class="mt-2 text-3xl font-bold text-blue-600">$ {{ number_format($totalTransferValue, 0, ',', '.') }}</p>
                </div>
                 <!-- Kartu Total Transaksi -->
                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Hari Produksi</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-800">{{ $totalSoldCount }}</p>
                </div>
            </div>

            <!-- Grafik Analisis -->
            <div class="mt-8 bg-white p-6 rounded-2xl shadow-sm">
                 <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Produksi Harian</h3>
                 <canvas id="productionChart" height="100"></canvas>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('productionChart').getContext('2d');

    // Ambil data dari variabel Blade yang di-encode sebagai JSON
    const chartLabels = {!! $chartLabels !!};
    const chartGrData = {!! $chartGrData !!};
    const chartWhfgData = {!! $chartWhfgData !!};

    const productionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Goods Receipt (GR)',
                    data: chartGrData,
                    borderColor: 'rgba(22, 163, 74, 1)',
                    backgroundColor: 'rgba(22, 163, 74, 0.2)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Transfer ke WHFG',
                    data: chartWhfgData,
                    borderColor: 'rgba(79, 70, 229, 1)',
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                   grid: {
                        display: false
                   }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
});
</script>
</x-app-layout>
