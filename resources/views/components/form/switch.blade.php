@props([
    'name',
    'label',
    'checked' => false,
])

<div class="ui-form-group">
    <input type="hidden" name="{{ $name }}" value="0">

    <label class="ui-switch">
        <input
            type="checkbox"
            name="{{ $name }}"
            value="1"
            @checked(old($name, $checked))
            {{ $attributes }}
        >

        <span class="ui-switch-track"></span>

        <span class="ui-switch-label">
            {{ $label }}
        </span>
    </label>

    @error($name)
        <div class="ui-form-error">{{ $message }}</div>
    @enderror
</div>