@extends('layouts.admin')

@section('title', 'Dasbor')

@section('content')
<div class="admin-page admin-dashboard">
    <x-ui.page-header
        title="Dasbor"
        description="Ringkasan konten, publikasi, dan layanan informasi."
    />

    {{-- RINGKASAN UTAMA --}}
    <section class="dashboard-primary-stats" aria-label="Ringkasan utama">
        <a href="{{ route('admin.news.index') }}" class="dashboard-primary-card">
            <div class="dashboard-primary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2" />
                    <path d="M10 6h8v4h-8z" />
                    <path d="M10 14h8" />
                    <path d="M10 18h5" />
                </svg>
            </div>
            <div class="dashboard-primary-content">
                <span class="dashboard-primary-label">Berita</span>
                <strong class="dashboard-primary-value">{{ number_format($stats['news']) }}</strong>
                <span class="dashboard-primary-meta">Total konten berita</span>
            </div>
            <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
        </a>

        <a href="{{ route('admin.ppid-informations.index') }}" class="dashboard-primary-card">
            <div class="dashboard-primary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                </svg>
            </div>
            <div class="dashboard-primary-content">
                <span class="dashboard-primary-label">Informasi PPID</span>
                <strong class="dashboard-primary-value">{{ number_format($stats['ppid']) }}</strong>
                <span class="dashboard-primary-meta">Informasi publik</span>
            </div>
            <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
        </a>

        <a href="{{ route('admin.services.index') }}" class="dashboard-primary-card">
            <div class="dashboard-primary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
            </div>
            <div class="dashboard-primary-content">
                <span class="dashboard-primary-label">Layanan</span>
                <strong class="dashboard-primary-value">{{ number_format($stats['services']) }}</strong>
                <span class="dashboard-primary-meta">Layanan publik</span>
            </div>
            <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="dashboard-primary-card">
            <div class="dashboard-primary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                </svg>
            </div>
            <div class="dashboard-primary-content">
                <span class="dashboard-primary-label">User Aktif</span>
                <strong class="dashboard-primary-value">{{ number_format($stats['active_users']) }}</strong>
                <span class="dashboard-primary-meta">dari {{ number_format($stats['users']) }} akun</span>
            </div>
            <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
        </a>
    </section>

    {{-- RINGKASAN MODUL --}}
    <section class="dashboard-overview" aria-label="Ringkasan modul">
        <a href="{{ route('admin.announcements.index') }}" class="dashboard-overview-card">
            <span>Pengumuman</span>
            <strong>{{ number_format($stats['announcements']) }}</strong>
        </a>
        <a href="{{ route('admin.agendas.index') }}" class="dashboard-overview-card">
            <span>Agenda</span>
            <strong>{{ number_format($stats['agendas']) }}</strong>
        </a>
        <a href="{{ route('admin.galleries.index') }}" class="dashboard-overview-card">
            <span>Galeri</span>
            <strong>{{ number_format($stats['galleries']) }}</strong>
        </a>
        <a href="{{ route('admin.pages.index') }}" class="dashboard-overview-card">
            <span>Halaman</span>
            <strong>{{ number_format($stats['pages']) }}</strong>
        </a>
        <a href="{{ route('admin.media.index') }}" class="dashboard-overview-card">
            <span>Media</span>
            <strong>{{ number_format($stats['media']) }}</strong>
        </a>
        <a href="{{ route('admin.units.index') }}" class="dashboard-overview-card">
            <span>Unit Kerja</span>
            <strong>{{ number_format($stats['units']) }}</strong>
        </a>
    </section>

    {{-- KONTEN UTAMA --}}
    <div class="dashboard-main-grid">
        <section class="dashboard-panel">
            <header class="dashboard-panel-header">
                <div>
                    <span class="dashboard-panel-kicker">Konten</span>
                    <h2>Berita Terbaru</h2>
                    <p>Publikasi berita yang terakhir dikelola.</p>
                </div>
                <a href="{{ route('admin.news.index') }}" class="dashboard-panel-link">
                    Lihat Semua <span aria-hidden="true">&rarr;</span>
                </a>
            </header>

            <div class="dashboard-list">
                @forelse ($latestNews as $news)
                    <a href="{{ route('admin.news.edit', $news) }}" class="dashboard-list-item">
                        <div class="dashboard-list-main">
                            <strong>{{ $news->title }}</strong>
                            <div class="dashboard-list-meta">
                                <span>{{ $news->category?->name ?? 'Tanpa kategori' }}</span>
                                @if ($news->unit)
                                    <span>{{ $news->unit->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="dashboard-list-side">
                            <span class="dashboard-status {{ $news->status === 'published' ? 'published' : '' }}">
                                {{ ucfirst($news->status) }}
                            </span>
                            <small>{{ optional($news->created_at)->translatedFormat('d M Y') }}</small>
                        </div>
                    </a>
                @empty
                    <div class="dashboard-empty">
                        <strong>Belum ada berita</strong>
                        <span>Berita terbaru akan tampil di sini.</span>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel">
            <header class="dashboard-panel-header">
                <div>
                    <span class="dashboard-panel-kicker">Jadwal</span>
                    <h2>Agenda Terdekat</h2>
                    <p>Agenda yang akan segera berlangsung.</p>
                </div>
                <a href="{{ route('admin.agendas.index') }}" class="dashboard-panel-link">
                    Lihat Semua <span aria-hidden="true">&rarr;</span>
                </a>
            </header>

            <div class="dashboard-agenda-list">
                @forelse ($upcomingAgendas as $agenda)
                    <a href="{{ route('admin.agendas.edit', $agenda) }}" class="dashboard-agenda-item">
                        <div class="dashboard-agenda-date">
                            <strong>{{ $agenda->start_at->format('d') }}</strong>
                            <span>{{ $agenda->start_at->translatedFormat('M') }}</span>
                        </div>
                        <div class="dashboard-agenda-content">
                            <strong>{{ $agenda->title }}</strong>
                            <span>
                                {{ $agenda->start_at->format('H:i') }} WIB
                                @if ($agenda->location)
                                    &middot; {{ $agenda->location }}
                                @endif
                            </span>
                        </div>
                        <span class="dashboard-agenda-arrow" aria-hidden="true">&rarr;</span>
                    </a>
                @empty
                    <div class="dashboard-empty">
                        <strong>Tidak ada agenda mendatang</strong>
                        <span>Agenda terdekat akan tampil di sini.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- BAGIAN BAWAH --}}
    <div class="dashboard-bottom-grid">
        <section class="dashboard-panel">
            <header class="dashboard-panel-header">
                <div>
                    <span class="dashboard-panel-kicker">Informasi</span>
                    <h2>Pengumuman Terbaru</h2>
                    <p>Informasi dan pengumuman terakhir.</p>
                </div>
                <a href="{{ route('admin.announcements.index') }}" class="dashboard-panel-link">
                    Lihat Semua <span aria-hidden="true">&rarr;</span>
                </a>
            </header>

            <div class="dashboard-list">
                @forelse ($latestAnnouncements as $announcement)
                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="dashboard-list-item">
                        <div class="dashboard-list-main">
                            <strong>{{ $announcement->title }}</strong>
                            <div class="dashboard-list-meta">
                                <span>{{ $announcement->unit?->name ?? 'Global' }}</span>
                            </div>
                        </div>
                        <small class="dashboard-list-date">
                            {{ optional($announcement->created_at)->translatedFormat('d M Y') }}
                        </small>
                    </a>
                @empty
                    <div class="dashboard-empty">
                        <strong>Belum ada pengumuman</strong>
                        <span>Pengumuman terbaru akan tampil di sini.</span>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel">
            <header class="dashboard-panel-header">
                <div>
                    <span class="dashboard-panel-kicker">Pintasan</span>
                    <h2>Akses Cepat</h2>
                    <p>Buat konten baru langsung dari dashboard.</p>
                </div>
            </header>

            <div class="dashboard-shortcuts">
                <a href="{{ route('admin.news.create') }}">
                    <span class="dashboard-shortcut-icon" aria-hidden="true">+</span>
                    <span class="dashboard-shortcut-copy">
                        <strong>Tambah Berita</strong>
                        <small>Buat publikasi berita baru</small>
                    </span>
                </a>

                <a href="{{ route('admin.announcements.create') }}">
                    <span class="dashboard-shortcut-icon" aria-hidden="true">+</span>
                    <span class="dashboard-shortcut-copy">
                        <strong>Tambah Pengumuman</strong>
                        <small>Buat informasi pengumuman</small>
                    </span>
                </a>

                <a href="{{ route('admin.agendas.create') }}">
                    <span class="dashboard-shortcut-icon" aria-hidden="true">+</span>
                    <span class="dashboard-shortcut-copy">
                        <strong>Tambah Agenda</strong>
                        <small>Tambahkan jadwal kegiatan</small>
                    </span>
                </a>

                <a href="{{ route('admin.ppid-informations.create') }}">
                    <span class="dashboard-shortcut-icon" aria-hidden="true">+</span>
                    <span class="dashboard-shortcut-copy">
                        <strong>Informasi PPID</strong>
                        <small>Tambahkan informasi publik</small>
                    </span>
                </a>

                <a href="{{ route('admin.services.create') }}">
                    <span class="dashboard-shortcut-icon" aria-hidden="true">+</span>
                    <span class="dashboard-shortcut-copy">
                        <strong>Tambah Layanan</strong>
                        <small>Tambahkan layanan publik</small>
                    </span>
                </a>

                <a href="{{ route('admin.media.create') }}">
                    <span class="dashboard-shortcut-icon" aria-hidden="true">+</span>
                    <span class="dashboard-shortcut-copy">
                        <strong>Unggah Media</strong>
                        <small>Tambahkan aset Media Manager</small>
                    </span>
                </a>
            </div>
        </section>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pages/dashboard.css') }}">
@endpush
@endsection