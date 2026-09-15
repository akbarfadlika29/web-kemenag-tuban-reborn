<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->foreignId('unit_id')
                ->nullable()
                ->after('id')
                ->constrained('units')
                ->nullOnDelete();

            $table
                ->string('position')
                ->nullable()
                ->after('email');

            $table
                ->string('phone', 30)
                ->nullable()
                ->after('position');

            $table
                ->boolean('is_active')
                ->default(true)
                ->after('phone');

            $table
                ->timestamp('last_login_at')
                ->nullable()
                ->after('is_active');

            $table->index('is_active');
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['unit_id']);

            $table->dropForeign(['unit_id']);

            $table->dropColumn([
                'unit_id',
                'position',
                'phone',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};