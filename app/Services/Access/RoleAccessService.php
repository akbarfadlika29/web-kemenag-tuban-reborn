<?php

namespace App\Services\Access;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleAccessService extends AccessService
{
    /**
     * null      : ditolak
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

        if (!DB::table('permissions')->where('slug', $permission)->exists()) {
            return null;
        }

        $level = $this->role($user);

        if ($level === 'super-admin') {
            return 'all_units';
        }

        if (!in_array($level, ['admin', 'user'], true)) {
            return null;
        }

        if ($forDelegation && $level !== 'admin') {
            return null;
        }

        $profile = DB::table('user_access_profiles')
            ->where('user_id', $user->id)
            ->first();

        if (
            !$profile
            || !$profile->is_enabled
            || !$profile->parent_user_id
        ) {
            return null;
        }

        $grant = DB::table('access_role_user')
            ->join(
                'access_roles',
                'access_roles.id',
                '=',
                'access_role_user.access_role_id'
            )
            ->join(
                'access_role_permissions',
                'access_role_permissions.access_role_id',
                '=',
                'access_roles.id'
            )
            ->join(
                'permissions',
                'permissions.id',
                '=',
                'access_role_permissions.permission_id'
            )
            ->where('access_role_user.user_id', $user->id)
            ->where('access_roles.is_active', true)
            ->where('permissions.slug', $permission)
            ->first([
                'access_roles.owner_user_id',
                'access_role_permissions.data_scope',
                'access_role_permissions.can_delegate',
            ]);

        if (!$grant) {
            return null;
        }

        if ($forDelegation && !$grant->can_delegate) {
            return null;
        }

        if (!in_array($grant->data_scope, ['own_unit', 'all_units'], true)) {
            return null;
        }

        if ($grant->data_scope === 'own_unit' && $user->unit_id === null) {
            return null;
        }

        $parent = User::find($profile->parent_user_id);
        $owner = User::find($grant->owner_user_id);

        if (!$parent || !$parent->is_active || !$owner || !$owner->is_active) {
            return null;
        }

        $parentLevel = $this->role($parent);

        if ($level === 'admin' && $parentLevel !== 'super-admin') {
            return null;
        }

        if (
            $level === 'user'
            && !in_array($parentLevel, ['admin', 'super-admin'], true)
        ) {
            return null;
        }

        /*
         * Role phải thuộc pemberi akses langsung atau Super Admin.
         * Role milik Admin lain tidak boleh dipakai diam-diam.
         */
        if (
            (int) $owner->id !== (int) $parent->id
            && !$this->isSuperAdmin($owner)
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

        /*
         * Cakupan role pengguna tidak boleh melampaui pemberi akses.
         * Pembatasan dihitung ulang, sehingga pencabutan langsung berlaku.
         */
        if ($parentScope === 'own_unit') {
            if (
                $parent->unit_id === null
                || $user->unit_id === null
                || (string) $parent->unit_id !== (string) $user->unit_id
            ) {
                return null;
            }

            return 'own_unit';
        }

        return $grant->data_scope;
    }
}
