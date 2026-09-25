document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-regulation-naskah]');
    if (!root) return;

    const input = root.querySelector('[data-naskah-id]');
    const removed = root.querySelector('[data-naskah-remove]');
    const name = root.querySelector('[data-naskah-name]');
    const preview = root.querySelector('[data-naskah-preview]');
    const choose = root.querySelector('[data-naskah-picker]');
    const clear = root.querySelector('[data-naskah-clear]');
    const error = root.querySelector('[data-naskah-error]');

    let selected = input.value ? {
        id: Number(input.value),
        title: root.dataset.initialTitle,
        url: root.dataset.initialUrl,
        type: 'document',
        mime_type: 'application/pdf',
        extension: 'pdf'
    } : null;

    function render() {
        input.value = selected ? String(selected.id) : '';
        name.textContent = selected
            ? selected.title || selected.original_name || 'Naskah PDF'
            : 'Belum ada naskah dipilih';

        clear.hidden = !selected;
        preview.hidden = !selected?.url;
        choose.textContent = selected ? 'Ganti Naskah' : 'Pilih Naskah';

        preview.removeAttribute('href');

        if (selected?.url) {
            const url = new URL(selected.url, location.href);
            if (url.origin === location.origin) preview.href = url.href;
            else preview.hidden = true;
        }
    }

    choose.addEventListener('click', async function () {
        error.hidden = true;

        if (!window.AdminMediaPicker?.open) {
            error.textContent = 'Media picker belum tersedia. Muat ulang halaman.';
            error.hidden = false;
            return;
        }

        choose.disabled = true;

        try {
            const pending = window.AdminMediaPicker.open({
                title: 'Pilih Naskah Regulasi',
                description: 'Pilih satu PDF atau unggah naskah baru. Maksimal 20 MB.',
                type: 'document',
                multiple: false,
                selected: selected ? [selected] : []
            });

            const picker = document.querySelector('[data-admin-media-picker]');
            const upload = picker?.querySelector('[data-media-upload-file]');
            const help = picker?.querySelector('[data-media-upload-help]');

            if (upload) upload.accept = '.pdf,application/pdf';
            if (help) help.textContent = 'PDF saja. Maksimal 20 MB.';

            const result = await pending;
            const item = Array.isArray(result) ? result[0] : null;

            // Cancel preserves the current selection.
            if (!item) return;

            if (item.mime_type !== 'application/pdf') {
                throw new Error('Naskah regulasi harus berupa PDF.');
            }

            selected = item;
            removed.value = '0';
            render();
        } catch (exception) {
            error.textContent = exception.message || 'Naskah gagal dipilih.';
            error.hidden = false;
        } finally {
            choose.disabled = false;
            choose.focus();
        }
    });

    clear.addEventListener('click', function () {
        selected = null;
        removed.value = '1';
        render();
        choose.focus();
    });

    render();
});
