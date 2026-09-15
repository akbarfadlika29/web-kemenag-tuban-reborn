<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('pages')
                ->nullOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();

            $table->string('title');

            $table->string('slug')
                ->unique();

            $table->text('excerpt')
                ->nullable();

            $table->longText('content');

            $table->string(
                'template',
                50
            )->default('default');

            $table->string(
                'status',
                30
            )->default('draft');

            $table->timestamp('published_at')
                ->nullable();

            $table->boolean('show_in_menu')
                ->default(false);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->string('meta_title')
                ->nullable();

            $table->text('meta_description')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('unit_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('show_in_menu');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};