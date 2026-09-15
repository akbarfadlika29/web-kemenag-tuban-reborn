@php
    $hasChildren =
        $item['children']->isNotEmpty();
@endphp

<div
    class="site-nav-item
        {{ $hasChildren ? 'has-children' : '' }}
        {{ $item['active'] ? 'is-active' : '' }}"
>
    <div class="site-nav-link-row">
        @if ($item['url'])
            <a
                href="{{ $item['url'] }}"
                class="site-nav-link {{ $item['active'] ? 'active' : '' }}"
                @if ($item['current'])
                    aria-current="page"
                @endif
                @if ($item['target'])
                    target="{{ $item['target'] }}"
                @endif
                @if ($item['rel'])
                    rel="{{ $item['rel'] }}"
                @endif
            >
                {{ $item['label'] }}
            </a>
        @else
            <span
                class="site-nav-link site-nav-link-disabled"
            >
                {{ $item['label'] }}
            </span>
        @endif

        @if ($hasChildren)
            <button
                type="button"
                class="site-nav-submenu-toggle"
                data-nav-submenu-toggle
                aria-label="Buka submenu {{ $item['label'] }}"
                aria-expanded="false"
            >
                <svg
                    viewBox="0 0 20 20"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="m6 8 4 4 4-4" />
                </svg>
            </button>
        @endif
    </div>

    @if ($hasChildren)
        <div
            class="site-nav-submenu"
            data-nav-submenu
        >
            @foreach ($item['children'] as $child)
                @include(
                    'frontend.partials.menu-item',
                    [
                        'item' => $child,
                    ]
                )
            @endforeach
        </div>
    @endif
</div>
