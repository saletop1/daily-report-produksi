<x-app-layout>
    {{-- CSS untuk Running Text --}}
    <style>
        .marquee-container { width: 100%; overflow: hidden; background-color: #1f2937; color: white; padding: 10px 0; white-space: nowrap; box-sizing: border-box; }
        .marquee-text { display: inline-block; padding-left: 100%; animation: marquee 60s linear infinite; }
        @keyframes marquee { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }

        /* Transisi untuk modal */
        .modal, .modal-panel {
            transition: all 0.2s ease-in-out;
        }
    </style>

    {{-- Running Text Dinamis --}}
    <div class="marquee-container">
        <div class="marquee-text">{!! $runningText !!}</div>
    </div>

    {{-- Wrapper utama yang responsif --}}
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Kolom Kalender Utama (Kiri) --}}
            <div class="w-full lg:w-2/3 xl:w-3/4">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full">
                    {{-- Header Kalender --}}
                    <div class="p-4 border-b">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</h1>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('calendar.index', ['plant' => $plant, 'year' => $prevYear, 'month' => $prevMonth]) }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition">&laquo; Prev</a>
                                <a href="{{ route('calendar.index', ['plant' => $plant, 'year' => $nextYear, 'month' => $nextMonth]) }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 transition">Next &raquo;</a>
                            </div>
                        </div>
                    </div>

                    {{-- Grid Kalender --}}
                    <div class="flex-grow grid grid-cols-7 text-xs sm:text-sm">
                        {{-- Nama Hari --}}
                        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
                            <div class="text-center py-3 bg-gray-50 font-semibold {{ $dayName == 'Min' ? 'text-red-600' : 'text-gray-600' }} uppercase border-b border-gray-200">
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
                                    <div class="relative bg-white p-1.5 sm:p-2 flex flex-col border-t border-r border-gray-200 min-h-[80px] sm:min-h-[120px] {{ $hasData ? 'cursor-pointer hover:bg-blue-50 transition data-day' : '' }}"
                                         @if($hasData)
                                             data-date='{{ $day->isoFormat('dddd, D MMMM YYYY') }}'
                                             data-details='{{ json_encode($data[$dateKey]) }}'
                                             data-date-key='{{ $dateKey }}'
                                         @endif>
                                        <span class="font-medium {{ $isToday ? 'bg-blue-600 text-white rounded-full flex items-center justify-center h-6 w-6 sm:h-7 sm:w-7' : ($isSunday ? 'text-red-600' : 'text-gray-800') }}">{{ $day->day }}</span>

                                        @if ($hasData)
                                            <div class="mt-1.5 flex-grow hidden md:block">
                                                <ul class="text-xs space-y-1">
                                                    <li class="flex items-center text-green-700">
                                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                                                        <span class="truncate">GR: <strong>{{ number_format($data[$dateKey]['gr']) }}</strong></span>
                                                    </li>
                                                    <li class="flex items-center text-blue-700">
                                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-1.5"></span>
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

            {{-- Kolom Rekapitulasi (Kanan) --}}
            <div class="w-full lg:w-1/3 xl:w-1/4">
                <div class="bg-white p-6 rounded-2xl shadow-lg sticky top-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center"><i class="fa-solid fa-industry mr-3 text-gray-400"></i>Rekap Bulan Ini (Plant {{ $plant }})</h2>
                    {{-- MODIFIED: Added icons to the summary cards --}}
                    <div class="space-y-4">
                        <div class="bg-green-50 p-4 rounded-xl">
                            <div class="flex items-center text-sm text-gray-800 font-medium">
                                <i class="fa-solid fa-box-open w-4 text-center mr-2 text-green-600"></i>
                                <span>Total Goods Receipt (PRO)</span>
                            </div>
                            <p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($totals['totalGr'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-xl">
                             <div class="flex items-center text-sm text-gray-800 font-medium">
                                <i class="fa-solid fa-dollar-sign w-4 text-center mr-2 text-blue-600"></i>
                                <span>Total Value GR</span>
                            </div>
                            <p class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($totals['totalValue'], 2, ',', '.') }}</p>
                        </div>
                        <div class="bg-gray-100 p-4 rounded-xl">
                            <div class="flex items-center text-sm text-gray-800 font-medium">
                                <i class="fa-solid fa-truck-fast w-4 text-center mr-2 text-indigo-500"></i>
                                <span>Total Transfer to WHFG</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-700 mt-1">{{ number_format($totals['totalWhfg'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-gray-100 p-4 rounded-xl">
                            <div class="flex items-center text-sm text-gray-800 font-medium">
                                <i class="fa-solid fa-hand-holding-dollar w-4 text-center mr-2 text-amber-500"></i>
                                <span>Total Transfer Value</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-700 mt-1">{{ number_format($totals['totalSoldValue'], 2, ',', '.') }}</p>
                        </div>
                    </div>
                    {{-- <div class="w-48">
                        <div class="text-center">
                         <span class="text-sm font-medium text-gray-500">Pencapaian Target Hari Ini</span>
                          <div class="relative h-24 w-full">
                            <canvas id="dailyTargetGauge"></canvas>
                            <div id="gauge-text" class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-gray-800" style="top: 50%;">
                                0%
                            </div>
                        </div>
                    </div> --}}
                </div>
                    <div class="mt-8">
                        <a href="{{ route('calendar.exportPdf', ['plant' => $plant, 'year' => $year, 'month' => $month]) }}"
                           class="flex w-full items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium transition">
                            <i class="fa-solid fa-download mr-2"></i>
                            Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- HTML untuk Modal --}}
    <!-- Modal Detail Produksi -->
    <div id="details-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4 modal opacity-0">
        <div id="modal-panel" class="bg-white rounded-2xl shadow-xl w-full max-w-lg modal-panel scale-95">
            <div class="flex items-center justify-between p-5 border-b">
                <h3 class="text-xl font-bold" id="modal-title">Detail Produksi</h3>
                <button id="modal-close-btn" class="text-gray-400 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6"><div id="modal-body" class="space-y-4"></div></div>
            <div class="p-5 border-t bg-gray-50 rounded-b-2xl">
                <button id="send-email-btn" class="w-full flex items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium disabled:bg-blue-300">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Kirim Notifikasi Email
                </button>
                <p id="email-status" class="text-xs text-center mt-2 text-gray-500"></p>
            </div>
        </div>
    </div>

    <!-- Modal Pemilihan Penerima Email -->
    <div id="email-selection-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden flex items-center justify-center z-[60] p-4 modal opacity-0">
        <div id="email-modal-panel" class="bg-white rounded-2xl shadow-xl w-full max-w-md modal-panel scale-95">
            <div class="flex items-center justify-between p-5 border-b">
                <h3 class="text-xl font-bold">Pilih Penerima Notifikasi</h3>
                <button id="cancel-send-btn" class="text-gray-400 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>
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
    document.addEventListener('DOMContentLoaded', function () {
        const recipients = @json($recipients);
        let currentDetails = {};
        let currentDateKey = '';

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

        const openModal = (modal, panel) => {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                panel.classList.remove('scale-95');
            }, 10);
        };

        const closeModal = (modal, panel) => {
            modal.classList.add('opacity-0');
            panel.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        };

        const openDetailsModal = (date, details, dateKey) => {
            currentDetails = details;
            currentDateKey = dateKey;
            modalTitle.textContent = `Detail Produksi - ${date}`;
            const formatNumber = (num, decimals = 0) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(num || 0);

            modalBody.innerHTML = `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="bg-green-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Goods Receipt (GR)</p><p class="text-2xl font-bold text-green-700">${formatNumber(details.gr)}</p></div>
                    <div class="bg-blue-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Total Value GR</p><p class="text-2xl font-bold text-blue-700">$ ${formatNumber(details['Total Value'], 2)}</p></div>
                    <div class="bg-gray-100 p-4 rounded-lg"><p class="text-gray-500 font-medium">Transfer to WHFG</p><p class="text-2xl font-bold text-gray-700">${formatNumber(details.whfg)}</p></div>
                    <div class="bg-gray-100 p-4 rounded-lg"><p class="text-gray-500 font-medium">Total Transfer Value</p><p class="text-2xl font-bold text-gray-700">$ ${formatNumber(details['Sold Value'], 2)}</p></div>
                </div>`;

            emailStatus.textContent = '';
            emailStatus.className = 'text-xs text-center mt-2 text-gray-500';
            sendEmailBtn.disabled = false;
            openModal(detailsModal, detailsModalPanel);
        };

        const openEmailSelectionModal = () => {
            recipientList.innerHTML = '';
            recipients.forEach(recipient => {
                const listItem = document.createElement('label');
                listItem.className = 'flex items-center space-x-3 p-2 rounded-md hover:bg-gray-100 cursor-pointer';
                listItem.innerHTML = `<input type="checkbox" value="${recipient.email}" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span class="flex flex-col"><span class="font-medium text-sm">${recipient.name}</span><span class="text-xs text-gray-500">${recipient.email}</span></span>`;
                recipientList.appendChild(listItem);
            });
            recipientError.classList.add('hidden');
            openModal(emailSelectionModal, emailModalPanel);
        };

        dayCells.forEach(cell => {
            cell.addEventListener('click', function () {
                try {
                    openDetailsModal(this.dataset.date, JSON.parse(this.dataset.details), this.dataset.dateKey);
                } catch (e) { console.error("Gagal parsing JSON:", e); }
            });
        });

        sendEmailBtn.addEventListener('click', openEmailSelectionModal);

        confirmSendBtn.addEventListener('click', function() {
            const selectedEmails = Array.from(document.querySelectorAll('#recipient-list input:checked')).map(cb => cb.value);
            if (selectedEmails.length === 0) {
                recipientError.classList.remove('hidden'); return;
            }

            recipientError.classList.add('hidden');
            closeModal(emailSelectionModal, emailModalPanel);
            emailStatus.textContent = 'Mengirim notifikasi...';
            sendEmailBtn.disabled = true;

            fetch("{{ route('api.notification.send') }}", {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({date: currentDateKey, details: currentDetails, recipients: selectedEmails, plant: '{{ $plant }}' })
            })
            .then(response => response.json().then(data => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) throw data;
                emailStatus.textContent = data.message || 'Notifikasi berhasil dikirim!';
                emailStatus.className = 'text-xs text-center mt-2 text-green-600 font-semibold';
            })
            .catch(error => {
                emailStatus.textContent = `Error: ${error.message || 'Terjadi kesalahan jaringan.'}`;
                emailStatus.className = 'text-xs text-center mt-2 text-red-600 font-semibold';
                sendEmailBtn.disabled = false;
            });
        });

        detailsCloseBtn.addEventListener('click', () => closeModal(detailsModal, detailsModalPanel));
        cancelSendBtn.addEventListener('click', () => closeModal(emailSelectionModal, emailModalPanel));
        cancelSendBtn2.addEventListener('click', () => closeModal(emailSelectionModal, emailModalPanel));
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (!emailSelectionModal.classList.contains('hidden')) closeModal(emailSelectionModal, emailModalPanel);
                else if (!detailsModal.classList.contains('hidden')) closeModal(detailsModal, detailsModalPanel);
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
