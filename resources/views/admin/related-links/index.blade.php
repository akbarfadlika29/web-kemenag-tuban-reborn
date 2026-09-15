@extends('layouts.admin')

@section('title', 'Tautan Terkait')

@section('content')
    <div class="admin-page related-links-page">
        <x-ui.page-header
            title="Tautan Terkait"
            description="Kelola tautan menuju website instansi terkait."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.related-links.create')"
                    variant="primary"
                >
                    Tambah Link
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <section class="related-links-card">
            <header class="related-links-card-header">
                <div>
                    <span>
                        Website Terkait
                    </span>

                    <h2>
                        Daftar Link Terkait
                    </h2>

                    <p>
                        Data berikut nantinya ditampilkan sebagai slider tepat di atas footer.
                    </p>
                </div>

                <strong>
                    {{ number_format($relatedLinks->total()) }}
                    link
                </strong>
            </header>

            @if ($relatedLinks->isEmpty())
                <div class="related-links-empty">
                    <x-ui.empty-state
                        title="Belum Ada Link Terkait"
                        description="Tambahkan logo dan alamat website terkait."
                    >
                        <x-slot:action>
                            <x-ui.button
                                :href="route('admin.related-links.create')"
                                variant="primary"
                            >
                                Tambah Link
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @else
                <div class="related-links-table-wrap">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th class="related-links-image-column">
                                    Gambar
                                </th>

                                <th>
                                    Nama Link
                                </th>

                                <th>
                                    Alamat URL
                                </th>

                                <th class="related-links-action-column">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($relatedLinks as $relatedLink)
                                @php
                                    $imageUrl = $relatedLink->media
                                        ? Storage::disk(
                                            $relatedLink->media->disk
                                        )->url(
                                            $relatedLink->media->path
                                        )
                                        : null;
                                @endphp

                                <tr>
                                    <td>
                                        <div class="related-link-logo">
                                            @if ($imageUrl)
                                                <img
                                                    src="{{ $imageUrl }}"
                                                    alt="{{ $relatedLink->media->alt_text ?: $relatedLink->name }}"
                                                >
                                            @else
                                                <span>IMG</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <strong class="related-link-name">
                                            {{ $relatedLink->name }}
                                        </strong>
                                    </td>

                                    <td>
                                        <a
                                            href="{{ $relatedLink->url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="related-link-url"
                                        >
                                            {{ $relatedLink->url }}
                                        </a>
                                    </td>

                                    <td>
                                        <div class="table-actions">
                                            <x-ui.button
                                                :href="route(
                                                    'admin.related-links.edit',
                                                    $relatedLink
                                                )"
                                                size="sm"
                                            >
                                                Ubah
                                            </x-ui.button>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'admin.related-links.destroy',
                                                    $relatedLink
                                                ) }}"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <x-ui.button
                                                    type="submit"
                                                    variant="danger"
                                                    size="sm"
                                                    data-confirm="Yakin ingin menghapus link terkait ini?"
                                                >
                                                    Hapus
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>

                @if ($relatedLinks->hasPages())
                    <div class="related-links-pagination">
                        {{ $relatedLinks->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/related-links.css') }}"
    >
@endpush
