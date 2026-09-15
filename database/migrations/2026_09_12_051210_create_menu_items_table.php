<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menu_items')
                ->nullOnDelete();

            $table->foreignId('page_id')
                ->nullable()
                ->constrained('pages')
                ->nullOnDelete();

            $table->string('label', 150);

            $table->string('location', 30)
                ->default('header');

            $table->string('type', 30)
                ->default('url');

            $table->string('url', 2048)
                ->nullable();

            $table->string('route_name', 255)
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(1);

            $table->boolean('open_in_new_tab')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index([
                'location',
                'is_active',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
