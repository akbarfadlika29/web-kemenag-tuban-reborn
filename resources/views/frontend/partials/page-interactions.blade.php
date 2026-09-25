@php
    $interactionKey = hash(
        'sha256',
        get_class($interactionModel).':'.$interactionModel->getKey()
    );

    $interactionTitle = $interactionModel->getAttribute('title')
        ?: $interactionModel->getAttribute('name')
        ?: 'Informasi Publik';

    $interactionDate = $interactionModel->getAttribute('updated_at');

    $interactionUnit = $interactionModel->relationLoaded('unit')
        ? $interactionModel->getRelation('unit')?->name
        : null;
@endphp

@push('styles')
<link
    rel="stylesheet"
    href="{{ asset('css/frontend/components/page-interactions.css') }}?v={{ filemtime(public_path('css/frontend/components/page-interactions.css')) }}"
>
@endpush

<div
    class="page-interactions container"
    data-page-interactions data-interaction-icons
    data-title="{{ $interactionTitle }}"
    data-preferences="{{ json_encode(app(\App\Services\Frontend\ExperienceSettings::class)->all()) }}"
    data-home="{{ route('home') }}"
    data-endpoint="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'public.interactions.store',
        now()->addHours(12),
        ['key' => $interactionKey]
    ) }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="page-interaction-meta" data-interaction-meta>
        @if ($interactionUnit)
            <span class="interaction-meta-item"><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg><span>{{ $interactionUnit }}</span></span>
        @endif

        @if ($interactionDate)
            <span>
                <svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M16 3v4M8 3v4M3 11h18M8 15h2M14 15h2"/></svg> Diperbarui
                <time datetime="{{ $interactionDate->toDateString() }}">
                    {{ $interactionDate->format('d/m/Y') }}
                </time>
            </span>
        @endif

        <span class="interaction-meta-item"><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg><span>Dibaca <strong data-view-count>—</strong> kali</span></span>
        <span class="interaction-meta-item"><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg><span>Disukai <strong data-like-count>—</strong></span></span>
    </div>

    <div class="page-interaction-actions">
        <button
            type="button"
            class="page-action page-like"
            data-page-like
            aria-pressed="false"
            disabled
        >
            <span class="interaction-heart-character" aria-hidden="true">♡</span><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
            <span data-like-label>Suka</span>
        </button>

        <div class="page-share-group" aria-label="Bagikan halaman">
            <span class="page-share-label">Bagikan</span>

            <a class="page-action" data-share="facebook"
               target="_blank" rel="noopener noreferrer"><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 21v-8h3l.5-4H14V7c0-1 .3-2 2-2h2V2.3A20 20 0 0 0 15 2c-3 0-5 1.8-5 5v2H7v4h3v8"/></svg><span>Facebook</span></a>

            <a class="page-action" data-share="whatsapp"
               target="_blank" rel="noopener noreferrer"><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 11.5a9 9 0 0 1-13.5 7.8L3 21l1.6-4.7A9 9 0 1 1 21 11.5Z"/><path d="m8 7 1.5 3-1 1c1 2 2 3 4 4l1-1 3 1.5c-1 3-4 2-7-1s-4-6-1.5-7.5Z"/></svg><span>WhatsApp</span></a>

            <a class="page-action" data-share="telegram"
               target="_blank" rel="noopener noreferrer"><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m22 3-4 18-6-5-3 3v-6L22 3Z"/><path d="m22 3-20 8 7 2M9 13l9-7"/></svg><span>Telegram</span></a>

            <a class="page-action" data-share="x"
               target="_blank" rel="noopener noreferrer"><svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m4 3 16 18h-5L3 3h5l13 18M21 3 3 21"/></svg><span>X</span></a>

            <button class="page-action" type="button" data-copy-page>
                <svg class="interaction-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg><span>Salin Tautan</span>
            </button>
        </div>
    </div>

    <p class="page-interaction-status" data-interaction-status role="status"></p>
    <noscript>Aktifkan JavaScript untuk menggunakan tombol suka dan bagikan.</noscript>
</div>

@push('scripts')
<script
    src="{{ asset('js/frontend/components/page-interactions.js') }}?v={{ filemtime(public_path('js/frontend/components/page-interactions.js')) }}"
    defer
></script>
@endpush
