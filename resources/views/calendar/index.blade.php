<x-app-layout>
    {{-- CSS untuk Running Text --}}
    <style>
        .marquee-container { width: 100%; overflow: hidden; background-color: #1a202c; color: white; padding: 10px 0; white-space: nowrap; box-sizing: border-box; }
        .marquee-text { display: inline-block; padding-left: 100%; animation: marquee 20s linear infinite; }
        @keyframes marquee { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }
    </style>

    {{-- Running Text --}}
    <div class="marquee-container"><div class="marquee-text">Selamat datang di laporan hasil produksi harian PT. Kayu Mebel Indonesia.</div></div>

    {{-- Wrapper utama --}}
    <div class="flex flex-col" style="height: calc(100vh - 4rem - 44px);">
        <div class="flex flex-col lg:flex-row flex-grow overflow-hidden">

            <div class="w-full lg:w-1/3 xl:w-1/4 bg-white p-6 order-1 lg:order-2 flex flex-col overflow-y-auto">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex-shrink-0">Rekap Bulan Ini</h2>
                @php
                    $totalGr = 0; $totalWhfg = 0; $totalSoldValue = 0; $totalValue = 0;
                    foreach ($data as $dailyData) {
                        $totalGr += $dailyData['gr'];
                        $totalWhfg += $dailyData['whfg'];
                        $totalSoldValue += $dailyData['Sold Value'];
                        $totalValue += $dailyData['Total Value'];
                    }
                @endphp
                <div class="space-y-4">
                    <div class="bg-green-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Goods Receipt (PRO)</p><p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($totalGr, 0, ',', '.') }}</p></div>
                    <div class="bg-green-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Sold Value</p><p class="text-2xl font-bold text-green-700 mt-1">$ {{ number_format($totalValue, 0, ',', '.') }}</p></div>
                    <div class="bg-gray-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Transfer to WHFG</p><p class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($totalWhfg, 0, ',', '.') }}</p></div>
                    <div class="bg-gray-50 p-4 rounded-xl"><p class="text-sm text-gray-800 font-medium">Total Transfer Value</p><p class="text-2xl font-bold text-blue-700 mt-1">$ {{ number_format($totalSoldValue, 0, ',', '.') }}</p></div>
                </div>
                <div class="mt-auto pt-6 flex-shrink-0">
                    <a href="{{ route('calendar.export', ['year' => $year, 'month' => $month]) }}" class="flex w-full items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        Export PDF
                    </a>
                </div>
            </div>

            <div class="w-full lg:w-2/3 xl:w-3/4 bg-gray-100 p-4 sm:p-6 lg:p-2 order-2 lg:order-1 flex flex-col">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full w-full">
                    <div class="p-3 border-b"><div class="flex flex-col sm:flex-row items-center justify-between"><h1 class="text-2xl ms-4 font-bold text-gray-900">{{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</h1><div class="flex items-center space-x-2 mt-4 sm:mt-0"><a href="{{ route('calendar.index', ['year' => $prevYear, 'month' => $prevMonth]) }}" class="p-2 rounded-full text-gray-500 hover:bg-gray-100"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg></a><a href="{{ route('calendar.index', ['year' => $nextYear, 'month' => $nextMonth]) }}" class="p-2 rounded-full text-gray-500 hover:bg-gray-100"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></a></div></div></div>
                    <div class="flex-grow grid grid-cols-7 gap-px bg-gray-200" style="grid-template-rows: 32px repeat({{ count($weeks) }}, 1fr);">
                        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)<div class="text-center bg-white h-8 text-xs font-semibold {{ $dayName == 'Min' ? 'text-red-600' : 'text-gray-600' }} uppercase flex items-center justify-center">{{ $dayName }}</div>@endforeach
                        @foreach ($weeks as $week)
                            @foreach ($week as $day)
                                @if ($day)
                                    @php
                                        $dateKey = $day->format('Y-m-d');
                                        $hasData = isset($data[$dateKey]);
                                        $isToday = $day->isToday();
                                        $isSunday = $day->isSunday();
                                    @endphp
                                    <div class="relative bg-white p-2 flex flex-col {{ $hasData ? 'cursor-pointer hover:scale-105 hover:shadow-lg hover:z-10 data-day' : '' }}"
                                         @if($hasData) data-date='{{ $day->isoFormat('dddd, D MMMM YYYY') }}' data-details='{{ json_encode($data[$dateKey]) }}' data-date-key='{{ $dateKey }}' @endif>
                                        <span class="font-medium text-sm {{ $isToday ? 'bg-blue-600 text-white rounded-full flex items-center justify-center h-7 w-7' : ($isSunday ? 'text-red-600' : 'text-gray-800') }}">{{ $day->day }}</span>
                                        @if ($hasData)<div class="mt-1.5 flex-grow"><ul class="text-xs space-y-1 pr-1"><li class="flex items-center text-green-600"><span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span><span class="truncate">GR: <strong>{{ number_format($data[$dateKey]['gr'], 0, ',', '.') }}</strong></span></li><li class="flex items-center text-violet-600"><span class="w-2 h-2 bg-violet-500 rounded-full mr-2"></span><span class="truncate">Value: <strong>${{ number_format($data[$dateKey]['Total Value'], 0, ',', '.') }}</strong></span></li></ul></div>@endif
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
        <footer class="flex-shrink-0 bg-white border-t"><div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8"><p class="text-center text-sm text-gray-500">&copy; {{ date('Y') }} PT. Kayu Mebel Indonesia. All rights reserved.</p></div></footer>
    </div>

    <div id="details-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg transform transition-all duration-300 scale-95 opacity-0" id="modal-panel">
            <div class="flex items-center justify-between p-5 border-b"><h3 class="text-xl font-bold text-gray-900" id="modal-title"></h3><button id="modal-close-btn" class="text-gray-400 hover:text-gray-800"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button></div>
            <div class="p-6"><div id="modal-body" class="space-y-4"></div></div>
            <div class="p-5 border-t bg-gray-50 rounded-b-2xl">
                <button id="send-email-btn" class="w-full flex items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium disabled:bg-blue-300">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>
                    Kirim Notifikasi Email
                </button>
                <p id="email-status" class="text-xs text-center mt-2 text-gray-500"></p>
            </div>
        </div>
    </div>

    <div id="email-selection-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="email-modal-panel">
            <div class="flex items-center justify-between p-5 border-b"><h3 class="text-xl font-bold">Pilih Penerima Notifikasi</h3><button id="cancel-send-btn" class="text-gray-400 hover:text-gray-800"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button></div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">Pilih satu atau lebih alamat email.</p>
                <div id="recipient-list" class="space-y-3 max-h-60 overflow-y-auto border rounded-lg p-3"></div>
                <p id="recipient-error" class="text-xs text-red-500 mt-2 hidden">Pilih minimal satu penerima.</p>
            </div>
            <div class="p-5 border-t bg-gray-50 rounded-b-2xl flex justify-end space-x-3">
                <button id="cancel-send-btn-2" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</button>
                <button id="confirm-send-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Kirim Sekarang</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const recipients = @json($recipients);

        document.addEventListener('DOMContentLoaded', function () {
            // Variabel Global
            let currentDetails = {};
            let currentDateKey = '';

            // Selektor Elemen
            const detailsModal = document.getElementById('details-modal');
            const detailsModalPanel = document.getElementById('modal-panel');
            const detailsCloseBtn = document.getElementById('modal-close-btn');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');
            const dayCells = document.querySelectorAll('.data-day');
            const sendEmailBtn = document.getElementById('send-email-btn');
            const emailStatus = document.getElementById('email-status');

            const emailSelectionModal = document.getElementById('email-selection-modal');
            const emailModalPanel = document.getElementById('email-modal-panel');
            const recipientList = document.getElementById('recipient-list');
            const recipientError = document.getElementById('recipient-error');
            const confirmSendBtn = document.getElementById('confirm-send-btn');
            const cancelSendBtn = document.getElementById('cancel-send-btn');
            const cancelSendBtn2 = document.getElementById('cancel-send-btn-2');

            // --- Fungsi ---
            const openDetailsModal = (date, details, dateKey) => {
                currentDetails = details;
                currentDateKey = dateKey;
                modalTitle.textContent = `Detail Produksi - ${date}`;
                const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
                modalBody.innerHTML = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Goods Receipt (GR) PRO</p><p class="text-2xl font-bold text-green-700">${formatNumber(details.gr)}</p></div>
                        <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Total Sold Value</p><p class="text-2xl font-bold text-green-700">$ ${formatNumber(details['Total Value'])}</p></div>
                        <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Transfer to WHFG</p><p class="text-2xl font-bold text-blue-700">${formatNumber(details.whfg)}</p></div>
                        <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Total Transfer Value</p><p class="text-2xl font-bold text-blue-700">$ ${formatNumber(details['Sold Value'])}</p></div>
                    </div>`;
                emailStatus.textContent = '';
                sendEmailBtn.disabled = false;
                detailsModal.classList.remove('hidden');
                setTimeout(() => { detailsModalPanel.classList.remove('scale-95', 'opacity-0'); }, 10);
            };

            const closeDetailsModal = () => {
                detailsModalPanel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => { detailsModal.classList.add('hidden'); }, 300);
            };

            const openEmailSelectionModal = () => {
                recipientList.innerHTML = '';
                recipients.forEach(recipient => {
                    const listItem = document.createElement('label');
                    listItem.className = 'flex items-center space-x-3 p-2 rounded-md hover:bg-gray-100 cursor-pointer';
                    listItem.innerHTML = `<input type="checkbox" value="${recipient.email}" class="h-4 w-4 rounded"><span class="flex flex-col"><span class="font-medium">${recipient.name}</span><span class="text-xs text-gray-500">${recipient.email}</span></span>`;
                    recipientList.appendChild(listItem);
                });
                emailSelectionModal.classList.remove('hidden');
                setTimeout(() => { emailModalPanel.classList.remove('scale-95', 'opacity-0'); }, 10);
            };

            const closeEmailSelectionModal = () => {
                emailModalPanel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => { emailSelectionModal.classList.add('hidden'); }, 300);
            };

            // Event Listeners
            dayCells.forEach(cell => {
                cell.addEventListener('click', function () {
                    try {
                        openDetailsModal(this.dataset.date, JSON.parse(this.dataset.details), this.dataset.dateKey);
                    } catch (e) { console.error("Gagal parsing JSON:", e); }
                });
            });

            if (sendEmailBtn) {
                sendEmailBtn.addEventListener('click', openEmailSelectionModal);
            }

            confirmSendBtn.addEventListener('click', function() {
                const selectedEmails = Array.from(document.querySelectorAll('#recipient-list input:checked')).map(cb => cb.value);
                if (selectedEmails.length === 0) { recipientError.classList.remove('hidden'); return; }
                recipientError.classList.add('hidden');
                closeEmailSelectionModal();
                emailStatus.textContent = 'Mengirim notifikasi...';
                sendEmailBtn.disabled = true;

                fetch("{{ route('api.notification.send') }}", {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({date: currentDateKey, details: currentDetails, recipients: selectedEmails})
                })
                .then(response => response.json().then(data => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    if (!ok) throw data;
                    emailStatus.textContent = data.message || 'Notifikasi berhasil dikirim!';
                    emailStatus.classList.add('text-green-500');
                })
                .catch(error => {
                    emailStatus.textContent = `Error: ${error.message || 'Terjadi kesalahan jaringan.'}`;
                    emailStatus.classList.add('text-red-500');
                    if(sendEmailBtn) sendEmailBtn.disabled = false;
                });
            });

            // Listeners untuk menutup modal
            if (detailsCloseBtn) detailsCloseBtn.addEventListener('click', closeDetailsModal);
            if (cancelSendBtn) cancelSendBtn.addEventListener('click', closeEmailSelectionModal);
            if (cancelSendBtn2) cancelSendBtn2.addEventListener('click', closeEmailSelectionModal);
            detailsModal.addEventListener('click', e => { if (e.target === detailsModal) closeDetailsModal(); });
            emailSelectionModal.addEventListener('click', e => { if (e.target === emailSelectionModal) closeEmailSelectionModal(); });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    if (!emailSelectionModal.classList.contains('hidden')) closeEmailSelectionModal();
                    else if (!detailsModal.classList.contains('hidden')) closeDetailsModal();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
