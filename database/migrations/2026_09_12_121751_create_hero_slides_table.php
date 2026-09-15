<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hero_slides',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('media_id')
                    ->nullable()
                    ->constrained('media')
                    ->nullOnDelete();

                $table
                    ->string('title', 180)
                    ->nullable();

                $table
                    ->text('description')
                    ->nullable();

                $table
                    ->string('button_label', 100)
                    ->nullable();

                $table
                    ->string('url', 2048)
                    ->nullable();

                $table
                    ->unsignedInteger('sort_order')
                    ->default(1);

                $table
                    ->boolean('is_active')
                    ->default(true);

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'is_active',
                    'sort_order',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hero_slides'
        );
    }
};
