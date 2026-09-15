@extends('layouts.admin')
@section('title', 'Atur Akses Pengguna')

@section('content')
<div class="ui-card ui-card-body">
    @include('admin.access._tabs')
    <p>
        <strong>{{ $target->name }}</strong> · {{ $target->email }}<br>
        Unit: {{ $target->unit?->name ?? 'Belum ditentukan' }}
    </p>

    @if ($errors->any())
        <div role="alert"><ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul></div>
    @endif

    <form method="POST" action="{{ route('admin.access.update', $target) }}">
        @csrf
        @method('PUT')

        <div class="assignment-fields">
            <label>
                Tingkat akun
                <select name="role" class="ui-control">
                    @if ($isSuper)
                        <option value="admin" @selected(old('role', $currentRole) === 'admin')>Admin</option>
                    @endif
                    <option value="user" @selected(old('role', $currentRole) === 'user')>User</option>
                </select>
            </label>

            <label>
                Pemberi akses
                <select name="parent_user_id" class="ui-control">
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}"
                            @selected((string) old('parent_user_id', $profile?->parent_user_id ?? auth()->id()) === (string) $parent->id)>
                            {{ $parent->name }} — {{ $parent->email }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Role akses
                <select name="access_role_id" class="ui-control" required>
                    <option value="">Pilih role akses</option>
                    @foreach ($accessRoles as $item)
                        <option value="{{ $item->id }}"
                            @selected((string) old('access_role_id', $assignedRoleId) === (string) $item->id)>
                            {{ $item->name }} — {{ $item->owner_name }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <label style="display:flex;align-items:center;gap:8px">
            <input type="hidden" name="is_enabled" value="0">
            <input type="checkbox" name="is_enabled" value="1"
                   @checked(old('is_enabled', $profile?->is_enabled ?? true))>
            Aktifkan akses pengguna
        </label>

        <p style="margin-top:20px">
            Permission dan cakupan mengikuti role akses.
            Kewenangan pemberi akses tetap menjadi batasnya.
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:20px">
            <button class="ui-btn ui-btn-md ui-btn-primary">Simpan Akses</button>
            <a class="ui-btn ui-btn-md ui-btn-secondary"
               href="{{ route('admin.access.index') }}">Kembali</a>
            
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.assignment-fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin:24px 0}
.assignment-fields label{display:grid;gap:8px;font-size:14px}
.assignment-fields select{width:100%;min-height:40px}
@media(max-width:760px){.assignment-fields{grid-template-columns:1fr}}
</style>
@endpush
