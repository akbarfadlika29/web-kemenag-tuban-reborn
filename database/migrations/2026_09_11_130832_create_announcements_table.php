<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();

            $table->foreignId('attachment_media_id')
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
                'status',
                30
            )->default('draft');

            $table->timestamp('published_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable();

            $table->boolean('is_pinned')
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
            $table->index('published_at');
            $table->index('expires_at');
            $table->index('is_pinned');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'announcements'
        );
    }
};