<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Paket akses yang dapat digunakan bersama.
         * owner_user_id menentukan siapa yang mengelola paket ini.
         */
        Schema::create('access_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();

            $table->foreignId('owner_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['owner_user_id', 'name'],
                'access_roles_owner_name_unique'
            );
        });

        /*
         * Cakupan melekat pada permission, bukan pada seluruh role.
         * can_delegate terpisah dari izin menjalankan tindakan.
         */
        Schema::create('access_role_permissions', function (Blueprint $table) {
            $table->foreignId('access_role_id')
                ->constrained('access_roles')
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->restrictOnDelete();

            $table->enum('data_scope', [
                'own_unit',
                'all_units',
            ])->default('own_unit');

            $table->boolean('can_delegate')->default(false);
            $table->timestamps();

            $table->primary(
                ['access_role_id', 'permission_id'],
                'access_role_permissions_primary'
            );
        });

        /*
         * Satu role akses per pengguna agar tidak ada penggabungan
         * cakupan yang tidak disengaja.
         */
        Schema::create('access_role_user', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->primary()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('access_role_id')
                ->constrained('access_roles')
                ->restrictOnDelete();

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_role_user');
        Schema::dropIfExists('access_role_permissions');
        Schema::dropIfExists('access_roles');
    }
};