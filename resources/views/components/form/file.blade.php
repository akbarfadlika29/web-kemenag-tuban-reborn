@props([
    'name' => 'file',
    'label' => 'File',
    'required' => false,
    'help' => null,
    'accept' => null,
])

<div class="ui-form-group">
    <label for="{{ $name }}" class="ui-form-label">
        {{ $label }}

        @if ($required)
            <span class="ui-required">*</span>
        @endif
    </label>

    <div class="ui-file">
        <input
            type="file"
            name="{{ $name }}"
            id="{{ $name }}"
            @if($accept) accept="{{ $accept }}" @endif
            @required($required)
            {{ $attributes }}
        >

        <div class="ui-file-copy">
            <strong>Pilih file</strong>

            @if ($help)
                <span>{{ $help }}</span>
            @endif
        </div>
    </div>

    @error($name)
        <div class="ui-form-error">{{ $message }}</div>
    @enderror
</div>