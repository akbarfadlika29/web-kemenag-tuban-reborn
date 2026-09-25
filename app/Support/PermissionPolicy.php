<?php

namespace App\Support;

class PermissionPolicy
{
    /**
     * System-role User hanya berfungsi sebagai kontributor berita.
     * Publish/review tetap menjadi kewenangan Admin/Super Admin.
     */
    private const USER_DENIED = [
        'news.review',
        'news.publish',
        'news.unpublish',
    ];

    /**
     * Perubahan struktur unit adalah operasi organisasi global.
     * units.view tetap boleh own_unit agar Admin/User dapat melihat unitnya.
     */
    private const UNIT_MUTATIONS = [
        'units.create',
        'units.update',
        'units.delete',
    ];

    public static function allowedScopes(string $permission): array
    {
        $definition = PermissionCatalog::definition($permission);

        if ($definition === null) {
            return [];
        }

        if (
            $definition['scope'] === PermissionCatalog::SCOPE_GLOBAL
            || in_array($permission, self::UNIT_MUTATIONS, true)
        ) {
            return ['all_units'];
        }

        return ['own_unit', 'all_units'];
    }

    public static function scopeAllowed(string $permission, ?string $scope): bool
    {
        return $scope !== null
            && in_array($scope, self::allowedScopes($permission), true);
    }

    public static function systemRoleAllows(?string $systemRole, string $permission): bool
    {
        if (!PermissionCatalog::has($permission)) {
            return false;
        }

        if ($systemRole === 'super-admin') {
            return true;
        }

        if (!in_array($systemRole, ['admin', 'user'], true)) {
            return false;
        }

        return !(
            $systemRole === 'user'
            && in_array($permission, self::USER_DENIED, true)
        );
    }

    public static function isGlobal(string $permission): bool
    {
        $definition = PermissionCatalog::definition($permission);

        return ($definition['scope'] ?? null) === PermissionCatalog::SCOPE_GLOBAL;
    }

    public static function requiresAllUnits(string $permission): bool
    {
        return self::allowedScopes($permission) === ['all_units'];
    }
}
