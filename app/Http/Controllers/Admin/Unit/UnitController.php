<?php

namespace App\Http\Controllers\Admin\Unit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Unit\StoreUnitRequest;
use App\Http\Requests\Admin\Unit\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\Access\AccessService;
use App\Services\Access\UnitAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct(
        private readonly AccessService $access,
        private readonly UnitAccessService $units
    ) {
    }

    public function index(Request $request): View
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        $search = trim((string) $request->input('search', ''));
        $type = (string) $request->input('type', '');
        $status = (string) $request->input('status', '');
        $hasFilter = $search !== '' || $type !== '' || $status !== '';
        $scope = $this->access->scope($actor, 'units.view');

        $base = $this->units->query($actor, 'units.view', false);

        if ($hasFilter || $scope !== 'all_units') {
            $units = $base
                ->with('parent')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'ilike', '%'.$search.'%')
                            ->orWhere('short_name', 'ilike', '%'.$search.'%')
                            ->orWhere('code', 'ilike', '%'.$search.'%');
                    });
                })
                ->when($type !== '', fn ($query) => $query->where('type', $type))
                ->when(
                    $status !== '',
                    fn ($query) => $query->where('is_active', $status === '1')
                )
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } else {
            $units = $base
                ->whereNull('parent_id')
                ->with(['parent', 'childrenRecursive'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return view('admin.units.index', compact(
            'units',
            'search',
            'type',
            'status',
            'hasFilter'
        ));
    }

    public function create(Request $request): View
    {
        $actor = $request->user();
        abort_unless($actor, 401);
        $this->units->assertAllUnits($actor, 'units.create');

        $parents = Unit::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $excludedIds = [];
        $nextSortOrders = $this->nextSortOrders();

        return view('admin.units.create', compact(
            'parents',
            'excludedIds',
            'nextSortOrders'
        ));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor, 401);
        $this->units->assertAllUnits($actor, 'units.create');

        Unit::create($request->validated());

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(Request $request, Unit $unit): View
    {
        $actor = $request->user();
        abort_unless($actor, 401);
        $this->units->assertAllUnits($actor, 'units.update');

        $excludedIds = array_merge([$unit->id], $unit->descendantIds());

        $parents = Unit::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $nextSortOrders = $this->nextSortOrders($unit->id);

        return view('admin.units.edit', compact(
            'unit',
            'parents',
            'excludedIds',
            'nextSortOrders'
        ));
    }

    public function update(
        UpdateUnitRequest $request,
        Unit $unit
    ): RedirectResponse {
        $actor = $request->user();
        abort_unless($actor, 401);
        $this->units->assertAllUnits($actor, 'units.update');

        $unit->update($request->validated());

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor, 401);
        $this->units->assertAllUnits($actor, 'units.delete');

        if ($unit->children()->exists()) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', 'Unit kerja tidak dapat dihapus karena masih memiliki unit turunan.');
        }

        $unit->delete();

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit kerja berhasil dihapus.');
    }

    public function move(Request $request, Unit $unit)
    {
        $actor = $request->user();
        abort_unless($actor, 401);
        $this->units->assertAllUnits($actor, 'units.update');

        $validated = $request->validate([
            'target_id' => ['nullable', 'integer', 'exists:units,id'],
            'position' => ['required', Rule::in(['before', 'after', 'inside', 'root'])],
        ]);

        $target = isset($validated['target_id'])
            ? Unit::findOrFail($validated['target_id'])
            : null;
        $position = $validated['position'];

        if ($target && $target->id === $unit->id) {
            throw ValidationException::withMessages([
                'target_id' => 'Unit tidak dapat dipindahkan ke dirinya sendiri.',
            ]);
        }

        if ($target && in_array($target->id, $unit->descendantIds(), true)) {
            throw ValidationException::withMessages([
                'target_id' => 'Unit tidak dapat dipindahkan ke salah satu unit turunannya.',
            ]);
        }

        DB::transaction(function () use ($unit, $target, $position) {
            $oldParentId = $unit->parent_id;

            if ($position === 'root') {
                $newParentId = null;
                $siblingIds = Unit::query()
                    ->whereNull('parent_id')
                    ->where('id', '!=', $unit->id)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->lockForUpdate()
                    ->pluck('id')
                    ->all();
                $siblingIds[] = $unit->id;
            } elseif ($position === 'inside') {
                $newParentId = $target->id;
                $siblingIds = Unit::query()
                    ->where('parent_id', $newParentId)
                    ->where('id', '!=', $unit->id)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->lockForUpdate()
                    ->pluck('id')
                    ->all();
                $siblingIds[] = $unit->id;
            } else {
                $newParentId = $target->parent_id;
                $siblingsQuery = Unit::query()->where('id', '!=', $unit->id);

                if ($newParentId === null) {
                    $siblingsQuery->whereNull('parent_id');
                } else {
                    $siblingsQuery->where('parent_id', $newParentId);
                }

                $siblingIds = $siblingsQuery
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->lockForUpdate()
                    ->pluck('id')
                    ->all();

                $targetIndex = array_search($target->id, $siblingIds, true);

                if ($targetIndex === false) {
                    throw ValidationException::withMessages([
                        'target_id' => 'Posisi tujuan tidak ditemukan.',
                    ]);
                }

                $insertIndex = $position === 'before'
                    ? $targetIndex
                    : $targetIndex + 1;
                array_splice($siblingIds, $insertIndex, 0, [$unit->id]);
            }

            $unit->update(['parent_id' => $newParentId]);

            foreach ($siblingIds as $index => $id) {
                Unit::whereKey($id)->update(['sort_order' => $index + 1]);
            }

            if ($oldParentId !== $newParentId) {
                $this->normalizeSiblingOrder($oldParentId);
            }
        });

        return response()->json([
            'message' => 'Unit kerja berhasil dipindahkan.',
        ]);
    }

    private function nextSortOrders(?int $ignoreId = null): array
    {
        $query = Unit::query();

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        $orders = $query
            ->selectRaw('parent_id, MAX(sort_order) as max_sort')
            ->groupBy('parent_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->parent_id ?? 'root';

                return [$key => ((int) $item->max_sort) + 1];
            })
            ->toArray();

        $orders['root'] = $orders['root'] ?? 1;

        return $orders;
    }

    private function normalizeSiblingOrder(?int $parentId): void
    {
        $query = Unit::query();

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function (Unit $unit, int $index) {
                $unit->update(['sort_order' => $index + 1]);
            });
    }
}
