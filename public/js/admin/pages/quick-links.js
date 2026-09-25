document.addEventListener(
    'DOMContentLoaded',
    function () {
        const root =
            document.querySelector(
                '[data-quick-link-editor]'
            );

        if (! root) {
            return;
        }

        const targetType =
            root.querySelector(
                '#target_type'
            );

        const targetBlocks =
            root.querySelectorAll(
                '[data-quick-link-target]'
            );

        const mediaId =
            root.querySelector(
                '#quick_link_media_id'
            );

        const chooseMedia =
            root.querySelector(
                '#quick-link-media-choose'
            );

        const removeMedia =
            root.querySelector(
                '#quick-link-media-remove'
            );

        const mediaPreview =
            root.querySelector(
                '[data-quick-link-media-preview]'
            );

        const mediaEmpty =
            root.querySelector(
                '[data-quick-link-media-empty]'
            );

        const mediaSelected =
            root.querySelector(
                '[data-quick-link-media-selected]'
            );

        const mediaImage =
            root.querySelector(
                '[data-quick-link-media-image]'
            );

        const mediaTitle =
            root.querySelector(
                '[data-quick-link-media-title]'
            );

        const selectedMediaJson =
            root.querySelector(
                '[data-quick-link-selected-media]'
            );

        let currentMedia = null;

        if (
            selectedMediaJson
            && selectedMediaJson.textContent.trim()
        ) {
            try {
                currentMedia =
                    JSON.parse(
                        selectedMediaJson.textContent
                    );
            } catch (error) {
                currentMedia = null;
            }
        }

        function updateTargetFields() {
            if (! targetType) {
                return;
            }

            targetBlocks.forEach(
                function (block) {
                    block.hidden =
                        block.dataset.quickLinkTarget
                        !== targetType.value;
                }
            );
        }

        function displayMedia(
            item
        ) {
            if (
                ! item
                || ! item.id
            ) {
                clearMedia();

                return;
            }

            currentMedia = item;

            mediaId.value =
                String(item.id);

            mediaImage.src =
                item.url || '';

            mediaImage.alt =
                item.alt_text
                || item.title
                || '';

            mediaTitle.textContent =
                item.title
                || item.original_name
                || 'Media';

            mediaEmpty.hidden = true;
            mediaSelected.hidden = false;

            mediaPreview.classList.add(
                'has-media'
            );
        }

        function clearMedia() {
            currentMedia = null;

            mediaId.value = '';

            mediaImage.src = '';
            mediaImage.alt = '';

            mediaTitle.textContent = '';

            mediaSelected.hidden = true;
            mediaEmpty.hidden = false;

            mediaPreview.classList.remove(
                'has-media'
            );
        }

        if (targetType) {
            updateTargetFields();

            targetType.addEventListener(
                'change',
                updateTargetFields
            );
        }

        if (chooseMedia) {
            chooseMedia.addEventListener(
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

                    const result =
                        await window.AdminMediaPicker.open({
                            title:
                                'Pilih Gambar Akses Cepat',

                            description:
                                'Pilih gambar dari Media Manager atau upload gambar baru.',

                            type:
                                'image',

                            multiple:
                                false,

                            selected:
                                currentMedia
                                    ? [
                                        currentMedia,
                                    ]
                                    : [],
                        });

                    if (
                        Array.isArray(result)
                        && result.length
                    ) {
                        displayMedia(
                            result[0]
                        );
                    }
                }
            );
        }

        if (removeMedia) {
            removeMedia.addEventListener(
                'click',
                clearMedia
            );
        }
    }
);
