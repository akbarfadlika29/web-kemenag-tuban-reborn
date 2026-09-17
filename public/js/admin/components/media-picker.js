document.addEventListener(
    'DOMContentLoaded',
    function () {
        const root =
            document.querySelector(
                '[data-admin-media-picker]'
            );

        if (! root) {
            return;
        }

        const listUrl =
            root.dataset.listUrl;

        const uploadUrl =
            root.dataset.uploadUrl;

        const title =
            root.querySelector(
                '#admin-media-picker-title'
            );

        const description =
            root.querySelector(
                '[data-media-picker-description]'
            );

        const search =
            root.querySelector(
                '[data-media-picker-search]'
            );

        const typeFilter =
            root.querySelector(
                '[data-media-picker-type]'
            );

        const unitFilter =
            root.querySelector(
                '[data-media-picker-unit]'
            );

        const grid =
            root.querySelector(
                '[data-media-picker-grid]'
            );

        const loading =
            root.querySelector(
                '[data-media-picker-loading]'
            );

        const empty =
            root.querySelector(
                '[data-media-picker-empty]'
            );

        const more =
            root.querySelector(
                '[data-media-picker-more]'
            );

        const confirm =
            root.querySelector(
                '[data-media-picker-confirm]'
            );

        const cancel =
            root.querySelector(
                '[data-media-picker-cancel]'
            );

        const selectedCount =
            root.querySelector(
                '[data-media-picker-selected-count]'
            );

        const uploadForm =
            root.querySelector(
                '[data-media-upload-form]'
            );

        const uploadFile =
            root.querySelector(
                '[data-media-upload-file]'
            );

        const uploadType =
            root.querySelector(
                '[data-media-upload-type]'
            );

        const uploadUnit =
            root.querySelector(
                '[data-media-upload-unit]'
            );

        const uploadError =
            root.querySelector(
                '[data-media-upload-error]'
            );

        const uploadSubmit =
            root.querySelector(
                '[data-media-upload-submit]'
            );

        const uploadFilename =
            root.querySelector(
                '[data-media-upload-filename]'
            );

        const uploadHelp =
            root.querySelector(
                '[data-media-upload-help]'
            );

        const altGroup =
            root.querySelector(
                '[data-media-upload-alt-group]'
            );

        const dropzone =
            root.querySelector(
                '[data-media-dropzone]'
            );

        const selected =
            new Map();

        let page = 1;
        let hasMore = false;
        let busy = false;
        let pendingMediaReset = false;
        let searchTimer = null;
        let resolver = null;

        let options = {
            title: 'Pilih Media',
            description:
                'Pilih aset dari pustaka media atau unggah file baru.',
            type: 'all',
            multiple: false,
            unitId: '',
        };

        function setTab(tab) {
            root
                .querySelectorAll(
                    '[data-media-picker-tab]'
                )
                .forEach(
                    function (button) {
                        button.classList.toggle(
                            'is-active',
                            button.dataset.mediaPickerTab
                                === tab
                        );
                    }
                );

            root
                .querySelectorAll(
                    '[data-media-picker-panel]'
                )
                .forEach(
                    function (panel) {
                        panel.hidden =
                            panel.dataset.mediaPickerPanel
                            !== tab;
                    }
                );
        }

        function openPicker(
            nextOptions = {}
        ) {
            if (resolver) {
                resolver([]);
                resolver = null;
            }

            options = {
                title:
                    nextOptions.title
                    || 'Pilih Media',

                description:
                    nextOptions.description
                    || 'Pilih aset dari pustaka media atau unggah file baru.',

                type:
                    nextOptions.type
                    || 'all',

                multiple:
                    Boolean(
                        nextOptions.multiple
                    ),

                unitId:
                    nextOptions.unitId
                    ? String(
                        nextOptions.unitId
                    )
                    : '',
            };

            selected.clear();

            if (
                Array.isArray(
                    nextOptions.selected
                )
            ) {
                nextOptions.selected.forEach(
                    function (item) {
                        if (item?.id) {
                            selected.set(
                                String(item.id),
                                item
                            );
                        }
                    }
                );
            }

            title.textContent =
                options.title;

            description.textContent =
                options.description;

            typeFilter.value =
                options.type;

            typeFilter.disabled =
                options.type !== 'all';

            uploadType.value =
                options.type;

            configureUpload();

            search.value = '';

            grid.innerHTML = '';

            page = 1;

            updateSelection();

            setTab('library');

            root.hidden = false;

            root.setAttribute(
                'aria-hidden',
                'false'
            );

            window.PpidScrollLock.lock(root);

            loadMedia(true);

            return new Promise(
                function (resolve) {
                    resolver = resolve;
                }
            );
        }

        function closePicker(
            result = []
        ) {
            root.hidden = true;

            root.setAttribute(
                'aria-hidden',
                'true'
            );

            window.PpidScrollLock.unlock(root);

            uploadForm.reset();

            if (uploadFilename) {
                uploadFilename.textContent =
                    'Belum ada file dipilih';
            }

            if (uploadError) {
                uploadError.hidden = true;
                uploadError.textContent = '';
            }

            if (resolver) {
                resolver(result);
                resolver = null;
            }
        }

        function configureUpload() {
            const type =
                options.type;

            const acceptMap = {
                image:
                    '.jpg,.jpeg,.png,.webp,.gif',

                document:
                    '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv',

                audio:
                    '.mp3,.wav',

                video:
                    '.mp4,.webm',

                all:
                    '.jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.mp3,.wav,.mp4,.webm',

                other:
                    '',
            };

            uploadFile.accept =
                acceptMap[type]
                ?? acceptMap.all;

            uploadHelp.textContent =
                type === 'image'
                    ? 'JPG, JPEG, PNG, WEBP, atau GIF. Maksimal 50 MB.'
                    : type === 'document'
                        ? 'PDF, Word, Excel, PowerPoint, TXT, atau CSV. Maksimal 50 MB.'
                        : 'Maksimal 50 MB.';

            altGroup.hidden =
                type !== 'image'
                && type !== 'all';

            uploadSubmit.textContent =
                options.multiple
                    ? 'Upload & Tambahkan'
                    : 'Upload & Gunakan';
        }

        function updateSelection() {
            selectedCount.textContent =
                String(selected.size);

            confirm.disabled =
                selected.size === 0;

            confirm.textContent =
                options.multiple
                    ? 'Gunakan Media Terpilih'
                    : 'Gunakan Media';

            root
                .querySelectorAll(
                    '[data-media-item]'
                )
                .forEach(
                    function (item) {
                        item.classList.toggle(
                            'is-selected',
                            selected.has(
                                item.dataset.mediaId
                            )
                        );
                    }
                );
        }

        function selectItem(item) {
            const id =
                String(item.id);

            if (options.multiple) {
                if (selected.has(id)) {
                    selected.delete(id);
                } else {
                    selected.set(
                        id,
                        item
                    );
                }
            } else {
                selected.clear();

                selected.set(
                    id,
                    item
                );
            }

            updateSelection();
        }

        function mediaCard(item) {
            const button =
                document.createElement(
                    'button'
                );

            button.type = 'button';

            button.className =
                'admin-media-picker-item';

            button.dataset.mediaItem = '';

            button.dataset.mediaId =
                String(item.id);

            const preview =
                document.createElement(
                    'div'
                );

            preview.className =
                'admin-media-picker-item-preview';

            if (
                item.type === 'image'
            ) {
                const image =
                    document.createElement(
                        'img'
                    );

                image.src =
                    item.url;

                image.alt =
                    item.alt_text
                    || item.title
                    || '';

                image.loading =
                    'lazy';

                preview.appendChild(
                    image
                );
            } else {
                const icon =
                    document.createElement(
                        'div'
                    );

                icon.className =
                    'admin-media-picker-file-icon';

                icon.textContent =
                    (
                        item.extension
                        || item.type
                        || 'FILE'
                    ).toUpperCase();

                preview.appendChild(
                    icon
                );
            }

            const check =
                document.createElement(
                    'span'
                );

            check.className =
                'admin-media-picker-check';

            check.textContent = '✓';

            preview.appendChild(
                check
            );

            const body =
                document.createElement(
                    'div'
                );

            body.className =
                'admin-media-picker-item-body';

            const mediaTitle =
                document.createElement(
                    'strong'
                );

            mediaTitle.textContent =
                item.title
                || item.original_name
                || 'Media';

            const meta =
                document.createElement(
                    'div'
                );

            meta.className =
                'admin-media-picker-item-meta';

            const unit =
                document.createElement(
                    'span'
                );

            unit.textContent =
                item.unit_name
                || 'Global';

            const extension =
                document.createElement(
                    'span'
                );

            extension.textContent =
                (
                    item.extension
                    || item.type
                    || ''
                ).toUpperCase();

            meta.append(
                unit,
                extension
            );

            body.append(
                mediaTitle,
                meta
            );

            button.append(
                preview,
                body
            );

            button.addEventListener(
                'click',
                function () {
                    selectItem(item);
                }
            );

            button.addEventListener(
                'dblclick',
                function () {
                    if (
                        ! options.multiple
                    ) {
                        selected.clear();

                        selected.set(
                            String(item.id),
                            item
                        );

                        closePicker([
                            item,
                        ]);
                    }
                }
            );

            return button;
        }

        function populateUnits(
            units
        ) {
            if (
                unitFilter.dataset.loaded
                === '1'
            ) {
                return;
            }

            units.forEach(
                function (unit) {
                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        String(unit.id);

                    option.textContent =
                        unit.name;

                    unitFilter.appendChild(
                        option
                    );

                    const uploadOption =
                        option.cloneNode(true);

                    uploadUnit.appendChild(
                        uploadOption
                    );
                }
            );

            unitFilter.dataset.loaded =
                '1';

            uploadUnit.dataset.loaded =
                '1';
        }

        async function loadMedia(
            reset = false
        ) {
            if (!listUrl) return;

            if (busy) {
                if (reset) pendingMediaReset = true;
                return;
            }

            pendingMediaReset = false;

            busy = true;

            if (reset) {
                page = 1;
                grid.innerHTML = '';
            }

            loading.hidden = false;
            empty.hidden = true;
            more.hidden = true;

            try {
                const url =
                    new URL(
                        listUrl,
                        window.location.origin
                    );

                url.searchParams.set(
                    'page',
                    String(page)
                );

                url.searchParams.set(
                    'type',
                    typeFilter.value
                );

                const query =
                    search.value.trim();

                if (query) {
                    url.searchParams.set(
                        'search',
                        query
                    );
                }

                const activeUnit =
                    unitFilter.value
                    || options.unitId
                    || '';

                if (activeUnit) {
                    url.searchParams.set(
                        'unit_id',
                        activeUnit
                    );
                }

                const response =
                    await fetch(
                        url.toString(),
                        {
                            headers: {
                                Accept:
                                    'application/json',
                            },
                        }
                    );

                if (! response.ok) {
                    throw new Error(
                        'Media gagal dimuat.'
                    );
                }

                const payload =
                    await response.json();

                // Abaikan hasil lama jika filter berubah atau picker ditutup.
                if (pendingMediaReset || root.hidden) return;

                populateUnits(
                    payload.filters?.units
                    || []
                );

                if (
                    reset
                    && options.unitId
                    && unitFilter.value === ''
                ) {
                    unitFilter.value =
                        options.unitId;

                    uploadUnit.value =
                        options.unitId;
                }

                (
                    payload.data
                    || []
                ).forEach(
                    function (item) {
                        grid.appendChild(
                            mediaCard(item)
                        );
                    }
                );

                hasMore =
                    Boolean(
                        payload.meta
                            ?.has_more
                    );

                more.hidden =
                    ! hasMore;

                empty.hidden =
                    grid.children.length > 0;

                updateSelection();
            } catch (error) {
                if (pendingMediaReset || root.hidden) return;

                empty.textContent =
                    error.message
                    || 'Media gagal dimuat.';

                empty.hidden = false;
            } finally {
                loading.hidden = true;
                busy = false;

                if (pendingMediaReset && !root.hidden) {
                    pendingMediaReset = false;
                    loadMedia(true);
                }
            }
        }

        async function uploadMedia(
            event
        ) {
            event.preventDefault();

            if (
                ! uploadUrl
                || ! uploadFile.files.length
            ) {
                return;
            }

            uploadError.hidden = true;
            uploadError.textContent = '';

            uploadSubmit.disabled = true;

            const oldText =
                uploadSubmit.textContent;

            uploadSubmit.textContent =
                'Mengunggah...';

            const formData =
                new FormData(
                    uploadForm
                );

            formData.set(
                'expected_type',
                options.type
            );

            try {
                const response =
                    await fetch(
                        uploadUrl,
                        {
                            method: 'POST',

                            headers: {
                                Accept:
                                    'application/json',
                            },

                            body:
                                formData,
                        }
                    );

                const payload =
                    await response.json();

                if (! response.ok) {
                    const firstError =
                        Object.values(
                            payload.errors
                            || {}
                        )
                            .flat()
                            .find(Boolean);

                    throw new Error(
                        firstError
                        || payload.message
                        || 'Upload gagal.'
                    );
                }

                const item =
                    payload.data;

                if (options.multiple) {
                    selected.set(
                        String(item.id),
                        item
                    );

                    uploadForm.reset();

                    uploadFilename.textContent =
                        'Belum ada file dipilih';

                    setTab(
                        'library'
                    );

                    grid.innerHTML = '';

                    await loadMedia(true);

                    updateSelection();

                    return;
                }

                closePicker([
                    item,
                ]);
            } catch (error) {
                uploadError.textContent =
                    error.message
                    || 'Upload gagal.';

                uploadError.hidden = false;
            } finally {
                uploadSubmit.disabled =
                    false;

                uploadSubmit.textContent =
                    oldText;
            }
        }

        root
            .querySelectorAll(
                '[data-media-picker-tab]'
            )
            .forEach(
                function (button) {
                    button.addEventListener(
                        'click',
                        function () {
                            setTab(
                                button.dataset
                                    .mediaPickerTab
                            );
                        }
                    );
                }
            );

        root
            .querySelectorAll(
                '[data-media-picker-close]'
            )
            .forEach(
                function (button) {
                    button.addEventListener(
                        'click',
                        function () {
                            closePicker([]);
                        }
                    );
                }
            );

        cancel.addEventListener(
            'click',
            function () {
                closePicker([]);
            }
        );

        confirm.addEventListener(
            'click',
            function () {
                closePicker(
                    Array.from(
                        selected.values()
                    )
                );
            }
        );

        search.addEventListener(
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
                        300
                    );
            }
        );

        unitFilter.addEventListener(
            'change',
            function () {
                uploadUnit.value =
                    unitFilter.value === 'global'
                        ? ''
                        : unitFilter.value;

                loadMedia(true);
            }
        );

        typeFilter.addEventListener(
            'change',
            function () {
                uploadType.value =
                    typeFilter.value;

                options.type =
                    typeFilter.value;

                configureUpload();

                loadMedia(true);
            }
        );

        more.addEventListener(
            'click',
            function () {
                if (! hasMore) {
                    return;
                }

                page += 1;

                loadMedia(false);
            }
        );

        uploadFile.addEventListener(
            'change',
            function () {
                uploadFilename.textContent =
                    uploadFile.files[0]?.name
                    || 'Belum ada file dipilih';
            }
        );

        [
            'dragenter',
            'dragover',
        ].forEach(
            function (eventName) {
                dropzone.addEventListener(
                    eventName,
                    function (event) {
                        event.preventDefault();

                        dropzone.classList.add(
                            'is-dragging'
                        );
                    }
                );
            }
        );

        [
            'dragleave',
            'drop',
        ].forEach(
            function (eventName) {
                dropzone.addEventListener(
                    eventName,
                    function () {
                        dropzone.classList.remove(
                            'is-dragging'
                        );
                    }
                );
            }
        );

        dropzone.addEventListener(
            'drop',
            function (event) {
                event.preventDefault();

                if (
                    event.dataTransfer
                        ?.files
                        ?.length
                ) {
                    uploadFile.files =
                        event.dataTransfer.files;

                    uploadFilename.textContent =
                        uploadFile.files[0].name;
                }
            }
        );

        uploadForm.addEventListener(
            'submit',
            uploadMedia
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                    && ! root.hidden
                ) {
                    closePicker([]);
                }
            }
        );

        window.AdminMediaPicker = {
            open: openPicker,

            close() {
                closePicker([]);
            },
        };
    }
);
