(function () {
    'use strict';

    function init() {
        const nav = document.querySelector(
            '.admin-wrapper > .sidebar .sidebar-nav'
        );

        if (!nav || nav.dataset.ppidAccordionReady === '1') return;

        const headings = Array.from(nav.children).filter(
            node => node.classList.contains('sidebar-section-title')
        );

        if (!headings.length) return;

        nav.dataset.ppidAccordionReady = '1';

        const groups = [];
        const storageKey = 'web-ppid.admin.sidebar.open-group';

        let saved = null;

        try {
            saved = localStorage.getItem(storageKey);
        } catch (_) {}

        /*
         * Reuse existing links and elements.
         * Existing URLs, active classes, and listeners are preserved.
         */
        headings.forEach(function (heading, index) {
            const title = heading.textContent.replace(/\s+/g, ' ').trim();

            const section = document.createElement('section');
            section.className = 'ppid-nav-group';

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'ppid-nav-group-toggle';

            const buttonId = 'ppid-nav-group-button-' + index;
            const panelId = 'ppid-nav-group-panel-' + index;

            button.id = buttonId;
            button.setAttribute('aria-controls', panelId);
            button.setAttribute('aria-expanded', 'false');

            const label = document.createElement('span');
            label.className = 'ppid-nav-group-label';
            label.textContent = title;

            const icon = document.createElementNS(
                'http://www.w3.org/2000/svg',
                'svg'
            );

            icon.classList.add('ppid-nav-group-chevron');
            icon.setAttribute('viewBox', '0 0 24 24');
            icon.setAttribute('fill', 'none');
            icon.setAttribute('stroke', 'currentColor');
            icon.setAttribute('stroke-width', '1.8');
            icon.setAttribute('stroke-linecap', 'round');
            icon.setAttribute('stroke-linejoin', 'round');
            icon.setAttribute('aria-hidden', 'true');

            const path = document.createElementNS(
                'http://www.w3.org/2000/svg',
                'path'
            );

            path.setAttribute('d', 'm6 9 6 6 6-6');
            icon.append(path);

            button.append(label, icon);

            const panel = document.createElement('div');
            panel.id = panelId;
            panel.className = 'ppid-nav-group-panel';
            panel.setAttribute('role', 'region');
            panel.setAttribute('aria-labelledby', buttonId);
            panel.hidden = true;

            /*
             * Collect the links following this heading,
             * stopping at the next original group heading.
             */
            let sibling = heading.nextSibling;

            while (sibling) {
                if (
                    sibling.nodeType === Node.ELEMENT_NODE &&
                    sibling.classList.contains('sidebar-section-title')
                ) {
                    break;
                }

                const next = sibling.nextSibling;
                panel.append(sibling);
                sibling = next;
            }

            section.append(button, panel);
            heading.replaceWith(section);

            const active = Boolean(
                panel.querySelector('.sidebar-link.active, [aria-current="page"]')
            );

            section.classList.toggle('has-active', active);

            groups.push({
                section,
                button,
                panel,
                active,
                key: title
            });
        });

        function setOpen(group, open) {
            group.button.setAttribute('aria-expanded', String(open));
            group.panel.hidden = !open;
            group.section.classList.toggle('is-open', open);
        }

        function remember(key) {
            try {
                localStorage.setItem(storageKey, key);
            } catch (_) {}
        }

        groups.forEach(function (group) {
            group.button.addEventListener('click', function () {
                const willOpen =
                    group.button.getAttribute('aria-expanded') !== 'true';

                groups.forEach(function (other) {
                    setOpen(other, other === group && willOpen);
                });

                remember(willOpen ? group.key : '');
            });

            /*
             * Optional arrow-key navigation between group headers.
             * Tab still follows the normal link order.
             */
            group.button.addEventListener('keydown', function (event) {
                const position = groups.indexOf(group);
                let target = null;

                if (event.key === 'ArrowDown') {
                    target = groups[(position + 1) % groups.length];
                } else if (event.key === 'ArrowUp') {
                    target = groups[
                        (position - 1 + groups.length) % groups.length
                    ];
                } else if (event.key === 'Home') {
                    target = groups[0];
                } else if (event.key === 'End') {
                    target = groups[groups.length - 1];
                }

                if (target) {
                    event.preventDefault();
                    target.button.focus();
                }
            });
        });

        /*
         * Show the current page's group first.
         * Otherwise use the last opened group.
         */
        const initial =
            groups.find(group => group.active) ||
            groups.find(group => group.key === saved);

        groups.forEach(group => setOpen(group, group === initial));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
