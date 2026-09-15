<?php

namespace App\Services\Access;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccessService
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
         * Penugasan ganda harus dibereskan melalui pengelolaan akses.
         * Jangan memilih role paling tinggi secara otomatis.
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
     * all_units : seluruh unit
     */
    public function scope(
        User $user,
        string $permission,
        bool $forDelegation = false,
        array $visited = []
    ): ?string {
        if (!$user->is_active || in_array($user->id, $visited, true)) {
            return null;
        }

        /*
         * Permission yang tidak dikenal tidak boleh memperoleh akses,
         * termasuk melalui salah ketik nama permission.
         */
        if (!DB::table('permissions')->where('slug', $permission)->exists()) {
            return null;
        }

        $role = $this->role($user);

        if ($role === 'super-admin') {
            return 'all_units';
        }

        if (!in_array($role, ['admin', 'user'], true)) {
            return null;
        }

        if ($forDelegation && $role !== 'admin') {
            return null;
        }

        $profile = DB::table('user_access_profiles')
            ->where('user_id', $user->id)
            ->first();

        if (!$profile || !$profile->is_enabled || !$profile->parent_user_id) {
            return null;
        }

        if (!in_array($profile->data_scope, ['own_unit', 'all_units'], true)) {
            return null;
        }

        if ($profile->data_scope === 'own_unit' && !$user->unit_id) {
            return null;
        }

        $grant = DB::table('user_permission_grants')
            ->join(
                'permissions',
                'permissions.id',
                '=',
                'user_permission_grants.permission_id'
            )
            ->where('user_permission_grants.user_id', $user->id)
            ->where('permissions.slug', $permission)
            ->select('user_permission_grants.can_delegate')
            ->first();

        if (!$grant || ($forDelegation && !$grant->can_delegate)) {
            return null;
        }

        $parent = User::find($profile->parent_user_id);

        if (!$parent || !$parent->is_active) {
            return null;
        }

        $parentRole = $this->role($parent);

        /*
         * Admin hanya menerima kewenangan dari Super Admin.
         * User dapat menerima kewenangan dari Admin atau Super Admin.
         */
        if ($role === 'admin' && $parentRole !== 'super-admin') {
            return null;
        }

        if (
            $role === 'user'
            && !in_array($parentRole, ['admin', 'super-admin'], true)
        ) {
            return null;
        }

        $parentScope = $this->scope(
            $parent,
            $permission,
            true,
            [...$visited, $user->id]
        );

        if ($parentScope === null) {
            return null;
        }

        if ($parentScope === 'own_unit') {
            if (
                !$parent->unit_id
                || (string) $parent->unit_id !== (string) $user->unit_id
            ) {
                return null;
            }

            /*
             * Pencabutan cakupan seluruh unit dari parent langsung
             * membatasi akses turunannya menjadi unit sendiri.
             */
            return 'own_unit';
        }

        return $profile->data_scope;
    }

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
