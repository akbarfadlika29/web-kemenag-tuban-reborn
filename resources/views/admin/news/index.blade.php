{{-- PPID_NEWS_ACTION_VISIBILITY_V2 --}}
@php
    $newsActionAccess = app(\App\Services\Access\AccessService::class);
    $newsActionActor = auth()->user();

    $newsActionAllowed = function (
        string $permission,
        $record = null
    ) use ($newsActionAccess, $newsActionActor): bool {
        if (!$newsActionActor || !$newsActionActor->is_active) {
            return false;
        }

        if ($record === null) {
            return $newsActionAccess->allows($newsActionActor, $permission);
        }

        if (in_array($permission, ['news.update', 'news.delete'], true)) {
            return app(\App\Services\Access\NewsEditorialPolicy::class)
                ->canWrite(
                    $newsActionActor,
                    $record,
                    $permission === 'news.delete' ? 'delete' : 'update'
                );
        }

        return $newsActionAccess->allowsUnit(
            $newsActionActor, $permission, $record->unit_id
        );
    };
@endphp
@extends('layouts.admin')

@section('title', 'Berita')

@section('content')
    <x-ui.page-header
        title="Berita"
        description="Kelola berita dan pengaturan publikasinya."
    >
        <x-slot:actions>
            <div class="news-header-actions">
                @if ($newsActionAllowed('news-categories.view'))
<x-ui.button
                    :href="route('admin.news-categories.index')"
                    variant="secondary"
                >
                    Kategori
                </x-ui.button>
@endif

                @if ($newsActionAllowed('news-tags.view'))
<x-ui.button
                    :href="route('admin.news-tags.index')"
                    variant="secondary"
                >
                    Tag
                </x-ui.button>
@endif

                @if ($newsActionAllowed('news.create'))
<x-ui.button
                    :href="route('admin.news.create')"
                    variant="primary"
                >
                    Tambah Berita
                </x-ui.button>
