<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman dan Filter Tanggal -->
            <header class="mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Dashboard Analisis Produksi
                        </h1>
                        <p class="text-gray-600 mt-1">Ringkasan data produksi untuk periode yang dipilih.</p>
                    </div>

                    {{-- [FIXED] Form Filter Tanggal --}}
                    <form action="{{ route('dashboard') }}" method="GET" class="flex items-center space-x-2 mt-4 md:mt-0">
                        <input type="date" name="start_date" value="{{ $startDate }}" class=" rounded-md p-2 border-2 border-blue-400 shadow-sm focus:border-blue-900 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <span class="text-gray-500">to</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-md p-2 border-2 border-blue-400 shadow-sm focus:border-blue-900 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Filter
                        </button>
                    </form>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Goods Receipt (PRO)</h3>
            <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($totalGr, 0, ',', '.') }}</p>
        </div>
        <div class="bg-green-100 rounded-full p-3">
            <img src="{{ asset('images/icons/product.png') }}" alt="Goods Receipt Icon" class="h-10 w-10">
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Sold Value</h3>
            <p class="mt-2 text-3xl font-bold text-amber-600">$ {{ number_format($totalTransferValue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-amber-100 rounded-full p-3">
            <img src="{{ asset('images/icons/sold.png') }}" alt="Sold Value Icon" class="h-10 w-10">
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Transfer to WHFG</h3>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($totalWhfg, 0, ',', '.') }}</p>
        </div>
        <div class="bg-indigo-100 rounded-full p-3">
            <img src="{{ asset('images/icons/forklift.png') }}" alt="Warehouse Icon" class="h-10 w-10">
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Total Transfer Value</h3>
            <p class="mt-2 text-3xl font-bold text-blue-600">$ {{ number_format($totalSoldValue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-blue-100 rounded-full p-3">
            <img src="{{ asset('images/icons/exchange.png') }}" alt="Transfer Value Icon" class="h-10 w-10">
        </div>
    </div>

</div>

            <!-- Grafik Analisis -->
            <div class="mt-8 bg-white p-6 rounded-2xl shadow-sm">
                 <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Produksi Harian</h3>
                 <canvas id="productionChart" height="100"></canvas>
            </div>
        </div>
    </div>

@push('scripts')
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
                    label: 'Goods Receipt (PRO)',
                    data: chartGrData,
                    borderColor: 'rgba(22, 163, 74, 1)',
                    backgroundColor: 'rgba(22, 163, 74, 0.2)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Transfer to WHFG',
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
@endpush
</x-app-layout>
