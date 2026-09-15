document.addEventListener(
    'DOMContentLoaded',
    function () {
        const toggle =
            document.querySelector(
                '[data-nav-toggle]'
            );

        const navigation =
            document.querySelector(
                '[data-site-nav]'
            );

        if (!navigation) {
            return;
        }

        const submenuToggles =
            navigation.querySelectorAll(
                '[data-nav-submenu-toggle]'
            );

        const submenuItems =
            navigation.querySelectorAll(
                '.site-nav-item.has-children'
            );

        const navLinks =
            navigation.querySelectorAll(
                '.site-nav-link'
            );

        /*
         * Desktop menggunakan hover.
         * Perangkat touch / mobile menggunakan tombol submenu.
         */
        const hoverNavigation =
            window.matchMedia(
                '(min-width: 761px) and ' +
                '(hover: hover) and ' +
                '(pointer: fine)'
            );

        function usesHoverNavigation() {
            return hoverNavigation.matches;
        }

        function directToggle(item) {
            if (!item) {
                return null;
            }

            return item.querySelector(
                ':scope > ' +
                '.site-nav-link-row > ' +
                '[data-nav-submenu-toggle]'
            );
        }

        function setExpanded(
            item,
            expanded
        ) {
            const button =
                directToggle(item);

            if (!button) {
                return;
            }

            button.setAttribute(
                'aria-expanded',
                expanded
                    ? 'true'
                    : 'false'
            );
        }

        function closeSubmenus() {
            navigation
                .querySelectorAll(
                    '.site-nav-item.is-submenu-open'
                )
                .forEach(function (item) {
                    item.classList.remove(
                        'is-submenu-open'
                    );
                });

            submenuToggles.forEach(
                function (button) {
                    button.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }
            );
        }

        /*
         * Desktop:
         * hover membuka menu,
         * mouseleave menutup menu.
         *
         * CSS yang mengatur display submenu.
         * JS hanya menjaga state agar tidak terkunci.
         */
        submenuItems.forEach(
            function (item) {
                item.addEventListener(
                    'mouseenter',
                    function () {
                        if (
                            !usesHoverNavigation()
                        ) {
                            return;
                        }

                        setExpanded(
                            item,
                            true
                        );
                    }
                );

                item.addEventListener(
                    'mouseleave',
                    function () {
                        if (
                            !usesHoverNavigation()
                        ) {
                            return;
                        }

                        item.classList.remove(
                            'is-submenu-open'
                        );

                        setExpanded(
                            item,
                            false
                        );
                    }
                );

                /*
                 * Tetap mendukung navigasi keyboard
                 * melalui :focus-within.
                 */
                item.addEventListener(
                    'focusin',
                    function () {
                        setExpanded(
                            item,
                            true
                        );
                    }
                );

                item.addEventListener(
                    'focusout',
                    function () {
                        window.setTimeout(
                            function () {
                                if (
                                    item.contains(
                                        document.activeElement
                                    )
                                ) {
                                    return;
                                }

                                setExpanded(
                                    item,
                                    false
                                );
                            },
                            0
                        );
                    }
                );
            }
        );

        submenuToggles.forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const item =
                            button.closest(
                                '.site-nav-item'
                            );

                        if (!item) {
                            return;
                        }

                        /*
                         * Klik mouse pada desktop tidak
                         * boleh mengunci submenu.
                         *
                         * Hover adalah kontrol utama.
                         */
                        if (
                            usesHoverNavigation()
                            && event.detail > 0
                        ) {
                            item.classList.remove(
                                'is-submenu-open'
                            );

                            setExpanded(
                                item,
                                false
                            );

                            button.blur();

                            return;
                        }

                        /*
                         * Mobile/touch dan aktivasi
                         * melalui keyboard tetap toggle.
                         */
                        const isOpen =
                            item.classList.toggle(
                                'is-submenu-open'
                            );

                        setExpanded(
                            item,
                            isOpen
                        );
                    }
                );
            }
        );

        /*
         * Klik link dengan mouse pada desktop harus
         * membersihkan state/focus agar :focus-within
         * tidak membuat dropdown tertinggal terbuka.
         */
        navLinks.forEach(
            function (link) {
                link.addEventListener(
                    'click',
                    function (event) {
                        if (
                            usesHoverNavigation()
                            && event.detail > 0
                        ) {
                            closeSubmenus();

                            link.blur();

                            return;
                        }

                        if (
                            !usesHoverNavigation()
                        ) {
                            closeSubmenus();

                            navigation.classList.remove(
                                'is-open'
                            );

                            if (toggle) {
                                toggle.setAttribute(
                                    'aria-expanded',
                                    'false'
                                );
                            }
                        }
                    }
                );
            }
        );

        if (toggle) {
            toggle.addEventListener(
                'click',
                function () {
                    const isOpen =
                        navigation.classList.toggle(
                            'is-open'
                        );

                    toggle.setAttribute(
                        'aria-expanded',
                        isOpen
                            ? 'true'
                            : 'false'
                    );

                    if (!isOpen) {
                        closeSubmenus();
                    }
                }
            );
        }

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                closeSubmenus();

                navigation.classList.remove(
                    'is-open'
                );

                if (toggle) {
                    toggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }
            }
        );

        /*
         * Jika viewport berubah dari mobile
         * ke desktop, hapus state submenu
         * yang sebelumnya dibuka melalui tap.
         */
        function syncNavigationMode() {
            if (
                usesHoverNavigation()
            ) {
                closeSubmenus();
            }
        }

        if (
            typeof hoverNavigation
                .addEventListener
            === 'function'
        ) {
            hoverNavigation.addEventListener(
                'change',
                syncNavigationMode
            );
        } else if (
            typeof hoverNavigation.addListener
            === 'function'
        ) {
            hoverNavigation.addListener(
                syncNavigationMode
            );
        }
    }
);
