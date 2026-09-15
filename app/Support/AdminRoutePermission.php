<?php

namespace App\Support;

class AdminRoutePermission
{
    public static function publicRoutes(): array
    {
        return [
            'admin.login',
            'admin.login.store',
            'admin.logout',
        ];
    }

    /**
     * null berarti route belum dikenali.
     * Array kosong hanya untuk route autentikasi yang dikecualikan.
     */
    public static function required(string $name): ?array
    {
        if (in_array($name, self::publicRoutes(), true)) {
            return [];
        }

        $special = [
            'admin.access-roles.index' => ['roles.view'],
            'admin.access-roles.store' => ['roles.create'],
            'admin.access-roles.update' => ['roles.update'],
            'admin.access.index' => ['access.view'],
            'admin.access.edit' => ['access.assign'],
            'admin.access.update' => ['access.assign'],
            'admin.dashboard' => ['dashboard.view'],

            'admin.settings.index' => ['settings.view'],
            'admin.settings.update' => ['settings.update'],

            'admin.frontend-settings.edit' => ['frontend-settings.view'],
            'admin.frontend-settings.update' => ['frontend-settings.update'],

            'admin.content-statistics.index' => ['content-statistics.view'],

            'admin.media.create' => ['media.upload'],
            'admin.media.store' => ['media.upload'],
            'admin.media.picker.index' => ['media.view'],
            'admin.media.picker.upload' => ['media.upload'],

            'admin.regulations.document' => ['regulations.view'],
            'admin.regulations.media.index' => ['regulations.view', 'media.view'],
            'admin.regulations.media.preview' => ['regulations.view', 'media.view'],
            'admin.regulations.media.upload' => ['regulations.create', 'media.upload'],

            'admin.units.move' => ['units.update'],
            'admin.menus.move' => ['menus.update'],
        ];

        if (isset($special[$name])) {
            return $special[$name];
        }

        if (preg_match(
            '/^admin\.(news|pages|announcements|agendas|galleries|ppid-informations|services)\.media-picker$/',
            $name,
            $match
        )) {
            return [$match[1].'.view', 'media.view'];
        }

        if (!preg_match(
            '/^admin\.([a-z-]+)\.(index|show|create|store|edit|update|destroy)$/',
            $name,
            $match
        )) {
            return null;
        }

        $module = $match[1];

        if (!array_key_exists($module, PermissionCatalog::modules())) {
            return null;
        }

        $action = match ($match[2]) {
            'index', 'show' => 'view',
            'create', 'store' => 'create',
            'edit', 'update' => 'update',
            'destroy' => 'delete',
        };

        $permission = $module.'.'.$action;

        $known = array_column(PermissionCatalog::permissions(), 'slug');

        return in_array($permission, $known, true)
            ? [$permission]
            : null;
    }
}
