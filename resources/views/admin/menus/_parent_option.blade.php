@if (!in_array($parent->id, $excludedIds ?? [], true))
    <option
        value="{{ $parent->id }}"
        @selected(
            old(
                'parent_id',
                $menu->parent_id ?? null
            ) == $parent->id
        )
    >
        {{ str_repeat('— ', $level) }}{{ $parent->label }}
    </option>

    @foreach ($parent->childrenRecursive as $child)
        @include('admin.menus._parent_option', [
            'parent' => $child,
            'level' => $level + 1,
            'excludedIds' => $excludedIds ?? [],
        ])
    @endforeach
@endif
