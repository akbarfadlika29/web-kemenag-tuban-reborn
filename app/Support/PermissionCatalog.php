<?php

namespace App\Support;

class PermissionCatalog
{
    public static function modules(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dasbor',
                'actions' => ['view'],
            ],
            'news' => [
                'label' => 'Berita',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'submit', 'review', 'publish', 'unpublish',
                ],
            ],
            'news-categories' => [
                'label' => 'Kategori Berita',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'news-tags' => [
                'label' => 'Tag Berita',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'ppid-informations' => [
                'label' => 'Informasi PPID',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'ppid-categories' => [
                'label' => 'Kategori PPID',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'regulations' => [
                'label' => 'Regulasi',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'regulation-types' => [
                'label' => 'Jenis Regulasi',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'pages' => [
                'label' => 'Halaman',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'announcements' => [
                'label' => 'Pengumuman',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'agendas' => [
                'label' => 'Agenda',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'galleries' => [
                'label' => 'Galeri',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'services' => [
                'label' => 'Layanan',
                'actions' => [
                    'view', 'create', 'update', 'delete',
                    'publish', 'unpublish',
                ],
            ],
            'service-categories' => [
                'label' => 'Kategori Layanan',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'media' => [
                'label' => 'Pustaka Media',
                'actions' => ['view', 'upload', 'update', 'delete'],
            ],
            'menus' => [
                'label' => 'Menu Navigasi',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'hero-slides' => [
                'label' => 'Slide Beranda',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'quick-links' => [
                'label' => 'Akses Cepat',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'related-links' => [
                'label' => 'Tautan Terkait',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'settings' => [
                'label' => 'Pengaturan Website',
                'actions' => ['view', 'update'],
            ],
            'frontend-settings' => [
                'label' => 'Tampilan dan Interaksi',
                'actions' => ['view', 'update'],
            ],
            'content-statistics' => [
                'label' => 'Statistik Konten',
                'actions' => ['view'],
            ],
            'users' => [
                'label' => 'Pengguna',
                'actions' => ['view', 'create', 'update'],
            ],
            'units' => [
                'label' => 'Unit Kerja',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'roles' => [
                'label' => 'Role',
                'actions' => ['view', 'create', 'update', 'delete'],
            ],
            'access' => [
                'label' => 'Penugasan Akses Pengguna',
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
                ];
            }
        }

        return $permissions;
    }
}
