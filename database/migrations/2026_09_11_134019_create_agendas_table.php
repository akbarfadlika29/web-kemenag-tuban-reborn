<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

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

            $table->string('location')
                ->nullable();

            $table->timestamp('start_at');

            $table->timestamp('end_at')
                ->nullable();

            $table->string(
                'status',
                30
            )->default('draft');

            $table->timestamp('published_at')
                ->nullable();

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedBigInteger('view_count')
                ->default(0);

            $table->string('meta_title')
                ->nullable();

            $table->text('meta_description')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('unit_id');
            $table->index('status');
            $table->index('start_at');
            $table->index('end_at');
            $table->index('published_at');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};