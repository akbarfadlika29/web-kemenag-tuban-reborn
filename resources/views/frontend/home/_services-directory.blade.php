<section class="portal-block-card" aria-labelledby="home-services-title">
    <div class="section-heading">
        <div>
            <span class="section-kicker">
                Pelayanan Publik
            </span>

            <h2 id="home-services-title">
                Layanan Masyarakat
            </h2>
        </div>

        <a
            href="{{ route('services.index') }}"
            class="section-link"
        >
            Semua Layanan →
        </a>
    </div>

    <div class="home-directory" role="table" aria-label="Daftar layanan masyarakat">
        <div class="home-directory-head home-directory-row home-directory-row-service" role="row">
            <span role="columnheader">No</span>
            <span role="columnheader">Layanan</span>
            <span role="columnheader">Kategori</span>
            <span role="columnheader">Kanal</span>
            <span role="columnheader" class="home-directory-action-head">Aksi</span>
        </div>

        <div class="home-directory-body" role="rowgroup">
            @forelse ($services as $service)
                <article
                    class="home-directory-row home-directory-row-service"
                    role="row"
                >
                    <span class="home-directory-number" role="cell">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <div class="home-directory-primary" role="cell">
                        <h3>
                            <a href="{{ route('services.show', $service->slug) }}">
                                {{ $service->title }}
                            </a>
                        </h3>

                        @if (filled($service->excerpt))
                            <p>
                                {{ Str::limit($service->excerpt, 92) }}
                            </p>
                        @endif
                    </div>

                    <div class="home-directory-cell" role="cell" data-label="Kategori">
                        <span class="home-directory-badge">
                            {{ $service->category?->name ?? 'Layanan' }}
                        </span>
                    </div>

                    <div class="home-directory-cell" role="cell" data-label="Kanal">
                        <span class="home-directory-muted">
                            {{ $service->service_channel_label ?: '—' }}
                        </span>
                    </div>

                    <div class="home-directory-action" role="cell">
                        <a href="{{ route('services.show', $service->slug) }}">
                            Detail
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty-public-state">
                    Belum ada layanan.
                </div>
            @endforelse
        </div>
    </div>
</section>
