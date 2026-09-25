@extends('layouts.admin')
@section('title', 'Pengajuan Berita')

@section('content')
@php
    $actor = auth()->user();
    $labels = [
        'draft' => 'Draf',
        'submitted' => 'Diajukan',
        'rejected' => 'Ditolak',
        'approved' => 'Disetujui / terbit',
    ];
    $actions = [
        'create' => 'Membuat berita',
        'update' => 'Menyimpan perbaikan',
        'submit' => 'Mengajukan',
        'reject' => 'Menolak',
        'publish' => 'Menerbitkan / memperbarui publikasi',
        'unpublish' => 'Menarik publikasi',
    ];
@endphp

<x-ui.page-header title="Pengajuan Berita" :description="$news->title">
    <x-slot:actions>
        <x-ui.button :href="route('admin.news.index')" variant="secondary">
            Kembali
        </x-ui.button>
        @if ($policy->canWrite($actor, $news))
            <x-ui.button :href="route('admin.news.edit', $news)" variant="primary">
                Perbaiki Berita
            </x-ui.button>
        @endif
    </x-slot:actions>
</x-ui.page-header>

@if ($errors->any())
    <div role="alert" class="ui-card ui-card-body">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<x-ui.card>
    <p><strong>Status:</strong> {{ $labels[$news->editorial_state] ?? $news->editorial_state }}</p>
    <p>
        <strong>Penulis:</strong> {{ $news->author?->name ?? '—' }}
        · <strong>Unit:</strong> {{ $news->unit?->name ?? '—' }}
    </p>

    @if ($news->rejection_reason)
        <strong>Alasan penolakan</strong>
        <p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $news->rejection_reason }}</p>
    @endif

    @if ($news->editorial_state === 'submitted')
        <p>Menunggu pemeriksaan. Penulis dapat memperbaiki setelah berita dikembalikan.</p>
    @elseif ($news->revision_required)
        <p>Perbaiki dan simpan berita sebelum mengajukan kembali.</p>
    @endif

    <div class="table-actions">
        @if ($policy->canSubmit($actor, $news))
            <form method="POST" action="{{ route('admin.news.submit', $news) }}">
                @csrf
                <input type="hidden" name="editorial_version" value="{{ $news->editorial_version }}">
                <x-ui.button type="submit" variant="primary">
                    {{ $news->editorial_state === 'rejected' ? 'Ajukan Kembali' : 'Ajukan Berita' }}
                </x-ui.button>
            </form>
        @endif

        @if ($policy->canPublish($actor, $news))
            <form method="POST" action="{{ route('admin.news.publish', $news) }}">
                @csrf
                <input type="hidden" name="editorial_version" value="{{ $news->editorial_version }}">
                <x-ui.button type="submit" variant="primary"
                             data-confirm="Terbitkan berita ini sekarang?">
                    Terbitkan Sekarang
                </x-ui.button>
            </form>
        @endif
    </div>

    @if ($policy->canReject($actor, $news))
        <form method="POST" action="{{ route('admin.news.reject', $news) }}"
              style="margin-top:20px">
            @csrf
            <input type="hidden" name="editorial_version" value="{{ $news->editorial_version }}">
            <label for="rejection-reason">Alasan penolakan dan perbaikan yang diperlukan</label>
            <textarea id="rejection-reason" name="reason" class="ui-control"
                      required maxlength="4000" rows="4"
                      style="display:block;width:100%;margin:8px 0 12px">{{ old('reason') }}</textarea>
            <x-ui.button type="submit" variant="danger">
                Tolak dan Kembalikan
            </x-ui.button>
        </form>
    @endif
</x-ui.card>

<div style="margin-top:20px">
    <x-ui.card>
        <h2>{{ $news->title }}</h2>
        <div style="overflow-wrap:anywhere;overflow-x:auto">
            {!! $news->content !!}
        </div>
    </x-ui.card>
</div>

<div style="margin-top:20px">
    <x-ui.card>
        <h2>Riwayat Pemeriksaan</h2>
        <x-ui.table>
            <thead>
                <tr>
                    <th>Waktu</th><th>Pengguna</th>
                    <th>Tindakan</th><th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $event)
                    <tr>
                        <td>{{ $event->created_at }}</td>
                        <td>{{ $event->actor_name ?? 'Akun tidak tersedia' }}</td>
                        <td>{{ $actions[$event->action] ?? $event->action }}</td>
                        <td style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $event->reason ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada riwayat sejak fitur ini dipasang.</td></tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="table-actions" style="margin-top:12px">
            @if ($history->previousPageUrl())
                <x-ui.button :href="$history->previousPageUrl()" variant="secondary">
                    Sebelumnya
                </x-ui.button>
            @endif
            @if ($history->nextPageUrl())
                <x-ui.button :href="$history->nextPageUrl()" variant="secondary">
                    Berikutnya
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>
</div>
@endsection