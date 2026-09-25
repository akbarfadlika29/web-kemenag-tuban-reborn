<?php

namespace App\Support;

use App\Models\User;
use App\Services\Access\AccessService;
use App\Services\Access\UnitAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ManagedUsers
{
    public static function query(User $actor, string $permission): Builder
    {
        $access = app(AccessService::class);
        $query = User::query();

        if ($access->isSuperAdmin($actor)) {
            return $query;
        }

        $scope = $access->scope($actor, $permission);

        if (
            !$actor->is_active
            || $access->role($actor) !== 'admin'
            || $scope === null
        ) {
            return $query->whereRaw('1 = 0');
        }

        $query
            ->where('users.id', '!=', $actor->id)
            ->where('users.id', '!=', 2)
            ->whereIn(
                'users.id',
                DB::table('user_access_profiles')
                    ->select('user_id')
                    ->where('parent_user_id', $actor->id)
            )
            ->whereIn(
                'users.id',
                DB::table('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->select('role_user.user_id')
                    ->where('roles.slug', 'user')
            )
            ->whereNotIn(
                'users.id',
                DB::table('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->select('role_user.user_id')
                    ->where('roles.slug', '!=', 'user')
            );

        if ($scope === 'own_unit') {
            if ($actor->unit_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('users.unit_id', $actor->unit_id);
        }

        return $query;
    }

    public static function assertTarget(
        User $actor,
        User $target,
        string $permission
    ): void {
        $access = app(AccessService::class);

        abort_unless(
            $actor->is_active
            && in_array($access->role($actor), ['super-admin', 'admin'], true)
            && $access->allows($actor, $permission),
            403,
            'Anda tidak memiliki izin mengelola pengguna.'
        );

        abort_unless(
            self::query($actor, $permission)
                ->whereKey($target->id)
                ->exists(),
            403,
            'Pengguna berada di luar kewenangan pengelolaan Anda.'
        );
    }

    public static function units(User $actor, string $permission): Builder
    {
        return app(UnitAccessService::class)
            ->query($actor, $permission, true);
    }

    public static function visibleUnitCount(User $actor): int
    {
        return app(UnitAccessService::class)
            ->query($actor, 'units.view', false)
            ->count();
    }
}