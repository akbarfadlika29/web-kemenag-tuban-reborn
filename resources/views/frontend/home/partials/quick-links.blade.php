@if ($quickLinks->isNotEmpty())
    @php
        $embedded = $embedded ?? false;
    @endphp

    <section
        class="home-quick-links {{ $embedded ? 'home-quick-links-embedded' : '' }}"
        aria-labelledby="home-quick-links-title"
    >
        @unless ($embedded)
            <div class="container">
        @endunless

        <div class="home-quick-links-panel">
            <div class="home-quick-links-heading">
                <div>
                    <span class="section-kicker">
                        Layanan & Informasi
                    </span>

                    <h2 id="home-quick-links-title">
                        Akses Cepat
                    </h2>

                    <p>
                        Akses langsung ke layanan dan informasi yang sering dibutuhkan.
                    </p>
                </div>
            </div>

            <div class="home-quick-links-grid">
                @foreach ($quickLinks as $quickLink)
                    <a
                        href="{{ $quickLink['url'] }}"
                        class="home-quick-link-card"
                        @if ($quickLink['target'])
                            target="{{ $quickLink['target'] }}"
                        @endif
                        @if ($quickLink['rel'])
                            rel="{{ $quickLink['rel'] }}"
                        @endif
                    >
                        <span class="home-quick-link-media">
                            @if ($quickLink['media_url'])
                                <img
                                    src="{{ $quickLink['media_url'] }}"
                                    alt="{{ $quickLink['media_alt'] }}"
                                    loading="lazy"
                                >
                            @else
                                <span
                                    class="home-quick-link-fallback"
                                    aria-hidden="true"
                                >
                                    {{
                                        mb_strtoupper(
                                            mb_substr(
                                                $quickLink['label'],
                                                0,
                                                1
                                            )
                                        )
                                    }}
                                </span>
                            @endif
                        </span>

                        <span class="home-quick-link-content">
                            <strong>
                                {{ $quickLink['label'] }}
                            </strong>

                            <span class="home-quick-link-action">
                                Buka
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M5 12h14" />
                                    <path d="m13 6 6 6-6 6" />
                                </svg>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        @unless ($embedded)
            </div>
        @endunless
    </section>
@endif
