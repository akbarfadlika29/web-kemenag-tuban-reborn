<section class="portal-block-card" aria-labelledby="home-ppid-title">
    <div class="section-heading">
        <div>
            <span class="section-kicker">
                Keterbukaan Informasi
            </span>

            <h2 id="home-ppid-title">
                Informasi PPID
            </h2>
        </div>

        <a
            href="{{ route('ppid.index') }}"
            class="section-link"
        >
            Lihat Semua
        </a>
    </div>

    <div class="home-directory" role="table" aria-label="Daftar informasi PPID">
        <div class="home-directory-head home-directory-row home-directory-row-ppid" role="row">
            <span role="columnheader">No</span>
            <span role="columnheader">Informasi</span>
            <span role="columnheader">Klasifikasi</span>
            <span role="columnheader">Tahun</span>
            <span role="columnheader" class="home-directory-action-head">Aksi</span>
        </div>

        <div class="home-directory-body" role="rowgroup">
            @forelse ($ppidInformations as $information)
                <article
                    class="home-directory-row home-directory-row-ppid"
                    role="row"
                >
                    <span class="home-directory-number" role="cell">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <div class="home-directory-primary" role="cell">
                        <h3>
                            <a href="{{ route('ppid.show', $information->slug) }}">
                                {{ $information->title }}
                            </a>
                        </h3>

                        @if (filled($information->excerpt))
                            <p>
                                {{ Str::limit($information->excerpt, 92) }}
                            </p>
                        @endif
                    </div>

                    <div class="home-directory-cell" role="cell" data-label="Klasifikasi">
                        <span class="home-directory-badge">
                            {{ $information->classification_label }}
                        </span>
                    </div>

                    <div class="home-directory-cell" role="cell" data-label="Tahun">
                        <span class="home-directory-muted">
                            {{ $information->year ?: '—' }}
                        </span>
                    </div>

                    <div class="home-directory-action" role="cell">
                        <a href="{{ route('ppid.show', $information->slug) }}">
                            Lihat
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty-public-state">
                    Belum ada informasi PPID.
                </div>
            @endforelse
        </div>
    </div>
</section>
