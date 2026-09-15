<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'related_links',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('media_id')
                    ->nullable()
                    ->constrained('media')
                    ->nullOnDelete();

                $table->string(
                    'name',
                    150
                );

                $table->string(
                    'url',
                    2048
                );

                $table->timestamps();

                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'related_links'
        );
    }
};
