<?php

namespace App\Services\Access;

use App\Models\Unit;
use App\Models\User;
use App\Support\AdminRoutePermission;
use Illuminate\Database\Eloquent\Builder;

class UnitAccessService
{
    public function __construct(
        private readonly AccessService $access
    ) {
    }

    public function query(
        User $actor,
        string $permission,
        bool $activeOnly = true,
        int|string|null $includeUnitId = null
    ): Builder {
        $query = Unit::query();
        $scope = $this->access->scope($actor, $permission);

        if ($activeOnly) {
            $query->where(function (Builder $query) use ($includeUnitId) {
                $query->where('is_active', true);

                if ($includeUnitId !== null && $includeUnitId !== '') {
                    $query->orWhereKey($includeUnitId);
                }
            });
        }

        if ($scope === 'all_units') {
            return $query;
        }

        if ($scope === 'own_unit' && $actor->unit_id !== null) {
            return $query->whereKey($actor->unit_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function forCurrentRoute(
        User $actor,
        string $module,
        bool $activeOnly = true,
        int|string|null $includeUnitId = null
    ): Builder {
        return $this->query(
            $actor,
            $this->permissionForCurrentRoute($module),
            $activeOnly,
            $includeUnitId
        );
    }

    public function permissionForCurrentRoute(string $module): string
    {
        $routeName = request()->route()?->getName();
        $required = is_string($routeName)
            ? AdminRoutePermission::required($routeName)
            : null;

        foreach ($required ?? [] as $permission) {
            if (str_starts_with($permission, $module.'.')) {
                return $permission;
            }
        }

        return $module.'.view';
    }

    public function assertVisible(
        User $actor,
        string $permission,
        Unit|int|string|null $unit
    ): void {
        $unitId = $unit instanceof Unit ? $unit->getKey() : $unit;

        abort_unless(
            $unitId !== null
            && $this->query($actor, $permission, false)
                ->whereKey($unitId)
                ->exists(),
            403,
            'Unit berada di luar cakupan akses Anda.'
        );
    }

    public function assertAllUnits(User $actor, string $permission): void
    {
        abort_unless(
            $this->access->scope($actor, $permission) === 'all_units',
            403,
            'Tindakan ini memerlukan cakupan seluruh unit.'
        );
    }
}
