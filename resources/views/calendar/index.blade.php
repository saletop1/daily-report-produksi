<x-app-layout>
    {{-- CSS untuk Running Text --}}
    <style>
        .marquee-container { width: 100%; overflow: hidden; background-color: #1a202c; color: white; padding: 10px 0; white-space: nowrap; box-sizing: border-box; }
        .marquee-text { display: inline-block; padding-left: 100%; animation: marquee 30s linear infinite; }
        @keyframes marquee { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }
    </style>

    {{-- Running Text --}}
    <div class="marquee-container"><div class="marquee-text">Selamat datang di laporan hasil produksi harian PT. Kayu Mebel Indonesia.</div></div>

    {{-- Wrapper utama --}}
    <div class="flex flex-col" style="height: calc(100vh - 4rem - 44px);">
        <div class="flex flex-col lg:flex-row flex-grow overflow-hidden">

            {{-- Kolom Rekapitulasi (Sidebar Kanan) --}}
            <div class="w-full lg:w-1/3 xl:w-1/4 bg-white p-6 order-1 lg:order-2 flex flex-col overflow-y-auto">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex-shrink-0">Rekap Bulan Ini</h2>
                <div class="space-y-4">
                    <div class="bg-green-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Goods Receipt (PRO)</p><p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($totals['totalGr'], 0, ',', '.') }}</p></div>
                    <div class="bg-green-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Sold Value</p><p class="text-2xl font-bold text-green-700 mt-1">$ {{ number_format($totals['totalValue'], 2, ',', '.') }}</p></div>
                    <div class="bg-gray-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Transfer to WHFG</p><p class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($totals['totalWhfg'], 0, ',', '.') }}</p></div>
                    <div class="bg-gray-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Transfer Value</p><p class="text-2xl font-bold text-blue-700 mt-1">$ {{ number_format($totals['totalSoldValue'], 2, ',', '.') }}</p></div>
                </div>

                {{-- Tombol Export --}}
                <div class="mt-auto pt-6 flex-shrink-0">
                    <a href="{{ route('calendar.exportPdf', ['year' => $year, 'month' => $month]) }}" class="flex w-full items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        Export PDF
                    </a>
                </div>
            </div>

            {{-- Kalender Utama (Konten Kiri) --}}
            <div class="w-full lg:w-2/3 xl:w-3/4 bg-gray-100 p-4 sm:p-6 lg:p-2 order-2 lg:order-1 flex flex-col">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full w-full">

                    {{-- Header Kalender --}}
                    <div class="p-3 border-b"><div class="flex flex-col sm:flex-row items-center justify-between"><h1 class="text-2xl ms-4 font-bold text-gray-900">{{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</h1><div class="flex items-center space-x-2 mt-4 sm:mt-0"><a href="{{ route('calendar.index', ['year' => $prevYear, 'month' => $prevMonth]) }}" class="p-2 rounded-full text-gray-500 hover:bg-gray-100"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg></a><a href="{{ route('calendar.index', ['year' => $nextYear, 'month' => $nextMonth]) }}" class="p-2 rounded-full text-gray-500 hover:bg-gray-100"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></a></div></div></div>

                    {{-- Grid Kalender --}}
                    <div class="flex-grow grid grid-cols-7 gap-px bg-gray-200" style="grid-template-rows: 32px repeat({{ count($weeks) }}, 1fr);">
                        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
                            <div class="text-center bg-white h-8 text-xs font-semibold {{ $dayName == 'Min' ? 'text-red-600' : 'text-gray-600' }} uppercase flex items-center justify-center">{{ $dayName }}</div>
                        @endforeach
                        @foreach ($weeks as $week)
                            @foreach ($week as $day)
    @if ($day)
        @php
            $dateKey = $day->format('Y-m-d');
            $hasData = isset($data[$dateKey]);
            $isToday = $day->isToday();
            $isSunday = $day->isSunday();

            // LOGIKA BARU: Cek jika value rendah
            $isLowValue = $hasData && $data[$dateKey]['Total Value'] <= 20000;
        @endphp

        {{-- PERUBAHAN DI SINI: tambahkan kondisi untuk background --}}
        <div class="relative bg-white p-2 flex flex-col ...
            {{ $hasData ? 'cursor-pointer transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg hover:z-10 data-day' : '' }}"
             @if($hasData)
                data-date='{{ $day->isoFormat('dddd, D MMMM YYYY') }}'
                data-details='{{ json_encode($data[$dateKey]) }}'
                data-date-key='{{ $dateKey }}'
             @endif>

            <span class="font-medium text-sm {{ $isToday ? 'bg-blue-600 text-white rounded-full flex items-center justify-center h-7 w-7' : ($isSunday ? 'text-red-600' : 'text-gray-800') }}">{{ $day->day }}</span>

            @if ($hasData)
                <div class="mt-1.5 flex-grow">
                    <ul class="text-xs space-y-1 pr-1">
                        <li class="flex items-center text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            <span class="truncate">GR: <strong>{{ number_format($data[$dateKey]['gr'], 0, ',', '.') }}</strong></span>
                        </li>
                        <li class="flex items-center text-violet-600">
                            <span class="w-2 h-2 bg-violet-500 rounded-full mr-2"></span>
                            <span class="truncate">Value: <strong>${{ number_format($data[$dateKey]['Total Value'], 0, ',', '.') }}</strong></span>
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

        {{-- Footer --}}
        <footer class="flex-shrink-0 bg-white border-t"><div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8"><p class="text-center text-sm text-gray-500">&copy; {{ date('Y') }} PT. Kayu Mebel Indonesia. All rights reserved.</p></div></footer>
    </div>

    {{-- Memanggil file partials/modals.blade.php --}}
    @include('calendar.partials.modals')

    @push('scripts')
        {{-- Mengirim data PHP ke JavaScript --}}
        <script>
            window.calendarData = {
                recipients: @json($recipients),
                apiUrl: "{{ route('api.notification.send') }}",
                csrfToken: "{{ csrf_token() }}"
            };
        </script>
        {{-- Memanggil file JavaScript eksternal --}}
        <script src="{{ asset('js/calendar-logic.js') }}" defer></script>
    @endpush
</x-app-layout>
