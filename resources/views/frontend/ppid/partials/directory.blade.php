<section class="ppid-directory">
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

                                        <colgroup data-ppid-columns>
                        <col style="width:19%">
                        <col style="width:13%">
                        <col style="width:13%">
                        <col style="width:12%">
                        <col style="width:11%">
                        <col style="width:6%">
                        <col style="width:9%">
                        <col style="width:9%">
                        <col style="width:8%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Nama Informasi</th>
                            <th scope="col">Penguasa Informasi</th>
                            <th scope="col">Penanggung Jawab</th>
                            <th scope="col">Ketersediaan</th>
                            <th scope="col">Bentuk Informasi</th>
                            <th scope="col">Tahun</th>
                            <th scope="col">Jangka Retensi</th>
                            <th scope="col">Satuan Retensi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($informations as $information)
                            @php
                                $documents = in_array(
                                    $information->availability,
                                    ['online', 'both'],
                                    true
                                )
                                    ? $information->documents->filter(
                                        fn ($document) =>
                                            $document->media
                                            && $document->media->is_public
                                            && $document->media->disk === 'public'
                                            && filled($document->media->path)
                                    )
                                    : collect();

                                $availabilityLabel = [
                                    'online' => 'Online',
                                    'by_request' => 'Melalui Permohonan',
                                    'both' => 'Online & Permohonan',
                                ][$information->availability] ?? '—';

                                $formLabel = [
                                    'digital' => 'Digital',
                                    'print' => 'Cetak',
                                    'both' => 'Digital & Cetak',
                                ][$information->information_form] ?? '—';

                                $retentionLabel = [
                                    'day' => 'Hari',
                                    'month' => 'Bulan',
                                    'year' => 'Tahun',
                                    'permanent' => 'Permanen',
                                ][$information->retention_unit] ?? '—';
                            @endphp

                            <tr>
                                <th scope="row">
                                    {{ $information->title }}
                                </th>
                                <td>
                                    {{ $information->information_holder ?: '—' }}
                                </td>
                                <td>
                                    {{ $information->person_in_charge ?: '—' }}
                                </td>
                                <td>{{ $availabilityLabel }}</td>
                                <td>{{ $formLabel }}</td>
                                <td>{{ $information->year ?? '—' }}</td>
                                <td>
                                    {{ $information->retention_unit === 'permanent'
                                        ? '—'
                                        : ($information->retention_period ?? '—') }}
                                </td>
                                <td>{{ $retentionLabel }}</td>
                                <td>
                                    @if ($documents->count() === 1)
                                        <a class="ppid-directory-document"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($documents->first()->media->path) }}"
                                           aria-label="Lihat dokumen {{ $information->title }} (tab baru)">
                                            Lihat
                                        </a>
                                    @elseif ($documents->isNotEmpty())
                                        <details class="ppid-directory-documents">
                                            <summary>Lihat</summary>
                                            <ul>
                                                @foreach ($documents as $document)
                                                    <li>
                                                        <a target="_blank"
                                                           rel="noopener noreferrer"
                                                           href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($document->media->path) }}">
                                                            {{ $document->title ?: $document->media->original_name }}
                                                            <span>(tab baru)</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </details>
                                    @elseif (in_array(
                                        $information->availability,
                                        ['by_request', 'both'],
                                        true
                                    ))
                                        <span>Melalui Permohonan</span>
                                    @else
                                        <span>Dokumen belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="ppid-directory-empty">
                                    Tidak ada informasi yang sesuai dengan filter Anda.
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