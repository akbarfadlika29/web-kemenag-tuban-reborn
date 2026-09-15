@extends('layouts.admin')

@section('content')
<div class="ui-card ui-card-body">
    <h1>Statistik Konten</h1>
    <p>Baca dihitung sekali per sesi per hari. Suka dihitung per sesi browser.</p>

    <div class="statistics-summary">
        @foreach ([
            'views' => 'Total baca',
            'likes' => 'Total suka',
            'pages' => 'Konten berinteraksi',
            'visitors' => 'Pengunjung',
            'visitors_today' => 'Pengunjung hari ini',
        ] as $key => $label)
            <div>
                <span>{{ $label }}</span>
                <strong>{{ number_format($totals[$key], 0, ',', '.') }}</strong>
            </div>
        @endforeach
    </div>

    <form method="GET" class="statistics-filter">
        <input class="ui-control" type="search" name="search"
               value="{{ $search }}" placeholder="Cari judul atau jenis konten…"
               aria-label="Cari konten" maxlength="200">
        <select class="ui-control" name="sort" aria-label="Urutkan statistik">
            <option value="views" @selected($sort === 'views')>Paling banyak dibaca</option>
            <option value="likes" @selected($sort === 'likes')>Paling banyak disukai</option>
        </select>
        <button class="ui-btn ui-btn-md ui-btn-primary">Terapkan</button>
    </form>

    <div style="overflow-x:auto">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Konten</th>
                    <th>Jenis</th>
                    <th>Dibaca</th>
                    <th>Disukai</th>
                    <th>Diperbarui</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ number_format($item['views'], 0, ',', '.') }}</td>
                        <td>{{ number_format($item['likes'], 0, ',', '.') }}</td>
                        <td>{{ $item['updated'] ?: '—' }}</td>
                        <td>
                            @if ($item['edit'])
                                <a href="{{ $item['edit'] }}"
                                   class="ui-btn ui-btn-sm ui-btn-secondary">
                                    Edit Konten
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada konten berinteraksi yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="statistics-filter">
        @if ($items->previousPageUrl())
            <a class="ui-btn ui-btn-md ui-btn-secondary"
               href="{{ $items->previousPageUrl() }}">Sebelumnya</a>
        @endif

        <span>Halaman {{ $items->currentPage() }} / {{ $items->lastPage() }}</span>

        @if ($items->nextPageUrl())
            <a class="ui-btn ui-btn-md ui-btn-secondary"
               href="{{ $items->nextPageUrl() }}">Berikutnya</a>
        @endif
    </div>

    <p>Ringkasan mencakup seluruh interaksi tersimpan. Tabel menampilkan konten yang masih tersedia.</p>
</div>
@endsection

@push('styles')
<style>
.statistics-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin:24px 0}
.statistics-summary>div{padding:20px;border:1px solid #dfe7e2;border-radius:10px;background:#f7faf8}
.statistics-summary span{display:block;font-size:13px;color:#647169}
.statistics-summary strong{display:block;margin-top:6px;font-size:28px;color:#247052}
.statistics-filter{display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin:20px 0}
.statistics-filter input{flex:1;min-width:180px}
@media(max-width:640px){.statistics-summary{grid-template-columns:1fr}}
</style>
@endpush
