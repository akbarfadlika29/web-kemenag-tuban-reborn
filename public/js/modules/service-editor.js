document.addEventListener('DOMContentLoaded', function () {
    const root =
        document.querySelector('[data-service-editor]');

    if (!root) {
        return;
    }

    const form =
        root.closest('form');

    const titleInput =
        document.getElementById('title');

    const slugInput =
        document.getElementById('slug');

    const excerptInput =
        document.getElementById('excerpt');

    const descriptionInput =
        document.getElementById('description');

    const metaTitleInput =
        document.getElementById('meta_title');

    const metaDescriptionInput =
        document.getElementById('meta_description');

    const channelInput =
        document.getElementById('service_channel');

    const serviceUrlInput =
        document.getElementById('service_url');

    const isFreeInput =
        document.getElementById('is_free');

    const feeSection =
        document.getElementById('service-fee-section');

    const feeInput =
        document.getElementById('fee_description');

    const statusInput =
        document.getElementById('status');

    const publishedInput =
        document.getElementById('published_at');

    const unitInput =
        document.getElementById('unit_id');

    const coverInput =
        document.getElementById('cover_media_id');

    const modal =
        document.getElementById('service-media-picker');

    const mediaGrid =
        document.getElementById('service-media-grid');

    const mediaSearch =
        document.getElementById('service-media-search');

    const mediaUnit =
        document.getElementById('service-media-unit');

    const mediaLoading =
        document.getElementById('service-media-loading');

    const mediaEmpty =
        document.getElementById('service-media-empty');

    const loadMore =
        document.getElementById('service-media-load-more');

    const mediaPickerUrl =
        root.dataset.mediaPickerUrl;

    let currentPage = 1;
    let hasMore = false;
    let loading = false;
    let searchTimer = null;
    let dirty = false;

    function generateSlug(value) {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function updateSeo() {
        const title =
            (
                metaTitleInput?.value
                || titleInput?.value
                || 'Nama layanan'
            ).trim();

        const slug =
            slugInput?.value
            || 'slug-layanan';

        const description =
            (
                metaDescriptionInput?.value
                || excerptInput?.value
                || descriptionInput?.value
                || 'Ringkasan layanan akan tampil di sini.'
            ).trim();

        document.getElementById(
            'service-seo-title'
        ).textContent =
            title;

        document.getElementById(
            'service-seo-slug'
        ).textContent =
            slug;

        document.getElementById(
            'service-seo-description'
        ).textContent =
            description.substring(
                0,
                180
            );
    }

    function updateFeeSection() {
        const isFree =
            isFreeInput?.checked;

        feeSection.hidden =
            isFree;

        if (
            isFree
            && feeInput
        ) {
            feeInput.value = '';
        }
    }

    function updateChannelFields() {
        const channel =
            channelInput?.value;

        const online =
            document.getElementById(
                'service-online-fields'
            );

        const offline =
            document.getElementById(
                'service-offline-fields'
            );

        online.hidden =
            channel === 'offline';

        offline.hidden =
            channel === 'online';

        if (
            channel === 'offline'
            && serviceUrlInput
        ) {
            serviceUrlInput.value = '';
        }
    }

    function updatePublicationHint() {
        const hint =
            document.getElementById(
                'service-publication-hint'
            );

        if (!hint) {
            return;
        }

        hint.textContent = '';

        hint.classList.remove(
            'is-visible'
        );

        if (
            statusInput?.value !==
            'published'
        ) {
            return;
        }

        if (!publishedInput?.value) {
            hint.textContent =
                'Layanan akan dipublikasikan saat disimpan.';

            hint.classList.add(
                'is-visible'
            );

            return;
        }

        if (
            new Date(
                publishedInput.value
            ) > new Date()
        ) {
            hint.textContent =
                'Publikasi layanan terjadwal.';

            hint.classList.add(
                'is-visible'
            );
        }
    }

    titleInput?.addEventListener(
        'input',
        function () {
            slugInput.value =
                generateSlug(
                    titleInput.value
                );

            dirty = true;

            updateSeo();
        }
    );

    [
        excerptInput,
        descriptionInput,
        metaTitleInput,
        metaDescriptionInput
    ].forEach(
        function (input) {
            input?.addEventListener(
                'input',
                function () {
                    dirty = true;

                    updateSeo();
                }
            );
        }
    );

    isFreeInput?.addEventListener(
        'change',
        function () {
            dirty = true;

            updateFeeSection();
        }
    );

    channelInput?.addEventListener(
        'change',
        function () {
            dirty = true;

            updateChannelFields();
        }
    );

    [
        statusInput,
        publishedInput
    ].forEach(
        function (input) {
            input?.addEventListener(
                'change',
                function () {
                    dirty = true;

                    updatePublicationHint();
                }
            );
        }
    );

    function openModal() {
        modal.hidden = false;

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow =
            'hidden';
    }

    function closeModal() {
        modal.hidden = true;

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.style.overflow =
            '';
    }

    document
        .querySelectorAll(
            '[data-service-media-close]'
        )
        .forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    closeModal
                );
            }
        );

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape'
                && !modal.hidden
            ) {
                closeModal();
            }
        }
    );

    /*
     * =====================================================
     * GLOBAL MEDIA PICKER INTEGRATION: SERVICE
     * =====================================================
     */

    const globalServiceCoverPicker =
        document.getElementById(
            'service-cover-picker'
        );

    globalServiceCoverPicker?.addEventListener(
        'click',
        async function (event) {
            if (! window.AdminMediaPicker) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            const result =
                await window.AdminMediaPicker.open({
                    title:
                        'Pilih Cover Layanan',

                    description:
                        'Pilih atau upload gambar cover layanan.',

                    type:
                        'image',

                    multiple:
                        false,

                    unitId:
                        unitInput?.value
                        || '',
                });

            const media =
                result[0];

            if (! media) {
                return;
            }

            mediaGrid.innerHTML = '';

            renderMedia(media);

            mediaGrid
                .lastElementChild
                ?.click();

            mediaGrid.innerHTML = '';
        },
        true
    );

    document
        .getElementById(
            'service-cover-picker'
        )
        ?.addEventListener(
            'click',
            function () {
                currentPage = 1;

                mediaGrid.innerHTML = '';

                if (
                    mediaUnit.value === ''
                    && unitInput?.value
                ) {
                    mediaUnit.value =
                        unitInput.value;
                }

                openModal();

                loadMedia(true);
            }
        );

    async function loadMedia(reset = false) {
        if (
            loading
            || !mediaPickerUrl
        ) {
            return;
        }

        if (reset) {
            currentPage = 1;

            mediaGrid.innerHTML = '';
        }

        loading = true;

        mediaLoading.hidden = false;
        mediaEmpty.hidden = true;
        loadMore.hidden = true;

        try {
            const url =
                new URL(
                    mediaPickerUrl,
                    window.location.origin
                );

            url.searchParams.set(
                'page',
                currentPage
            );

            if (
                mediaSearch.value.trim()
            ) {
                url.searchParams.set(
                    'search',
                    mediaSearch.value.trim()
                );
            }

            if (mediaUnit.value) {
                url.searchParams.set(
                    'unit_id',
                    mediaUnit.value
                );
            }

            const response =
                await fetch(
                    url.toString(),
                    {
                        headers: {
                            Accept:
                                'application/json'
                        }
                    }
                );

            if (!response.ok) {
                throw new Error(
                    'Media gagal dimuat.'
                );
            }

            const payload =
                await response.json();

            payload.data.forEach(
                renderMedia
            );

            hasMore =
                payload.meta.has_more;

            loadMore.hidden =
                !hasMore;

            mediaEmpty.hidden =
                mediaGrid.children.length > 0;
        } catch (error) {
            mediaEmpty.textContent =
                'Media gagal dimuat.';

            mediaEmpty.hidden = false;
        } finally {
            mediaLoading.hidden = true;

            loading = false;
        }
    }

    function renderMedia(media) {
        const button =
            document.createElement(
                'button'
            );

        button.type =
            'button';

        button.className =
            'service-media-item';

        const image =
            document.createElement(
                'img'
            );

        image.src =
            media.url;

        image.alt =
            media.alt_text || '';

        image.loading =
            'lazy';

        const body =
            document.createElement(
                'div'
            );

        body.className =
            'service-media-item-body';

        const title =
            document.createElement(
                'strong'
            );

        title.textContent =
            media.title;

        const unit =
            document.createElement(
                'span'
            );

        unit.textContent =
            media.unit_name;

        body.appendChild(title);
        body.appendChild(unit);

        button.appendChild(image);
        button.appendChild(body);

        button.addEventListener(
            'click',
            function () {
                setCover(media);

                closeModal();
            }
        );

        mediaGrid.appendChild(
            button
        );
    }

    function setCover(media) {
        coverInput.value =
            media.id;

        const image =
            document.getElementById(
                'service-cover-image'
            );

        image.src =
            media.url;

        image.alt =
            media.alt_text || '';

        document.getElementById(
            'service-cover-title'
        ).textContent =
            media.title;

        document.getElementById(
            'service-cover-unit'
        ).textContent =
            media.unit_name;

        document.getElementById(
            'service-cover-empty'
        ).hidden = true;

        document.getElementById(
            'service-cover-preview'
        ).hidden = false;

        dirty = true;
    }

    async function restoreCover() {
        const id =
            root.dataset.selectedCoverId;

        if (!id) {
            return;
        }

        try {
            const url =
                new URL(
                    mediaPickerUrl,
                    window.location.origin
                );

            url.searchParams.set(
                'id',
                id
            );

            const response =
                await fetch(
                    url.toString(),
                    {
                        headers: {
                            Accept:
                                'application/json'
                        }
                    }
                );

            const payload =
                await response.json();

            if (
                payload.data
                && payload.data[0]
            ) {
                setCover(
                    payload.data[0]
                );

                dirty = false;
            }
        } catch (error) {
            console.warn(
                'Preview cover gagal dimuat.'
            );
        }
    }

    mediaSearch?.addEventListener(
        'input',
        function () {
            clearTimeout(
                searchTimer
            );

            searchTimer =
                setTimeout(
                    function () {
                        loadMedia(true);
                    },
                    350
                );
        }
    );

    mediaUnit?.addEventListener(
        'change',
        function () {
            loadMedia(true);
        }
    );

    loadMore?.addEventListener(
        'click',
        function () {
            if (!hasMore) {
                return;
            }

            currentPage++;

            loadMedia(false);
        }
    );

    form?.addEventListener(
        'submit',
        function () {
            dirty = false;
        }
    );

    window.addEventListener(
        'beforeunload',
        function (event) {
            if (!dirty) {
                return;
            }

            event.preventDefault();

            event.returnValue = '';
        }
    );

    updateSeo();
    updateFeeSection();
    updateChannelFields();
    updatePublicationHint();
    restoreCover();
});