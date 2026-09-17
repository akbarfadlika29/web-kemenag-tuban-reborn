(function () {
    'use strict';

    function init() {
        const sidebar = document.querySelector('.admin-wrapper > .sidebar');
        const shell = document.querySelector('.admin-shell');
        const toggle = document.querySelector('[data-admin-sidebar-toggle]');
        const overlay = document.querySelector('[data-admin-sidebar-overlay]');

        if (!sidebar || !shell || !toggle || !overlay) {
            console.error('Sidebar: elemen layout belum lengkap.');
            return;
        }

        if (sidebar.dataset.ppidSidebarReady === '1') return;
        sidebar.dataset.ppidSidebarReady = '1';

        const mobile = window.matchMedia('(max-width: 768px)');
        const storageKey = 'web-ppid.admin.sidebar.hidden';

        let desktopHidden = false;
        let mobileOpen = false;
        let shellPreviousInert = shell.inert;
        let shellLocked = false;

        try {
            desktopHidden = localStorage.getItem(storageKey) === '1';
        } catch (_) {
            // Sidebar still works when storage is unavailable.
        }

        sidebar.id = sidebar.id || 'ppid-admin-sidebar';
        toggle.setAttribute('aria-controls', sidebar.id);
        toggle.type = 'button';

        overlay.type = 'button';
        overlay.tabIndex = -1;
        overlay.setAttribute('aria-label', 'Tutup menu navigasi');

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'ppid-sidebar-close';
        close.textContent = '×';
        close.setAttribute('aria-label', 'Tutup menu navigasi');

        (sidebar.querySelector('.sidebar-brand') || sidebar).append(close);

        function focusable() {
            return Array.from(sidebar.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), ' +
                'select:not([disabled]), textarea:not([disabled]), ' +
                '[tabindex]:not([tabindex="-1"])'
            )).filter(node =>
                node.getClientRects().length > 0 &&
                !node.closest('[inert]')
            );
        }

        function render() {
            const visible = mobile.matches ? mobileOpen : !desktopHidden;

            document.body.classList.toggle(
                'ppid-sidebar-hidden',
                !mobile.matches && desktopHidden
            );

            document.body.classList.toggle(
                'admin-sidebar-open',
                mobile.matches && mobileOpen
            );

            sidebar.inert = !visible;
            sidebar.setAttribute('aria-hidden', String(!visible));

            toggle.setAttribute('aria-expanded', String(visible));
            toggle.setAttribute(
                'aria-label',
                visible ? 'Sembunyikan menu navigasi' : 'Tampilkan menu navigasi'
            );
            toggle.title = toggle.getAttribute('aria-label');

            if (mobile.matches && mobileOpen) {
                if (!shellLocked) {
                    shellPreviousInert = shell.inert;
                    shell.inert = true;
                    shellLocked = true;
                }
            } else if (shellLocked) {
                shell.inert = shellPreviousInert;
                shellLocked = false;
            }
        }

        function closeMobile(restoreFocus = true) {
            mobileOpen = false;
            render();

            if (restoreFocus) {
                toggle.focus({ preventScroll: true });
            }
        }

        /*
         * Capture known sidebar controls before older click handlers.
         * Other admin buttons, dialogs, and links remain unaffected.
         */
        document.addEventListener('click', function (event) {
            if (!(event.target instanceof Element)) return;

            const control = event.target.closest(
                '[data-admin-sidebar-toggle], ' +
                '[data-admin-sidebar-overlay], ' +
                '.ppid-sidebar-close'
            );

            if (!control) return;

            event.preventDefault();
            event.stopImmediatePropagation();

            if (control === toggle) {
                if (mobile.matches) {
                    mobileOpen = !mobileOpen;
                    render();

                    if (mobileOpen) {
                        close.focus({ preventScroll: true });
                    }
                } else {
                    desktopHidden = !desktopHidden;

                    try {
                        localStorage.setItem(
                            storageKey,
                            desktopHidden ? '1' : '0'
                        );
                    } catch (_) {}

                    render();
                }

                return;
            }

            closeMobile();
        }, true);

        document.addEventListener('keydown', function (event) {
            if (!mobile.matches || !mobileOpen) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopImmediatePropagation();
                closeMobile();
                return;
            }

            if (event.key !== 'Tab') return;

            const items = focusable();
            const first = items[0];
            const last = items[items.length - 1];

            if (!first) {
                event.preventDefault();
                return;
            }

            if (event.shiftKey &&
                (document.activeElement === first ||
                 !sidebar.contains(document.activeElement))) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey &&
                (document.activeElement === last ||
                 !sidebar.contains(document.activeElement))) {
                event.preventDefault();
                first.focus();
            }
        }, true);

        sidebar.addEventListener('click', function (event) {
            if (!(event.target instanceof Element)) return;

            const link = event.target.closest('a[href]');

            if (mobile.matches && link) {
                closeMobile();
            }
        });

        mobile.addEventListener('change', function () {
            const focusWasInside = sidebar.contains(document.activeElement);

            mobileOpen = false;
            render();

            if (sidebar.inert && focusWasInside) {
                toggle.focus({ preventScroll: true });
            }
        });

        window.addEventListener('pageshow', function () {
            mobileOpen = false;
            render();
        });

        render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
