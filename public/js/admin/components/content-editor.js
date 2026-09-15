(function () {
    const instances = new WeakMap();

    function registerImageFormat() {
        if (
            typeof Quill === 'undefined'
            || window.__AdminContentEditorImageRegistered
        ) {
            return;
        }

        const BaseImage =
            Quill.import('formats/image');

        class ContentEditorImage extends BaseImage {
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
                    url:
                        node.getAttribute('src'),

                    alt:
                        node.getAttribute('alt')
                        || '',

                    title:
                        node.getAttribute('title')
                        || '',
                };
            }
        }

        Quill.register(
            ContentEditorImage,
            true
        );

        window.__AdminContentEditorImageRegistered =
            true;
    }

    function htmlToText(html) {
        const container =
            document.createElement('div');

        container.innerHTML =
            html || '';

        return (
            container.textContent
            || container.innerText
            || ''
        )
            .replace(/\s+/g, ' ')
            .trim();
    }

    function init(root) {
        if (
            !root
            || instances.has(root)
        ) {
            return instances.get(root) || null;
        }

        if (typeof Quill === 'undefined') {
            return null;
        }

        registerImageFormat();

        const input =
            root.querySelector(
                '[data-content-editor-input]'
            );

        const quillElement =
            root.querySelector(
                '[data-content-editor-quill]'
            );

        const visualPane =
            root.querySelector(
                '[data-content-editor-visual]'
            );

        const sourcePane =
            root.querySelector(
                '[data-content-editor-source]'
            );

        const sourceInput =
            root.querySelector(
                '[data-content-editor-source-input]'
            );

        const modeButtons =
            root.querySelectorAll(
                '[data-content-editor-mode]'
            );

        if (
            !input
            || !quillElement
            || !sourceInput
        ) {
            return null;
        }

        let mode = 'visual';

        const quill =
            new Quill(
                quillElement,
                {
                    theme: 'snow',

                    placeholder:
                        root.dataset
                            .contentEditorPlaceholder
                        || 'Tulis konten di sini...',

                    modules: {
                        toolbar: {
                            container: [
                                [
                                    {
                                        header: [
                                            2,
                                            3,
                                            4,
                                            false,
                                        ],
                                    },
                                ],

                                [
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strike',
                                ],

                                [
                                    {
                                        list:
                                            'ordered',
                                    },
                                    {
                                        list:
                                            'bullet',
                                    },
                                ],

                                [
                                    {
                                        align: [],
                                    },
                                ],

                                [
                                    'blockquote',
                                    'code-block',
                                    'link',
                                    'image',
                                ],

                                [
                                    'clean',
                                ],
                            ],

                            handlers: {
                                image() {
                                    root.dispatchEvent(
                                        new CustomEvent(
                                            'content-editor:request-image',
                                            {
                                                bubbles: true,
                                            }
                                        )
                                    );
                                },
                            },
                        },

                        history: {
                            delay: 1000,
                            maxStack: 100,
                            userOnly: true,
                        },
                    },
                }
            );

        if (input.value.trim() !== '') {
            quill.clipboard
                .dangerouslyPasteHTML(
                    input.value,
                    'silent'
                );
        }

        sourceInput.value =
            input.value;

        function visualHTML() {
            return quill.getSemanticHTML();
        }

        function getHTML() {
            if (mode === 'source') {
                return sourceInput.value;
            }

            return visualHTML();
        }

        function getText() {
            if (mode === 'source') {
                return htmlToText(
                    sourceInput.value
                );
            }

            return quill
                .getText()
                .replace(/\s+/g, ' ')
                .trim();
        }

        function sync() {
            input.value =
                getHTML();

            return input.value;
        }

        function emitChange() {
            sync();

            root.dispatchEvent(
                new CustomEvent(
                    'content-editor:change',
                    {
                        bubbles: true,
                        detail: {
                            editor: api,
                        },
                    }
                )
            );
        }

        function setMode(nextMode) {
            if (
                nextMode !== 'visual'
                && nextMode !== 'source'
            ) {
                return;
            }

            if (nextMode === mode) {
                return;
            }

            if (nextMode === 'source') {
                sourceInput.value =
                    visualHTML();

                visualPane.hidden = true;
                sourcePane.hidden = false;
            } else {
                const html =
                    sourceInput.value;

                quill.setText(
                    '',
                    'silent'
                );

                if (html.trim() !== '') {
                    quill.clipboard
                        .dangerouslyPasteHTML(
                            html,
                            'silent'
                        );
                }

                visualPane.hidden = false;
                sourcePane.hidden = true;
            }

            mode = nextMode;

            modeButtons.forEach(
                function (button) {
                    const active =
                        button.dataset
                            .contentEditorMode
                        === mode;

                    button.classList.toggle(
                        'is-active',
                        active
                    );

                    button.setAttribute(
                        'aria-pressed',
                        active
                            ? 'true'
                            : 'false'
                    );
                }
            );

            sync();

            root.dispatchEvent(
                new CustomEvent(
                    'content-editor:mode-change',
                    {
                        bubbles: true,
                        detail: {
                            mode,
                        },
                    }
                )
            );
        }

        function insertImage(media) {
            if (!media?.url) {
                return;
            }

            if (mode === 'source') {
                const start =
                    sourceInput
                        .selectionStart;

                const end =
                    sourceInput
                        .selectionEnd;

                const imageHtml =
                    '<img src="'
                    + String(media.url)
                        .replace(/"/g, '&quot;')
                    + '" alt="'
                    + String(
                        media.alt_text || ''
                    ).replace(
                        /"/g,
                        '&quot;'
                    )
                    + '"'
                    + (
                        media.title
                            ? ' title="'
                                + String(
                                    media.title
                                ).replace(
                                    /"/g,
                                    '&quot;'
                                )
                                + '"'
                            : ''
                    )
                    + '>';

                sourceInput.value =
                    sourceInput.value.slice(
                        0,
                        start
                    )
                    + imageHtml
                    + sourceInput.value.slice(
                        end
                    );

                const cursor =
                    start
                    + imageHtml.length;

                sourceInput.setSelectionRange(
                    cursor,
                    cursor
                );

                sourceInput.focus();

                emitChange();

                return;
            }

            const range =
                quill.getSelection(true);

            const index =
                range
                    ? range.index
                    : Math.max(
                        0,
                        quill.getLength() - 1
                    );

            quill.insertEmbed(
                index,
                'image',
                {
                    url: media.url,
                    alt:
                        media.alt_text || '',
                    title:
                        media.title || '',
                },
                'user'
            );

            quill.insertText(
                index + 1,
                '\n',
                'user'
            );

            quill.setSelection(
                index + 2,
                0,
                'silent'
            );
        }

        const api = {
            root,
            quill,

            get mode() {
                return mode;
            },

            getHTML,
            getText,
            sync,
            setMode,
            insertImage,
        };

        instances.set(
            root,
            api
        );

        modeButtons.forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    function () {
                        setMode(
                            button.dataset
                                .contentEditorMode
                        );
                    }
                );
            }
        );

        quill.on(
            'text-change',
            function () {
                if (mode !== 'visual') {
                    return;
                }

                emitChange();
            }
        );

        sourceInput.addEventListener(
            'input',
            emitChange
        );

        root
            .closest('form')
            ?.addEventListener(
                'submit',
                sync
            );

        sync();

        return api;
    }

    function initAll() {
        document
            .querySelectorAll(
                '[data-content-editor]'
            )
            .forEach(init);
    }

    window.AdminContentEditor = {
        init,

        initAll,

        get(target) {
            const root =
                typeof target === 'string'
                    ? document.querySelector(
                        target
                    )
                    : target;

            if (!root) {
                return null;
            }

            return instances.get(root)
                || init(root);
        },
    };

    document.addEventListener(
        'DOMContentLoaded',
        initAll
    );
})();
