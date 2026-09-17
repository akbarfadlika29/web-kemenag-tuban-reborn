document.addEventListener(
    'DOMContentLoaded',
    function () {
        const root =
            document.querySelector(
                '[data-related-link-editor]'
            );

        if (! root) {
            return;
        }

        const mediaId =
            root.querySelector(
                '#related_link_media_id'
            );

        const choose =
            root.querySelector(
                '#related-link-media-choose'
            );

        const remove =
            root.querySelector(
                '#related-link-media-remove'
            );

        const empty =
            root.querySelector(
                '[data-related-link-media-empty]'
            );

        const selected =
            root.querySelector(
                '[data-related-link-media-selected]'
            );

        const image =
            root.querySelector(
                '[data-related-link-media-image]'
            );

        const title =
            root.querySelector(
                '[data-related-link-media-title]'
            );

        const payloadElement =
            root.querySelector(
                '[data-related-link-selected-media]'
            );

        let currentMedia = null;

        if (
            payloadElement
            && payloadElement.textContent.trim()
        ) {
            try {
                currentMedia =
                    JSON.parse(
                        payloadElement.textContent
                    );
            } catch (error) {
                currentMedia = null;
            }
        }

        function showMedia(item) {
            currentMedia = item;

            mediaId.value =
                String(item.id);

            image.src =
                item.url || '';

            image.alt =
                item.alt_text
                || item.title
                || '';

            title.textContent =
                item.title
                || item.original_name
                || 'Media';

            empty.hidden = true;
            selected.hidden = false;
        }

        function clearMedia() {
            currentMedia = null;

            mediaId.value = '';

            image.src = '';
            image.alt = '';

            title.textContent = '';

            selected.hidden = true;
            empty.hidden = false;
        }

        choose.addEventListener(
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
                            'Pilih Gambar Link Terkait',

                        description:
                            'Pilih logo atau gambar dari Media Manager.',

                        type:
                            'image',

                        multiple:
                            false,

                        selected:
                            currentMedia
                                ? [currentMedia]
                                : [],
                    });

                if (
                    Array.isArray(result)
                    && result.length
                ) {
                    showMedia(
                        result[0]
                    );
                }
            }
        );

        remove.addEventListener(
            'click',
            clearMedia
        );
    }
);
