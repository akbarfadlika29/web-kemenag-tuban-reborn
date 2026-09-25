@extends('layouts.admin')
@section('title', 'Role & Permission')

@section('content')
<div class="ui-card ui-card-body">
    @include('admin.access._tabs')

    <div class="role-actions">
        @if ($accessService->allows(auth()->user(), 'roles.create'))
            <a class="ui-btn ui-btn-md ui-btn-primary"
               href="{{ route('admin.access-roles.index', ['new' => 1]) }}">Tambah Role</a>
        @endif
        
    </div>

    @if ($errors->any())
        <div role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($showForm)
        <form method="POST"
              action="{{ $editing
                  ? route('admin.access-roles.update', $editing->id)
                  : route('admin.access-roles.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="role-fields">
                <label>
                    Nama role
                    <input class="ui-control" name="name" required maxlength="100"
                           value="{{ old('name', $editing?->name) }}">
                </label>
                <label>
                    Keterangan
                    <input class="ui-control" name="description" maxlength="2000"
                           value="{{ old('description', $editing?->description) }}">
                </label>
            </div>

            <label class="role-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $editing?->is_active ?? true))>
                Role aktif
            </label>

            <p>Perubahan ini berlaku bagi semua pengguna role tersebut.
               Cakupan unit sendiri mengikuti unit masing-masing pengguna.</p>

            @php
                $oldScopes = old('scopes');
                $oldDelegable = old('delegable', []);
                $oldDelegable = is_array($oldDelegable)
                    ? array_map('strval', $oldDelegable) : [];
            @endphp

            @foreach ($permissions as $module => $items)
                <section class="role-module">
                    <h2>{{ $modules[$module]['label'] ?? $module }}</h2>
                    @foreach ($items as $permission)
                        @php
                            $existing = $selected->get($permission->id);
                            $scope = is_array($oldScopes)
                                ? ($oldScopes[$permission->id] ?? 'none')
                                : ($existing?->data_scope ?? 'none');
                            $delegate = is_array($oldScopes)
                                ? in_array((string) $permission->id, $oldDelegable, true)
                                : (bool) ($existing?->can_delegate ?? false);
                            $policyScopes = \App\Support\PermissionPolicy::allowedScopes(
                                $permission->slug
                            );
                        @endphp
                        <div class="role-permission">
                            <label for="scope-{{ $permission->id }}">
                                {{ $permission->name }}
                            </label>

                            <select id="scope-{{ $permission->id }}"
                                    class="ui-control"
                                    name="scopes[{{ $permission->id }}]">
                                <option value="none" @selected($scope === 'none')>Tidak diberikan</option>
                                <option value="own_unit"
                                        @selected($scope === 'own_unit')
                                        @disabled(!in_array('own_unit', $policyScopes, true))>
                                    Unit sendiri
                                </option>
                                <option value="all_units"
                                        @selected($scope === 'all_units')
                                        @disabled(!in_array('all_units', $policyScopes, true))>
                                    Seluruh unit
                                </option>
                            </select>

                            <label class="role-check">
                                <input type="checkbox" name="delegable[]"
                                       value="{{ $permission->id }}" @checked($delegate)>
                                Boleh didelegasikan
                            </label>
                        </div>
                    @endforeach
                </section>
            @endforeach

            <p>Delegasi hanya efektif untuk akun bertingkat Admin.
               Pengguna bertingkat User tidak dapat meneruskan akses.</p>

            <div class="role-actions">
                <button class="ui-btn ui-btn-md ui-btn-primary">Simpan Role</button>
                <a class="ui-btn ui-btn-md ui-btn-secondary"
                   href="{{ route('admin.access-roles.index') }}">Tutup Formulir</a>
            </div>
        </form>
    @endif

    <div style="overflow-x:auto;margin-top:24px">
        <table class="ui-table">
            <thead><tr>
                <th>Role Akses</th><th>Pemilik</th><th>Pengguna</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            @forelse ($roles as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->owner_name }}</td>
                    <td>{{ $item->users_count }}</td>
                    <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        @if ($accessService->allows(auth()->user(), 'roles.update'))
                            <a class="ui-btn ui-btn-sm ui-btn-secondary"
                               href="{{ route('admin.access-roles.index', ['edit' => $item->id]) }}">
                                Edit
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada role akses.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="role-actions">
        @if ($roles->previousPageUrl())
            <a class="ui-btn ui-btn-md ui-btn-secondary" href="{{ $roles->previousPageUrl() }}">Sebelumnya</a>
        @endif
        <span>{{ $roles->currentPage() }} / {{ $roles->lastPage() }}</span>
        @if ($roles->nextPageUrl())
            <a class="ui-btn ui-btn-md ui-btn-secondary" href="{{ $roles->nextPageUrl() }}">Berikutnya</a>
        @endif
    </div>
</div>
@include('admin.access._permission_controls')
@endsection

@push('styles')
<style>
.role-actions{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin:20px 0}
.role-fields{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:20px 0}
.role-fields label{display:grid;gap:8px}
.role-check{display:flex;align-items:center;gap:8px;font-size:13px}
.role-check input[type=checkbox]{width:18px;height:18px;accent-color:#247052}
.role-module{border:1px solid #dfe7e2;border-radius:10px;overflow:hidden;margin:20px 0}
.role-module h2{padding:14px 18px;margin:0;background:#f3f7f4;font-size:16px}
.role-permission{display:grid;grid-template-columns:minmax(0,1fr) 170px 185px;gap:16px;align-items:center;padding:12px 18px;border-top:1px solid #edf1ee;font-size:14px}
.role-permission select,.role-fields input{width:100%;min-height:40px}
@media(max-width:760px){.role-fields,.role-permission{grid-template-columns:1fr}}
</style>
@endpush
