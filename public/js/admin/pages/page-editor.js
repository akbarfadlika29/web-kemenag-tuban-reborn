document.addEventListener('DOMContentLoaded', function () {
    const root =
        document.querySelector('[data-page-editor]');

    if (!root) {
        return;
    }

    const form = root.closest('form');

    const contentInput =
        document.getElementById('content');

    const contentEditorRoot =
        root.querySelector(
            '[data-content-editor]'
        );

    const contentEditor =
        window.AdminContentEditor
            ?.get(contentEditorRoot);

    if (!contentEditor) {
        console.error(
            'Global Content Editor Page gagal diinisialisasi.'
        );

        return;
    }

    const titleInput =
        document.getElementById('title');

    const slugInput =
        document.getElementById('slug');

    const excerptInput =
        document.getElementById('excerpt');

    const metaTitleInput =
        document.getElementById('meta_title');

    const metaDescriptionInput =
        document.getElementById('meta_description');

    const statusInput =
        document.getElementById('status');

    const publishedAtInput =
        document.getElementById('published_at');

    const publicationHint =
        document.getElementById('page-publication-hint');

    const unitInput =
        document.getElementById('unit_id');

    const coverInput =
        document.getElementById('cover_media_id');

    const coverEmpty =
        document.getElementById('page-cover-empty');

    const coverPreview =
        document.getElementById('page-cover-preview');

    const coverImage =
        document.getElementById('page-cover-image');

    const coverTitle =
        document.getElementById('page-cover-title');

    const coverUnit =
        document.getElementById('page-cover-unit');

    const coverButton =
        document.getElementById('page-cover-picker-button');

    const modal =
        document.getElementById('page-media-picker');

    const modalTitle =
        document.getElementById('page-media-modal-title');

    const mediaGrid =
        document.getElementById('page-media-grid');

    const mediaSearch =
        document.getElementById('page-media-search');

    const mediaUnit =
        document.getElementById('page-media-unit');

    const mediaLoading =
        document.getElementById('page-media-loading');

    const mediaEmpty =
        document.getElementById('page-media-empty');

    const mediaLoadMore =
        document.getElementById('page-media-load-more');

    const mediaPickerUrl =
        root.dataset.mediaPickerUrl;

    let pickerMode = 'cover';
    let currentPage = 1;
    let hasMore = false;
    let loading = false;
    let searchTimer = null;
    let dirty = false;

    contentEditorRoot.addEventListener(
        'content-editor:request-image',
        function () {
            openMediaPicker(
                'content'
            );
        }
    );
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

    function updateStats() {
        const text =
            contentEditor.getText()
                .replace(/\s+/g, ' ')
                .trim();

        const words =
            text === ''
                ? []
                : text.split(/\s+/u);

        document.getElementById(
            'page-word-count'
        ).textContent =
            words.length;

        document.getElementById(
            'page-character-count'
        ).textContent =
            text.length;

        document.getElementById(
            'page-reading-time'
        ).textContent =
            Math.max(
                1,
                Math.ceil(words.length / 200)
            );
    }

    function updateSeo() {
        const title =
            (
                metaTitleInput?.value
                || titleInput?.value
                || 'Judul halaman'
            ).trim();

        const slug =
            slugInput?.value
            || 'slug-halaman';

        const content =
            contentEditor.getText()
                .replace(/\s+/g, ' ')
                .trim();

        const description =
            (
                metaDescriptionInput?.value
                || excerptInput?.value
                || content
                || 'Ringkasan halaman akan tampil di sini.'
            ).trim();

        document.getElementById(
            'page-seo-title'
        ).textContent = title;

        document.getElementById(
            'page-seo-slug'
        ).textContent = slug;

        document.getElementById(
            'page-seo-description'
        ).textContent =
            description.substring(0, 180);

        document.getElementById(
            'page-meta-title-count'
        ).textContent =
            metaTitleInput?.value.length || 0;

        document.getElementById(
            'page-meta-description-count'
        ).textContent =
            metaDescriptionInput?.value.length || 0;
    }

    function updatePublicationHint() {
        publicationHint.classList.remove(
            'is-visible'
        );

        publicationHint.textContent = '';

        if (
            statusInput?.value !== 'published'
        ) {
            return;
        }

        if (!publishedAtInput?.value) {
            publicationHint.textContent =
                'Halaman akan dipublikasikan saat disimpan.';

            publicationHint.classList.add(
                'is-visible'
            );

            return;
        }

        const date =
            new Date(publishedAtInput.value);

        if (
            !Number.isNaN(date.getTime())
            && date > new Date()
        ) {
            publicationHint.textContent =
                'Halaman dijadwalkan dan belum tampil ke publik.';

            publicationHint.classList.add(
                'is-visible'
            );
        }
    }

    titleInput?.addEventListener(
        'input',
        function () {
            slugInput.value =
                generateSlug(titleInput.value);

            dirty = true;

            updateSeo();
        }
    );

    contentEditorRoot.addEventListener(
        'content-editor:change',
        function () {
            dirty = true;

            updateStats();
            updateSeo();
        }
    );

    document
        .getElementById('page-generate-excerpt')
        ?.addEventListener(
            'click',
            function () {
                const text =
                    contentEditor.getText()
                        .replace(/\s+/g, ' ')
                        .trim();

                excerptInput.value =
                    text.length > 300
                        ? text.substring(0, 300).trim() + '…'
                        : text;

                dirty = true;
                updateSeo();
            }
        );

    [
        excerptInput,
        metaTitleInput,
        metaDescriptionInput
    ].forEach(function (element) {
        element?.addEventListener(
            'input',
            function () {
                dirty = true;
                updateSeo();
            }
        );
    });

    statusInput?.addEventListener(
        'change',
        updatePublicationHint
    );

    publishedAtInput?.addEventListener(
        'change',
        updatePublicationHint
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
            '[data-page-media-close]'
        )
        .forEach(function (element) {
            element.addEventListener(
                'click',
                closeModal
            );
        });

    async function openMediaPicker(mode) {
        pickerMode = mode;

        if (! window.AdminMediaPicker) {
            currentPage = 1;
            mediaGrid.innerHTML = '';

            modalTitle.textContent =
                mode === 'cover'
                    ? 'Pilih Gambar Utama'
                    : 'Sisipkan Gambar';

            if (
                mediaUnit.value === ''
                && unitInput?.value
            ) {
                mediaUnit.value =
                    unitInput.value;
            }

            openModal();
            loadMedia(true);

            return;
        }

        const result =
            await window.AdminMediaPicker.open({
                title:
                    mode === 'cover'
                        ? 'Pilih Gambar Utama'
                        : 'Sisipkan Gambar ke Halaman',

                description:
                    mode === 'cover'
                        ? 'Pilih atau upload cover halaman.'
                        : 'Pilih atau upload gambar untuk konten halaman.',

                type: 'image',
                multiple: false,

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

        const item =
            mediaGrid.lastElementChild;

        item?.click();

        mediaGrid.innerHTML = '';
    }

    coverButton?.addEventListener(
        'click',
        function () {
            openMediaPicker('cover');
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
        mediaLoadMore.hidden = true;

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

            if (mediaSearch.value.trim()) {
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

            const payload =
                await response.json();

            payload.data.forEach(
                renderMedia
            );

            hasMore =
                payload.meta.has_more;

            mediaLoadMore.hidden =
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

        button.type = 'button';
        button.className =
            'page-media-item';

        button.innerHTML = `
            <img
                src="${media.url}"
                alt=""
                loading="lazy"
            >

            <div class="page-media-item-body">
                <div class="page-media-item-title"></div>
                <div class="page-media-item-unit"></div>
            </div>
        `;

        button.querySelector(
            '.page-media-item-title'
        ).textContent =
            media.title;

        button.querySelector(
            '.page-media-item-unit'
        ).textContent =
            media.unit_name;

        button.addEventListener(
            'click',
            function () {
                if (
                    pickerMode === 'cover'
                ) {
                    setCover(media);
                } else {
                    insertImage(media);
                }

                closeModal();
            }
        );

        mediaGrid.appendChild(button);
    }

    function setCover(media) {
        coverInput.value =
            media.id;

        coverImage.src =
            media.url;

        coverImage.alt =
            media.alt_text || '';

        coverTitle.textContent =
            media.title;

        coverUnit.textContent =
            media.unit_name;

        coverEmpty.hidden = true;
        coverPreview.hidden = false;

        dirty = true;
    }

    function insertImage(media) {
        contentEditor.insertImage(
            media
        );
    }
    mediaSearch?.addEventListener(
        'input',
        function () {
            clearTimeout(searchTimer);

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

    mediaLoadMore?.addEventListener(
        'click',
        function () {
            if (!hasMore) {
                return;
            }

            currentPage++;

            loadMedia(false);
        }
    );

    async function loadSelectedCover(id) {
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

            if (payload.data?.[0]) {
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

    if (root.dataset.selectedCoverId) {
        loadSelectedCover(
            root.dataset.selectedCoverId
        );
    }

    form?.addEventListener(
        'submit',
        function () {
            contentEditor.sync();

            dirty = false;
        }
    );

    form
        ?.querySelectorAll(
            'input, select, textarea'
        )
        .forEach(function (element) {
            element.addEventListener(
                'change',
                function () {
                    dirty = true;
                }
            );
        });

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

    updateStats();
    updateSeo();
    updatePublicationHint();
});