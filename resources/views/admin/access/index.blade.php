@extends('layouts.admin')

@section('title', 'Akses Pengguna')

@section('content')
<div class="ui-card ui-card-body">
    @include('admin.access._tabs')

    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin:20px 0">
        <input type="search" name="search" value="{{ $search }}"
               class="ui-control" placeholder="Cari nama atau email"
               aria-label="Cari pengguna" maxlength="150">
        <button class="ui-btn ui-btn-md ui-btn-primary">Cari</button>
    </form>

    <div style="overflow-x:auto">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Unit</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->name }}</strong><br>
                            {{ $item->email }}
                        </td>
                        <td>{{ $item->unit?->name ?? 'Belum ditentukan' }}</td>
                        <td>{{ $accessService->role($item) ?? 'Belum ditetapkan' }}</td>
                        <td>
                            @if (
                                (int) $item->id !== 2
                                && (int) $item->id !== (int) auth()->id()
                                && $accessService->role($item) !== 'super-admin'
                                && $accessService->allowsUnit(
                                    auth()->user(),
                                    'access.assign',
                                    $item->unit_id
                                )
                            )
                                <a href="{{ route('admin.access.edit', $item) }}"
                                   class="ui-btn ui-btn-sm ui-btn-secondary">
                                    Atur Akses
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada pengguna yang dapat ditampilkan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;align-items:center;gap:12px;margin-top:20px">
        @if ($users->previousPageUrl())
            <a class="ui-btn ui-btn-md ui-btn-secondary"
               href="{{ $users->previousPageUrl() }}">Sebelumnya</a>
        @endif
        <span>{{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
        @if ($users->nextPageUrl())
            <a class="ui-btn ui-btn-md ui-btn-secondary"
               href="{{ $users->nextPageUrl() }}">Berikutnya</a>
        @endif
    </div>
</div>
@endsection
