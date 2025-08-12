<x-app-layout>
    {{-- CSS untuk Running Text --}}
    <style>
        .marquee-container { width: 100%; overflow: hidden; background-color: #1a202c; color: white; padding: 10px 0; white-space: nowrap; box-sizing: border-box; }
        .marquee-text { display: inline-block; padding-left: 100%; animation: marquee 70s linear infinite; }
        @keyframes marquee { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }
    </style>

    {{-- Running Text Dinamis --}}
    <div class="marquee-container">
        <div class="marquee-text">{!! $runningText !!}</div>
    </div>

    {{-- Wrapper utama yang responsif --}}
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Kolom Kalender Utama (Sekarang di Kiri/Atas) --}}
            <div class="w-full lg:w-2/3 xl:w-3/4 order-1">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full">
                    {{-- Header Kalender --}}
                    <div class="p-4 border-b">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</h1>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('calendar.index', ['year' => $prevYear, 'month' => $prevMonth]) }}" class="p-2 rounded-full text-gray-500 hover:bg-gray-100 transition">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                </a>
                                <a href="{{ route('calendar.index', ['year' => $nextYear, 'month' => $nextMonth]) }}" class="p-2 rounded-full text-gray-500 hover:bg-gray-100 transition">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Grid Kalender --}}
                    <div class="flex-grow grid grid-cols-7 bg-gray-200 text-xs sm:text-sm">
                        {{-- Nama Hari (Responsif) --}}
                        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
                            <div class="text-center py-2 bg-white font-semibold {{ $dayName == 'Min' ? 'text-red-600' : 'text-gray-600' }} uppercase">
                                <span class="hidden sm:inline">{{ $dayName }}</span>
                                <span class="sm:hidden">{{ substr($dayName, 0, 1) }}</span>
                            </div>
                        @endforeach

                        {{-- Tanggal --}}
                        @foreach ($weeks as $week)
                            @foreach ($week as $day)
                                @if ($day)
                                    @php
                                        $dateKey = $day->format('Y-m-d');
                                        $hasData = isset($data[$dateKey]);
                                        $isToday = $day->isToday();
                                        $isSunday = $day->isSunday();
                                    @endphp
                                    <div class="relative bg-white p-1.5 sm:p-2 flex flex-col border-t border-r border-gray-200 min-h-[60px] sm:min-h-[100px] {{ $hasData ? 'cursor-pointer hover:bg-gray-50 data-day' : '' }}"
                                         @if($hasData)
                                            data-date='{{ $day->isoFormat('dddd, D MMMM YYYY') }}'
                                            data-details='{{ json_encode($data[$dateKey]) }}'
                                            data-date-key='{{ $dateKey }}'
                                         @endif>
                                        <span class="font-medium {{ $isToday ? 'bg-blue-600 text-white rounded-full flex items-center justify-center h-6 w-6 sm:h-7 sm:w-7' : ($isSunday ? 'text-red-600' : 'text-gray-800') }}">{{ $day->day }}</span>

                                        {{-- Detail Data (Hanya tampil di layar medium ke atas) --}}
                                        @if ($hasData)
                                            <div class="mt-1.5 flex-grow hidden md:block">
                                                <ul class="text-xs space-y-1">
                                                    <li class="flex items-center text-green-600">
                                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                                                        <span class="truncate">GR: <strong>{{ number_format($data[$dateKey]['gr']) }}</strong></span>
                                                    </li>
                                                    <li class="flex items-center text-violet-600">
                                                        <span class="w-2 h-2 bg-violet-500 rounded-full mr-1.5"></span>
                                                        <span class="truncate">Value: <strong>${{ number_format($data[$dateKey]['Total Value']) }}</strong></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="bg-gray-50 border-t border-r border-gray-200"></div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Kolom Rekapitulasi (Sekarang di Kanan/Bawah) --}}
            <div class="w-full lg:w-1/3 xl:w-1/4 order-2">
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Rekap Bulan Ini</h2>
                    <div class="space-y-4">
                        <div class="bg-green-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Goods Receipt (PRO)</p><p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($totals['totalGr'], 0, ',', '.') }}</p></div>
                        <div class="bg-green-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Sold Value</p><p class="text-2xl font-bold text-green-700 mt-1">$ {{ number_format($totals['totalValue'], 2, ',', '.') }}</p></div>
                        <div class="bg-gray-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Transfer to WHFG</p><p class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($totals['totalWhfg'], 0, ',', '.') }}</p></div>
                        <div class="bg-gray-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Transfer Value</p><p class="text-2xl font-bold text-blue-700 mt-1">$ {{ number_format($totals['totalSoldValue'], 2, ',', '.') }}</p></div>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('calendar.exportPdf', ['year' => $year, 'month' => $month]) }}" class="flex w-full items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium transition">
                            <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            Export PDF
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal tidak perlu diubah, akan tetap berfungsi --}}
    @include('calendar.partials.modals')

    @push('scripts')
        {{-- Skrip Anda tidak perlu diubah, akan tetap berfungsi --}}
    @endpush
</x-app-layout>
