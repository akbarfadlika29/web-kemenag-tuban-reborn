document.addEventListener('DOMContentLoaded', function () {
    const root =
        document.querySelector('[data-gallery-editor]');

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

    const statusInput =
        document.getElementById('status');

    const publishedInput =
        document.getElementById('published_at');

    const unitInput =
        document.getElementById('unit_id');

    const publicationHint =
        document.getElementById('gallery-publication-hint');

    const itemsContainer =
        document.getElementById('gallery-items');

    const itemsEmpty =
        document.getElementById('gallery-items-empty');

    const coverInput =
        document.getElementById('cover_media_id');

    const modal =
        document.getElementById('gallery-media-picker');

    const modalTitle =
        document.getElementById('gallery-media-modal-title');

    const mediaGrid =
        document.getElementById('gallery-media-grid');

    const mediaSearch =
        document.getElementById('gallery-media-search');

    const mediaUnit =
        document.getElementById('gallery-media-unit');

    const mediaLoading =
        document.getElementById('gallery-media-loading');

    const mediaEmpty =
        document.getElementById('gallery-media-empty');

    const loadMore =
        document.getElementById('gallery-media-load-more');

    const selectedCount =
        document.getElementById('gallery-selected-count');

    const confirmSelection =
        document.getElementById('gallery-confirm-selection');

    const mediaPickerUrl =
        root.dataset.mediaPickerUrl;

    let pickerMode = 'items';
    let currentPage = 1;
    let hasMore = false;
    let loading = false;
    let searchTimer = null;
    let dirty = false;

    const selectedMedia =
        new Map();

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
                || 'Judul galeri'
            ).trim();

        const slug =
            slugInput?.value
            || 'slug-galeri';

        const description =
            (
                metaDescriptionInput?.value
                || excerptInput?.value
                || descriptionInput?.value
                || 'Ringkasan galeri akan tampil di sini.'
            ).trim();

        document.getElementById(
            'gallery-seo-title'
        ).textContent =
            title;

        document.getElementById(
            'gallery-seo-slug'
        ).textContent =
            slug;

        document.getElementById(
            'gallery-seo-description'
        ).textContent =
            description.substring(
                0,
                180
            );

        document.getElementById(
            'gallery-meta-title-count'
        ).textContent =
            metaTitleInput?.value.length
            || 0;

        document.getElementById(
            'gallery-meta-description-count'
        ).textContent =
            metaDescriptionInput?.value.length
            || 0;
    }

    function updatePublicationHint() {
        if (!publicationHint) {
            return;
        }

        publicationHint.textContent = '';

        publicationHint.classList.remove(
            'is-visible'
        );

        if (
            statusInput?.value !==
            'published'
        ) {
            return;
        }

        if (!publishedInput?.value) {
            publicationHint.textContent =
                'Galeri akan dipublikasikan saat disimpan.';

            publicationHint.classList.add(
                'is-visible'
            );

            return;
        }

        const date =
            new Date(
                publishedInput.value
            );

        if (date > new Date()) {
            publicationHint.textContent =
                'Publikasi galeri terjadwal.';

            publicationHint.classList.add(
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

    function refreshItemIndexes() {
        const cards =
            itemsContainer.querySelectorAll(
                '[data-gallery-item]'
            );

        cards.forEach(
            function (card, index) {
                const mediaIdInput =
                    card.querySelector(
                        '[data-gallery-media-id]'
                    );

                const sortInput =
                    card.querySelector(
                        '[data-gallery-sort-order]'
                    );

                const captionInput =
                    card.querySelector(
                        '[data-gallery-caption]'
                    );

                const altInput =
                    card.querySelector(
                        '[data-gallery-alt-text]'
                    );

                mediaIdInput.name =
                    `items[${index}][media_id]`;

                sortInput.name =
                    `items[${index}][sort_order]`;

                sortInput.value =
                    index;

                captionInput.name =
                    `items[${index}][caption]`;

                altInput.name =
                    `items[${index}][alt_text]`;
            }
        );

        itemsEmpty.hidden =
            cards.length > 0;
    }

    if (
        typeof Sortable !==
        'undefined'
    ) {
        new Sortable(
            itemsContainer,
            {
                animation: 160,
                handle: '.gallery-item-drag',
                ghostClass:
                    'gallery-item-ghost',

                onEnd() {
                    dirty = true;

                    refreshItemIndexes();
                }
            }
        );
    }

    function createGalleryItem(media) {
        if (
            itemsContainer.querySelector(
                `[data-gallery-item][data-media-id="${media.id}"]`
            )
        ) {
            return;
        }

        const card =
            document.createElement(
                'article'
            );

        card.className =
            'gallery-item-card';

        card.dataset.galleryItem = '';
        card.dataset.mediaId =
            media.id;

        const drag =
            document.createElement(
                'div'
            );

        drag.className =
            'gallery-item-drag';

        drag.textContent =
            '⋮⋮';

        const imageWrap =
            document.createElement(
                'div'
            );

        imageWrap.className =
            'gallery-item-image-wrap';

        const image =
            document.createElement(
                'img'
            );

        image.className =
            'gallery-item-image';

        image.src =
            media.url;

        image.alt =
            media.alt_text || '';

        imageWrap.appendChild(
            image
        );

        const content =
            document.createElement(
                'div'
            );

        content.className =
            'gallery-item-content';

        const heading =
            document.createElement(
                'div'
            );

        heading.className =
            'gallery-item-heading';

        const titleWrap =
            document.createElement(
                'div'
            );

        const title =
            document.createElement(
                'strong'
            );

        title.className =
            'gallery-item-title';

        title.textContent =
            media.title;

        const mediaMeta =
            document.createElement(
                'span'
            );

        mediaMeta.textContent =
            `ID Media: ${media.id}`;

        titleWrap.appendChild(title);
        titleWrap.appendChild(
            mediaMeta
        );

        const remove =
            document.createElement(
                'button'
            );

        remove.type =
            'button';

        remove.className =
            'gallery-item-remove';

        remove.dataset.galleryItemRemove =
            '';

        remove.textContent =
            'Hapus';

        heading.appendChild(
            titleWrap
        );

        heading.appendChild(
            remove
        );

        const mediaIdInput =
            document.createElement(
                'input'
            );

        mediaIdInput.type =
            'hidden';

        mediaIdInput.value =
            media.id;

        mediaIdInput.dataset.galleryMediaId =
            '';

        const sortInput =
            document.createElement(
                'input'
            );

        sortInput.type =
            'hidden';

        sortInput.value =
            '0';

        sortInput.dataset.gallerySortOrder =
            '';

        const fields =
            document.createElement(
                'div'
            );

        fields.className =
            'gallery-item-fields';

        const captionGroup =
            document.createElement(
                'div'
            );

        const captionLabel =
            document.createElement(
                'label'
            );

        captionLabel.textContent =
            'Caption';

        const captionInput =
            document.createElement(
                'input'
            );

        captionInput.type =
            'text';

        captionInput.className =
            'ui-control';

        captionInput.maxLength =
            255;

        captionInput.placeholder =
            'Caption foto...';

        captionInput.dataset.galleryCaption =
            '';

        const altGroup =
            document.createElement(
                'div'
            );

        const altLabel =
            document.createElement(
                'label'
            );

        altLabel.textContent =
            'Alt Text';

        const altInput =
            document.createElement(
                'input'
            );

        altInput.type =
            'text';

        altInput.className =
            'ui-control';

        altInput.maxLength =
            255;

        altInput.placeholder =
            'Deskripsi gambar...';

        altInput.value =
            media.alt_text || '';

        altInput.dataset.galleryAltText =
            '';

        captionGroup.appendChild(
            captionLabel
        );

        captionGroup.appendChild(
            captionInput
        );

        altGroup.appendChild(
            altLabel
        );

        altGroup.appendChild(
            altInput
        );

        fields.appendChild(
            captionGroup
        );

        fields.appendChild(
            altGroup
        );

        content.appendChild(
            heading
        );

        content.appendChild(
            mediaIdInput
        );

        content.appendChild(
            sortInput
        );

        content.appendChild(
            fields
        );

        card.appendChild(
            drag
        );

        card.appendChild(
            imageWrap
        );

        card.appendChild(
            content
        );

        itemsContainer.appendChild(
            card
        );

        dirty = true;

        refreshItemIndexes();
    }

    itemsContainer.addEventListener(
        'click',
        function (event) {
            const removeButton =
                event.target.closest(
                    '[data-gallery-item-remove]'
                );

            if (!removeButton) {
                return;
            }

            const card =
                removeButton.closest(
                    '[data-gallery-item]'
                );

            card?.remove();

            dirty = true;

            refreshItemIndexes();
        }
    );

    itemsContainer.addEventListener(
        'input',
        function () {
            dirty = true;
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

        selectedMedia.clear();

        updateSelectedCount();
    }

    document
        .querySelectorAll(
            '[data-gallery-media-close]'
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

    async function openPicker(mode) {
        pickerMode = mode;

        if (! window.AdminMediaPicker) {
            selectedMedia.clear();
            updateSelectedCount();

            currentPage = 1;
            mediaGrid.innerHTML = '';

            modalTitle.textContent =
                mode === 'cover'
                    ? 'Pilih Cover Album'
                    : 'Pilih Foto Galeri';

            confirmSelection.hidden =
                mode === 'cover';

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

        const multiple =
            mode === 'items';

        const result =
            await window.AdminMediaPicker.open({
                title:
                    mode === 'cover'
                        ? 'Pilih Cover Album'
                        : 'Pilih Foto Galeri',

                description:
                    mode === 'cover'
                        ? 'Pilih atau upload satu gambar sebagai cover album.'
                        : 'Pilih beberapa gambar atau upload foto baru untuk galeri.',

                type: 'image',

                multiple,

                unitId:
                    unitInput?.value
                    || '',
            });

        if (! result.length) {
            return;
        }

        if (mode === 'cover') {
            mediaGrid.innerHTML = '';

            renderMedia(
                result[0]
            );

            mediaGrid
                .lastElementChild
                ?.click();

            mediaGrid.innerHTML = '';

            return;
        }

        selectedMedia.clear();

        result.forEach(
            function (media) {
                selectedMedia.set(
                    String(media.id),
                    media
                );
            }
        );

        updateSelectedCount();

        confirmSelection?.click();
    }

    document
        .getElementById(
            'gallery-add-media'
        )
        ?.addEventListener(
            'click',
            function () {
                openPicker(
                    'items'
                );
            }
        );

    document
        .getElementById(
            'gallery-cover-picker'
        )
        ?.addEventListener(
            'click',
            function () {
                openPicker(
                    'cover'
                );
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
            'gallery-media-item';

        button.dataset.mediaId =
            media.id;

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
            'gallery-media-item-body';

        const title =
            document.createElement(
                'div'
            );

        title.className =
            'gallery-media-item-title';

        title.textContent =
            media.title;

        const meta =
            document.createElement(
                'div'
            );

        meta.className =
            'gallery-media-item-meta';

        meta.textContent =
            media.unit_name;

        body.appendChild(title);
        body.appendChild(meta);

        button.appendChild(image);
        button.appendChild(body);

        button.addEventListener(
            'click',
            function () {
                if (
                    pickerMode ===
                    'cover'
                ) {
                    setCover(media);

                    closeModal();

                    return;
                }

                if (
                    selectedMedia.has(
                        media.id
                    )
                ) {
                    selectedMedia.delete(
                        media.id
                    );

                    button.classList.remove(
                        'is-selected'
                    );
                } else {
                    selectedMedia.set(
                        media.id,
                        media
                    );

                    button.classList.add(
                        'is-selected'
                    );
                }

                updateSelectedCount();
            }
        );

        mediaGrid.appendChild(
            button
        );
    }

    function updateSelectedCount() {
        selectedCount.textContent =
            selectedMedia.size;
    }

    confirmSelection?.addEventListener(
        'click',
        function () {
            selectedMedia.forEach(
                function (media) {
                    createGalleryItem(
                        media
                    );
                }
            );

            closeModal();
        }
    );

    function setCover(media) {
        coverInput.value =
            media.id;

        const image =
            document.getElementById(
                'gallery-cover-image'
            );

        image.src =
            media.url;

        image.alt =
            media.alt_text || '';

        document.getElementById(
            'gallery-cover-title'
        ).textContent =
            media.title;

        document.getElementById(
            'gallery-cover-unit'
        ).textContent =
            media.unit_name;

        document.getElementById(
            'gallery-cover-empty'
        ).hidden = true;

        document.getElementById(
            'gallery-cover-preview'
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
            refreshItemIndexes();

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

    refreshItemIndexes();
    restoreCover();
    updateSeo();
    updatePublicationHint();
});