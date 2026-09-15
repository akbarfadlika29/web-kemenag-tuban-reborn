<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PpidRbacSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::query()
                ->whereKey(2)
                ->where('email', 'superadmin@gmail.com')
                ->lockForUpdate()
                ->first();

            if (!$user || !$user->is_active) {
                throw new RuntimeException(
                    'Akun utama ID 2 / superadmin@gmail.com tidak cocok atau tidak aktif.'
                );
            }

            /*
             * Jangan mengubah role lama yang masih tersimpan secara diam-diam.
             * Normalisasi role harus selesai sebelum seeder dijalankan.
             */
            $legacy = Role::whereIn('slug', [
                'operator-kua',
                'editor-penerbit',
                'penerbit',
            ])->pluck('slug');

            if ($legacy->isNotEmpty()) {
                throw new RuntimeException(
                    'Masih ada role lama: '.$legacy->implode(', ')
                    .'. Jalankan penyesuaian role sebelumnya terlebih dahulu.'
                );
            }

            foreach (PermissionCatalog::permissions() as $definition) {
                Permission::updateOrCreate(
                    ['slug' => $definition['slug']],
                    [
                        'name' => $definition['name'],
                        'module' => $definition['module'],
                    ]
                );
            }

            foreach ([
                'super-admin' => [
                    'name' => 'Super Admin',
                    'description' => 'Pengelola utama sistem dan pemberi kewenangan kepada Admin.',
                ],
                'admin' => [
                    'name' => 'Admin',
                    'description' => 'Mengelola sesuai izin dan kewenangan delegasi yang diberikan.',
                ],
                'user' => [
                    'name' => 'User',
                    'description' => 'Mengakses fitur sesuai permission dan cakupan unit yang diberikan.',
                ],
            ] as $slug => $definition) {
                Role::firstOrCreate(
                    ['slug' => $slug],
                    $definition
                );
            }

            $super = Role::where('slug', 'super-admin')->firstOrFail();

            $super->forceFill(['is_system' => true])->save();

            $super->permissions()->syncWithoutDetaching(
                Permission::whereIn(
                    'slug',
                    array_column(PermissionCatalog::permissions(), 'slug')
                )->pluck('id')->all()
            );

            DB::table('role_user')->upsert(
                [[
                    'user_id' => $user->id,
                    'role_id' => $super->id,
                    'data_scope' => 'all_units',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]],
                ['user_id', 'role_id'],
                ['data_scope', 'updated_at']
            );

            /*
             * Tidak menambahkan permission default kepada Admin/User.
             * Tidak mengubah penugasan maupun cakupan pengguna lainnya.
             */
        });

        $this->command?->info(
            'Seeder selesai. Role: Super Admin, Admin, User.'
        );
    }
}
