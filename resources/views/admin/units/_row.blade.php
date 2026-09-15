<tr
    draggable="true"
    data-tree-dnd-item
    data-tree-id="{{ $unit->id }}"
    data-tree-label="{{ $unit->name }}"
    data-tree-move-url="{{ route('admin.units.move', $unit) }}"
>
    <td>
        <div
            class="unit-level"
            style="--unit-level: {{ $level }};"
        >
            <span
                class="tree-dnd-handle"
                data-tree-dnd-handle
                title="Tarik untuk memindahkan unit"
                aria-hidden="true"
            >
                ⋮⋮
            </span>

            @if ($level > 0)
                <span
                    class="unit-tree-marker"
                    aria-hidden="true"
                >
                    ↳
                </span>
            @endif

            <div class="unit-name">
                <strong>
                    {{ $unit->name }}
                </strong>

                @if ($unit->short_name)
                    <span class="unit-subtext">
                        {{ $unit->short_name }}
                    </span>
                @endif
            </div>
        </div>
    </td>

    <td class="unit-parent-cell">
        {{ $unit->parent?->name ?? 'Level Utama' }}
    </td>

    <td>
        <x-ui.badge variant="info">
            {{ strtoupper($unit->type) }}
        </x-ui.badge>
    </td>

    <td class="unit-code-cell">
        {{ $unit->code ?? '-' }}
    </td>

    <td>
        @if ($unit->is_active)
            <x-ui.badge variant="success">
                Aktif
            </x-ui.badge>
        @else
            <x-ui.badge variant="danger">
                Nonaktif
            </x-ui.badge>
        @endif
    </td>

    <td class="unit-order-cell">
        {{ $unit->sort_order }}
    </td>

    <td>
        <div class="table-actions">
            <x-ui.button
                :href="route('admin.units.edit', $unit)"
                size="sm"
            >
                Ubah
            </x-ui.button>

            <form
                action="{{ route('admin.units.destroy', $unit) }}"
                method="POST"
            >
                @csrf
                @method('DELETE')

                <x-ui.button
                    type="submit"
                    variant="danger"
                    size="sm"
                    data-confirm="Yakin ingin menghapus unit kerja ini?"
                >
                    Hapus
                </x-ui.button>
            </form>
        </div>
    </td>
</tr>

@foreach ($unit->childrenRecursive as $child)
    @include('admin.units._row', [
        'unit' => $child,
        'level' => $level + 1,
    ])
@endforeach
