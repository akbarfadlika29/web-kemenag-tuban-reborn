<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Satu profil akses untuk satu pengguna.
         * parent_user_id adalah pemberi kewenangan langsung.
         */
        Schema::create('user_access_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->primary()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('parent_user_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('data_scope', ['own_unit', 'all_units'])
                ->default('own_unit');

            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        /*
         * Permission yang benar-benar diberikan kepada pengguna.
         * can_delegate berarti permission tersebut boleh diteruskan.
         */
        Schema::create('user_permission_grants', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->restrictOnDelete();

            $table->boolean('can_delegate')->default(false);
            $table->timestamps();

            $table->primary(
                ['user_id', 'permission_id'],
                'user_permission_grants_primary'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permission_grants');
        Schema::dropIfExists('user_access_profiles');
    }
};