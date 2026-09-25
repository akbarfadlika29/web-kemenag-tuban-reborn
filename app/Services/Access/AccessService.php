<?php

namespace App\Services\Access;

use App\Models\User;
use Illuminate\Support\Facades\DB;

abstract class AccessService
{
    public function role(User $user): ?string
    {
        $roles = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->pluck('roles.slug')
            ->unique()
            ->values();

        /*
         * System role harus tepat satu. Penugasan ganda bersifat fail-closed.
         */
        return $roles->count() === 1 ? $roles->first() : null;
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->is_active
            && $this->role($user) === 'super-admin';
    }

    /**
     * null      : tidak memiliki akses
     * own_unit  : hanya unit pengguna
     * all_units : seluruh unit / resource global
     */
    abstract public function scope(
        User $user,
        string $permission,
        bool $forDelegation = false,
        array $visited = []
    ): ?string;

    public function allows(User $user, string $permission): bool
    {
        return $this->scope($user, $permission) !== null;
    }

    public function allowsUnit(
        User $user,
        string $permission,
        int|string|null $unitId
    ): bool {
        $scope = $this->scope($user, $permission);

        if ($scope === 'all_units') {
            return true;
        }

        return $scope === 'own_unit'
            && $unitId !== null
            && $user->unit_id !== null
            && (string) $unitId === (string) $user->unit_id;
    }

    public function canDelegate(User $user, string $permission): bool
    {
        return $this->scope($user, $permission, true) !== null;
    }
}
