<nav
    class="site-nav"
    data-site-nav
    aria-label="Navigasi utama"
>
    <div class="container site-nav-inner">
        @foreach ($headerMenus as $item)
            @include(
                'frontend.partials.menu-item',
                [
                    'item' => $item,
                ]
            )
        @endforeach
    </div>
</nav>