@endif
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-5">
        <form
            method="GET"
            action="{{ route('admin.news.index') }}"
        >
            <div class="news-filter-grid">
                <x-form.input
                    name="search"
                    label="Pencarian"
                    :value="$search"
                    placeholder="Judul, ringkasan, atau slug..."
                />

                <x-form.select
                    name="status"
                    label="Status"
                >
                    <option value="">
                        Semua Status
                    </option>

                    <option value="submitted" @selected($status === 'submitted')>Diajukan</option>
                    <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                    <option value="draft"
                        @selected($status === 'draft')
                    >
                        Draft
                    </option>

                    <option
                        value="published"
                        @selected($status === 'published')
                    >
                        Dipublikasikan
                    </option>

                    <option
                        value="archived"
                        @selected($status === 'archived')
                    >
                        Diarsipkan
                    </option>
                </x-form.select>

                <x-form.select
                    name="category_id"
                    label="Kategori"
                >
                    <option value="">
                        Semua Kategori
                    </option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(
                                (string) $categoryId === (string) $category->id
                            )
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    name="tag_id"
                    label="Tag"
                >
                    <option value="">
                        Semua Tag
                    </option>

                    @foreach ($tags as $tag)
                        <option
                            value="{{ $tag->id }}"
                            @selected(
                                (string) $tagId === (string) $tag->id
                            )
                        >
                            {{ $tag->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    name="unit_id"
                    label="Unit Kerja"
                >
                    <option value="">
                        Semua Unit
                    </option>

                    @foreach ($units as $unit)
                        <option
                            value="{{ $unit->id }}"
                            @selected(
                                (string) $unitId === (string) $unit->id
                            )
                        >
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    name="featured"
                    label="Unggulan"
                >
                    <option value="">
                        Semua
                    </option>

                    <option
                        value="1"
                        @selected($featured === '1')
                    >
                        Unggulan
                    </option>

                    <option
                        value="0"
                        @selected($featured === '0')
                    >
                        Biasa
                    </option>
                </x-form.select>

                <div class="news-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Filter
                    </x-ui.button>

                    <x-ui.button
                        :href="route('admin.news.index')"
                        variant="secondary"
                    >
                        Atur Ulang
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>

    <div class="news-list-summary">
        <div>
            <strong>{{ number_format($news->total()) }}</strong>
            berita ditemukan
        </div>

        @if (
            $search !== ''
            || $status !== ''
            || $categoryId !== ''
            || $tagId !== ''
            || $unitId !== ''
            || $featured !== ''
        )
            <span class="news-filter-active">
                Filter aktif
            </span>
        @endif
    </div>

    <x-ui.card :padding="false">
        @if ($news->isEmpty())
            <x-ui.empty-state
                title="Belum ada berita"
                description="Belum ada berita yang sesuai dengan data atau filter yang dipilih."
            >
                <x-slot:action>
                    @if ($newsActionAllowed('news.create'))
<x-ui.button
                        :href="route('admin.news.create')"
                        variant="primary"
                    >
                        Tambah Berita
                    </x-ui.button>
@endif
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <div class="news-table-wrapper">
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Berita</th>
                            <th>Kategori</th>
                            <th>Tag</th>
                            <th>Unit Kerja</th>
                            <th>Status</th>
                            <th>Publikasi</th>
                            <th>Unggulan</th>
                            <th>Views</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($news as $item)
                            <tr>
                                <td>
                                    <div class="news-title-cell">
                                        @if ($item->coverMedia)
                                            <img
                                                src="{{ asset('storage/' . $item->coverMedia->path) }}"
                                                alt="{{ $item->coverMedia->alt_text ?: $item->title }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="news-placeholder">
                                                NEWS
                                            </div>
                                        @endif

                                        <div class="news-title-info">
                                            <strong class="news-title">
                                                {{ $item->title }}
                                            </strong>

                                            <div class="news-slug">
                                                /{{ $item->slug }}
                                            </div>

                                            <div class="news-meta-line">
                                                <span>
                                                    {{ $item->reading_time }} menit baca
                                                </span>

                                                @if ($item->author)
                                                    <span>
                                                        {{ $item->author->name }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($item->excerpt)
                                                <div class="news-excerpt">
                                                    {{ \Illuminate\Support\Str::limit($item->excerpt, 100) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($item->category)
                                        <x-ui.badge variant="info">
                                            {{ $item->category->name }}
                                        </x-ui.badge>
                                    @else
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="news-tag-list">
                                        @forelse ($item->tags->take(3) as $tag)
                                            <x-ui.badge variant="neutral">
                                                {{ $tag->name }}
                                            </x-ui.badge>
                                        @empty
                                            <span class="text-muted">
                                                -
                                            </span>
                                        @endforelse

                                        @if ($item->tags->count() > 3)
                                            <span class="news-tag-more">
                                                +{{ $item->tags->count() - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="news-unit">
                                        {{ $item->unit?->name ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    @if ($item->status === 'draft' && in_array(
                                        $item->editorial_state, ['submitted', 'rejected'], true
                                    ))
                                        <x-ui.badge variant="warning">
                                            {{ $item->editorial_state === 'submitted' ? 'Diajukan' : 'Ditolak' }}
                                        </x-ui.badge>
                                    @else
                                    @switch($item->publication_state)
                                        @case('published')
                                            <x-ui.badge variant="success">
                                                Dipublikasikan
                                            </x-ui.badge>
                                            @break

                                        @case('scheduled')
                                            <x-ui.badge variant="warning">
                                                Terjadwal
                                            </x-ui.badge>
                                            @break

                                        @case('archived')
                                            <x-ui.badge variant="neutral">
                                                Diarsipkan
                                            </x-ui.badge>
                                            @break

                                        @default
                                            <x-ui.badge variant="warning">
                                                Draft
                                            </x-ui.badge>
                                    @endswitch
                                    @endif
                                </td>

                                <td>
                                    @if ($item->published_at)
                                        <div class="news-date">
                                            {{ $item->published_at->format('d/m/Y') }}
                                        </div>

                                        <div class="news-time">
                                            {{ $item->published_at->format('H:i') }}
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($item->is_featured)
                                        <x-ui.badge variant="info">
                                            Unggulan
                                        </x-ui.badge>
                                    @else
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="news-view-count">
                                        {{ number_format($item->view_count) }}
                                    </div>
                                </td>

                                <td>
                                    <div class="table-actions">
                                        <x-ui.button
                                            :href="route('admin.news.editorial', $item)"
                                            size="sm"
                                            variant="secondary"
                                        >
                                            Pengajuan
                                        </x-ui.button>
                                        @if ($newsActionAllowed('news.update', $item))
<x-ui.button
                                            :href="route('admin.news.edit', $item)"
                                            size="sm"
                                            variant="secondary"
                                        >
                                            Ubah
                                        </x-ui.button>
@endif

                                        @if ($newsActionAllowed('news.delete', $item))
<form
                                            method="POST"
                                            action="{{ route('admin.news.destroy', $item) }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <x-ui.button
                                                type="submit"
                                                variant="danger"
                                                size="sm"
                                                data-confirm="Yakin ingin menghapus berita ini?"
                                            >
                                                Hapus
                                            </x-ui.button>
                                        </form>
@endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            </div>
        @endif
    </x-ui.card>

    @if ($news->hasPages())
        <div class="news-pagination">
            {{ $news->links() }}
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .news-header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .news-filter-grid {
            display: grid;
            grid-template-columns:
                minmax(220px, 2fr)
                repeat(5, minmax(135px, 1fr))
                auto;
            gap: 12px;
            align-items: end;
        }

        .news-filter-grid .ui-form-group {
            margin-bottom: 0;
        }

        .news-filter-actions {
            display: flex;
            gap: 8px;
        }

        .news-list-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            margin-bottom: 12px;

            color: #667085;
            font-size: 13px;
        }

        .news-list-summary strong {
            color: #344054;
        }

        .news-filter-active {
            display: inline-flex;
            align-items: center;

            padding: 4px 8px;

            border-radius: 999px;

            background: #eff6ff;

            color: #175cd3;
            font-size: 11px;
            font-weight: 700;
        }

        .news-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .news-title-cell {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            min-width: 340px;
            max-width: 500px;
        }

        .news-title-cell img,
        .news-placeholder {
            width: 76px;
            height: 54px;

            flex-shrink: 0;

            border-radius: 8px;

            object-fit: cover;
        }

        .news-title-cell img {
            border: 1px solid #e4e7ec;

            background: #f2f4f7;
        }

        .news-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;

            border: 1px solid #e1e7ef;

            background: #eef2f7;

            color: #667085;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .news-title-info {
            min-width: 0;
        }

        .news-title {
            display: block;

            color: #101828;
            line-height: 1.4;
        }

        .news-slug {
            margin-top: 3px;

            color: #98a2b3;
            font-size: 11px;

            word-break: break-word;
        }

        .news-meta-line {
            display: flex;
            flex-wrap: wrap;
            gap: 5px 12px;

            margin-top: 5px;

            color: #667085;
            font-size: 11px;
        }

        .news-meta-line span + span::before {
            content: "•";

            margin-right: 8px;

            color: #d0d5dd;
        }

        .news-excerpt {
            margin-top: 5px;

            color: #667085;
            font-size: 12px;
            line-height: 1.45;
        }

        .news-tag-list {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;

            min-width: 130px;
            max-width: 230px;
        }

        .news-tag-more {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 22px;
            padding: 2px 6px;

            border-radius: 999px;

            background: #f2f4f7;

            color: #667085;
            font-size: 10px;
            font-weight: 700;
        }

        .news-unit {
            min-width: 150px;

            color: #475467;
            font-size: 12px;
            line-height: 1.4;
        }

        .news-date {
            white-space: nowrap;

            color: #344054;
            font-size: 13px;
            font-weight: 600;
        }

        .news-time {
            margin-top: 2px;

            color: #98a2b3;
            font-size: 11px;
        }

        .news-view-count {
            min-width: 50px;

            color: #475467;
            font-variant-numeric: tabular-nums;
        }

        .news-pagination {
            margin-top: 24px;
        }

        @media (max-width: 1350px) {
            .news-filter-grid {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .news-filter-actions {
                align-self: end;
            }
        }

        @media (max-width: 850px) {
            .news-filter-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .news-header-actions {
                width: 100%;
            }

            .news-filter-grid {
                grid-template-columns: 1fr;
            }

            .news-filter-actions {
                flex-wrap: wrap;
            }

            .news-list-summary {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush