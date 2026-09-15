<tr
    @if ($treeDndEnabled ?? true)
        draggable="true"
        data-tree-dnd-item
        data-tree-id="{{ $menu->id }}"
        data-tree-label="{{ $menu->label }}"
        data-tree-move-url="{{ route('admin.menus.move', $menu) }}"
    @endif
>
    <td>
        <div
            class="menu-tree-level"
            style="--menu-level: {{ $level }};"
        >
            @if ($treeDndEnabled ?? true)
                <span
                    class="tree-dnd-handle"
                    data-tree-dnd-handle
                    title="Tarik untuk memindahkan menu"
                    aria-hidden="true"
                >
                    ⋮⋮
                </span>
            @endif

            @if ($level > 0)
                <span
                    class="menu-tree-marker"
                    aria-hidden="true"
                >
                    ↳
                </span>
            @endif

            <div class="menu-name">
                <strong>
                    {{ $menu->label }}
                </strong>

                <div class="menu-name-meta">
                    @if ($level === 0)
                        <span>
                            Menu Utama
                        </span>
                    @else
                        <span>
                            Submenu level {{ $level }}
                        </span>
                    @endif

                    @if ($menu->open_in_new_tab)
                        <span>
                            Tab baru
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </td>

    <td class="menu-parent-cell">
        {{ $menu->parent?->label ?? 'Menu Utama' }}
    </td>

    <td>
        <x-ui.badge variant="info">
            {{ strtoupper($menu->type) }}
        </x-ui.badge>
    </td>

    <td>
        <div class="menu-target">
            @if ($menu->type === 'page')
                <strong>
                    {{ $menu->page?->title ?? 'Halaman tidak tersedia' }}
                </strong>

                <span>
                    Halaman CMS
                </span>
            @elseif ($menu->type === 'route')
                <strong>
                    {{ $menu->route_name ?: '-' }}
                </strong>

                <span>
                    Named route
                </span>
            @elseif ($menu->type === 'url')
                <strong>
                    {{ $menu->url ?: '-' }}
                </strong>

                <span>
                    URL
                </span>
            @else
                <strong>
                    Grup / Judul
                </strong>

                <span>
                    Tanpa tautan
                </span>
            @endif
        </div>
    </td>

    <td>
        @if ($menu->is_active)
            <x-ui.badge variant="success">
                Aktif
            </x-ui.badge>
        @else
            <x-ui.badge variant="danger">
                Nonaktif
            </x-ui.badge>
        @endif
    </td>

    <td class="menu-order-cell">
        {{ $menu->sort_order }}
    </td>

    <td>
        <div class="table-actions">
            <x-ui.button
                :href="route('admin.menus.edit', $menu)"
                size="sm"
            >
                Ubah
            </x-ui.button>

            <form
                action="{{ route('admin.menus.destroy', $menu) }}"
                method="POST"
            >
                @csrf
                @method('DELETE')

                <x-ui.button
                    type="submit"
                    variant="danger"
                    size="sm"
                    data-confirm="Yakin ingin menghapus menu ini?"
                >
                    Hapus
                </x-ui.button>
            </form>
        </div>
    </td>
</tr>

@foreach ($menu->childrenRecursive as $child)
    @include('admin.menus._row', [
        'menu' => $child,
        'level' => $level + 1,
        'treeDndEnabled' => $treeDndEnabled ?? true,
    ])
@endforeach
