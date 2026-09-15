@if ($showForm)
    @php
        $permissionOwner = $editing
            ? \App\Models\User::find($editing->owner_user_id)
            : auth()->user();

        $permissionLimits = [];

        foreach ($permissions as $permissionItems) {
            foreach ($permissionItems as $permissionItem) {
                $permissionLimits[(string) $permissionItem->id] = [
                    'scope' => $permissionOwner
                        ? $accessService->scope(
                            $permissionOwner,
                            $permissionItem->slug,
                            true
                        )
                        : null,
                    'action' => substr(
                        $permissionItem->slug,
                        strrpos($permissionItem->slug, '.') + 1
                    ),
                ];
            }
        }
    @endphp

    <style>
    .role-permission-tools {
        margin:20px 0;
        padding:16px;
        border:1px solid #dce5df;
        border-radius:10px;
        background:#f7faf8;
    }
    .role-permission-tools .rpc-toolbar {
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        gap:12px;
    }
    .role-permission-tools .rpc-actions {
        display:flex;
        flex-wrap:wrap;
        gap:10px 18px;
        margin:16px 0;
    }
    .role-permission-tools label,
    .role-module .rpc-module-toggle,
    .role-permission .rpc-permission-toggle {
        display:inline-flex;
        align-items:center;
        gap:9px;
        cursor:pointer;
        line-height:1.6;
    }
    .role-permission-tools input[type=checkbox],
    .role-module .rpc-module-toggle input,
    .role-permission .rpc-permission-toggle input {
        width:18px;
        height:18px;
        flex-shrink:0;
        accent-color:#247052;
    }
    .role-module .rpc-module-toggle {
        font-size:13px;
        font-weight:500;
    }
    .role-module h2 {
        display:flex;
        flex-wrap:wrap;
        justify-content:space-between;
        align-items:center;
        gap:12px;
    }
    .role-permission.rpc-unavailable {
        background:#f7f8f9;
    }
    .role-permission .rpc-permission-info {
        display:grid;
        gap:5px;
    }
    .role-permission .rpc-note,
    .role-permission-tools .rpc-status {
        color:#626e67;
        font-size:13px;
        line-height:1.7;
    }
    .role-permission-tools .rpc-status {
        margin:12px 0 0;
    }
    .role-permission-tools select {
        min-height:40px;
        max-width:100%;
    }
    .role-permission-tools input:focus-visible,
    .role-module input:focus-visible {
        outline:3px solid #aac6b6;
        outline-offset:3px;
    }
    </style>

    <script>
    (() => {
        const limits = @json($permissionLimits);

        const start = () => {
            const scopeInputs = Array.from(
                document.querySelectorAll(
                    '.role-permission select[name^="scopes["]'
                )
            );

            if (!scopeInputs.length) return;

            const form = scopeInputs[0].closest('form');
            if (!form || form.dataset.permissionControls === 'ready') return;

            form.dataset.permissionControls = 'ready';

            const actionNames = {
                view: 'Lihat',
                create: 'Tambah',
                update: 'Ubah',
                delete: 'Hapus',
                upload: 'Unggah',
                submit: 'Ajukan',
                review: 'Periksa pengajuan',
                publish: 'Terbitkan',
                unpublish: 'Tarik publikasi',
                assign: 'Tetapkan akses'
            };

            const entries = [];
            const masters = [];

            const makeCheckbox = (text) => {
                const label = document.createElement('label');
                const input = document.createElement('input');
                input.type = 'checkbox';
                const caption = document.createElement('span');
                caption.textContent = text;
                label.append(input, caption);
                return {label, input};
            };

            scopeInputs.forEach(select => {
                const match = select.name.match(/^scopes\[(\d+)\]$/);
                if (!match) return;

                const id = match[1];
                const rule = limits[id] || {scope:null, action:''};
                const row = select.closest('.role-permission');
                const module = row.closest('.role-module');
                const originalLabel = row.querySelector('label');
                const delegate = row.querySelector(
                    'input[name="delegable[]"]'
                );

                if (!originalLabel || !delegate) return;

                const title = originalLabel.textContent.trim();
                const permission = makeCheckbox(title);
                permission.label.className = 'rpc-permission-toggle';

                const info = document.createElement('div');
                info.className = 'rpc-permission-info';
                info.append(permission.label);
                originalLabel.replaceWith(info);

                select.setAttribute('aria-label', 'Cakupan: ' + title);

                const allowed = ['own_unit','all_units'].includes(rule.scope);
                const originalScope = select.value;
                const stale = originalScope !== 'none' && (
                    !allowed ||
                    (originalScope === 'all_units' && rule.scope !== 'all_units')
                );

                // Cakupan yang sudah melampaui kewenangan perlu dipilih ulang.
                // Tidak ada perubahan database sampai Simpan Role ditekan.
                if (stale) {
                    select.value = 'none';
                    delegate.checked = false;

                    const note = document.createElement('span');
                    note.className = 'rpc-note';
                    note.textContent =
                        'Izin tersimpan melampaui kewenangan saat ini. ' +
                        'Pilih ulang bila tersedia; menyimpan formulir akan ' +
                        'mengganti pengaturan lama.';
                    info.append(note);
                }

                Array.from(select.options).forEach(option => {
                    if (option.value === 'all_units') {
                        option.disabled = rule.scope !== 'all_units';
                    }
                    if (option.value === 'own_unit') {
                        option.disabled = !allowed;
                    }
                });

                permission.input.checked = select.value !== 'none';
                permission.input.disabled = !allowed;

                if (!allowed) {
                    row.classList.add('rpc-unavailable');

                    const note = document.createElement('span');
                    note.className = 'rpc-note';
                    note.textContent =
                        'Tidak tersedia dalam kewenangan delegasi pemilik role.';
                    info.append(note);
                }

                const entry = {
                    select,
                    checkbox: permission.input,
                    delegate,
                    module,
                    allowed,
                    limit: rule.scope,
                    action: rule.action,
                    lastScope: select.value === 'none'
                        ? 'own_unit'
                        : select.value
                };

                entries.push(entry);
            });

            if (!entries.length) return;

            const tools = document.createElement('div');
            tools.className = 'role-permission-tools';

            const toolbar = document.createElement('div');
            toolbar.className = 'rpc-toolbar';

            const actionBar = document.createElement('div');
            actionBar.className = 'rpc-actions';

            const scopeBar = document.createElement('div');
            scopeBar.className = 'rpc-toolbar';

            const status = document.createElement('p');
            status.className = 'rpc-status';
            status.setAttribute('role', 'status');
            status.setAttribute('aria-live', 'polite');

            tools.append(toolbar, actionBar, scopeBar, status);
            entries[0].module.before(tools);

            let scopeMessage = '';

            const synchronize = () => {
                entries.forEach(entry => {
                    entry.checkbox.checked = entry.select.value !== 'none';
                    entry.delegate.disabled =
                        !entry.allowed || !entry.checkbox.checked;

                    if (entry.delegate.disabled) {
                        entry.delegate.checked = false;
                    }

                    // Select tetap aktif agar nilai "none" ikut dikirim.
                    entry.select.disabled = false;
                });

                masters.forEach(master => {
                    const available = master.items.filter(item => item.allowed);
                    const count = available.filter(
                        item => item.checkbox.checked
                    ).length;

                    master.input.disabled = available.length === 0;
                    master.input.checked =
                        available.length > 0 && count === available.length;
                    master.input.indeterminate =
                        count > 0 && count < available.length;
                });

                const count = entries.filter(
                    entry => entry.checkbox.checked
                ).length;

                status.textContent =
                    count + ' izin dipilih. ' +
                    'Delegasi tidak ikut dicentang oleh Pilih Semua.' +
                    (scopeMessage ? ' ' + scopeMessage : '');
            };

            const toggleEntry = (entry, checked) => {
                if (!entry.allowed) return;

                if (checked) {
                    entry.select.value =
                        entry.lastScope === 'all_units' &&
                        entry.limit === 'all_units'
                            ? 'all_units'
                            : 'own_unit';
                } else {
                    if (entry.select.value !== 'none') {
                        entry.lastScope = entry.select.value;
                    }
                    entry.select.value = 'none';
                    entry.delegate.checked = false;
                }
            };

            const addMaster = (container, caption, items, className) => {
                const master = makeCheckbox(caption);
                if (className) master.label.className = className;

                masters.push({input:master.input, items});
                container.append(master.label);

                master.input.addEventListener('change', () => {
                    scopeMessage = '';
                    items.forEach(entry => {
                        toggleEntry(entry, master.input.checked);
                    });
                    synchronize();
                });
            };

            addMaster(toolbar, 'Pilih semua izin', entries);

            const clear = document.createElement('button');
            clear.type = 'button';
            clear.className = 'ui-btn ui-btn-md ui-btn-secondary';
            clear.textContent = 'Kosongkan pilihan';

            clear.addEventListener('click', () => {
                scopeMessage = '';
                entries.forEach(entry => toggleEntry(entry, false));
                synchronize();
            });

            toolbar.append(clear);

            const actions = [...new Set(entries.map(entry => entry.action))];

            actions.forEach(action => {
                addMaster(
                    actionBar,
                    actionNames[action] || action,
                    entries.filter(entry => entry.action === action)
                );
            });

            const scopeLabel = document.createElement('label');
            scopeLabel.textContent = 'Cakupan izin terpilih';

            const bulkScope = document.createElement('select');
            bulkScope.className = 'ui-control';

            [
                ['own_unit', 'Unit sendiri'],
                ['all_units', 'Seluruh unit']
            ].forEach(([value, caption]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = caption;
                if (
                    value === 'all_units' &&
                    !entries.some(entry => entry.limit === 'all_units')
                ) {
                    option.disabled = true;
                }
                bulkScope.append(option);
            });

            scopeLabel.append(bulkScope);

            const apply = document.createElement('button');
            apply.type = 'button';
            apply.className = 'ui-btn ui-btn-md ui-btn-secondary';
            apply.textContent = 'Terapkan cakupan';

            apply.addEventListener('click', () => {
                let changed = 0;
                let skipped = 0;

                entries.forEach(entry => {
                    if (!entry.allowed || !entry.checkbox.checked) return;

                    if (
                        bulkScope.value === 'all_units' &&
                        entry.limit !== 'all_units'
                    ) {
                        skipped++;
                        return;
                    }

                    entry.select.value = bulkScope.value;
                    entry.lastScope = bulkScope.value;
                    changed++;
                });

                scopeMessage =
                    'Cakupan diterapkan pada ' + changed + ' izin.' +
                    (skipped
                        ? ' ' + skipped +
                          ' izin tetap pada cakupan sebelumnya karena batas kewenangan.'
                        : '');

                synchronize();
            });

            scopeBar.append(scopeLabel, apply);

            const modules = [...new Set(entries.map(entry => entry.module))];

            modules.forEach(module => {
                const heading = module.querySelector('h2');
                if (!heading) return;

                addMaster(
                    heading,
                    'Semua izin modul',
                    entries.filter(entry => entry.module === module),
                    'rpc-module-toggle'
                );
            });

            entries.forEach(entry => {
                entry.checkbox.addEventListener('change', () => {
                    scopeMessage = '';
                    toggleEntry(entry, entry.checkbox.checked);
                    synchronize();
                });

                entry.select.addEventListener('change', () => {
                    scopeMessage = '';

                    if (!entry.allowed) {
                        entry.select.value = 'none';
                    } else if (
                        entry.select.value === 'all_units' &&
                        entry.limit !== 'all_units'
                    ) {
                        entry.select.value = 'own_unit';
                    }

                    if (entry.select.value !== 'none') {
                        entry.lastScope = entry.select.value;
                    }

                    synchronize();
                });
            });

            form.addEventListener('submit', synchronize);
            synchronize();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', start, {once:true});
        } else {
            start();
        }
    })();
    </script>
@endif