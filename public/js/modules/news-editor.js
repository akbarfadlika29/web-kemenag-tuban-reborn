document.addEventListener('DOMContentLoaded', function () {
    const root =
        document.querySelector('[data-news-editor]');

    if (
        !root
        || typeof Quill === 'undefined'
    ) {
        return;
    }

    const form =
        root.closest('form');

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
            'Global Content Editor News gagal diinisialisasi.'
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
        document.getElementById(
            'meta_description'
        );

    const statusInput =
        document.getElementById('status');

    const publishedAtInput =
        document.getElementById(
            'published_at'
        );

    const publicationHint =
        document.getElementById(
            'news-publication-hint'
        );

    const unitInput =
        document.getElementById('unit_id');

    /*
     * Tag
     */
    const tagSelect =
        document.getElementById('tag_ids');

    const tagCount =
        document.getElementById(
            'news-tag-count'
        );

    const tagChips =
        root.querySelectorAll(
            '[data-news-tag]'
        );

    /*
     * Cover
     */
    const coverInput =
        document.getElementById(
            'cover_media_id'
        );

    const coverEmpty =
        document.getElementById(
            'news-cover-empty'
        );

    const coverPreview =
        document.getElementById(
            'news-cover-preview'
        );

    const coverImage =
        document.getElementById(
            'news-cover-image'
        );

    const coverTitle =
        document.getElementById(
            'news-cover-title'
        );

    const coverUnit =
        document.getElementById(
            'news-cover-unit'
        );

    const coverPickerButton =
        document.getElementById(
            'news-cover-picker-button'
        );

    /*
     * Media Picker
     */
    const modal =
        document.getElementById(
            'news-media-picker'
        );

    const modalTitle =
        document.getElementById(
            'news-media-modal-title'
        );

    const mediaGrid =
        document.getElementById(
            'news-media-grid'
        );

    const mediaSearch =
        document.getElementById(
            'news-media-search'
        );

    const mediaUnit =
        document.getElementById(
            'news-media-unit'
        );

    const mediaLoading =
        document.getElementById(
            'news-media-loading'
        );

    const mediaEmpty =
        document.getElementById(
            'news-media-empty'
        );

    const mediaLoadMore =
        document.getElementById(
            'news-media-load-more'
        );

    const mediaPickerUrl =
        root.dataset.mediaPickerUrl;

    /*
     * State
     */
    let pickerMode = 'cover';
    let currentPage = 1;
    let hasMore = false;
    let isLoading = false;
    let searchTimer = null;
    let dirty = false;
    let previousFocusedElement = null;

    /*
     * =====================================================
     * QUILL IMAGE FORMAT
     * =====================================================
     */

    contentEditorRoot.addEventListener(
        'content-editor:request-image',
        function () {
            openMediaPicker(
                'content'
            );
        }
    );
    function updateEditorStats() {
        const text =
            contentEditor.getText()
                .replace(
                    /\s+/g,
                    ' '
                )
                .trim();

        const words =
            text === ''
                ? []
                : text.split(/\s+/u);

        const wordCount =
            words.length;

        const characterCount =
            text.length;

        const readingTime =
            Math.max(
                1,
                Math.ceil(
                    wordCount / 200
                )
            );

        const wordCounter =
            document.getElementById(
                'news-word-count'
            );

        const characterCounter =
            document.getElementById(
                'news-character-count'
            );

        const readingCounter =
            document.getElementById(
                'news-reading-time'
            );

        if (wordCounter) {
            wordCounter.textContent =
                wordCount;
        }

        if (characterCounter) {
            characterCounter.textContent =
                characterCount;
        }

        if (readingCounter) {
            readingCounter.textContent =
                readingTime;
        }
    }

    /*
     * =====================================================
     * SLUG
     * =====================================================
     */

    function generateSlug(value) {
        return value
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .toLowerCase()
            .trim()
            .replace(
                /[^a-z0-9\s-]/g,
                ''
            )
            .replace(
                /\s+/g,
                '-'
            )
            .replace(
                /-+/g,
                '-'
            )
            .replace(
                /^-+|-+$/g,
                ''
            );
    }

    titleInput?.addEventListener(
        'input',
        function () {
            if (slugInput) {
                slugInput.value =
                    generateSlug(
                        titleInput.value
                    );
            }

            dirty = true;

            updateSeoPreview();
        }
    );

    /*
     * =====================================================
     * SEO PREVIEW
     * =====================================================
     */

    function updateSeoPreview() {
        const title =
            (
                metaTitleInput?.value
                || titleInput?.value
                || 'Judul berita'
            ).trim();

        const slug =
            (
                slugInput?.value
                || 'slug-berita'
            ).trim();

        const contentText =
            contentEditor.getText()
                .replace(
                    /\s+/g,
                    ' '
                )
                .trim();

        const description =
            (
                metaDescriptionInput?.value
                || excerptInput?.value
                || contentText
                || 'Ringkasan berita akan tampil di sini.'
            ).trim();

        const seoTitle =
            document.getElementById(
                'news-seo-title'
            );

        const seoSlug =
            document.getElementById(
                'news-seo-slug'
            );

        const seoDescription =
            document.getElementById(
                'news-seo-description'
            );

        const metaTitleCount =
            document.getElementById(
                'news-meta-title-count'
            );

        const metaDescriptionCount =
            document.getElementById(
                'news-meta-description-count'
            );

        if (seoTitle) {
            seoTitle.textContent =
                title;
        }

        if (seoSlug) {
            seoSlug.textContent =
                slug;
        }

        if (seoDescription) {
            seoDescription.textContent =
                description.substring(
                    0,
                    180
                );
        }

        if (metaTitleCount) {
            metaTitleCount.textContent =
                metaTitleInput
                    ?.value.length
                || 0;
        }

        if (metaDescriptionCount) {
            metaDescriptionCount.textContent =
                metaDescriptionInput
                    ?.value.length
                || 0;
        }
    }

    /*
     * =====================================================
     * AUTO EXCERPT
     * =====================================================
     */

    document
        .getElementById(
            'news-generate-excerpt'
        )
        ?.addEventListener(
            'click',
            function () {
                if (!excerptInput) {
                    return;
                }

                const text =
                    contentEditor.getText()
                        .replace(
                            /\s+/g,
                            ' '
                        )
                        .trim();

                excerptInput.value =
                    text.length > 300
                        ? (
                            text
                                .substring(
                                    0,
                                    300
                                )
                                .trim()
                            + '…'
                        )
                        : text;

                dirty = true;

                updateSeoPreview();
            }
        );

    [
        metaTitleInput,
        metaDescriptionInput,
        excerptInput
    ].forEach(
        function (element) {
            element?.addEventListener(
                'input',
                function () {
                    dirty = true;

                    updateSeoPreview();
                }
            );
        }
    );

    /*
     * =====================================================
     * TAG CHIP SELECTOR
     * =====================================================
     */

    function updateTagCount() {
        if (
            !tagSelect
            || !tagCount
        ) {
            return;
        }

        const selected =
            Array.from(
                tagSelect.options
            ).filter(
                function (option) {
                    return option.selected;
                }
            );

        tagCount.textContent =
            selected.length
            + ' dipilih';
    }

    function syncTagChips() {
        if (!tagSelect) {
            return;
        }

        tagChips.forEach(
            function (chip) {
                const tagId =
                    chip.dataset.newsTag;

                const option =
                    Array.from(
                        tagSelect.options
                    ).find(
                        function (item) {
                            return (
                                item.value
                                === tagId
                            );
                        }
                    );

                const selected =
                    Boolean(
                        option?.selected
                    );

                chip.classList.toggle(
                    'is-selected',
                    selected
                );

                chip.setAttribute(
                    'aria-pressed',
                    selected
                        ? 'true'
                        : 'false'
                );
            }
        );

        updateTagCount();
    }

    tagChips.forEach(
        function (chip) {
            chip.addEventListener(
                'click',
                function () {
                    if (!tagSelect) {
                        return;
                    }

                    const tagId =
                        chip.dataset.newsTag;

                    const option =
                        Array.from(
                            tagSelect.options
                        ).find(
                            function (item) {
                                return (
                                    item.value
                                    === tagId
                                );
                            }
                        );

                    if (!option) {
                        return;
                    }

                    option.selected =
                        !option.selected;

                    syncTagChips();

                    dirty = true;
                }
            );
        }
    );

    syncTagChips();

    /*
     * =====================================================
     * PUBLICATION
     * =====================================================
     */

    function updatePublicationHint() {
        if (
            !statusInput
            || !publishedAtInput
            || !publicationHint
        ) {
            return;
        }

        publicationHint.classList.remove(
            'is-visible'
        );

        publicationHint.textContent =
            '';

        if (
            statusInput.value
            !== 'published'
        ) {
            return;
        }

        if (
            publishedAtInput.value === ''
        ) {
            publicationHint.textContent =
                'Berita akan dipublikasikan saat disimpan.';

            publicationHint.classList.add(
                'is-visible'
            );

            return;
        }

        const date =
            new Date(
                publishedAtInput.value
            );

        if (
            !Number.isNaN(
                date.getTime()
            )
            && date > new Date()
        ) {
            publicationHint.textContent =
                'Berita terjadwal dan belum tampil ke publik sampai waktu tersebut.';

            publicationHint.classList.add(
                'is-visible'
            );
        }
    }

    statusInput?.addEventListener(
        'change',
        function () {
            dirty = true;

            updatePublicationHint();
        }
    );

    publishedAtInput?.addEventListener(
        'change',
        function () {
            dirty = true;

            updatePublicationHint();
        }
    );

    /*
     * =====================================================
     * EDITOR CHANGE
     * =====================================================
     */

    contentEditorRoot.addEventListener(
        'content-editor:change',
        function () {
            dirty = true;

            updateEditorStats();
            updateSeoPreview();
        }
    );
    /*
     * =====================================================
     * MEDIA MODAL
     * =====================================================
     */

    function openModal() {
        if (!modal) {
            return;
        }

        previousFocusedElement =
            document.activeElement;

        modal.hidden =
            false;

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow =
            'hidden';

        window.setTimeout(
            function () {
                mediaSearch?.focus();
            },
            0
        );
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.hidden =
            true;

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.style.overflow =
            '';

        previousFocusedElement
            ?.focus?.();
    }

    document
        .querySelectorAll(
            '[data-news-media-close]'
        )
        .forEach(
            function (element) {
                element.addEventListener(
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
                && modal
                && !modal.hidden
            ) {
                closeModal();
            }
        }
    );

    /*
     * =====================================================
     * OPEN MEDIA PICKER
     * =====================================================
     */

    async function openMediaPicker(mode) {
        pickerMode = mode;

        if (! window.AdminMediaPicker) {
            currentPage = 1;

            if (mediaGrid) {
                mediaGrid.innerHTML = '';
            }

            if (modalTitle) {
                modalTitle.textContent =
                    mode === 'cover'
                        ? 'Pilih Gambar Utama'
                        : 'Sisipkan Gambar ke Artikel';
            }

            if (
                mediaUnit
                && mediaUnit.value === ''
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
                        : 'Sisipkan Gambar ke Artikel',

                description:
                    mode === 'cover'
                        ? 'Pilih atau upload gambar utama berita.'
                        : 'Pilih atau upload gambar untuk disisipkan ke artikel.',

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

        /*
         * Gunakan handler media existing agar
         * preview cover dan Quill tetap konsisten.
         */
        mediaGrid.innerHTML = '';

        renderMedia(media);

        const item =
            mediaGrid.lastElementChild;

        item?.click();

        mediaGrid.innerHTML = '';
    }

    coverPickerButton?.addEventListener(
        'click',
        function () {
            openMediaPicker(
                'cover'
            );
        }
    );

    /*
     * =====================================================
     * LOAD MEDIA
     * =====================================================
     */

    async function loadMedia(
        reset = false
    ) {
        if (
            isLoading
            || !mediaPickerUrl
            || !mediaGrid
        ) {
            return;
        }

        if (reset) {
            currentPage =
                1;

            mediaGrid.innerHTML =
                '';
        }

        isLoading =
            true;

        if (mediaLoading) {
            mediaLoading.hidden =
                false;
        }

        if (mediaEmpty) {
            mediaEmpty.hidden =
                true;
        }

        if (mediaLoadMore) {
            mediaLoadMore.hidden =
                true;
        }

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

            const search =
                mediaSearch
                    ?.value
                    .trim()
                || '';

            const unit =
                mediaUnit?.value
                || '';

            if (search !== '') {
                url.searchParams.set(
                    'search',
                    search
                );
            }

            if (unit !== '') {
                url.searchParams.set(
                    'unit_id',
                    unit
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
                    'Gagal memuat media.'
                );
            }

            const payload =
                await response.json();

            payload.data.forEach(
                renderMediaItem
            );

            hasMore =
                payload.meta.has_more;

            if (
                mediaGrid.children.length
                === 0
                && mediaEmpty
            ) {
                mediaEmpty.textContent =
                    'Tidak ada gambar ditemukan.';

                mediaEmpty.hidden =
                    false;
            }

            if (mediaLoadMore) {
                mediaLoadMore.hidden =
                    !hasMore;
            }
        } catch (error) {
            if (mediaEmpty) {
                mediaEmpty.textContent =
                    'Media gagal dimuat. Silakan coba kembali.';

                mediaEmpty.hidden =
                    false;
            }

            console.error(
                error
            );
        } finally {
            isLoading =
                false;

            if (mediaLoading) {
                mediaLoading.hidden =
                    true;
            }
        }
    }

    /*
     * =====================================================
     * RENDER MEDIA
     * =====================================================
     */

    function renderMediaItem(media) {
        if (!mediaGrid) {
            return;
        }

        const button =
            document.createElement(
                'button'
            );

        button.type =
            'button';

        button.className =
            'news-media-item';

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

        const content =
            document.createElement(
                'div'
            );

        content.className =
            'news-media-item-content';

        const title =
            document.createElement(
                'div'
            );

        title.className =
            'news-media-item-title';

        title.textContent =
            media.title;

        const unit =
            document.createElement(
                'div'
            );

        unit.className =
            'news-media-item-unit';

        unit.textContent =
            media.unit_name;

        content.appendChild(
            title
        );

        content.appendChild(
            unit
        );

        button.appendChild(
            image
        );

        button.appendChild(
            content
        );

        button.addEventListener(
            'click',
            function () {
                selectMedia(
                    media
                );
            }
        );

        mediaGrid.appendChild(
            button
        );
    }

    /*
     * =====================================================
     * SELECT MEDIA
     * =====================================================
     */

    function selectMedia(media) {
        if (
            pickerMode === 'cover'
        ) {
            setCover(media);
        } else {
            insertMediaIntoEditor(
                media
            );
        }

        closeModal();
    }

    /*
     * =====================================================
     * COVER
     * =====================================================
     */

    function setCover(media) {
        if (
            !coverInput
            || !coverImage
            || !coverPreview
            || !coverEmpty
        ) {
            return;
        }

        coverInput.value =
            media.id;

        coverImage.src =
            media.url;

        coverImage.alt =
            media.alt_text || '';

        if (coverTitle) {
            coverTitle.textContent =
                media.title;
        }

        if (coverUnit) {
            coverUnit.textContent =
                media.unit_name;
        }

        coverEmpty.hidden =
            true;

        coverPreview.hidden =
            false;

        dirty =
            true;
    }

    /*
     * =====================================================
     * INSERT IMAGE INTO ARTICLE
     * =====================================================
     */

    function insertMediaIntoEditor(
        media
    ) {
        contentEditor.insertImage(
            media
        );
    }
    /*
     * =====================================================
     * MEDIA FILTER
     * =====================================================
     */

    mediaSearch?.addEventListener(
        'input',
        function () {
            clearTimeout(
                searchTimer
            );

            searchTimer =
                setTimeout(
                    function () {
                        loadMedia(
                            true
                        );
                    },
                    350
                );
        }
    );

    mediaUnit?.addEventListener(
        'change',
        function () {
            loadMedia(
                true
            );
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

    /*
     * =====================================================
     * RESTORE COVER SAAT EDIT
     * =====================================================
     */

    async function loadSelectedCover(
        id
    ) {
        if (!mediaPickerUrl) {
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

            if (!response.ok) {
                return;
            }

            const payload =
                await response.json();

            if (
                payload.data
                && payload.data[0]
            ) {
                setCover(
                    payload.data[0]
                );

                /*
                 * Loading data existing
                 * bukan perubahan user.
                 */
                dirty =
                    false;
            }
        } catch (error) {
            console.warn(
                'Preview cover gagal dimuat.',
                error
            );
        }
    }

    const selectedCoverId =
        root.dataset.selectedCoverId;

    if (selectedCoverId) {
        loadSelectedCover(
            selectedCoverId
        );
    }

    /*
     * =====================================================
     * SUBMIT
     * =====================================================
     */

    form?.addEventListener(
        'submit',
        function () {
            contentEditor.sync();

            dirty = false;
        }
    );
    /*
     * Deteksi perubahan field lain.
     */
    form
        ?.querySelectorAll(
            'input, select, textarea'
        )
        .forEach(
            function (element) {
                element.addEventListener(
                    'change',
                    function () {
                        dirty =
                            true;
                    }
                );
            }
        );

    /*
     * =====================================================
     * UNSAVED CHANGE WARNING
     * =====================================================
     */

    window.addEventListener(
        'beforeunload',
        function (event) {
            if (!dirty) {
                return;
            }

            event.preventDefault();

            event.returnValue =
                '';
        }
    );

    /*
     * =====================================================
     * INITIAL STATE
     * =====================================================
     */

    updateEditorStats();
    updateSeoPreview();
    updatePublicationHint();
    syncTagChips();
});