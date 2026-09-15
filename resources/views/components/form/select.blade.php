@props([
    'name',
    'label' => null,
    'size' => 'md',
    'required' => false,
    'help' => null,
])

<div class="ui-form-group">
    @if ($label)
        <label for="{{ $name }}" class="ui-form-label">
            {{ $label }}

            @if ($required)
                <span class="ui-required">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->class([
            'ui-control',
            'ui-control-' . $size,
            'is-invalid' => $errors->has($name),
        ]) }}
        @required($required)
    >
        {{ $slot }}
    </select>

    @if ($help)
        <div class="ui-form-help">{{ $help }}</div>
    @endif

    @error($name)
        <div class="ui-form-error">{{ $message }}</div>
    @enderror
</div>