<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            $table->string('original_name');
            $table->string('file_name');
            $table->string('path');
            $table->string('disk')->default('public');

            $table->string('mime_type')->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->string('type', 30)->default('other');

            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_public')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('unit_id');
            $table->index('type');
            $table->index('is_public');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};