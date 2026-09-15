@extends('frontend.layouts.app')

@section('title', 'Agenda')

@section('content')
    <x-frontend.page-header>
    <x-slot:title>Agenda</x-slot:title>
    <x-slot:description>Agenda kegiatan Kementerian Agama
                Kabupaten Tuban.</x-slot:description>
</x-frontend.page-header>

    <section class="section">
        <div class="container">
            <div class="public-tabs">
                <a
                    href="{{ route('agendas.index', ['state' => 'upcoming']) }}"
                    class="{{ $state === 'upcoming' ? 'active' : '' }}"
                >
                    Akan Datang
                </a>

                <a
                    href="{{ route('agendas.index', ['state' => 'finished']) }}"
                    class="{{ $state === 'finished' ? 'active' : '' }}"
                >
                    Selesai
                </a>

                <a
                    href="{{ route('agendas.index', ['state' => 'all']) }}"
                    class="{{ $state === 'all' ? 'active' : '' }}"
                >
                    Semua
                </a>
            </div>

            <div class="agenda-list-public">
                @forelse ($agendas as $agenda)
                    <article class="agenda-row-public">
                        <div class="agenda-big-date">
                            <strong>
                                {{ $agenda->start_at->format('d') }}
                            </strong>

                            <span>
                                {{ $agenda->start_at->translatedFormat('M Y') }}
                            </span>
                        </div>

                        <div class="agenda-row-content">
                            <span>
                                {{ $agenda->start_at->format('H:i') }}

                                @if ($agenda->end_at)
                                    – {{ $agenda->end_at->format('H:i') }}
                                @endif
                            </span>

                            <h2>
                                <a href="{{ route('agendas.show', $agenda->slug) }}">
                                    {{ $agenda->title }}
                                </a>
                            </h2>

                            @if ($agenda->location)
                                <p>
                                    {{ $agenda->location }}
                                </p>
                            @endif

                            @if ($agenda->unit)
                                <small>
                                    {{ $agenda->unit->name }}
                                </small>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-public-state">
                        Belum ada agenda.
                    </div>
                @endforelse
            </div>

            {{ $agendas->links() }}
        </div>
    </section>
@endsection