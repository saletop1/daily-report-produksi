{{-- ============================================= --}}
{{--    File: resources/views/calendar/partials/modals.blade.php    --}}
{{-- ============================================= --}}

{{-- Modal untuk menampilkan detail data harian --}}
<div id="details-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg transform transition-all duration-300 scale-95 opacity-0" id="modal-panel">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-xl font-bold text-gray-900" id="modal-title"></h3>
            <button id="modal-close-btn" class="text-gray-400 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <div id="modal-body" class="space-y-4"></div>
        </div>
        <div class="p-5 border-t bg-gray-50 rounded-b-2xl">
            <button id="send-email-btn" class="w-full flex items-center justify-center bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium disabled:bg-blue-300">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>
                Kirim Notifikasi Email
            </button>
            <p id="email-status" class="text-xs text-center mt-2 text-gray-500"></p>
        </div>
    </div>
</div>

{{-- Modal untuk memilih penerima email --}}
<div id="email-selection-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="email-modal-panel">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-xl font-bold">Pilih Penerima Notifikasi</h3>
            <button id="cancel-send-btn" class="text-gray-400 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
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
