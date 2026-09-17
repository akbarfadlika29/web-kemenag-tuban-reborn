document.addEventListener(
    'DOMContentLoaded',
    function () {
        const root =
            document.querySelector(
                '[data-settings-page]'
            );

        if (! root) {
            return;
        }

        initSettingsTabs(root);
        initSettingsLogo(root);
    }
);

function initSettingsTabs(root) {
    const tabs =
        Array.from(
            root.querySelectorAll(
                '[data-settings-tab]'
            )
        );

    const panels =
        Array.from(
            root.querySelectorAll(
                '[data-settings-panel]'
            )
        );

    if (
        ! tabs.length
        || ! panels.length
    ) {
        return;
    }

    function activateTab(
        groupKey,
        focus = false
    ) {
        tabs.forEach(
            function (tab) {
                const active =
                    tab.dataset.settingsTab
                    === groupKey;

                tab.classList.toggle(
                    'is-active',
                    active
                );

                tab.setAttribute(
                    'aria-selected',
                    active
                        ? 'true'
                        : 'false'
                );

                tab.tabIndex =
                    active
                        ? 0
                        : -1;

                if (
                    active
                    && focus
                ) {
                    tab.focus();
                }
            }
        );

        panels.forEach(
            function (panel) {
                const active =
                    panel.dataset.settingsPanel
                    === groupKey;

                panel.classList.toggle(
                    'is-active',
                    active
                );

                panel.hidden =
                    ! active;
            }
        );
    }

    tabs.forEach(
        function (
            tab,
            index
        ) {
            tab.addEventListener(
                'click',
                function () {
                    activateTab(
                        tab.dataset.settingsTab
                    );
                }
            );

            tab.addEventListener(
                'keydown',
                function (event) {
                    if (
                        event.key !== 'ArrowLeft'
                        && event.key !== 'ArrowRight'
                    ) {
                        return;
                    }

                    event.preventDefault();

                    const direction =
                        event.key === 'ArrowRight'
                            ? 1
                            : -1;

                    const nextIndex =
                        (
                            index
                            + direction
                            + tabs.length
                        )
                        % tabs.length;

                    activateTab(
                        tabs[nextIndex]
                            .dataset
                            .settingsTab,
                        true
                    );
                }
            );
        }
    );

    const initial =
        root.dataset.settingsActiveTab
        || tabs[0].dataset.settingsTab;

    activateTab(initial);
}

function initSettingsLogo(root) {
    const input =
        root.querySelector(
            '[data-setting-logo-input]'
        );

    const image =
        root.querySelector(
            '[data-setting-logo-image]'
        );

    const empty =
        root.querySelector(
            '[data-setting-logo-empty]'
        );

    const select =
        root.querySelector(
            '[data-setting-logo-select]'
        );

    const remove =
        root.querySelector(
            '[data-setting-logo-remove]'
        );

    const removeWrap =
        root.querySelector(
            '[data-setting-logo-remove-wrap]'
        );

    if (
        ! input
        || ! image
        || ! empty
        || ! select
    ) {
        return;
    }

    select.addEventListener(
        'click',
        async function () {
            if (
                ! window.AdminMediaPicker
                || typeof window.AdminMediaPicker.open
                    !== 'function'
            ) {
                window.PpidPopup.alert(
                    'Media Manager belum tersedia.'
                );

                return;
            }

            const currentId =
                Number(input.value);

            const selected =
                Number.isInteger(currentId)
                && currentId > 0
                    ? [
                        {
                            id:
                                currentId,
                        },
                    ]
                    : [];

            const result =
                await window.AdminMediaPicker.open({
                    title:
                        'Pilih Logo Website',

                    description:
                        'Pilih gambar yang sudah ada atau upload logo baru.',

                    type:
                        'image',

                    multiple:
                        false,

                    selected:
                        selected,
                });

            if (
                ! Array.isArray(result)
                || ! result.length
            ) {
                return;
            }

            const media =
                result[0];

            input.value =
                String(media.id);

            image.src =
                media.url;

            image.alt =
                media.alt_text
                || media.title
                || 'Logo website';

            image.hidden =
                false;

            empty.hidden =
                true;

            if (removeWrap) {
                removeWrap.hidden =
                    false;
            }
        }
    );

    remove?.addEventListener(
        'click',
        function () {
            input.value = '';

            image.src = '';
            image.alt = '';
            image.hidden = true;

            empty.hidden = false;

            if (removeWrap) {
                removeWrap.hidden =
                    true;
            }
        }
    );
}
