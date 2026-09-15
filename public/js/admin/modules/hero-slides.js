document.addEventListener(
    'DOMContentLoaded',
    function () {
        const root =
            document.querySelector(
                '[data-hero-slide-editor]'
            );

        if (! root) {
            return;
        }

        const mediaId =
            root.querySelector(
                '#hero_slide_media_id'
            );

        const choose =
            root.querySelector(
                '#hero-slide-media-choose'
            );

        const remove =
            root.querySelector(
                '#hero-slide-media-remove'
            );

        const empty =
            root.querySelector(
                '[data-hero-slide-media-empty]'
            );

        const selected =
            root.querySelector(
                '[data-hero-slide-media-selected]'
            );

        const image =
            root.querySelector(
                '[data-hero-slide-media-image]'
            );

        const title =
            root.querySelector(
                '[data-hero-slide-media-title]'
            );

        const payload =
            root.querySelector(
                '[data-hero-slide-selected-media]'
            );

        let currentMedia = null;

        if (
            payload
            && payload.textContent.trim()
        ) {
            try {
                currentMedia =
                    JSON.parse(
                        payload.textContent
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
                            'Pilih Gambar Hero',

                        description:
                            'Pilih gambar atau banner untuk slide homepage.',

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
