(function () {
    'use strict';

    const LIMIT = 50;
    let operationRunning = false;

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function initTable(table) {
        if (table.dataset.ppidBulkReady) return;

        const headerRow = table.tHead?.rows[0];
        if (!headerRow) return;

        const records = [];

        Array.from(table.tBodies).forEach(function (body) {
            Array.from(body.rows).forEach(function (row) {
                const form = Array.from(row.querySelectorAll('form'))
                    .find(function (candidate) {
                        return candidate.closest('tr') === row &&
                            candidate.method.toLowerCase() === 'post' &&
                            candidate.querySelector(
                                'input[name="_method"]'
                            )?.value.toUpperCase() === 'DELETE' &&
                            candidate.querySelector('input[name="_token"]');
                    });

                if (!form) return;

                const action = new URL(form.action, location.href);
                if (action.origin !== location.origin) return;

                const submitter = form.querySelector(
                    'button[type="submit"], input[type="submit"]'
                );

                if (submitter?.disabled) return;

                const titleNode = row.querySelector(
                    'td h3, td strong, td a:not(.ui-btn)'
                );

                const label = (
                    titleNode?.textContent ||
                    row.cells[0]?.textContent ||
                    'Data'
                ).replace(/\s+/g, ' ').trim().slice(0, 120);

                records.push({ row, form, submitter, label });
            });
        });

        if (!records.length) return;

        table.dataset.ppidBulkReady = '1';

        const toolbar = element('div', 'ppid-bulk-toolbar');
        const copy = element('div', 'ppid-bulk-copy');
        const count = element('strong', '', '0 data terpilih');
        const note = element(
            'small',
            '',
            'Pilih baris pada halaman ini. Maksimal 50 data per proses.'
        );

        count.setAttribute('aria-live', 'polite');
        copy.append(count, note);

        const clear = element('button', 'ppid-bulk-button', 'Batal pilih');
        const remove = element(
            'button',
            'ppid-bulk-button ppid-bulk-button-danger',
            'Hapus terpilih'
        );

        clear.type = 'button';
        remove.type = 'button';
        toolbar.append(copy, clear, remove);

        const results = element('div', 'ppid-bulk-results');
        results.hidden = true;
        results.setAttribute('aria-live', 'polite');

        let anchor = table.parentElement;
        if (!anchor || anchor.tagName === 'FORM') anchor = table;
        anchor.before(toolbar, results);

        const selectAll = element('input', 'ppid-bulk-check');
        selectAll.type = 'checkbox';
        selectAll.setAttribute(
            'aria-label',
            'Pilih hingga 50 data yang terlihat pada halaman ini'
        );

        const heading = element('th', 'ppid-bulk-cell');
        heading.scope = 'col';
        heading.append(selectAll);
        headerRow.prepend(heading);

        const byRow = new Map(records.map(record => [record.row, record]));

        Array.from(table.tBodies).forEach(function (body) {
            Array.from(body.rows).forEach(function (row) {
                const record = byRow.get(row);

                if (!record && row.cells.length === 1 &&
                    row.cells[0].colSpan > 1) {
                    row.cells[0].colSpan++;
                    return;
                }

                const cell = element('td', 'ppid-bulk-cell');
                row.prepend(cell);

                if (!record) return;

                const checkbox = element('input', 'ppid-bulk-check');
                checkbox.type = 'checkbox';
                checkbox.setAttribute('aria-label', 'Pilih ' + record.label);
                checkbox.addEventListener('click', event => event.stopPropagation());
                checkbox.addEventListener('mousedown', event => event.stopPropagation());
                checkbox.addEventListener('change', sync);

                record.checkbox = checkbox;
                cell.append(checkbox);
            });
        });

        let busy = false;

        function available() {
            return records.filter(record =>
                record.row.isConnected &&
                record.row.getClientRects().length > 0 &&
                !record.checkbox.disabled
            );
        }

        function selected() {
            return available().filter(record => record.checkbox.checked);
        }

        function sync() {
            const rows = available();
            const picked = selected();

            records.forEach(record => {
                record.row.classList.toggle(
                    'ppid-bulk-selected',
                    record.checkbox.checked
                );
            });

            count.textContent = picked.length + ' data terpilih';
            remove.disabled = busy || !picked.length || picked.length > LIMIT;
            clear.disabled = busy || !picked.length;
            selectAll.disabled = busy || !rows.length;

            selectAll.checked =
                rows.length > 0 && picked.length === rows.length;

            selectAll.indeterminate =
                picked.length > 0 && picked.length < rows.length;

            note.textContent = picked.length > LIMIT
                ? 'Maksimal 50 data. Kurangi pilihan sebelum melanjutkan.'
                : 'Pilih baris pada halaman ini. Maksimal 50 data per proses.';
        }

        selectAll.addEventListener('change', function () {
            const checked = selectAll.checked;

            available().forEach(function (record, index) {
                record.checkbox.checked = checked && index < LIMIT;
            });

            sync();
        });

        clear.addEventListener('click', function () {
            records.forEach(record => record.checkbox.checked = false);
            sync();
        });

        remove.addEventListener('click', async function () {
            const picked = selected();

            if (operationRunning || !picked.length || picked.length > LIMIT) return;

            if (!window.PpidPopup) {
                results.hidden = false;
                results.textContent = 'Popup belum tersedia. Muat ulang halaman.';
                return;
            }

            operationRunning = true;
            busy = true;
            sync();

            let completed = 0;
            const failures = [];
            let stopped = false;

            try {
                const accepted = await window.PpidPopup.confirm(
                    'Hapus ' + picked.length + ' data terpilih?\n\n' +
                    'Data diproses satu per satu. Data yang masih digunakan ' +
                    'atau memiliki turunan dapat ditolak oleh sistem.\n\n' +
                    'Untuk media, penghapusan juga mengikuti proses hapus file yang berlaku.',
                    {
                        title: 'Hapus data terpilih?',
                        confirmText: 'Ya, hapus terpilih',
                        danger: true
                    }
                );

                if (!accepted) return;

                results.hidden = false;
                results.textContent = 'Memulai penghapusan…';

                const wasInert = table.inert;
                table.inert = true;

                try {
                    for (let index = 0; index < picked.length; index++) {
                        const record = picked[index];

                        if (!record.form.isConnected) {
                            failures.push(record.label + ': baris sudah berubah.');
                            continue;
                        }

                        results.textContent =
                            'Memproses ' + (index + 1) + ' dari ' + picked.length +
                            '. Jangan tutup halaman.';

                        const data = new FormData(record.form);

                        if (record.submitter?.name) {
                            data.append(record.submitter.name, record.submitter.value);
                        }

                        let response;
                        let payload;

                        try {
                            response = await fetch(record.form.action, {
                                method: 'POST',
                                body: data,
                                credentials: 'same-origin',
                                redirect: 'error',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-PPID-Bulk': '1'
                                }
                            });

                            payload = await response.json();
                        } catch (error) {
                            record.checkbox.checked = false;
                            record.checkbox.disabled = true;

                            failures.push(
                                record.label +
                                ': hasil belum diketahui karena koneksi/respons bermasalah. ' +
                                'Muat ulang sebelum mencoba lagi.'
                            );

                            stopped = true;
                            break;
                        }

                        if (response.ok &&
                            payload.ppid_bulk === true &&
                            payload.ok === true) {
                            completed++;
                            record.row.remove();
                            continue;
                        }

                        if (payload.ppid_bulk === true && payload.uncertain) {
                            record.checkbox.checked = false;
                            record.checkbox.disabled = true;
                            failures.push(record.label + ': ' + payload.message);
                            stopped = true;
                            break;
                        }

                        if (response.ok || response.status >= 500) {
                            record.checkbox.checked = false;
                            record.checkbox.disabled = true;

                            failures.push(
                                record.label +
                                ': hasil belum dapat dipastikan. Muat ulang halaman.'
                            );

                            stopped = true;
                            break;
                        }

                        failures.push(
                            record.label + ': ' +
                            (payload.message || 'Data tidak dapat dihapus.')
                        );

                        if ([401, 403, 419, 429].includes(response.status)) {
                            stopped = true;
                            break;
                        }
                    }
                } finally {
                    table.inert = wasInert;
                }

                results.replaceChildren();

                results.append(element(
                    'strong',
                    '',
                    completed + ' data berhasil dihapus.' +
                    (stopped ? ' Proses dihentikan; sebagian data belum diproses.' : '')
                ));

                if (failures.length) {
                    const list = element('ul');
                    failures.forEach(message => list.append(element('li', '', message)));
                    results.append(list);
                }

                const reload = element(
                    'button',
                    'ppid-bulk-button',
                    'Muat ulang daftar'
                );

                reload.type = 'button';
                reload.style.marginTop = '12px';
                reload.addEventListener('click', () => location.reload());
                results.append(reload);

            } catch (error) {
                console.error('Bulk delete:', error);
                results.hidden = false;
                results.textContent =
                    'Proses terhenti. Muat ulang daftar untuk memastikan keadaan data.';
            } finally {
                busy = false;
                operationRunning = false;
                sync();
            }
        });

        sync();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.admin-content-inner table')
            .forEach(initTable);
    });

    window.addEventListener('beforeunload', function (event) {
        if (!operationRunning) return;
        event.preventDefault();
        event.returnValue = '';
    });
})();
