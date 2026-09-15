@if ($relatedLinks->isNotEmpty())
    <section
        class="related-links-section"
        aria-labelledby="related-links-title"
        data-related-links-slider
    >
        <div class="container">
            <div class="related-links-heading">
                <div class="related-links-heading-copy">
                    <h2 id="related-links-title">
                        Link Terkait
                    </h2>

                    <p>
                        Akses cepat menuju website dan layanan terkait.
                    </p>
                </div>

                <div
                    class="related-links-controls"
                    aria-label="Navigasi Link Terkait"
                >
                    <button
                        type="button"
                        class="related-links-control"
                        data-related-links-prev
                        aria-label="Link terkait sebelumnya"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="related-links-control"
                        data-related-links-next
                        aria-label="Link terkait berikutnya"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </div>

            <div
                class="related-links-viewport"
                data-related-links-viewport
                tabindex="0"
                aria-label="Daftar Link Terkait"
            >
                <div
                    class="related-links-track"
                    data-related-links-track
                >
                    @foreach ($relatedLinks as $relatedLink)
                        <a
                            href="{{ $relatedLink['url'] }}"
                            class="related-link-slide"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="{{ $relatedLink['name'] }}"
                        >
                            <span class="related-link-slide-image">
                                <img
                                    src="{{ $relatedLink['image_url'] }}"
                                    alt="{{ $relatedLink['image_alt'] }}"
                                    loading="lazy"
                                >
                            </span>

                            <strong>
                                {{ $relatedLink['name'] }}
                            </strong>

                            <span class="related-link-slide-action">
                                Kunjungi Website

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M7 17 17 7" />
                                    <path d="M8 7h9v9" />
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
