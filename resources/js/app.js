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

// Padaidi Custom DatePicker (Format dd/mm/yyyy & Interactive Calendar Popup)
(() => {
    function initDatePickers() {
        const wrappers = document.querySelectorAll('[data-datepicker-wrapper]');
        if (!wrappers.length) return;

        let popup = document.getElementById('padaidi-calendar-popup');
        if (!popup) {
            popup = document.createElement('div');
            popup.id = 'padaidi-calendar-popup';
            popup.className = 'padaidi-calendar-popup hidden absolute z-[9999] bg-white rounded-xl border border-slate-200 shadow-2xl p-4 w-[310px] text-slate-800 select-none';
            popup.innerHTML = `
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <button type="button" class="cal-nav-btn p-1.5 rounded-lg hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors cursor-pointer" data-cal-prev aria-label="Bulan sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="cal-title font-extrabold text-xs uppercase tracking-wider text-slate-900" data-cal-title></span>
                    <button type="button" class="cal-nav-btn p-1.5 rounded-lg hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors cursor-pointer" data-cal-next aria-label="Bulan berikutnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center mb-1">
                    <span class="text-[11px] font-bold text-rose-500">Min</span>
                    <span class="text-[11px] font-bold text-slate-500">Sen</span>
                    <span class="text-[11px] font-bold text-slate-500">Sel</span>
                    <span class="text-[11px] font-bold text-slate-500">Rab</span>
                    <span class="text-[11px] font-bold text-slate-500">Kam</span>
                    <span class="text-[11px] font-bold text-slate-500">Jum</span>
                    <span class="text-[11px] font-bold text-slate-500">Sab</span>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-xs" data-cal-grid></div>
                <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-slate-100 text-[11px]">
                    <button type="button" class="font-bold text-amber-600 hover:text-amber-700 cursor-pointer" data-cal-today>Hari ini</button>
                    <button type="button" class="font-bold text-slate-400 hover:text-slate-600 cursor-pointer" data-cal-close>Tutup</button>
                </div>
            `;
            document.body.appendChild(popup);
        }

        const monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        let activeWrapper = null;
        let viewYear = new Date().getFullYear();
        let viewMonth = new Date().getMonth();

        const titleEl = popup.querySelector('[data-cal-title]');
        const gridEl = popup.querySelector('[data-cal-grid]');
        const prevBtn = popup.querySelector('[data-cal-prev]');
        const nextBtn = popup.querySelector('[data-cal-next]');
        const todayBtn = popup.querySelector('[data-cal-today]');
        const closeBtn = popup.querySelector('[data-cal-close]');

        function pad(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        function parseISODate(str) {
            if (!str || typeof str !== 'string') return null;
            const parts = str.trim().split('-');
            if (parts.length !== 3) return null;
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            if (isNaN(y) || isNaN(m) || isNaN(d)) return null;
            return new Date(y, m, d);
        }

        function formatDisplay(isoDateStr) {
            const d = parseISODate(isoDateStr);
            if (!d) return '';
            return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
        }

        function formatISO(d) {
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
        }

        function parseDisplayDate(str) {
            if (!str || typeof str !== 'string') return null;
            const parts = str.trim().split('/');
            if (parts.length !== 3) return null;
            const d = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const y = parseInt(parts[2], 10);
            if (isNaN(y) || isNaN(m) || isNaN(d)) return null;
            const date = new Date(y, m, d);
            if (date.getFullYear() === y && date.getMonth() === m && date.getDate() === d) {
                return date;
            }
            return null;
        }

        function positionPopup(targetWrapper) {
            const rect = targetWrapper.getBoundingClientRect();
            const popupWidth = 310;
            const popupHeight = 330;

            let left = rect.left + window.scrollX;
            let top = rect.bottom + window.scrollY + 6;

            if (rect.left + popupWidth > window.innerWidth - 12) {
                left = window.innerWidth - popupWidth - 12 + window.scrollX;
            }
            if (left < 12) left = 12 + window.scrollX;

            if (rect.bottom + popupHeight > window.innerHeight && rect.top > popupHeight) {
                top = rect.top + window.scrollY - popupHeight - 6;
            }

            popup.style.left = `${Math.round(left)}px`;
            popup.style.top = `${Math.round(top)}px`;
        }

        function renderCalendar() {
            if (!activeWrapper) return;
            const hiddenInput = activeWrapper.querySelector('[data-datepicker-value]');
            const minStr = hiddenInput.dataset.min || '';
            const minDate = parseISODate(minStr);
            const maxStr = hiddenInput.dataset.max || '';
            const maxDate = parseISODate(maxStr);
            const selectedDate = parseISODate(hiddenInput.value);

            const now = new Date();
            const todayStr = formatISO(now);

            titleEl.textContent = `${monthNames[viewMonth]} ${viewYear}`;

            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            const firstDayOfWeek = new Date(viewYear, viewMonth, 1).getDay();

            gridEl.innerHTML = '';

            for (let i = 0; i < firstDayOfWeek; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'h-8 w-8';
                gridEl.appendChild(emptyCell);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateObj = new Date(viewYear, viewMonth, day);
                const dateIso = formatISO(dateObj);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = day;
                btn.dataset.date = dateIso;

                let isDisabled = false;
                if (minDate && dateObj < minDate) {
                    isDisabled = true;
                }
                if (maxDate && dateObj > maxDate) {
                    isDisabled = true;
                }

                const isSelected = selectedDate && formatISO(selectedDate) === dateIso;
                const isToday = dateIso === todayStr;

                if (isDisabled) {
                    btn.className = 'h-8 w-8 mx-auto flex items-center justify-center rounded-lg text-slate-300 opacity-30 cursor-not-allowed';
                    btn.disabled = true;
                } else if (isSelected) {
                    btn.className = 'h-8 w-8 mx-auto flex items-center justify-center rounded-lg bg-[#F5A623] text-[#0b1220] font-black shadow-sm cursor-pointer';
                } else if (isToday) {
                    btn.className = 'h-8 w-8 mx-auto flex items-center justify-center rounded-lg font-bold text-amber-700 border border-amber-400 hover:bg-amber-100 cursor-pointer transition-colors';
                } else {
                    btn.className = 'h-8 w-8 mx-auto flex items-center justify-center rounded-lg font-semibold text-slate-700 hover:bg-amber-100 hover:text-amber-900 cursor-pointer transition-colors';
                }

                gridEl.appendChild(btn);
            }
        }

        function openCalendar(wrapper) {
            activeWrapper = wrapper;
            const hiddenInput = wrapper.querySelector('[data-datepicker-value]');
            const curDate = parseISODate(hiddenInput.value) || new Date();
            viewYear = curDate.getFullYear();
            viewMonth = curDate.getMonth();

            positionPopup(wrapper);
            renderCalendar();
            popup.classList.remove('hidden');
        }

        function closeCalendar() {
            popup.classList.add('hidden');
            activeWrapper = null;
        }

        function selectDate(isoDateStr) {
            if (!activeWrapper) return;
            const displayInput = activeWrapper.querySelector('[data-datepicker-display]');
            const hiddenInput = activeWrapper.querySelector('[data-datepicker-value]');

            hiddenInput.value = isoDateStr;
            displayInput.value = formatDisplay(isoDateStr);

            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            displayInput.dispatchEvent(new Event('input', { bubbles: true }));
            displayInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Automatic synchronization for linked pickup -> return date pickers
            if (hiddenInput.id === 'pickup_date') {
                const returnWrapper = document.querySelector('[data-datepicker-wrapper] #return_date')?.closest('[data-datepicker-wrapper]');
                if (returnWrapper) {
                    const returnHidden = returnWrapper.querySelector('[data-datepicker-value]');
                    const returnDisplay = returnWrapper.querySelector('[data-datepicker-display]');
                    returnHidden.dataset.min = isoDateStr;

                    if (!returnHidden.value || returnHidden.value <= isoDateStr) {
                        const nextDay = new Date(parseISODate(isoDateStr));
                        nextDay.setDate(nextDay.getDate() + 2);
                        const nextIso = formatISO(nextDay);
                        returnHidden.value = nextIso;
                        returnDisplay.value = formatDisplay(nextIso);
                        returnHidden.dispatchEvent(new Event('input', { bubbles: true }));
                        returnHidden.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }

            closeCalendar();
        }

        prevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            renderCalendar();
        });

        nextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            renderCalendar();
        });

        todayBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const now = new Date();
            const todayIso = formatISO(now);
            if (activeWrapper) {
                const hidden = activeWrapper.querySelector('[data-datepicker-value]');
                const min = hidden.dataset.min;
                if (!min || todayIso >= min) {
                    selectDate(todayIso);
                } else {
                    viewYear = now.getFullYear();
                    viewMonth = now.getMonth();
                    renderCalendar();
                }
            }
        });

        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeCalendar();
        });

        gridEl.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-date]');
            if (btn && !btn.disabled) {
                e.stopPropagation();
                selectDate(btn.dataset.date);
            }
        });

        document.addEventListener('click', (e) => {
            if (!popup.classList.contains('hidden')) {
                if (!popup.contains(e.target) && !activeWrapper?.contains(e.target)) {
                    closeCalendar();
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !popup.classList.contains('hidden')) {
                closeCalendar();
            }
        });

        window.addEventListener('resize', () => {
            if (!popup.classList.contains('hidden') && activeWrapper) {
                positionPopup(activeWrapper);
            }
        });

        wrappers.forEach((wrapper) => {
            const displayInput = wrapper.querySelector('[data-datepicker-display]');
            const hiddenInput = wrapper.querySelector('[data-datepicker-value]');
            const triggerBtn = wrapper.querySelector('[data-datepicker-trigger]');

            if (hiddenInput.value && !displayInput.value) {
                displayInput.value = formatDisplay(hiddenInput.value);
            }

            if (triggerBtn) {
                triggerBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (activeWrapper === wrapper && !popup.classList.contains('hidden')) {
                        closeCalendar();
                    } else {
                        openCalendar(wrapper);
                    }
                });
            }

            displayInput.addEventListener('click', (e) => {
                e.stopPropagation();
                openCalendar(wrapper);
            });

            displayInput.addEventListener('input', () => {
                let v = displayInput.value.replace(/[^\d]/g, '');
                if (v.length > 8) v = v.substring(0, 8);
                if (v.length > 4) {
                    displayInput.value = v.substring(0, 2) + '/' + v.substring(2, 4) + '/' + v.substring(4);
                } else if (v.length > 2) {
                    displayInput.value = v.substring(0, 2) + '/' + v.substring(2);
                } else {
                    displayInput.value = v;
                }

                if (displayInput.value.length === 10) {
                    const parsed = parseDisplayDate(displayInput.value);
                    if (parsed) {
                        const iso = formatISO(parsed);
                        hiddenInput.value = iso;
                        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });

            displayInput.addEventListener('blur', () => {
                if (displayInput.value.length === 10) {
                    const parsed = parseDisplayDate(displayInput.value);
                    if (parsed) {
                        const iso = formatISO(parsed);
                        const minStr = hiddenInput.dataset.min;
                        if (minStr && iso < minStr) {
                            hiddenInput.value = minStr;
                            displayInput.value = formatDisplay(minStr);
                        } else {
                            hiddenInput.value = iso;
                        }
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        displayInput.value = formatDisplay(hiddenInput.value);
                    }
                } else if (displayInput.value.trim() === '') {
                    hiddenInput.value = '';
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    displayInput.value = formatDisplay(hiddenInput.value);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDatePickers);
    } else {
        initDatePickers();
    }
})();
