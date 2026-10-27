// Tombol buka/tutup menu (HP)
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-toggle]');
    if (btn) {
        document.querySelector(btn.dataset.toggle)?.classList.toggle('hidden');
    }
});

// Konfirmasi sebelum aksi penting (data-confirm="Pesan")
document.addEventListener('submit', (e) => {
    const msg = e.target.dataset.confirm;
    if (msg && !window.confirm(msg)) {
        e.preventDefault();
    }
});
