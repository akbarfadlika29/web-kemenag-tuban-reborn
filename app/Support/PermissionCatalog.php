<?php

namespace App\Support;

class PermissionCatalog
{
    public const SCOPE_TENANT = 'tenant';
    public const SCOPE_GLOBAL = 'global';

    /**
     * tenant: permission mengikuti own_unit/all_units.
     * global: resource berlaku untuk seluruh website dan hanya boleh all_units.
     */
    public static function modules(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dasbor',
                'scope' => self::SCOPE_TENANT,
                'actions' => ['view'],
            ],
            'news' => [
                'label' => 'Berita',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'submit', 'review', 'publish', 'unpublish',
                ],
            ],
            'news-categories' => [
                'label' => 'Kategori Berita',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'news-tags' => [
                'label' => 'Tag Berita',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'ppid-informations' => [
                'label' => 'Informasi PPID',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'ppid-categories' => [
                'label' => 'Kategori PPID',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'regulations' => [
                'label' => 'Regulasi',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'regulation-types' => [
                'label' => 'Jenis Regulasi',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'pages' => [
                'label' => 'Halaman',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'announcements' => [
                'label' => 'Pengumuman',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'agendas' => [
                'label' => 'Agenda',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'galleries' => [
                'label' => 'Galeri',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'services' => [
                'label' => 'Layanan',
                'scope' => self::SCOPE_TENANT,
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'service-categories' => [
                'label' => 'Kategori Layanan',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'media' => [
                'label' => 'Pustaka Media',
                'scope' => self::SCOPE_TENANT,
                'actions' => ['view', 'upload', 'update', 'delete'],
            ],
            'menus' => [
                'label' => 'Menu Navigasi',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'hero-slides' => [
                'label' => 'Slide Beranda',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'quick-links' => [
                'label' => 'Akses Cepat',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'related-links' => [
                'label' => 'Tautan Terkait',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'settings' => [
                'label' => 'Pengaturan Website',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'update'],
            ],
            'frontend-settings' => [
                'label' => 'Tampilan dan Interaksi',
                'scope' => self::SCOPE_GLOBAL,
                'actions' => ['view', 'update'],
            ],
            'content-statistics' => [
                'label' => 'Statistik Konten',
                'scope' => self::SCOPE_TENANT,
                'actions' => ['view'],
            ],
            'users' => [
                'label' => 'Pengguna',
                'scope' => self::SCOPE_TENANT,
                'actions' => ['view', 'create', 'update'],
            ],
            'units' => [
                'label' => 'Unit Kerja',
                'scope' => self::SCOPE_TENANT,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'roles' => [
                'label' => 'Role Akses',
                'scope' => self::SCOPE_TENANT,
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'access' => [
                'label' => 'Penugasan Akses Pengguna',
                'scope' => self::SCOPE_TENANT,
                'actions' => ['view', 'assign'],
            ],
        ];
    }

    public static function permissions(): array
    {
        $labels = [
            'view' => 'Lihat',
            'create' => 'Tambah',
            'update' => 'Ubah',
            'delete' => 'Hapus',
            'upload' => 'Unggah',
            'submit' => 'Ajukan',
            'review' => 'Periksa Pengajuan',
            'publish' => 'Terbitkan',
            'unpublish' => 'Tarik Publikasi',
            'assign' => 'Tetapkan Akses',
        ];

        $permissions = [];

        foreach (self::modules() as $module => $definition) {
            foreach ($definition['actions'] as $action) {
                $permissions[] = [
                    'slug' => $module.'.'.$action,
                    'module' => $module,
                    'name' => $labels[$action].' '.$definition['label'],
                    'scope' => $definition['scope'],
                ];
            }
        }

        return $permissions;
    }

    public static function has(string $permission): bool
    {
        return self::definition($permission) !== null;
    }

    public static function definition(string $permission): ?array
    {
        [$module, $action] = array_pad(explode('.', $permission, 2), 2, null);
        $definition = self::modules()[$module] ?? null;

        if (
            $definition === null
            || $action === null
            || !in_array($action, $definition['actions'], true)
        ) {
            return null;
        }

        return [
            'slug' => $permission,
            'module' => $module,
            'action' => $action,
            'label' => $definition['label'],
            'scope' => $definition['scope'],
        ];
    }
}
