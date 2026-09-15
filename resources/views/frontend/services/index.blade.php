@extends('frontend.layouts.app')

@section('title', 'Layanan Publik')

@section('content')
    <x-frontend.page-header class="services-header">
    <x-slot:title>Layanan</x-slot:title>
    <x-slot:description>Temukan informasi layanan publik
                sesuai kebutuhan Anda.</x-slot:description>
</x-frontend.page-header>

    <section class="section">
        <div class="container">
            <form
                method="GET"
                action="{{ route('services.index') }}"
                class="public-filter-bar"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari layanan..."
                >

                <select name="category_id">
                    <option value="">
                        Semua Kategori
                    </option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(
                                (string) $categoryId ===
                                (string) $category->id
                            )
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="channel">
                    <option value="">
                        Semua Kanal
                    </option>

                    <option
                        value="online"
                        @selected($channel === 'online')
                    >
                        Online
                    </option>

                    <option
                        value="offline"
                        @selected($channel === 'offline')
                    >
                        Offline
                    </option>

                    <option
                        value="hybrid"
                        @selected($channel === 'hybrid')
                    >
                        Online & Offline
                    </option>
                </select>

                <button type="submit">
                    Terapkan
                </button>
            </form>

            <div class="service-public-grid">
                @forelse ($services as $service)
                    <article class="service-public-card">
                        <div class="service-public-icon">
                            {{ strtoupper(
                                mb_substr(
                                    $service->title,
                                    0,
                                    1
                                )
                            ) }}
                        </div>

                        <span class="service-public-category">
                            {{ $service->category?->name ?? 'Layanan' }}
                        </span>

                        <h3>
                            <a href="{{ route('services.show', $service->slug) }}">
                                {{ $service->title }}
                            </a>
                        </h3>

                        <p>
                            {{ Str::limit(
                                $service->excerpt
                                    ?: strip_tags($service->description ?? ''),
                                130
                            ) }}
                        </p>

                        <div class="service-public-meta">
                            <span>
                                {{ $service->service_channel_label }}
                            </span>

                            @if ($service->is_free)
                                <span>Gratis</span>
                            @endif
                        </div>

                        <a
                            href="{{ route('services.show', $service->slug) }}"
                            class="public-action-link"
                        >
                            Detail Layanan →
                        </a>
                    </article>
                @empty
                    <div class="empty-public-state">
                        Belum ada layanan.
                    </div>
                @endforelse
            </div>

            {{ $services->links() }}
        </div>
    </section>
@endsection