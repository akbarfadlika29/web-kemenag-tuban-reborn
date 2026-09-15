@props([
    'id',
    'title',
    'size' => 'md',
])

<div
    id="{{ $id }}"
    class="ui-modal-backdrop"
    aria-hidden="true"
>
    <div
        class="ui-modal ui-modal-{{ $size }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}-title"
    >
        <div class="ui-modal-header">
            <h3 id="{{ $id }}-title">
                {{ $title }}
            </h3>

            <button
                type="button"
                class="ui-modal-close"
                data-modal-close
                aria-label="Tutup"
            >
                ×
            </button>
        </div>

        <div class="ui-modal-body">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="ui-modal-footer">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>