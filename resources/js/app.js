// Navigasi Seluler Aksesibel (Mobile Drawer Navigation)
(() => {
    function initMobileMenu() {
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        initMobileMenu();
    }
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

// Padaidi Custom DatePicker (Format dd/mm/yyyy & Interactive Calendar)
(() => {
    function initDatePickers() {
        const wrappers = document.querySelectorAll('[data-datepicker-wrapper]');
        if (!wrappers.length) return;

        function pad(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        function formatDisplay(isoDateStr) {
            if (!isoDateStr) return '';
            const parts = isoDateStr.trim().split('-');
            if (parts.length !== 3) return '';
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        function formatISO(d) {
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
        }

        wrappers.forEach((wrapper) => {
            const displayInput = wrapper.querySelector('[data-datepicker-display]');
            const dateInput = wrapper.querySelector('[data-datepicker-value]');
            if (!displayInput || !dateInput) return;

            // Sync initial value if present
            if (dateInput.value && !displayInput.value) {
                displayInput.value = formatDisplay(dateInput.value);
            }

            // Clicking the display input opens the calendar picker
            displayInput.addEventListener('click', (e) => {
                e.stopPropagation();
                if (typeof dateInput.showPicker === 'function') {
                    try {
                        dateInput.showPicker();
                        return;
                    } catch (err) {}
                }
                dateInput.focus();
            });

            // Clicking the native date input (over the icon) ensures picker triggers
            dateInput.addEventListener('click', () => {
                if (typeof dateInput.showPicker === 'function') {
                    try {
                        dateInput.showPicker();
                    } catch (err) {}
                }
            });

            // When date is chosen from the picker
            const onDateChanged = () => {
                if (dateInput.value) {
                    displayInput.value = formatDisplay(dateInput.value);
                } else {
                    displayInput.value = '';
                }

                // Trigger change & input events on displayInput for any listeners
                displayInput.dispatchEvent(new Event('input', { bubbles: true }));
                displayInput.dispatchEvent(new Event('change', { bubbles: true }));

                // Synchronize pickup_date -> return_date
                if (dateInput.id === 'pickup_date') {
                    const returnDateInput = document.getElementById('return_date');
                    const returnDisplayInput = document.getElementById('return_date_display');
                    if (returnDateInput) {
                        returnDateInput.min = dateInput.value;
                        if (!returnDateInput.value || returnDateInput.value <= dateInput.value) {
                            const next = new Date(dateInput.value);
                            next.setDate(next.getDate() + 2);
                            const nextIso = formatISO(next);
                            returnDateInput.value = nextIso;
                            if (returnDisplayInput) {
                                returnDisplayInput.value = formatDisplay(nextIso);
                            }
                            returnDateInput.dispatchEvent(new Event('change', { bubbles: true }));
                            if (returnDisplayInput) {
                                returnDisplayInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    }
                }
            };

            dateInput.addEventListener('input', onDateChanged);
            dateInput.addEventListener('change', onDateChanged);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDatePickers);
    } else {
        initDatePickers();
    }
})();

// Proteksi Double Submit & Feedback Loading State (F2-UI-01)
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.dataset.noDisable || e.defaultPrevented) return;

    const submitBtn = form.querySelector('button[type="submit"]:not([data-no-disable])');
    if (submitBtn && !submitBtn.disabled) {
        setTimeout(() => {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            const processingText = submitBtn.dataset.loadingText || 'Memproses...';
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>${processingText}</span>
            `;
        }, 10);
    }
});

// Auto Uppercase untuk Input Kode Promo (F1-TXT-02)
document.addEventListener('input', (e) => {
    if (e.target && (e.target.name === 'promo_code' || e.target.id === 'promo_code')) {
        const start = e.target.selectionStart;
        const end = e.target.selectionEnd;
        e.target.value = e.target.value.toUpperCase();
        if (start !== null && end !== null) {
            e.target.setSelectionRange(start, end);
        }
    }
});
