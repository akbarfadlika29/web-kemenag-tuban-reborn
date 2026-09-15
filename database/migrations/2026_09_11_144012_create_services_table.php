<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('service_categories')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            $table->text('excerpt')
                ->nullable();

            $table->longText('description')
                ->nullable();

            $table->longText('requirements')
                ->nullable();

            $table->longText('procedure')
                ->nullable();

            $table->string('completion_time')
                ->nullable();

            $table->boolean('is_free')
                ->default(true);

            $table->text('fee_description')
                ->nullable();

            $table->text('service_output')
                ->nullable();

            $table->longText('legal_basis')
                ->nullable();

            $table->string('service_channel', 30)
                ->default('offline');

            $table->string('service_url')
                ->nullable();

            $table->string('service_location')
                ->nullable();

            $table->string('service_hours')
                ->nullable();

            $table->string('contact_name')
                ->nullable();

            $table->string('contact_phone', 100)
                ->nullable();

            $table->string('contact_email')
                ->nullable();

            $table->string('status', 30)
                ->default('draft');

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

            $table->index('category_id');
            $table->index('unit_id');
            $table->index('status');
            $table->index('service_channel');
            $table->index('published_at');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};