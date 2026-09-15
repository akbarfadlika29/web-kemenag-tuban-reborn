(function () {
    'use strict';

    if (window.PpidPopup) {
        return;
    }

    let queue = Promise.resolve();

    function openPopup(message, options = {}) {
        return new Promise(function (resolve) {
            const previousFocus = document.activeElement;
            const dialog = document.createElement('dialog');

            dialog.className = 'ppid-popup';
            dialog.dataset.danger = String(Boolean(options.danger));
            dialog.setAttribute('aria-labelledby', 'ppid-popup-title');
            dialog.setAttribute('aria-describedby', 'ppid-popup-message');

            // Static markup only. Dynamic text is inserted with textContent.
            dialog.innerHTML = `
                <div class="ppid-popup-main">
                    <div class="ppid-popup-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 7v6"></path>
                            <path d="M12 16h.01"></path>
                        </svg>
                    </div>
                    <h2 class="ppid-popup-title" id="ppid-popup-title"></h2>
                    <p class="ppid-popup-message" id="ppid-popup-message"></p>
                </div>
                <div class="ppid-popup-actions">
                    <button type="button"
                        class="ppid-popup-btn ppid-popup-cancel">Batal</button>
                    <button type="button"
                        class="ppid-popup-btn ppid-popup-confirm"></button>
                </div>
            `;

            const title = dialog.querySelector('.ppid-popup-title');
            const copy = dialog.querySelector('.ppid-popup-message');
            const cancel = dialog.querySelector('.ppid-popup-cancel');
            const confirm = dialog.querySelector('.ppid-popup-confirm');

            title.textContent = options.title || 'Konfirmasi tindakan';
            copy.textContent = String(message || 'Lanjutkan tindakan ini?');
            confirm.textContent = options.confirmText || 'Lanjutkan';
            cancel.hidden = Boolean(options.alert);

            let completed = false;

            function finish(result) {
                if (completed) return;
                completed = true;

                if (dialog.open) dialog.close();
                dialog.remove();

                if (previousFocus instanceof HTMLElement &&
                    previousFocus.isConnected) {
                    previousFocus.focus({ preventScroll: true });
                }

                resolve(result);
            }

            cancel.addEventListener('click', () => finish(false));
            confirm.addEventListener('click', () => finish(true));

            dialog.addEventListener('cancel', function (event) {
                event.preventDefault();
                finish(false);
            });

            // Prevent Escape from closing an underlying admin modal.
            dialog.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    event.stopPropagation();
                }
            });

            dialog.addEventListener('click', function (event) {
                if (event.target !== dialog) return;

                const rect = dialog.getBoundingClientRect();
                const outside =
                    event.clientX < rect.left ||
                    event.clientX > rect.right ||
                    event.clientY < rect.top ||
                    event.clientY > rect.bottom;

                if (outside) finish(false);
            });

            document.body.appendChild(dialog);
            dialog.showModal();

            // Cancellation gets initial focus for confirmations.
            (options.alert ? confirm : cancel).focus();
        });
    }

    function enqueue(message, options) {
        const task = queue.then(() => openPopup(message, options));
        queue = task.catch(() => {});
        return task;
    }

    window.PpidPopup = {
        confirm(message, options = {}) {
            const danger = options.danger ?? /hapus/i.test(String(message));

            return enqueue(message, {
                title: danger ? 'Hapus data?' : 'Konfirmasi perubahan',
                confirmText: danger ? 'Ya, hapus' : 'Lanjutkan',
                ...options,
                danger,
                alert: false
            });
        },

        alert(message, options = {}) {
            return enqueue(message, {
                title: 'Pemberitahuan',
                confirmText: 'Mengerti',
                ...options,
                alert: true
            });
        }
    };

    const approvedForms = new WeakSet();
    let pendingForms = new WeakSet();

    window.addEventListener('pageshow', function () {
        pendingForms = new WeakSet();
    });

    document.addEventListener('submit', async function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        if (approvedForms.has(form)) {
            approvedForms.delete(form);

            // Allow another attempt if another handler cancels submission.
            queueMicrotask(function () {
                if (event.defaultPrevented) pendingForms.delete(form);
            });

            return;
        }

        const submitter = event.submitter;
        const message =
            submitter?.getAttribute('data-confirm') ||
            form.getAttribute('data-confirm') ||
            (!submitter
                ? form.querySelector('[data-confirm]')?.getAttribute('data-confirm')
                : '');

        if (!message) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        if (pendingForms.has(form)) return;
        pendingForms.add(form);

        let submitted = false;

        try {
            const accepted = await window.PpidPopup.confirm(message);

            if (!accepted || !form.isConnected) return;

            // Preserve native validation and the clicked submit button.
            if (!form.noValidate &&
                !submitter?.formNoValidate &&
                !form.reportValidity()) {
                return;
            }

            approvedForms.add(form);

            if (submitter && submitter.isConnected && submitter.form === form) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }

            // If no submit event fired, don't retain the approval.
            submitted = !approvedForms.has(form);
        } catch (error) {
            console.error('Popup admin:', error);
        } finally {
            approvedForms.delete(form);
            if (!submitted) pendingForms.delete(form);
        }
    }, true);
})();
