document.addEventListener('DOMContentLoaded', function () {
    const root =
        document.querySelector('[data-ppid-editor]');

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

    const retentionPeriod =
        document.getElementById('retention_period');

    const retentionUnit =
        document.getElementById('retention_unit');

    const documentsContainer =
        document.getElementById('ppid-documents');

    const documentsEmpty =
        document.getElementById('ppid-documents-empty');

    const modal =
        document.getElementById('ppid-media-picker');

    const mediaGrid =
        document.getElementById('ppid-media-grid');

    const mediaSearch =
        document.getElementById('ppid-media-search');

    const mediaUnit =
        document.getElementById('ppid-media-unit');

    const mediaLoading =
        document.getElementById('ppid-media-loading');

    const mediaEmpty =
        document.getElementById('ppid-media-empty');

    const loadMore =
        document.getElementById('ppid-media-load-more');

    const selectedCount =
        document.getElementById('ppid-selected-count');

    const confirmDocuments =
        document.getElementById('ppid-confirm-documents');

    const mediaPickerUrl =
        root.dataset.mediaPickerUrl;

    let currentPage = 1;
    let hasMore = false;
    let loading = false;
    let dirty = false;
    let searchTimer = null;

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
                || 'Judul informasi'
            ).trim();

        const slug =
            slugInput?.value
            || 'slug-informasi';

        const description =
            (
                metaDescriptionInput?.value
                || excerptInput?.value
                || descriptionInput?.value
                || 'Ringkasan informasi akan tampil di sini.'
            ).trim();

        document.getElementById(
            'ppid-seo-title'
        ).textContent =
            title;

        document.getElementById(
            'ppid-seo-slug'
        ).textContent =
            slug;

        document.getElementById(
            'ppid-seo-description'
        ).textContent =
            description.substring(0, 180);
    }

    function updatePublicationHint() {
        const hint =
            document.getElementById(
                'ppid-publication-hint'
            );

        if (!hint) {
            return;
        }

        hint.classList.remove(
            'is-visible'
        );

        hint.textContent = '';

        if (
            statusInput?.value !==
            'published'
        ) {
            return;
        }

        if (!publishedInput?.value) {
            hint.textContent =
                'Informasi akan dipublikasikan saat disimpan.';

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
                'Publikasi informasi terjadwal.';

            hint.classList.add(
                'is-visible'
            );
        }
    }

    function updateRetention() {
        if (!retentionUnit) {
            return;
        }

        if (
            retentionUnit.value ===
            'permanent'
        ) {
            retentionPeriod.value = '';
            retentionPeriod.disabled = true;
        } else {
            retentionPeriod.disabled = false;
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

    retentionUnit?.addEventListener(
        'change',
        function () {
            dirty = true;
            updateRetention();
        }
    );

    function refreshDocumentIndexes() {
        const cards =
            documentsContainer.querySelectorAll(
                '[data-ppid-document]'
            );

        cards.forEach(
            function (card, index) {
                card.querySelector(
                    '[data-ppid-media-id]'
                ).name =
                    `documents[${index}][media_id]`;

                const sort =
                    card.querySelector(
                        '[data-ppid-sort-order]'
                    );

                sort.name =
                    `documents[${index}][sort_order]`;

                sort.value =
                    index;

                card.querySelector(
                    '[data-ppid-is-primary]'
                ).name =
                    `documents[${index}][is_primary]`;

                card.querySelector(
                    '[data-ppid-document-title]'
                ).name =
                    `documents[${index}][title]`;

                card.querySelector(
                    '[data-ppid-version]'
                ).name =
                    `documents[${index}][version]`;

                card.querySelector(
                    '[data-ppid-document-status]'
                ).name =
                    `documents[${index}][document_status]`;

                card.querySelector(
                    '[data-ppid-document-date]'
                ).name =
                    `documents[${index}][document_date]`;

                card.querySelector(
                    '[data-ppid-document-description]'
                ).name =
                    `documents[${index}][description]`;
            }
        );

        documentsEmpty.hidden =
            cards.length > 0;
    }

    function enforcePrimary(card) {
        documentsContainer
            .querySelectorAll(
                '[data-ppid-document]'
            )
            .forEach(
                function (item) {
                    const input =
                        item.querySelector(
                            '[data-ppid-is-primary]'
                        );

                    const button =
                        item.querySelector(
                            '[data-ppid-primary]'
                        );

                    const active =
                        item === card;

                    input.value =
                        active
                            ? '1'
                            : '0';

                    button.classList.toggle(
                        'is-primary',
                        active
                    );

                    button.textContent =
                        active
                            ? 'Dokumen Utama'
                            : 'Jadikan Utama';
                }
            );
    }

    function ensurePrimaryExists() {
        const cards =
            [
                ...documentsContainer.querySelectorAll(
                    '[data-ppid-document]'
                )
            ];

        if (cards.length === 0) {
            return;
        }

        const primary =
            cards.find(
                function (card) {
                    return card
                        .querySelector(
                            '[data-ppid-is-primary]'
                        )
                        .value === '1';
                }
            );

        if (!primary) {
            enforcePrimary(
                cards[0]
            );
        }
    }

    if (
        typeof Sortable !==
        'undefined'
    ) {
        new Sortable(
            documentsContainer,
            {
                animation: 160,
                handle:
                    '.ppid-document-drag',

                ghostClass:
                    'ppid-document-ghost',

                onEnd() {
                    dirty = true;

                    refreshDocumentIndexes();
                }
            }
        );
    }

    documentsContainer.addEventListener(
        'click',
        function (event) {
            const primaryButton =
                event.target.closest(
                    '[data-ppid-primary]'
                );

            if (primaryButton) {
                const card =
                    primaryButton.closest(
                        '[data-ppid-document]'
                    );

                enforcePrimary(card);

                dirty = true;

                return;
            }

            const removeButton =
                event.target.closest(
                    '[data-ppid-document-remove]'
                );

            if (!removeButton) {
                return;
            }

            const card =
                removeButton.closest(
                    '[data-ppid-document]'
                );

            const wasPrimary =
                card
                    .querySelector(
                        '[data-ppid-is-primary]'
                    )
                    .value === '1';

            card.remove();

            refreshDocumentIndexes();

            if (wasPrimary) {
                ensurePrimaryExists();
            }

            dirty = true;
        }
    );

    documentsContainer.addEventListener(
        'input',
        function () {
            dirty = true;
        }
    );

    function createDocument(media) {
        if (
            documentsContainer.querySelector(
                `[data-ppid-document][data-media-id="${media.id}"]`
            )
        ) {
            return;
        }

        const card =
            document.createElement(
                'article'
            );

        card.className =
            'ppid-document-card';

        card.dataset.ppidDocument = '';
        card.dataset.mediaId =
            media.id;

        card.innerHTML = `
            <div class="ppid-document-drag">⋮⋮</div>

            <div class="ppid-document-icon">
                ${(media.extension || 'FILE').toUpperCase()}
            </div>

            <div class="ppid-document-content">
                <div class="ppid-document-heading">
                    <div>
                        <strong></strong>
                        <span>ID Media: ${media.id}</span>
                    </div>

                    <div class="ppid-document-heading-actions">
                        <button
                            type="button"
                            class="ppid-primary-button"
                            data-ppid-primary
                        >
                            Jadikan Utama
                        </button>

                        <button
                            type="button"
                            class="ppid-document-remove"
                            data-ppid-document-remove
                        >
                            Hapus
                        </button>
                    </div>
                </div>

                <input
                    type="hidden"
                    value="${media.id}"
                    data-ppid-media-id
                >

                <input
                    type="hidden"
                    value="0"
                    data-ppid-sort-order
                >

                <input
                    type="hidden"
                    value="0"
                    data-ppid-is-primary
                >

                <div class="ppid-document-grid">
                    <div>
                        <label>Judul Dokumen</label>
                        <input
                            type="text"
                            class="ui-control"
                            maxlength="255"
                            data-ppid-document-title
                        >
                    </div>

                    <div>
                        <label>Versi</label>
                        <input
                            type="text"
                            class="ui-control"
                            maxlength="50"
                            data-ppid-version
                        >
                    </div>

                    <div>
                        <label>Status Dokumen</label>
                        <select
                            class="ui-control"
                            data-ppid-document-status
                        >
                            <option value="active">
                                Aktif
                            </option>

                            <option value="superseded">
                                Digantikan
                            </option>

                            <option value="expired">
                                Kedaluwarsa
                            </option>
                        </select>
                    </div>

                    <div>
                        <label>Tanggal Dokumen</label>
                        <input
                            type="date"
                            class="ui-control"
                            data-ppid-document-date
                        >
                    </div>
                </div>

                <div class="ppid-document-description">
                    <label>Keterangan</label>

                    <textarea
                        class="ui-control"
                        rows="2"
                        maxlength="2000"
                        data-ppid-document-description
                    ></textarea>
                </div>
            </div>
        `;

        card.querySelector(
            '.ppid-document-heading strong'
        ).textContent =
            media.title;

        card.querySelector(
            '[data-ppid-document-title]'
        ).value =
            media.title;

        documentsContainer.appendChild(
            card
        );

        refreshDocumentIndexes();

        ensurePrimaryExists();

        dirty = true;
    }

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
            '[data-ppid-media-close]'
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
     * GLOBAL MEDIA PICKER INTEGRATION: PPID
     * =====================================================
     */

    const globalPpidDocumentPicker =
        document.getElementById(
            'ppid-add-document'
        );

    globalPpidDocumentPicker?.addEventListener(
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
                        'Pilih Dokumen PPID',

                    description:
                        'Pilih beberapa dokumen atau upload dokumen baru.',

                    type:
                        'document',

                    multiple:
                        true,

                    unitId:
                        unitInput?.value
                        || '',
                });

            if (! result.length) {
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

            confirmDocuments?.click();
        },
        true
    );

    document
        .getElementById(
            'ppid-add-document'
        )
        ?.addEventListener(
            'click',
            function () {
                selectedMedia.clear();

                updateSelectedCount();

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
            'ppid-media-item';

        const preview =
            document.createElement(
                'div'
            );

        preview.className =
            'ppid-media-preview';

        preview.textContent =
            (
                media.extension
                || media.type
                || 'FILE'
            ).toUpperCase();

        const body =
            document.createElement(
                'div'
            );

        body.className =
            'ppid-media-item-body';

        const title =
            document.createElement(
                'strong'
            );

        title.textContent =
            media.title;

        const meta =
            document.createElement(
                'span'
            );

        meta.textContent =
            media.unit_name
            + ' • '
            + (
                media.extension
                || media.type
                || 'file'
            );

        body.appendChild(title);
        body.appendChild(meta);

        button.appendChild(preview);
        button.appendChild(body);

        button.addEventListener(
            'click',
            function () {
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

    confirmDocuments?.addEventListener(
        'click',
        function () {
            selectedMedia.forEach(
                function (media) {
                    createDocument(
                        media
                    );
                }
            );

            closeModal();
        }
    );

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
            refreshDocumentIndexes();
            ensurePrimaryExists();

            /*
             * Disabled input tidak dikirim browser.
             * Backend akan mengosongkan period bila permanen.
             */
            if (
                retentionUnit?.value ===
                'permanent'
            ) {
                retentionPeriod.disabled =
                    false;

                retentionPeriod.value =
                    '';
            }

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

    refreshDocumentIndexes();
    ensurePrimaryExists();
    updateSeo();
    updatePublicationHint();
    updateRetention();
});