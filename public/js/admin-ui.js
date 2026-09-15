/* Shared scroll ownership for admin modals and media picker. */
(function () {
    if (window.PpidScrollLock) return;

    const owners = new Set();
    let previousValue = '';
    let previousPriority = '';

    window.PpidScrollLock = {
        lock(owner) {
            if (owners.has(owner)) return;

            if (owners.size === 0) {
                previousValue = document.body.style.getPropertyValue('overflow');
                previousPriority = document.body.style.getPropertyPriority('overflow');
                document.body.style.setProperty('overflow', 'hidden');
            }

            owners.add(owner);
        },

        unlock(owner) {
            if (!owners.delete(owner) || owners.size !== 0) return;

            if (previousValue) {
                document.body.style.setProperty(
                    'overflow',
                    previousValue,
                    previousPriority
                );
            } else {
                document.body.style.removeProperty('overflow');
            }
        }
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-alert-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            const alert = button.closest('.ui-alert');

            if (alert) {
                alert.remove();
            }
        });
    });

    // Confirmations are handled by admin/popup.js.

    document.querySelectorAll('[data-modal-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.dataset.modalOpen;
            const modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');

            window.PpidScrollLock.lock(modal);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal(button.closest('.ui-modal-backdrop'));
        });
    });

    document.querySelectorAll('.ui-modal-backdrop').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        const picker = document.querySelector('[data-admin-media-picker]');
        if (picker && !picker.hidden) return;
        if (document.querySelector('dialog[open]')) return;

        const modals = document.querySelectorAll('.ui-modal-backdrop.is-open');
        const topModal = modals[modals.length - 1];

        if (topModal) closeModal(topModal);
    });

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        window.PpidScrollLock.unlock(modal);
    }
});