@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 4,
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

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->class([
            'ui-control',
            'ui-textarea',
            'is-invalid' => $errors->has($name),
        ]) }}
        @required($required)
    >{{ old($name, $value) }}</textarea>

    @if ($help)
        <div class="ui-form-help">{{ $help }}</div>
    @endif

    @error($name)
        <div class="ui-form-error">{{ $message }}</div>
    @enderror
</div>