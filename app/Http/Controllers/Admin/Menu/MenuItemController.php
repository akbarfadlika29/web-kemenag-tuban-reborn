<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Menu\StoreMenuItemRequest;
use App\Http\Requests\Admin\Menu\UpdateMenuItemRequest;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $status = (string) $request->input(
            'status',
            ''
        );

        if (
            !in_array(
                $status,
                ['', '1', '0'],
                true
            )
        ) {
            $status = '';
        }

        $hasFilter =
            $search !== ''
            || $status !== '';

        $headerMenus =
            $this->menuTreeByLocation(
                'header'
            );

        $footerMenus =
            $this->menuTreeByLocation(
                'footer'
            );

        if ($hasFilter) {
            $headerMenus =
                $this->filterMenuTree(
                    $headerMenus,
                    $search,
                    $status
                );

            $footerMenus =
                $this->filterMenuTree(
                    $footerMenus,
                    $search,
                    $status
                );
        }

        $headerMenuCount =
            $this->countMenuTree(
                $headerMenus
            );

        $footerMenuCount =
            $this->countMenuTree(
                $footerMenus
            );

        return view(
            'admin.menus.index',
            compact(
                'headerMenus',
                'footerMenus',
                'search',
                'status',
                'hasFilter',
                'headerMenuCount',
                'footerMenuCount'
            )
        );
    }
    public function create(): View
    {
        $parents = $this->parentOptions();

        $pages = Page::query()
            ->orderBy('title')
            ->get();

        $frontendRoutes =
            $this->frontendRouteOptions();

        $nextSortOrders =
            $this->nextSortOrders();

        $excludedIds = [];

        return view(
            'admin.menus.create',
            compact(
                'parents',
                'pages',
                'frontendRoutes',
                'nextSortOrders',
                'excludedIds'
            )
        );
    }

    public function store(
        StoreMenuItemRequest $request
    ): RedirectResponse {
        $data = $this->normalizeTarget(
            $request->validated()
        );

        $this->validateParentLocation(
            $data
        );

        MenuItem::create($data);

        return redirect()
            ->route('admin.menus.index')
            ->with(
                'success',
                'Menu navigasi berhasil ditambahkan.'
            );
    }

    public function edit(
        MenuItem $menu
    ): View {
        $excludedIds = array_merge(
            [$menu->id],
            $menu->descendantIds()
        );

        $parents = $this->parentOptions();

        $pages = Page::query()
            ->orderBy('title')
            ->get();

        $frontendRoutes =
            $this->frontendRouteOptions();

        $nextSortOrders =
            $this->nextSortOrders(
                $menu->id
            );

        return view(
            'admin.menus.edit',
            compact(
                'menu',
                'parents',
                'pages',
                'frontendRoutes',
                'excludedIds',
                'nextSortOrders'
            )
        );
    }

    public function update(
        UpdateMenuItemRequest $request,
        MenuItem $menu
    ): RedirectResponse {
        $data = $request->validated();

        if (
            !empty($data['parent_id']) &&
            in_array(
                (int) $data['parent_id'],
                array_merge(
                    [$menu->id],
                    $menu->descendantIds()
                ),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'parent_id' =>
                    'Menu tidak dapat dijadikan anak dari dirinya sendiri atau turunannya.',
            ]);
        }

        $this->validateParentLocation(
            $data,
            $menu
        );

        $menu->update(
            $this->normalizeTarget($data)
        );

        return redirect()
            ->route('admin.menus.index')
            ->with(
                'success',
                'Menu navigasi berhasil diperbarui.'
            );
    }

    public function destroy(
        MenuItem $menu
    ): RedirectResponse {
        if ($menu->children()->exists()) {
            return redirect()
                ->route('admin.menus.index')
                ->with(
                    'error',
                    'Menu tidak dapat dihapus karena masih memiliki submenu.'
                );
        }

        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with(
                'success',
                'Menu navigasi berhasil dihapus.'
            );
    }

    public function move(
        Request $request,
        MenuItem $menu
    ) {
        $validated = $request->validate([
            'target_id' => [
                'nullable',
                'integer',
                'exists:menu_items,id',
            ],

            'position' => [
                'required',
                Rule::in([
                    'before',
                    'after',
                    'inside',
                    'root',
                ]),
            ],


        ]);

        $location =
            $menu->location;

        $target = isset(
            $validated['target_id']
        )
            ? MenuItem::findOrFail(
                $validated['target_id']
            )
            : null;

        $position =
            $validated['position'];

        if (
            $target &&
            $target->location !== $location
        ) {
            throw ValidationException::withMessages([
                'target_id' =>
                    'Menu tujuan berada pada lokasi navigasi yang berbeda.',
            ]);
        }

        if (
            $target &&
            $target->id === $menu->id
        ) {
            throw ValidationException::withMessages([
                'target_id' =>
                    'Menu tidak dapat dipindahkan ke dirinya sendiri.',
            ]);
        }

        if (
            $target &&
            in_array(
                $target->id,
                $menu->descendantIds(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'target_id' =>
                    'Menu tidak dapat dipindahkan ke salah satu submenu turunannya.',
            ]);
        }

        DB::transaction(
            function () use (
                $menu,
                $target,
                $position,
                $location
            ) {
                $oldParentId =
                    $menu->parent_id;

                if ($position === 'root') {
                    $newParentId = null;

                    $siblingIds =
                        MenuItem::query()
                            ->where(
                                'location',
                                $location
                            )
                            ->whereNull(
                                'parent_id'
                            )
                            ->where(
                                'id',
                                '!=',
                                $menu->id
                            )
                            ->orderBy(
                                'sort_order'
                            )
                            ->orderBy(
                                'label'
                            )
                            ->lockForUpdate()
                            ->pluck('id')
                            ->all();

                    $siblingIds[] =
                        $menu->id;
                } elseif (
                    $position === 'inside'
                ) {
                    $newParentId =
                        $target->id;

                    $siblingIds =
                        MenuItem::query()
                            ->where(
                                'location',
                                $location
                            )
                            ->where(
                                'parent_id',
                                $newParentId
                            )
                            ->where(
                                'id',
                                '!=',
                                $menu->id
                            )
                            ->orderBy(
                                'sort_order'
                            )
                            ->orderBy(
                                'label'
                            )
                            ->lockForUpdate()
                            ->pluck('id')
                            ->all();

                    $siblingIds[] =
                        $menu->id;
                } else {
                    $newParentId =
                        $target->parent_id;

                    $siblingsQuery =
                        MenuItem::query()
                            ->where(
                                'location',
                                $location
                            )
                            ->where(
                                'id',
                                '!=',
                                $menu->id
                            );

                    if (
                        $newParentId === null
                    ) {
                        $siblingsQuery
                            ->whereNull(
                                'parent_id'
                            );
                    } else {
                        $siblingsQuery
                            ->where(
                                'parent_id',
                                $newParentId
                            );
                    }

                    $siblingIds =
                        $siblingsQuery
                            ->orderBy(
                                'sort_order'
                            )
                            ->orderBy(
                                'label'
                            )
                            ->lockForUpdate()
                            ->pluck('id')
                            ->all();

                    $targetIndex =
                        array_search(
                            $target->id,
                            $siblingIds,
                            true
                        );

                    if (
                        $targetIndex === false
                    ) {
                        throw ValidationException::withMessages([
                            'target_id' =>
                                'Posisi menu tujuan tidak ditemukan.',
                        ]);
                    }

                    $insertIndex =
                        $position === 'before'
                            ? $targetIndex
                            : $targetIndex + 1;

                    array_splice(
                        $siblingIds,
                        $insertIndex,
                        0,
                        [$menu->id]
                    );
                }

                $menu->update([
                    'parent_id' =>
                        $newParentId,
                ]);

                foreach (
                    $siblingIds as
                    $index => $id
                ) {
                    MenuItem::whereKey(
                        $id
                    )->update([
                        'sort_order' =>
                            $index + 1,
                    ]);
                }

                if (
                    $oldParentId !==
                    $newParentId
                ) {
                    $this
                        ->normalizeSiblingOrder(
                            $oldParentId,
                            $location
                        );
                }
            }
        );

        return response()->json([
            'message' =>
                'Menu navigasi berhasil dipindahkan.',
        ]);
    }

    private function normalizeSiblingOrder(
        ?int $parentId,
        string $location
    ): void {
        $query =
            MenuItem::query()
                ->where(
                    'location',
                    $location
                );

        if ($parentId === null) {
            $query->whereNull(
                'parent_id'
            );
        } else {
            $query->where(
                'parent_id',
                $parentId
            );
        }

        $query
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->each(
                function (
                    MenuItem $menu,
                    int $index
                ) {
                    $menu->update([
                        'sort_order' =>
                            $index + 1,
                    ]);
                }
            );
    }
    private function menuTreeByLocation(
        string $location
    ): \Illuminate\Database\Eloquent\Collection {
        return MenuItem::query()
            ->where(
                'location',
                $location
            )
            ->whereNull('parent_id')
            ->with([
                'page',
                'childrenRecursive.page',
            ])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    private function filterMenuTree(
        \Illuminate\Database\Eloquent\Collection $menus,
        string $search,
        string $status
    ): \Illuminate\Database\Eloquent\Collection {
        return $menus
            ->filter(
                function (MenuItem $menu) use (
                    $search,
                    $status
                ) {
                    $children =
                        $this->filterMenuTree(
                            $menu->childrenRecursive,
                            $search,
                            $status
                        );

                    $menu->setRelation(
                        'childrenRecursive',
                        $children
                    );

                    $matchesSearch =
                        $search === ''
                        || mb_stripos(
                            (string) $menu->label,
                            $search
                        ) !== false;

                    $matchesStatus =
                        $status === ''
                        || $menu->is_active
                            === ($status === '1');

                    return
                        (
                            $matchesSearch
                            && $matchesStatus
                        )
                        || $children->isNotEmpty();
                }
            )
            ->values();
    }

    private function countMenuTree(
        \Illuminate\Database\Eloquent\Collection $menus
    ): int {
        return $menus->sum(
            fn (MenuItem $menu) =>
                1
                + $this->countMenuTree(
                    $menu->childrenRecursive
                )
        );
    }

    private function frontendRouteOptions(): array
    {
        return collect(Route::getRoutes())
            ->filter(
                fn ($route) =>
                    filled($route->getName())
            )
            ->filter(
                fn ($route) =>
                    in_array(
                        'GET',
                        $route->methods(),
                        true
                    )
            )
            ->filter(
                fn ($route) =>
                    str_starts_with(
                        $route->getActionName(),
                        'App\\Http\\Controllers\\Frontend\\'
                    )
            )
            ->reject(
                fn ($route) =>
                    str_starts_with(
                        (string) $route->getName(),
                        'admin.'
                    )
            )
            ->filter(
                fn ($route) =>
                    count(
                        $route->parameterNames()
                    ) === 0
            )
            ->map(
                fn ($route) => [
                    'name' =>
                        (string) $route->getName(),

                    'uri' =>
                        $route->uri(),
                ]
            )
            ->sortBy('name')
            ->values()
            ->all();
    }
    private function parentOptions()
    {
        return MenuItem::query()
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    private function nextSortOrders(
        ?int $excludedId = null
    ): array {
        $query = MenuItem::query();

        if ($excludedId !== null) {
            $query->where(
                'id',
                '!=',
                $excludedId
            );
        }

        $orders = $query
            ->selectRaw(
                'parent_id, MAX(sort_order) as max_sort'
            )
            ->groupBy('parent_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $key =
                    $item->parent_id ??
                    'root';

                return [
                    $key =>
                        ((int) $item->max_sort) + 1,
                ];
            })
            ->toArray();

        $orders['root'] =
            $orders['root'] ?? 1;

        return $orders;
    }

    private function validateParentLocation(
        array $data,
        ?MenuItem $menu = null
    ): void {
        $location =
            $data['location']
            ?? $menu?->location;

        $parentId =
            $data['parent_id']
            ?? null;

        if ($parentId) {
            $parent =
                MenuItem::find(
                    $parentId
                );

            if (
                $parent
                && $parent->location
                    !== $location
            ) {
                throw ValidationException::withMessages([
                    'parent_id' =>
                        'Induk menu harus berada pada lokasi navigasi yang sama.',
                ]);
            }
        }

        if (
            $menu
            && $menu->location !== $location
            && $menu->children()->exists()
        ) {
            throw ValidationException::withMessages([
                'location' =>
                    'Lokasi menu yang memiliki submenu tidak dapat diubah sebelum submenu dipindahkan.',
            ]);
        }
    }

    private function normalizeTarget(
        array $data
    ): array {
        if ($data['type'] !== 'page') {
            $data['page_id'] = null;
        }

        if ($data['type'] !== 'route') {
            $data['route_name'] = null;
        }

        if ($data['type'] !== 'url') {
            $data['url'] = null;
        }

        return $data;
    }
}
