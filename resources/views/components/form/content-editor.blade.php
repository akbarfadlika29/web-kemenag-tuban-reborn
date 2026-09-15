@props([
    'name',
    'id' => null,
    'value' => null,
    'placeholder' => 'Tulis konten di sini...',
])

@php
    $fieldId = $id ?: $name;

    $contentValue = old(
        $name,
        $value ?? ''
    );
@endphp

<div
    class="content-editor"
    data-content-editor
    data-content-editor-placeholder="{{ $placeholder }}"
>
    <div class="content-editor-toolbar">
        <div
            class="content-editor-modes"
            role="group"
            aria-label="Mode editor"
        >
            <button
                type="button"
                class="content-editor-mode is-active"
                data-content-editor-mode="visual"
                aria-pressed="true"
            >
                Visual
            </button>

            <button
                type="button"
                class="content-editor-mode"
                data-content-editor-mode="source"
                aria-pressed="false"
            >
                HTML
            </button>
        </div>

        <span class="content-editor-mode-hint">
            HTML akan disanitasi saat disimpan.
        </span>
    </div>

    <textarea
        name="{{ $name }}"
        id="{{ $fieldId }}"
        hidden
        data-content-editor-input
    >{{ $contentValue }}</textarea>

    <div
        class="content-editor-visual"
        data-content-editor-visual
    >
        <div data-content-editor-quill></div>
    </div>

    <div
        class="content-editor-source"
        data-content-editor-source
        hidden
    >
        <textarea
            class="content-editor-source-input"
            data-content-editor-source-input
            spellcheck="false"
            aria-label="HTML source"
        ></textarea>
    </div>
</div>
