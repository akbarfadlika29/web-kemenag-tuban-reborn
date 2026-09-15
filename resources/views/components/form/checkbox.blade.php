@props([
    'name',
    'label',
    'value' => 1,
    'checked' => false,
])

<div class="ui-form-group">
    <label class="ui-check">
        <input type="hidden" name="{{ $name }}" value="0">

        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked(old($name, $checked))
            {{ $attributes }}
        >

        <span>{{ $label }}</span>
    </label>

    @error($name)
        <div class="ui-form-error">{{ $message }}</div>
    @enderror
</div>