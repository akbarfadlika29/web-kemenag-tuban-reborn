<section class="ppid-directory ppid-exempt-directory">
    <div class="container">
        <x-frontend.breadcrumb :title="$pageTitle" />

        <header class="ppid-directory-heading">
            <h1>{{ $pageTitle }}</h1>
            <p>
                Telusuri informasi dan dokumen publik berdasarkan
                klasifikasi, tahun, atau unit pengelola.
            </p>
        </header>

        <nav class="ppid-directory-tabs"
             aria-label="Klasifikasi informasi">
            @foreach ($classifications as $key => $label)
                <a href="{{ route('ppid.index', ['classification' => $key]) }}"
                   @if ($classification === $key) aria-current="page" @endif>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="ppid-directory-panel">
            <form method="GET"
                  action="{{ route('ppid.index') }}"
                  class="ppid-directory-filters">
                <input type="hidden"
                       name="classification"
                       value="{{ $classification }}">

                <label>
                    Tampilkan
                    <select name="per_page">
                        @foreach ([
                            '20' => '20',
                            '50' => '50',
                            '100' => '100',
                            'all' => 'Semua',
                        ] as $value => $label)
                            <option value="{{ $value }}"
                                    @selected($perPage === (string) $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="ppid-directory-search">
                    Cari informasi
                    <input type="search"
                           name="search"
                           value="{{ $search }}"
                           maxlength="200"
                           placeholder="Nama informasi atau penanggung jawab">
                </label>

                <label>
                    Tahun
                    <select name="year">
                        <option value="">Semua tahun</option>
                        @foreach ($years as $item)
                            <option value="{{ $item }}"
                                    @selected((string) $year === (string) $item)>
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Unit pengelola
                    <select name="unit_id">
                        <option value="">Semua unit</option>
                        @foreach ($units as $item)
                            <option value="{{ $item->id }}"
                                    @selected((string) $unitId === (string) $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit">Terapkan</button>

                <a class="ppid-directory-reset"
                   href="{{ route('ppid.index', ['classification' => $classification]) }}">
                    Atur ulang
                </a>
            </form>

            <p class="ppid-directory-hint" id="ppid-table-hint">
                Pada layar kecil, geser tabel ke samping untuk melihat
                seluruh kolom.
            </p>

            <div class="ppid-directory-scroll"
                 tabindex="0"
                 role="region"
                 aria-label="Tabel informasi publik"
                 aria-describedby="ppid-table-hint">
                <table>
                    <caption class="ppid-directory-caption">
                        {{ $pageTitle }}
                    </caption>

                                        
                    <colgroup>
    <col style="width:49%">
    <col style="width:11%">
    <col style="width:28%">
    <col style="width:12%">
</colgroup>

<thead>
    <tr>
        <th scope="col">Nama SK</th>
        <th scope="col">Tahun</th>
        <th scope="col">Unit Pengelola</th>
        <th scope="col">Aksi</th>
    </tr>
</thead>

<tbody>
    @forelse ($informations as $information)
        @php
            $document = in_array(
                $information->availability,
                ['online', 'both'],
                true
            )
                ? $information->documents->first(
                    fn ($item) =>
                        $item->document_status === 'active'
                        && $item->media
                        && $item->media->is_public
                        && $item->media->disk === 'public'
                        && $item->media->mime_type === 'application/pdf'
                        && filled($item->media->path)
                )
                : null;
        @endphp

        <tr>
            <th scope="row">{{ $information->title }}</th>
            <td>{{ $information->year ?? '—' }}</td>
            <td>{{ $information->unit?->name ?? '—' }}</td>
            <td>
                @if ($document)
                    <a class="ppid-directory-document"
                       href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($document->media->path) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Lihat {{ $information->title }} (PDF, tab baru)">
                        Lihat
                    </a>
                @else
                    <span class="ppid-exempt-empty-document">
                        Belum tersedia
                    </span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="ppid-directory-empty">
                Belum ada SK yang sesuai dengan filter Anda.
            </td>
        </tr>
    @endforelse
</tbody>
                </table>
            </div>

            <footer class="ppid-directory-footer">
                <p>
                    Menampilkan {{ $informations->firstItem() ?? 0 }}–{{ $informations->lastItem() ?? 0 }}
                    dari {{ number_format($informations->total(), 0, ',', '.') }}
                    informasi
                </p>

                @if ($informations->hasPages())
                    <nav aria-label="Halaman informasi">
                        @if ($informations->onFirstPage())
                            <span aria-disabled="true">Sebelumnya</span>
                        @else
                            <a rel="prev"
                               href="{{ $informations->previousPageUrl() }}">
                                Sebelumnya
                            </a>
                        @endif

                        <span aria-current="page">
                            {{ $informations->currentPage() }}
                            /
                            {{ $informations->lastPage() }}
                        </span>

                        @if ($informations->hasMorePages())
                            <a rel="next"
                               href="{{ $informations->nextPageUrl() }}">
                                Berikutnya
                            </a>
                        @else
                            <span aria-disabled="true">Berikutnya</span>
                        @endif
                    </nav>
                @endif
            </footer>
        </div>
    </div>
</section>