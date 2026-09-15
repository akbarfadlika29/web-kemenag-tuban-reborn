(function () {
    'use strict';

    function initTreeDnd(scope) {
        if (!scope) {
            return;
        }

        if (scope.dataset.treeDndInitialized === '1') {
            return;
        }

        const items = Array.from(
            scope.querySelectorAll('[data-tree-dnd-item]')
        );

        if (!items.length) {
            return;
        }

        const rootZone =
            scope.querySelector('[data-tree-dnd-root]');

        const csrfMeta =
            document.querySelector(
                'meta[name="csrf-token"]'
            );

        if (!csrfMeta) {
            console.error(
                'Tree DnD: CSRF token tidak ditemukan.'
            );

            return;
        }

        const csrfToken = csrfMeta.content;

        const itemLabel =
            scope.dataset.treeItemLabel ||
            'item';

        const defaultErrorMessage =
            scope.dataset.treeErrorMessage ||
            'Data gagal dipindahkan.';

        let draggedItem = null;
        let dropPosition = null;

        scope.dataset.treeDndInitialized = '1';

        function clearIndicators() {
            items.forEach(function (item) {
                item.classList.remove(
                    'is-tree-dnd-drop-before',
                    'is-tree-dnd-drop-after',
                    'is-tree-dnd-drop-inside'
                );
            });

            if (rootZone) {
                rootZone.classList.remove(
                    'is-tree-dnd-over'
                );
            }
        }

        function getErrorMessage(data) {
            if (
                data &&
                data.errors
            ) {
                const errors =
                    Object.values(data.errors);

                if (
                    errors.length &&
                    Array.isArray(errors[0]) &&
                    errors[0].length
                ) {
                    return errors[0][0];
                }
            }

            if (
                data &&
                data.message
            ) {
                return data.message;
            }

            return defaultErrorMessage;
        }

        async function submitMove(
            source,
            targetId,
            position,
            message
        ) {
            const moveUrl =
                source.dataset.treeMoveUrl;

            if (!moveUrl) {
                await window.PpidPopup.alert(
                    'URL pemindahan tidak ditemukan.'
                );

                clearIndicators();

                return;
            }

            if (!(await window.PpidPopup.confirm(message))) {
                clearIndicators();

                return;
            }

            scope.classList.add(
                'is-tree-dnd-busy'
            );

            try {
                const response =
                    await fetch(
                        moveUrl,
                        {
                            method: 'PATCH',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken
                            },

                            body: JSON.stringify({
                                target_id:
                                    targetId,

                                position:
                                    position
                            })
                        }
                    );

                let data = {};

                try {
                    data =
                        await response.json();
                } catch (error) {
                    data = {};
                }

                if (!response.ok) {
                    await window.PpidPopup.alert(
                        getErrorMessage(data)
                    );

                    scope.classList.remove(
                        'is-tree-dnd-busy'
                    );

                    clearIndicators();

                    return;
                }

                window.location.reload();
            } catch (error) {
                console.error(
                    'Tree DnD:',
                    error
                );

                await window.PpidPopup.alert(
                    'Terjadi kesalahan saat memindahkan data.'
                );

                scope.classList.remove(
                    'is-tree-dnd-busy'
                );

                clearIndicators();
            }
        }

        items.forEach(function (item) {
            item.setAttribute(
                'draggable',
                'true'
            );

            item.addEventListener(
                'dragstart',
                function (event) {
                    draggedItem = item;

                    item.classList.add(
                        'is-tree-dnd-dragging'
                    );

                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed =
                            'move';

                        event.dataTransfer.setData(
                            'text/plain',
                            item.dataset.treeId || ''
                        );
                    }
                }
            );

            item.addEventListener(
                'dragend',
                function () {
                    item.classList.remove(
                        'is-tree-dnd-dragging'
                    );

                    draggedItem = null;
                    dropPosition = null;

                    clearIndicators();
                }
            );

            item.addEventListener(
                'dragover',
                function (event) {
                    event.preventDefault();

                    if (
                        !draggedItem ||
                        draggedItem === item
                    ) {
                        return;
                    }

                    clearIndicators();

                    const rect =
                        item.getBoundingClientRect();

                    const y =
                        event.clientY -
                        rect.top;

                    const third =
                        rect.height / 3;

                    if (y < third) {
                        dropPosition =
                            'before';

                        item.classList.add(
                            'is-tree-dnd-drop-before'
                        );

                        return;
                    }

                    if (y > third * 2) {
                        dropPosition =
                            'after';

                        item.classList.add(
                            'is-tree-dnd-drop-after'
                        );

                        return;
                    }

                    dropPosition =
                        'inside';

                    item.classList.add(
                        'is-tree-dnd-drop-inside'
                    );
                }
            );

            item.addEventListener(
                'drop',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (
                        !draggedItem ||
                        draggedItem === item ||
                        !dropPosition
                    ) {
                        return;
                    }

                    const sourceLabel =
                        draggedItem.dataset.treeLabel ||
                        itemLabel;

                    const targetLabel =
                        item.dataset.treeLabel ||
                        itemLabel;

                    let message;

                    if (
                        dropPosition ===
                        'inside'
                    ) {
                        message =
                            'Pindahkan "' +
                            sourceLabel +
                            '" menjadi anak dari "' +
                            targetLabel +
                            '"?';
                    } else if (
                        dropPosition ===
                        'before'
                    ) {
                        message =
                            'Pindahkan "' +
                            sourceLabel +
                            '" sebelum "' +
                            targetLabel +
                            '"?';
                    } else {
                        message =
                            'Pindahkan "' +
                            sourceLabel +
                            '" sesudah "' +
                            targetLabel +
                            '"?';
                    }

                    submitMove(
                        draggedItem,
                        item.dataset.treeId,
                        dropPosition,
                        message
                    );
                }
            );
        });

        if (rootZone) {
            rootZone.addEventListener(
                'dragover',
                function (event) {
                    event.preventDefault();

                    if (!draggedItem) {
                        return;
                    }

                    clearIndicators();

                    rootZone.classList.add(
                        'is-tree-dnd-over'
                    );
                }
            );

            rootZone.addEventListener(
                'dragleave',
                function (event) {
                    if (
                        event.relatedTarget &&
                        rootZone.contains(
                            event.relatedTarget
                        )
                    ) {
                        return;
                    }

                    rootZone.classList.remove(
                        'is-tree-dnd-over'
                    );
                }
            );

            rootZone.addEventListener(
                'drop',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (!draggedItem) {
                        return;
                    }

                    const sourceLabel =
                        draggedItem.dataset.treeLabel ||
                        itemLabel;

                    submitMove(
                        draggedItem,
                        null,
                        'root',
                        'Pindahkan "' +
                        sourceLabel +
                        '" ke level utama?'
                    );
                }
            );
        }
    }

    function initAll() {
        document
            .querySelectorAll(
                '[data-tree-dnd]'
            )
            .forEach(function (scope) {
                initTreeDnd(scope);
            });
    }

    if (
        document.readyState ===
        'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            initAll
        );
    } else {
        initAll();
    }

    window.AdminTreeDnd = {
        init: initTreeDnd,
        initAll: initAll
    };
})();
