// Navigasi Seluler Aksesibel (Mobile Drawer Navigation)
(() => {
    const toggleBtn = document.querySelector('[data-toggle="#nav-mobile"]') || document.getElementById('mobile-menu-btn');
    const drawer = document.getElementById('nav-mobile');
    const iconOpen = document.getElementById('icon-menu-open');
    const iconClose = document.getElementById('icon-menu-close');

    if (!toggleBtn || !drawer) return;

    function openMenu() {
        drawer.classList.remove('hidden');
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.setAttribute('aria-label', 'Tutup navigasi menu');
        if (iconOpen) iconOpen.classList.add('hidden');
        if (iconClose) iconClose.classList.remove('hidden');
    }

    function closeMenu() {
        drawer.classList.add('hidden');
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.setAttribute('aria-label', 'Buka navigasi menu');
        if (iconOpen) iconOpen.classList.remove('hidden');
        if (iconClose) iconClose.classList.add('hidden');
    }

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = !drawer.classList.contains('hidden');
        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Tutup saat menekan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !drawer.classList.contains('hidden')) {
            closeMenu();
            toggleBtn.focus();
        }
    });

    // Tutup saat pengguna mengetuk di luar drawer menu
    document.addEventListener('click', (e) => {
        if (!drawer.classList.contains('hidden') && !drawer.contains(e.target) && !toggleBtn.contains(e.target)) {
            closeMenu();
        }
    });
})();

// Penanganan umum data-toggle selain menu utama (misalnya panel nav admin)
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-toggle]:not([data-toggle="#nav-mobile"])');
    if (btn) {
        const target = document.querySelector(btn.dataset.toggle);
        if (target) {
            target.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', target.classList.contains('hidden') ? 'false' : 'true');
        }
    }
});

// Konfirmasi sebelum aksi penting (data-confirm="Pesan")
document.addEventListener('submit', (e) => {
    const msg = e.target.dataset.confirm;
    if (msg && !window.confirm(msg)) {
        e.preventDefault();
    }
});
