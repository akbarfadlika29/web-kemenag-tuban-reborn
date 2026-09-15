(() => {
    'use strict';

    function initialize() {
        const nav = document.querySelector('[data-site-nav]');
        const toggle = document.querySelector('[data-nav-toggle]');

        if (!nav || nav.dataset.navigationReady === 'true') return;

        nav.dataset.navigationReady = 'true';
        nav.classList.add('ppid-nav-managed');

        const mobile = window.matchMedia('(max-width: 760px)');
        const items = [...nav.querySelectorAll('.site-nav-item.has-children')];

        function buttonFor(item) {
            return item.querySelector(
                ':scope > .site-nav-link-row > [data-nav-submenu-toggle]'
            );
        }

        function closeItem(item) {
            item.classList.remove('is-submenu-open');
            buttonFor(item)?.setAttribute('aria-expanded', 'false');

            item.querySelectorAll('.site-nav-item.has-children')
                .forEach(child => {
                    child.classList.remove('is-submenu-open');
                    buttonFor(child)?.setAttribute('aria-expanded', 'false');
                });
        }

        function closeSubmenus() {
            items.forEach(closeItem);
        }

        function closeNavigation(restoreFocus = false) {
            const focusInside = nav.contains(document.activeElement);

            closeSubmenus();
            nav.classList.remove('is-open');
            toggle?.setAttribute('aria-expanded', 'false');

            if (restoreFocus && focusInside) {
                toggle?.focus({preventScroll: true});
            }
        }

        function openItem(item) {
            // Tutup saudara saja, bukan induk submenu.
            [...item.parentElement.children].forEach(sibling => {
                if (
                    sibling !== item &&
                    sibling.matches('.site-nav-item.has-children')
                ) {
                    closeItem(sibling);
                }
            });

            item.classList.add('is-submenu-open');
            buttonFor(item)?.setAttribute('aria-expanded', 'true');

            // Geser dropdown utama jika melewati tepi layar.
            if (!mobile.matches && !item.closest('.site-nav-submenu')) {
                const panel = item.querySelector(':scope > .site-nav-submenu');

                if (panel) {
                    panel.style.setProperty('--nav-shift', '0px');
                    const rect = panel.getBoundingClientRect();
                    let shift = 0;

                    if (rect.right > window.innerWidth - 12) {
                        shift -= rect.right - window.innerWidth + 12;
                    }

                    if (rect.left + shift < 12) {
                        shift += 12 - (rect.left + shift);
                    }

                    panel.style.setProperty('--nav-shift', shift + 'px');
                }
            }
        }

        if (!nav.id) nav.id = 'ppid-site-navigation';

        toggle?.setAttribute('aria-controls', nav.id);
        toggle?.setAttribute('aria-expanded', 'false');

        items.forEach((item, index) => {
            const button = buttonFor(item);
            const panel = item.querySelector(':scope > .site-nav-submenu');

            if (!button || !panel) return;

            if (!panel.id) panel.id = 'ppid-navigation-submenu-' + index;

            button.setAttribute('aria-controls', panel.id);
            button.setAttribute('aria-expanded', 'false');

            function activate(event) {
                event.preventDefault();
                event.stopPropagation();

                if (item.classList.contains('is-submenu-open')) {
                    closeItem(item);
                } else {
                    openItem(item);
                }
            }

            button.addEventListener('click', activate);

            // Label tanpa URL juga bisa disentuh untuk membuka submenu.
            const label = item.querySelector(
                ':scope > .site-nav-link-row > .site-nav-link-disabled'
            );

            if (label) {
                label.style.cursor = 'pointer';
                label.addEventListener('click', activate);
            }
        });

        toggle?.addEventListener('click', () => {
            const open = !nav.classList.contains('is-open');

            if (open) {
                nav.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            } else {
                closeNavigation();
            }
        });

        // Tidak ada penutupan saat mouseleave atau perpindahan fokus submenu.
        document.addEventListener('click', event => {
            if (
                !nav.contains(event.target) &&
                !toggle?.contains(event.target)
            ) {
                closeNavigation();
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key !== 'Escape') return;

            const active = document.activeElement;
            if (!nav.contains(active) && active !== toggle) return;

            const opened = active.closest('.site-nav-item.is-submenu-open');

            if (opened) {
                event.preventDefault();
                closeItem(opened);
                buttonFor(opened)?.focus({preventScroll: true});
            } else {
                event.preventDefault();
                closeNavigation(true);
            }
        });

        nav.addEventListener('click', event => {
            const link = event.target.closest('a.site-nav-link');

            if (
                !link ||
                event.ctrlKey ||
                event.metaKey ||
                event.shiftKey ||
                link.target === '_blank'
            ) {
                return;
            }

            if (mobile.matches) {
                toggle?.focus({preventScroll: true});
            }

            closeNavigation();
        });

        mobile.addEventListener('change', () => closeNavigation(true));
        closeNavigation();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, {once:true});
    } else {
        initialize();
    }
})();