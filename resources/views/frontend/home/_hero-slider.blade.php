@php
    $heroSlideCount =
        1 + $heroSlides->count();
@endphp

<section
    class="hero hero-slider"
    data-hero-slider
    aria-roledescription="carousel"
    aria-label="Informasi utama"
>
    <div class="hero-slider-viewport">
        <div
            class="hero-slider-track"
            data-hero-slider-track
        >
            {{-- =====================================================
                 SLIDE 1 - HERO UTAMA PERMANEN
                 ===================================================== --}}
            <article
                class="hero-slider-slide hero-slider-slide-primary is-active"
                data-hero-slide
                aria-hidden="false"
                aria-label="1 dari {{ $heroSlideCount }}"
            >
                <div class="container hero-grid">
                    <div class="hero-content">
                        <span class="hero-kicker">
                            Kementerian Agama Kabupaten Tuban
                        </span>

                        <h1>
                            Melayani dengan
                            <span>
                                Profesional, Transparan, dan Terpercaya
                            </span>
                        </h1>

                        <p>
                            Akses informasi publik, berita, agenda,
                            pengumuman, dan layanan Kemenag Tuban.
                        </p>

                        <div class="hero-actions">
                            <a
                                href="{{ route('services.index') }}"
                                class="button button-primary"
                            >
                                Lihat Layanan
                            </a>

                            <a
                                href="{{ route('ppid.index') }}"
                                class="button button-outline"
                            >
                                Informasi PPID
                            </a>
                        </div>
                    </div>

                    <div class="hero-card">
                        <span class="hero-card-label">
                            Portal Pelayanan Publik
                        </span>

                        <strong>
                            Informasi lebih mudah, layanan lebih dekat.
                        </strong>

                        <div class="hero-stat-grid">
                            <div>
                                <strong>
                                    {{ $services->count() }}
                                </strong>

                                <span>
                                    Layanan
                                </span>
                            </div>

                            <div>
                                <strong>
                                    {{ $ppidInformations->count() }}
                                </strong>

                                <span>
                                    PPID
                                </span>
                            </div>

                            <div>
                                <strong>
                                    {{ $latestNews->count() }}
                                </strong>

                                <span>
                                    Berita
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- =====================================================
                 SLIDE TAMBAHAN DARI ADMIN
                 ===================================================== --}}
            @foreach ($heroSlides as $index => $heroSlide)
                @php
                    $imageUrl =
                        Storage::disk(
                            $heroSlide->media->disk
                        )->url(
                            $heroSlide->media->path
                        );

                    $imageAlt =
                        $heroSlide->media->alt_text
                        ?: $heroSlide->title
                        ?: 'Banner Kementerian Agama Kabupaten Tuban';

                    $slideUrl = null;

                    if ($heroSlide->url) {
                        $slideUrl =
                            str_starts_with(
                                $heroSlide->url,
                                '/'
                            )
                                ? url($heroSlide->url)
                                : $heroSlide->url;
                    }

                    $hasContent =
                        filled($heroSlide->title)
                        || filled($heroSlide->description)
                        || (
                            filled($heroSlide->button_label)
                            && filled($slideUrl)
                        );
                @endphp

                <article
                    class="hero-slider-slide hero-slider-slide-banner"
                    data-hero-slide
                    aria-hidden="true"
                    aria-label="{{ $index + 2 }} dari {{ $heroSlideCount }}"
                    inert
                >
                    <div class="hero-slide-banner-media">
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $imageAlt }}"
                            loading="lazy"
                        >
                    </div>


                    @if ($hasContent)
                        <div class="container hero-slide-banner-content">
                            <div class="hero-slide-banner-copy">
                                <span class="hero-slide-banner-kicker">
                                    Kementerian Agama Kabupaten Tuban
                                </span>

                                @if ($heroSlide->title)
                                    <h2>
                                        {{ $heroSlide->title }}
                                    </h2>
                                @endif

                                @if ($heroSlide->description)
                                    <p>
                                        {{ $heroSlide->description }}
                                    </p>
                                @endif

                                @if (
                                    $heroSlide->button_label
                                    && $slideUrl
                                )
                                    <div class="hero-slide-banner-actions">
                                        <a
                                            href="{{ $slideUrl }}"
                                            class="button button-primary"
                                        >
                                            {{ $heroSlide->button_label }}

                                            <span aria-hidden="true">
                                                →
                                            </span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>

    @if ($heroSlides->isNotEmpty())
        <button
            type="button"
            class="hero-slider-control hero-slider-control-prev"
            data-hero-slider-prev
            aria-label="Slide sebelumnya"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="m15 18-6-6 6-6" />
            </svg>
        </button>

        <button
            type="button"
            class="hero-slider-control hero-slider-control-next"
            data-hero-slider-next
            aria-label="Slide berikutnya"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="m9 18 6-6-6-6" />
            </svg>
        </button>

        <div
            class="hero-slider-dots"
            data-hero-slider-dots
            aria-label="Pilih slide"
        >
            @for ($index = 0; $index < $heroSlideCount; $index++)
                <button
                    type="button"
                    class="hero-slider-dot {{ $index === 0 ? 'is-active' : '' }}"
                    data-hero-slider-dot="{{ $index }}"
                    aria-label="Tampilkan slide {{ $index + 1 }}"
                    @if ($index === 0)
                        aria-current="true"
                    @endif
                ></button>
            @endfor
        </div>
    @endif
</section>
