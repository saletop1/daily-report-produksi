// File: public/js/calendar-logic.js

// Variabel global yang akan diinisialisasi dari Blade
let currentDetails = {};
let currentDateKey = '';

// Fungsi untuk memformat angka ke format Rupiah/Indonesia
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

// --- Fungsi untuk Modal Detail ---
const openDetailsModal = (date, details, dateKey) => {
    // Ambil elemen modal
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');
    const sendEmailBtn = document.getElementById('send-email-btn');
    const emailStatus = document.getElementById('email-status');
    const detailsModal = document.getElementById('details-modal');
    const detailsModalPanel = document.getElementById('modal-panel');

    // Set data global
    currentDetails = details;
    currentDateKey = dateKey;

    // Isi konten modal
    modalTitle.textContent = `Detail Produksi - ${date}`;
    modalBody.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Goods Receipt (GR) PRO</p><p class="text-2xl font-bold text-green-700">${formatNumber(details.gr)}</p></div>
            <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Total Sold Value</p><p class="text-2xl font-bold text-green-700">$ ${formatNumber(details['Total Value'])}</p></div>
            <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Transfer to WHFG</p><p class="text-2xl font-bold text-blue-700">${formatNumber(details.whfg)}</p></div>
            <div class="bg-gray-50 p-4 rounded-lg"><p class="text-gray-500 font-medium">Total Transfer Value</p><p class="text-2xl font-bold text-blue-700">$ ${formatNumber(details['Sold Value'])}</p></div>
        </div>`;

    // Reset status email dan tombol
    emailStatus.textContent = '';
    sendEmailBtn.disabled = false;

    // Tampilkan modal dengan animasi
    detailsModal.classList.remove('hidden');
    setTimeout(() => { detailsModalPanel.classList.remove('scale-95', 'opacity-0'); }, 10);
};

const closeDetailsModal = () => {
    const detailsModal = document.getElementById('details-modal');
    const detailsModalPanel = document.getElementById('modal-panel');
    detailsModalPanel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { detailsModal.classList.add('hidden'); }, 300);
};


// --- Fungsi untuk Modal Pemilihan Email ---
const openEmailSelectionModal = (recipients) => {
    const recipientList = document.getElementById('recipient-list');
    const emailSelectionModal = document.getElementById('email-selection-modal');
    const emailModalPanel = document.getElementById('email-modal-panel');

    recipientList.innerHTML = '';
    recipients.forEach(recipient => {
        const listItem = document.createElement('label');
        listItem.className = 'flex items-center space-x-3 p-2 rounded-md hover:bg-gray-100 cursor-pointer';
        listItem.innerHTML = `<input type="checkbox" value="${recipient.email}" class="h-4 w-4 rounded text-blue-600 focus:ring-blue-500"><span class="flex flex-col"><span class="font-medium">${recipient.name}</span><span class="text-xs text-gray-500">${recipient.email}</span></span>`;
        recipientList.appendChild(listItem);
    });

    emailSelectionModal.classList.remove('hidden');
    setTimeout(() => { emailModalPanel.classList.remove('scale-95', 'opacity-0'); }, 10);
};

const closeEmailSelectionModal = () => {
    const emailSelectionModal = document.getElementById('email-selection-modal');
    const emailModalPanel = document.getElementById('email-modal-panel');
    emailModalPanel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { emailSelectionModal.classList.add('hidden'); }, 300);
};

// --- Fungsi untuk Mengirim Notifikasi ---
const handleSendNotification = (apiUrl, csrfToken) => {
    const recipientError = document.getElementById('recipient-error');
    const emailStatus = document.getElementById('email-status');
    const sendEmailBtn = document.getElementById('send-email-btn');

    const selectedEmails = Array.from(document.querySelectorAll('#recipient-list input:checked')).map(cb => cb.value);

    if (selectedEmails.length === 0) {
        recipientError.classList.remove('hidden');
        return;
    }
    recipientError.classList.add('hidden');

    closeEmailSelectionModal();
    emailStatus.textContent = 'Mengirim notifikasi...';
    sendEmailBtn.disabled = true;

    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            date: currentDateKey,
            details: currentDetails,
            recipients: selectedEmails
        })
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) throw data;
        emailStatus.textContent = data.message || 'Notifikasi berhasil dikirim!';
        emailStatus.className = 'text-xs text-center mt-2 text-green-600';
    })
    .catch(error => {
        emailStatus.textContent = `Error: ${error.message || 'Terjadi kesalahan jaringan.'}`;
        emailStatus.className = 'text-xs text-center mt-2 text-red-600';
        if (sendEmailBtn) sendEmailBtn.disabled = false;
    });
};

// --- Event Listener Utama ---
document.addEventListener('DOMContentLoaded', function () {
    // Ambil data yang dikirim dari Blade
    const recipients = window.calendarData.recipients;
    const apiUrl = window.calendarData.apiUrl;
    const csrfToken = window.calendarData.csrfToken;

    // Selektor Elemen
    const dayCells = document.querySelectorAll('.data-day');
    const detailsModal = document.getElementById('details-modal');
    const emailSelectionModal = document.getElementById('email-selection-modal');

    // Event listeners untuk sel tanggal
    dayCells.forEach(cell => {
        cell.addEventListener('click', function () {
            try {
                const details = JSON.parse(this.dataset.details);
                openDetailsModal(this.dataset.date, details, this.dataset.dateKey);
            } catch (e) {
                console.error("Gagal parsing JSON:", e, this.dataset.details);
            }
        });
    });

    // Event listeners untuk tombol
    document.getElementById('send-email-btn')?.addEventListener('click', () => openEmailSelectionModal(recipients));
    document.getElementById('confirm-send-btn')?.addEventListener('click', () => handleSendNotification(apiUrl, csrfToken));

    // Event listeners untuk menutup modal
    document.getElementById('modal-close-btn')?.addEventListener('click', closeDetailsModal);
    document.getElementById('cancel-send-btn')?.addEventListener('click', closeEmailSelectionModal);
    document.getElementById('cancel-send-btn-2')?.addEventListener('click', closeEmailSelectionModal);
    detailsModal?.addEventListener('click', e => { if (e.target === detailsModal) closeDetailsModal(); });
    emailSelectionModal?.addEventListener('click', e => { if (e.target === emailSelectionModal) closeEmailSelectionModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (!emailSelectionModal.classList.contains('hidden')) closeEmailSelectionModal();
            else if (!detailsModal.classList.contains('hidden')) closeDetailsModal();
        }
    });
});
