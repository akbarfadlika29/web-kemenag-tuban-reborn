<?php

namespace App\Services\Access;

use App\Models\User;
use App\Support\PermissionCatalog;
use App\Support\PermissionPolicy;
use Illuminate\Support\Facades\DB;

class RoleAccessService extends AccessService
{
    public function scope(
        User $user,
        string $permission,
        bool $forDelegation = false,
        array $visited = []
    ): ?string {
        if (!$user->is_active || in_array($user->id, $visited, true)) {
            return null;
        }

        if (!PermissionCatalog::has($permission)) {
            return null;
        }

        $level = $this->role($user);

        /*
         * Role ceiling selalu diperiksa sebelum grant access-role.
         * Access role tidak dapat menaikkan kewenangan system role.
         */
        if (!PermissionPolicy::systemRoleAllows($level, $permission)) {
            return null;
        }

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
            ->first(['parent_user_id', 'is_enabled']);

        if (
            !$profile
            || !$profile->is_enabled
            || !$profile->parent_user_id
        ) {
            return null;
        }

        $grant = DB::table('access_role_user as aru')
            ->join('access_roles as ar', 'ar.id', '=', 'aru.access_role_id')
            ->join(
                'access_role_permissions as arp',
                'arp.access_role_id',
                '=',
                'ar.id'
            )
            ->join('permissions as p', 'p.id', '=', 'arp.permission_id')
            ->where('aru.user_id', $user->id)
            ->where('ar.is_active', true)
            ->where('p.slug', $permission)
            ->first([
                'ar.owner_user_id',
                'arp.data_scope',
                'arp.can_delegate',
            ]);

        if (!$grant) {
            return null;
        }

        if ($forDelegation && !$grant->can_delegate) {
            return null;
        }

        /*
         * Global resource dan mutasi struktur unit wajib all_units.
         * Grant lama yang menggunakan own_unit otomatis menjadi tidak efektif.
         */
        if (!PermissionPolicy::scopeAllowed($permission, $grant->data_scope)) {
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
         * Access role harus dimiliki pemberi akses langsung atau Super Admin.
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

        if ($parentScope === 'own_unit') {
            if (
                $grant->data_scope !== 'own_unit'
                || $parent->unit_id === null
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
