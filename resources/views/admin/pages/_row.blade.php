@php
    $state = $item->publication_state;

    $statusLabel = match ($state) {
        'published' => 'Published',
        'scheduled' => 'Terjadwal',
        'archived' => 'Archived',
        default => 'Draft',
    };

    $statusClass = match ($state) {
        'published' => 'is-published',
        'scheduled' => 'is-scheduled',
        'archived' => 'is-archived',
        default => 'is-draft',
    };

    $templateLabel = match ($item->template) {
        'full-width' => 'Full Width',
        'landing' => 'Landing Page',
        default => 'Default',
    };

    if (isset($item->children_count)) {
        $hasChildren =
            $item->children_count > 0;
    } else {
        $hasChildren =
            $item->relationLoaded('childrenRecursive')
            && $item->childrenRecursive->isNotEmpty();
    }
@endphp

<tr>
    <td class="pages-title-cell">
        <div
            class="pages-title-wrap"
            style="--page-depth: {{ $depth }}"
        >
            @if ($treeMode && $depth > 0)
                <span class="pages-tree-line">
                    ↳
                </span>
            @endif

            <div class="pages-title-content">
                <strong>
                    {{ $item->title }}
                </strong>

                <div class="pages-title-meta">
                    <span>
                        /halaman/{{ $item->slug }}
                    </span>

                    @if (!$treeMode && $item->parent)
                        <span>
                            Parent:
                            {{ $item->parent->title }}
                        </span>
                    @endif

                    @if ($hasChildren)
                        <span>
                            Memiliki turunan
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </td>

    <td>
        @if ($item->unit)
            <span class="pages-unit">
                {{ $item->unit->name }}
            </span>
        @else
            <span class="pages-muted">
                Global
            </span>
        @endif
    </td>

    <td>
        <span class="pages-template">
            {{ $templateLabel }}
        </span>
    </td>

    <td>
        <span
            class="pages-status {{ $statusClass }}"
        >
            {{ $statusLabel }}
        </span>
    </td>

    <td>
        @if ($item->show_in_menu)
            <span class="pages-menu-state is-on">
                Ya
            </span>
        @else
            <span class="pages-menu-state">
                Tidak
            </span>
        @endif
    </td>

    <td>
        <span class="pages-sort-order">
            {{ $item->sort_order }}
        </span>
    </td>

    <td>
        @if ($item->published_at)
            <div class="pages-publication">
                <strong>
                    {{ $item->published_at->format('d M Y') }}
                </strong>

                <span>
                    {{ $item->published_at->format('H:i') }}
                </span>
            </div>
        @else
            <span class="pages-muted">
                —
            </span>
        @endif
    </td>

    <td>
        <div class="pages-row-actions">
            <x-ui.button
                :href="route(
                    'admin.pages.edit',
                    $item
                )"
                size="sm"
                variant="secondary"
            >
                Ubah
            </x-ui.button>

            @if (!$hasChildren)
                <form
                    method="POST"
                    action="{{ route(
                        'admin.pages.destroy',
                        $item
                    ) }}"
                    data-confirm="Hapus halaman {{ $item->title }}?"
                >
                    @csrf
                    @method('DELETE')

                    <x-ui.button
                        type="submit"
                        size="sm"
                        variant="danger"
                    >
                        Hapus
                    </x-ui.button>
                </form>
            @else
                <button
                    type="button"
                    class="pages-delete-disabled"
                    disabled
                    title="Hapus halaman turunannya terlebih dahulu."
                >
                    Hapus
                </button>
            @endif
        </div>
    </td>
</tr>

@if (
    $recursive
    && $item->relationLoaded('childrenRecursive')
)
    @foreach ($item->childrenRecursive as $child)
        @include(
            'admin.pages._row',
            [
                'item' => $child,
                'depth' => $depth + 1,
                'recursive' => true,
                'treeMode' => true,
            ]
        )
    @endforeach
@endif