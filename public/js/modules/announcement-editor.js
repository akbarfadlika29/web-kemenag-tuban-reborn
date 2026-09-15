document.addEventListener('DOMContentLoaded', function () {
    const root =
        document.querySelector('[data-announcement-editor]');

    if (!root || typeof Quill === 'undefined') {
        return;
    }

    const form = root.closest('form');

    const contentInput =
        document.getElementById('content');

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

    const expiresAtInput =
        document.getElementById('expires_at');

    const publicationHint =
        document.getElementById('announcement-publication-hint');

    const unitInput =
        document.getElementById('unit_id');

    const coverInput =
        document.getElementById('cover_media_id');

    const attachmentInput =
        document.getElementById('attachment_media_id');

    const modal =
        document.getElementById('announcement-media-picker');

    const modalTitle =
        document.getElementById('announcement-media-modal-title');

    const mediaGrid =
        document.getElementById('announcement-media-grid');

    const mediaSearch =
        document.getElementById('announcement-media-search');

    const mediaUnit =
        document.getElementById('announcement-media-unit');

    const mediaLoading =
        document.getElementById('announcement-media-loading');

    const mediaEmpty =
        document.getElementById('announcement-media-empty');

    const mediaLoadMore =
        document.getElementById('announcement-media-load-more');

    const mediaPickerUrl =
        root.dataset.mediaPickerUrl;

    let pickerMode = 'cover';
    let currentPage = 1;
    let hasMore = false;
    let loading = false;
    let searchTimer = null;
    let dirty = false;

    const BaseImage =
        Quill.import('formats/image');

    class AnnouncementImage extends BaseImage {
        static create(value) {
            if (typeof value === 'string') {
                return super.create(value);
            }

            const node =
                super.create(value.url);

            node.setAttribute(
                'alt',
                value.alt || ''
            );

            if (value.title) {
                node.setAttribute(
                    'title',
                    value.title
                );
            }

            return node;
        }

        static value(node) {
            return {
                url: node.getAttribute('src'),
                alt: node.getAttribute('alt') || '',
                title: node.getAttribute('title') || ''
            };
        }
    }

    Quill.register(
        AnnouncementImage,
        true
    );

    const quill =
        new Quill(
            '#announcement-editor',
            {
                theme: 'snow',

                placeholder:
                    'Tulis isi pengumuman di sini...',

                modules: {
                    toolbar: {
                        container: [
                            [
                                {
                                    header: [
                                        2,
                                        3,
                                        4,
                                        false
                                    ]
                                }
                            ],

                            [
                                'bold',
                                'italic',
                                'underline',
                                'strike'
                            ],

                            [
                                { list: 'ordered' },
                                { list: 'bullet' }
                            ],

                            [
                                { align: [] }
                            ],

                            [
                                'blockquote',
                                'link',
                                'image'
                            ],

                            ['clean']
                        ],

                        handlers: {
                            image() {
                                openMediaPicker(
                                    'content'
                                );
                            }
                        }
                    }
                }
            }
        );

    if (
        contentInput
        && contentInput.value.trim() !== ''
    ) {
        quill.clipboard.dangerouslyPasteHTML(
            contentInput.value,
            'silent'
        );
    }

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
            quill
                .getText()
                .replace(/\s+/g, ' ')
                .trim();

        const words =
            text === ''
                ? []
                : text.split(/\s+/u);

        document.getElementById(
            'announcement-word-count'
        ).textContent = words.length;

        document.getElementById(
            'announcement-character-count'
        ).textContent = text.length;

        document.getElementById(
            'announcement-reading-time'
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
                || 'Judul pengumuman'
            ).trim();

        const slug =
            slugInput?.value
            || 'slug-pengumuman';

        const text =
            quill
                .getText()
                .replace(/\s+/g, ' ')
                .trim();

        const description =
            (
                metaDescriptionInput?.value
                || excerptInput?.value
                || text
                || 'Ringkasan pengumuman akan tampil di sini.'
            ).trim();

        document.getElementById(
            'announcement-seo-title'
        ).textContent = title;

        document.getElementById(
            'announcement-seo-slug'
        ).textContent = slug;

        document.getElementById(
            'announcement-seo-description'
        ).textContent =
            description.substring(0, 180);

        document.getElementById(
            'announcement-meta-title-count'
        ).textContent =
            metaTitleInput?.value.length || 0;

        document.getElementById(
            'announcement-meta-description-count'
        ).textContent =
            metaDescriptionInput?.value.length || 0;
    }

    function updatePublicationHint() {
        if (!publicationHint) {
            return;
        }

        publicationHint.classList.remove(
            'is-visible'
        );

        publicationHint.textContent = '';

        if (statusInput?.value !== 'published') {
            return;
        }

        if (!publishedAtInput?.value) {
            publicationHint.textContent =
                'Pengumuman akan dipublikasikan saat disimpan.';

            publicationHint.classList.add(
                'is-visible'
            );

            return;
        }

        const publicationDate =
            new Date(
                publishedAtInput.value
            );

        if (
            publicationDate > new Date()
        ) {
            publicationHint.textContent =
                'Pengumuman terjadwal dan belum tampil ke publik.';

            publicationHint.classList.add(
                'is-visible'
            );

            return;
        }

        if (expiresAtInput?.value) {
            const expiresDate =
                new Date(
                    expiresAtInput.value
                );

            if (expiresDate <= new Date()) {
                publicationHint.textContent =
                    'Pengumuman sudah melewati tanggal berakhir.';

                publicationHint.classList.add(
                    'is-visible'
                );
            }
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

    quill.on(
        'text-change',
        function () {
            dirty = true;
            updateStats();
            updateSeo();
        }
    );

    document
        .getElementById(
            'announcement-generate-excerpt'
        )
        ?.addEventListener(
            'click',
            function () {
                const text =
                    quill
                        .getText()
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
    ].forEach(
        function (element) {
            element?.addEventListener(
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
        publishedAtInput,
        expiresAtInput
    ].forEach(
        function (element) {
            element?.addEventListener(
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
            '[data-announcement-media-close]'
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
                && !modal.hidden
            ) {
                closeModal();
            }
        }
    );

    async function openMediaPicker(mode) {
        pickerMode = mode;

        if (! window.AdminMediaPicker) {
            currentPage = 1;
            mediaGrid.innerHTML = '';

            modalTitle.textContent =
                mode === 'cover'
                    ? 'Pilih Gambar Utama'
                    : mode === 'attachment'
                        ? 'Pilih Lampiran'
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

        const isAttachment =
            mode === 'attachment';

        const result =
            await window.AdminMediaPicker.open({
                title:
                    mode === 'cover'
                        ? 'Pilih Gambar Utama'
                        : isAttachment
                            ? 'Pilih Lampiran'
                            : 'Sisipkan Gambar ke Pengumuman',

                description:
                    isAttachment
                        ? 'Pilih atau upload file lampiran pengumuman.'
                        : 'Pilih atau upload gambar dari Media Manager.',

                type:
                    isAttachment
                        ? 'all'
                        : 'image',

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

        mediaGrid
            .lastElementChild
            ?.click();

        mediaGrid.innerHTML = '';
    }

    document
        .getElementById(
            'announcement-cover-picker'
        )
        ?.addEventListener(
            'click',
            function () {
                openMediaPicker(
                    'cover'
                );
            }
        );

    document
        .getElementById(
            'announcement-attachment-picker'
        )
        ?.addEventListener(
            'click',
            function () {
                openMediaPicker(
                    'attachment'
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

            url.searchParams.set(
                'type',
                pickerMode === 'attachment'
                    ? 'all'
                    : 'image'
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
            'announcement-media-item';

        if (media.type === 'image') {
            const image =
                document.createElement(
                    'img'
                );

            image.src = media.url;
            image.alt = media.alt_text || '';
            image.loading = 'lazy';

            button.appendChild(image);
        } else {
            const placeholder =
                document.createElement(
                    'div'
                );

            placeholder.className =
                'announcement-media-item-placeholder';

            placeholder.textContent =
                (
                    media.extension
                    || media.type
                    || 'FILE'
                ).toUpperCase();

            button.appendChild(
                placeholder
            );
        }

        const body =
            document.createElement(
                'div'
            );

        body.className =
            'announcement-media-item-body';

        const title =
            document.createElement(
                'div'
            );

        title.className =
            'announcement-media-item-title';

        title.textContent =
            media.title;

        const meta =
            document.createElement(
                'div'
            );

        meta.className =
            'announcement-media-item-meta';

        meta.textContent =
            media.unit_name
            + ' • '
            + (
                media.extension
                || media.type
                || 'media'
            );

        body.appendChild(title);
        body.appendChild(meta);

        button.appendChild(body);

        button.addEventListener(
            'click',
            function () {
                selectMedia(media);
            }
        );

        mediaGrid.appendChild(button);
    }

    function selectMedia(media) {
        if (pickerMode === 'cover') {
            setCover(media);
        } else if (
            pickerMode === 'attachment'
        ) {
            setAttachment(media);
        } else {
            insertImage(media);
        }

        closeModal();
    }

    function setCover(media) {
        coverInput.value =
            media.id;

        document.getElementById(
            'announcement-cover-image'
        ).src = media.url;

        document.getElementById(
            'announcement-cover-image'
        ).alt =
            media.alt_text || '';

        document.getElementById(
            'announcement-cover-title'
        ).textContent =
            media.title;

        document.getElementById(
            'announcement-cover-unit'
        ).textContent =
            media.unit_name;

        document.getElementById(
            'announcement-cover-empty'
        ).hidden = true;

        document.getElementById(
            'announcement-cover-preview'
        ).hidden = false;

        dirty = true;
    }

    function setAttachment(media) {
        attachmentInput.value =
            media.id;

        document.getElementById(
            'announcement-attachment-title'
        ).textContent =
            media.title;

        document.getElementById(
            'announcement-attachment-meta'
        ).textContent =
            media.unit_name
            + ' • '
            + (
                media.extension
                || media.type
                || 'media'
            );

        document.getElementById(
            'announcement-attachment-empty'
        ).hidden = true;

        document.getElementById(
            'announcement-attachment-preview'
        ).hidden = false;

        dirty = true;
    }

    function insertImage(media) {
        if (media.type !== 'image') {
            return;
        }

        const range =
            quill.getSelection(true);

        const index =
            range
                ? range.index
                : quill.getLength() - 1;

        quill.insertEmbed(
            index,
            'image',
            {
                url: media.url,
                alt: media.alt_text,
                title: media.title
            },
            'user'
        );

        quill.insertText(
            index + 1,
            '\n',
            'user'
        );
    }

    async function loadSelectedMedia(
        id,
        target
    ) {
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

            if (!payload.data?.[0]) {
                return;
            }

            if (target === 'cover') {
                setCover(
                    payload.data[0]
                );
            } else {
                setAttachment(
                    payload.data[0]
                );
            }

            dirty = false;
        } catch (error) {
            console.warn(
                'Preview media gagal dimuat.'
            );
        }
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

    loadSelectedMedia(
        root.dataset.selectedCoverId,
        'cover'
    );

    loadSelectedMedia(
        root.dataset.selectedAttachmentId,
        'attachment'
    );

    form?.addEventListener(
        'submit',
        function () {
            contentInput.value =
                quill.getSemanticHTML();

            dirty = false;
        }
    );

    form
        ?.querySelectorAll(
            'input, select, textarea'
        )
        .forEach(
            function (element) {
                element.addEventListener(
                    'change',
                    function () {
                        dirty = true;
                    }
                );
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

    updateStats();
    updateSeo();
    updatePublicationHint();
});