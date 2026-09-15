document.addEventListener('DOMContentLoaded', function () {
    const root =
        document.querySelector('[data-agenda-editor]');

    if (!root || typeof Quill === 'undefined') {
        return;
    }

    const form =
        root.closest('form');

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

    const startInput =
        document.getElementById('start_at');

    const endInput =
        document.getElementById('end_at');

    const statusInput =
        document.getElementById('status');

    const publishedInput =
        document.getElementById('published_at');

    const eventHint =
        document.getElementById('agenda-event-hint');

    const publicationHint =
        document.getElementById('agenda-publication-hint');

    const unitInput =
        document.getElementById('unit_id');

    const coverInput =
        document.getElementById('cover_media_id');

    const modal =
        document.getElementById('agenda-media-picker');

    const mediaGrid =
        document.getElementById('agenda-media-grid');

    const mediaSearch =
        document.getElementById('agenda-media-search');

    const mediaUnit =
        document.getElementById('agenda-media-unit');

    const mediaLoading =
        document.getElementById('agenda-media-loading');

    const mediaEmpty =
        document.getElementById('agenda-media-empty');

    const loadMore =
        document.getElementById('agenda-media-load-more');

    const mediaPickerUrl =
        root.dataset.mediaPickerUrl;

    let currentPage = 1;
    let hasMore = false;
    let loading = false;
    let dirty = false;
    let searchTimer = null;
    let pickerMode = 'cover';

    const BaseImage =
        Quill.import('formats/image');

    class AgendaImage extends BaseImage {
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
        AgendaImage,
        true
    );

    const quill =
        new Quill(
            '#agenda-editor',
            {
                theme: 'snow',

                placeholder:
                    'Tulis deskripsi agenda...',

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
            'agenda-word-count'
        ).textContent =
            words.length;

        document.getElementById(
            'agenda-character-count'
        ).textContent =
            text.length;

        document.getElementById(
            'agenda-reading-time'
        ).textContent =
            Math.max(
                1,
                Math.ceil(
                    words.length / 200
                )
            );
    }

    function updateSeo() {
        const title =
            (
                metaTitleInput?.value
                || titleInput?.value
                || 'Judul agenda'
            ).trim();

        const slug =
            slugInput?.value
            || 'slug-agenda';

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
                || 'Ringkasan agenda akan tampil di sini.'
            ).trim();

        document.getElementById(
            'agenda-seo-title'
        ).textContent = title;

        document.getElementById(
            'agenda-seo-slug'
        ).textContent = slug;

        document.getElementById(
            'agenda-seo-description'
        ).textContent =
            description.substring(
                0,
                180
            );

        document.getElementById(
            'agenda-meta-title-count'
        ).textContent =
            metaTitleInput?.value.length
            || 0;

        document.getElementById(
            'agenda-meta-description-count'
        ).textContent =
            metaDescriptionInput?.value.length
            || 0;
    }

    function updateEventHint() {
        if (!eventHint) {
            return;
        }

        eventHint.classList.remove(
            'is-visible'
        );

        eventHint.textContent = '';

        if (!startInput?.value) {
            return;
        }

        const now = new Date();

        const start =
            new Date(
                startInput.value
            );

        const end =
            endInput?.value
                ? new Date(
                    endInput.value
                )
                : null;

        if (start > now) {
            eventHint.textContent =
                'Agenda akan datang.';

            eventHint.classList.add(
                'is-visible'
            );

            return;
        }

        if (
            end
            && now >= start
            && now <= end
        ) {
            eventHint.textContent =
                'Agenda sedang berlangsung.';

            eventHint.classList.add(
                'is-visible'
            );

            return;
        }

        if (
            end
            && end < now
        ) {
            eventHint.textContent =
                'Agenda telah selesai.';

            eventHint.classList.add(
                'is-visible'
            );
        }
    }

    function updatePublicationHint() {
        if (!publicationHint) {
            return;
        }

        publicationHint.classList.remove(
            'is-visible'
        );

        publicationHint.textContent = '';

        if (
            statusInput?.value !==
            'published'
        ) {
            return;
        }

        if (!publishedInput?.value) {
            publicationHint.textContent =
                'Agenda akan dipublikasikan saat disimpan.';

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
                'Publikasi agenda terjadwal.';

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
            'agenda-generate-excerpt'
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
                        ? text.substring(
                            0,
                            300
                        ).trim() + '…'
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
        startInput,
        endInput
    ].forEach(
        function (element) {
            element?.addEventListener(
                'change',
                function () {
                    dirty = true;

                    updateEventHint();
                }
            );
        }
    );

    [
        statusInput,
        publishedInput
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
            '[data-agenda-media-close]'
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

    async function openMediaPicker(mode) {
        pickerMode = mode;

        if (! window.AdminMediaPicker) {
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

            return;
        }

        const result =
            await window.AdminMediaPicker.open({
                title:
                    mode === 'cover'
                        ? 'Pilih Cover Agenda'
                        : 'Sisipkan Gambar ke Agenda',

                description:
                    mode === 'cover'
                        ? 'Pilih atau upload gambar cover agenda.'
                        : 'Pilih atau upload gambar untuk konten agenda.',

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

        mediaGrid
            .lastElementChild
            ?.click();

        mediaGrid.innerHTML = '';
    }

    document
        .getElementById(
            'agenda-cover-picker'
        )
        ?.addEventListener(
            'click',
            function () {
                openMediaPicker(
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

        button.type = 'button';

        button.className =
            'agenda-media-item';

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
            'agenda-media-item-body';

        const title =
            document.createElement(
                'div'
            );

        title.className =
            'agenda-media-item-title';

        title.textContent =
            media.title;

        const meta =
            document.createElement(
                'div'
            );

        meta.className =
            'agenda-media-item-meta';

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
                } else {
                    insertImage(media);
                }

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
                'agenda-cover-image'
            );

        image.src =
            media.url;

        image.alt =
            media.alt_text || '';

        document.getElementById(
            'agenda-cover-title'
        ).textContent =
            media.title;

        document.getElementById(
            'agenda-cover-unit'
        ).textContent =
            media.unit_name;

        document.getElementById(
            'agenda-cover-empty'
        ).hidden = true;

        document.getElementById(
            'agenda-cover-preview'
        ).hidden = false;

        dirty = true;
    }

    function insertImage(media) {
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
                url:
                    media.url,

                alt:
                    media.alt_text,

                title:
                    media.title
            },
            'user'
        );

        quill.insertText(
            index + 1,
            '\n',
            'user'
        );
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
            contentInput.value =
                quill.getSemanticHTML();

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

    restoreCover();

    updateStats();
    updateSeo();
    updateEventHint();
    updatePublicationHint();
});