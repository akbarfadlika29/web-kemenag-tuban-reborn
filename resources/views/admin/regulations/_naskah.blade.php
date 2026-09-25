@php
    $currentDocument = $regulation->documents
        ->firstWhere('document_role', 'main');

    $chosenId = old('main_media_id', $currentDocument?->media_id);
    $chosenMedia = $chosenId ? \App\Models\Media::find($chosenId) : null;

    if (!\App\Services\Regulation\RegulationService::usable($chosenMedia)) {
        $chosenMedia = null;
    }
@endphp

<section class="ui-card ui-card-body reg-space">
    <h2>Naskah regulasi</h2>
    <p>Pilih satu PDF dari pustaka media atau unggah file baru melalui tombol berikut.</p>

    <div
        data-regulation-naskah
        data-initial-title="{{ $chosenMedia?->title ?: $chosenMedia?->original_name }}"
        data-initial-url="{{ $chosenMedia ? route('admin.regulations.media.preview', $chosenMedia->id) : '' }}"
    >
        <input type="hidden" name="main_media_id"
            value="{{ $chosenMedia?->id }}" data-naskah-id>

        <input type="hidden" name="remove_main"
            value="{{ old('remove_main', 0) }}" data-naskah-remove>

        <div class="reg-public-card">
            <strong data-naskah-name>Belum ada naskah dipilih</strong>
            <p>Format PDF · Maksimal 20 MB untuk upload baru.</p>

            <div class="reg-actions">
                <button type="button" class="ui-btn ui-btn-primary" data-naskah-picker>
                    Pilih Naskah
                </button>

                <a class="ui-btn ui-btn-secondary" data-naskah-preview
                    target="_blank" rel="noopener noreferrer" hidden>
                    Lihat PDF
                </a>

                <button type="button" class="ui-btn ui-btn-secondary"
                    data-naskah-clear hidden>
                    Lepaskan
                </button>
            </div>

            <p data-naskah-error role="alert" hidden></p>
        </div>
    </div>
</section>

@push('scripts')
<script src="{{ asset('js/admin/pages/regulation-naskah.js') }}?v={{ filemtime(public_path('js/admin/pages/regulation-naskah.js')) }}"></script>
@endpush
