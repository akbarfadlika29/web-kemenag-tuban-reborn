<?php

namespace App\Models\Scopes;

use App\Services\Access\AccessService;
use App\Support\AdminRoutePermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AdminUnitScope implements Scope
{
    public function __construct(
        private readonly string $module
    ) {
    }

    public function apply(Builder $builder, Model $model): void
    {
        $request = request();
        $route = $request->route();

        if (
            !$route
            || !str_starts_with((string) $route->getName(), 'admin.')
        ) {
            return;
        }

        $user = $request->user();

        if (!$user || !$user->is_active) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $access = app(AccessService::class);

        if ($access->isSuperAdmin($user)) {
            return;
        }

        $permission = $this->module.'.view';

        $required = AdminRoutePermission::required(
            (string) $route->getName()
        );

        foreach ($required ?? [] as $candidate) {
            if (str_starts_with($candidate, $this->module.'.')) {
                $permission = $candidate;
                break;
            }
        }

        $scope = $access->scope($user, $permission);

        if ($scope === 'all_units') {
            return;
        }

        if ($scope !== 'own_unit' || $user->unit_id === null) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where(
            $model->qualifyColumn('unit_id'),
            $user->unit_id
        );
    }
}
