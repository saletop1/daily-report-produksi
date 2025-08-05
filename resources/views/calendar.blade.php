<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Laporan Produksi</title>

    <!-- Memuat Tailwind CSS dari CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Memuat Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Menggunakan font Inter sebagai default */
        body {
            font-family: 'Inter', sans-serif;
            overflow: hidden; /* Mencegah scroll pada body */
        }
        /* Style untuk scrollbar yang lebih modern (opsional) */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #a0aec0;
            border-radius: 20px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background-color: #edf2f7;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="flex flex-col lg:flex-row h-screen">

        <!-- Panel Rekap Bulanan (Sisi Kanan di Desktop) -->
        <div class="w-full lg:w-1/3 xl:w-1/4 bg-white p-6 order-1 lg:order-2 flex flex-col">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Rekap Bulan Ini</h2>

            @php
                // Kalkulasi total untuk rekap bulanan
                $totalGr = 0;
                $totalWhfg = 0;
                $totalSoldValue = 0;
                $totalValue = 0;
                foreach ($data as $dailyData) {
                    $totalGr += $dailyData['gr'];
                    $totalWhfg += $dailyData['whfg'];
                    $totalSoldValue += $dailyData['Sold Value'];
                    $totalValue += $dailyData['Total Value'];
                }
            @endphp

            <div class="space-y-4">
                <div class="bg-green-50 p-4 rounded-xl">
                    <p class="text-sm text-green-800 font-medium">Total Goods Receipt (PRO)</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($totalGr, 0, ',', '.') }}</p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-xl">
                    <p class="text-sm text-indigo-800 font-medium">Total Transfer to WHFG</p>
                    <p class="text-2xl font-bold text-indigo-700 mt-1">{{ number_format($totalWhfg, 0, ',', '.') }}</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl">
                    <p class="text-sm text-blue-800 font-medium">Total Transfer Value</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">$ {{ number_format($totalValue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-amber-50 p-4 rounded-xl">
                    <p class="text-sm text-amber-800 font-medium">Total Sold Value</p>
                    <p class="text-2xl font-bold text-amber-700 mt-1">$ {{ number_format($totalSoldValue, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-auto pt-6">
                 <a href="{{ route('calendar.export', ['year' => $year, 'month' => $month]) }}"
                   class="flex w-full items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-300 font-medium">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                       <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                   </svg>
                    Export PDF
                </a>
            </div>
        </div>

        <!-- Panel Kalender (Sisi Kiri di Desktop) -->
        <div class="w-full lg:w-2/3 xl:w-3/4 bg-gray-100 p-4 sm:p-6 lg:p-8 order-2 lg:order-1 flex flex-col">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full">
                <div class="p-6 border-b border-gray-200">
                    <!-- Header Kalender: Navigasi dan Judul -->
                    <div class="flex flex-col sm:flex-row items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}
                        </h1>
                        <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                            <a href="{{ route('calendar.index', ['year' => $prevYear, 'month' => $prevMonth]) }}"
                               class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                            <a href="{{ route('calendar.index', ['year' => $nextYear, 'month' => $nextMonth]) }}"
                               class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!--
                    Grid Kalender
                    [FIXED] Menggunakan grid-cols-[...] untuk membuat kolom Minggu, Senin, Selasa lebih kecil.
                -->
                <div class="flex-grow grid grid-cols-[0.8fr_0.8fr_0.8fr_1fr_1fr_1fr_1fr] grid-rows-6 gap-px bg-gray-200 overflow-hidden">
                    <!-- Header Hari (Minggu, Senin, ...) -->
                    @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
                        <div class="text-center bg-white py-3 font-semibold text-gray-600 text-sm uppercase tracking-wider flex items-center justify-center">{{ $dayName }}</div>
                    @endforeach

                    <!-- Looping untuk setiap hari dalam sebulan -->
                    @foreach ($weeks as $week)
                        @foreach ($week as $day)
                            @if ($day)
                                @php
                                    $dateKey = $day->format('Y-m-d');
                                    $hasData = isset($data[$dateKey]);
                                    $isToday = $day->isToday();
                                @endphp
                                <div class="relative bg-white p-2 flex flex-col overflow-hidden
                                            {{ $hasData ? 'cursor-pointer transition-transform duration-200 hover:scale-105 hover:shadow-lg hover:z-10 data-day' : '' }}"
                                     @if($hasData)
                                         data-date='{{ $day->isoFormat('dddd, D MMMM YYYY') }}'
                                         data-details='{{ json_encode($data[$dateKey]) }}'
                                     @endif
                                >
                                    <span class="font-medium text-sm {{ $isToday ? 'bg-blue-600 text-white rounded-full flex items-center justify-center h-7 w-7' : 'text-gray-800' }}">
                                        {{ $day->day }}
                                    </span>

                                    @if ($hasData)
                                        <div class="mt-1.5 flex-grow overflow-y-auto custom-scrollbar">
                                            <ul class="text-xs space-y-1 pr-1">
                                                <li class="flex items-center text-green-600">
                                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 flex-shrink-0"></span>
                                                    <span class="truncate">GR :<strong class="ml-1">{{ number_format($data[$dateKey]['gr'], 0, ',', '.') }}</strong></span>
                                                </li>
                                                <li class="flex items-center text-violet-600">
                                                    <span class="w-2 h-2 bg-violet-500 rounded-full mr-2 flex-shrink-0"></span>
                                                    <span class="truncate">Value :<strong class="ml-1">$ {{ number_format($data[$dateKey]['Total Value'], 0, ',', '.') }}</strong></span>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="bg-gray-50"></div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan detail data harian -->
    <div id="details-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg transform transition-all duration-300 scale-95 opacity-0" id="modal-panel">
            <div class="flex items-center justify-between p-5 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900" id="modal-title">Detail Produksi</h3>
                <button id="modal-close-btn" class="text-gray-400 hover:text-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="modal-body" class="space-y-4">
                    <!-- Konten detail akan dimasukkan di sini oleh JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('details-modal');
            const modalPanel = document.getElementById('modal-panel');
            const closeBtn = document.getElementById('modal-close-btn');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');
            const dayCells = document.querySelectorAll('.data-day');

            const openModal = (date, details) => {
                console.log("Data yang diterima oleh modal:", details);

                modalTitle.textContent = `Detail Produksi - ${date}`;
                const formatNumber = (num) => {
                    if (typeof num === 'undefined' || num === null) return 0;
                    return new Intl.NumberFormat('id-ID').format(num);
                }

                modalBody.innerHTML = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-500 font-medium">Goods Receipt (GR) PRO</p>
                            <p class="text-2xl font-bold text-green-700">${formatNumber(details.gr)}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-500 font-medium">Transfer to WHFG</p>
                            <p class="text-2xl font-bold text-indigo-700">${formatNumber(details.whfg)}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-500 font-medium">Total Transfer Value</p>
                            <p class="text-2xl font-bold text-blue-700">$ ${formatNumber(details['Total Value'])}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-500 font-medium">Total Sold Value</p>
                            <p class="text-2xl font-bold text-rose-700">$ ${formatNumber(details['Sold Value'])}</p>
                        </div>

                    </div>
                `;
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modalPanel.classList.remove('scale-95', 'opacity-0');
                }, 10);
            };

            const closeModal = () => {
                modalPanel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            };

            dayCells.forEach(cell => {
                cell.addEventListener('click', function () {
                    const date = this.dataset.date;
                    try {
                        const details = JSON.parse(this.dataset.details);
                        openModal(date, details);
                    } catch (e) {
                        console.error("Gagal mem-parsing data JSON:", e, this.dataset.details);
                        alert("Terjadi kesalahan saat membaca data detail.");
                    }
                });
            });

            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });
        });
    </script>
</body>
</html>
