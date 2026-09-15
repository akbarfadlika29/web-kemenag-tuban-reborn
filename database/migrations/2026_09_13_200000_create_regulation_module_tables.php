<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('regulations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('regulation_type_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 500);
            $table->string('slug', 550)->unique();
            $table->string('number', 150);
            $table->smallInteger('year')->index();
            $table->string('issuing_authority');
            $table->string('subject')->nullable();

            $table->text('summary')->nullable();
            $table->text('description')->nullable();

            $table->date('issued_at')->nullable();
            $table->date('effective_at')->nullable();

            $table->string('legal_status', 30)->default('unverified');
            $table->text('legal_status_note')->nullable();

            $table->text('source_url')->nullable();

            $table->string('publication_status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['publication_status', 'published_at']);
        });

        Schema::create('regulation_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('regulation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('media_id')
                ->constrained('media')
                ->restrictOnDelete();

            $table->string('label');
            $table->string('document_role', 20);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['regulation_id', 'media_id']);
        });

        DB::statement(
            "CREATE UNIQUE INDEX regulation_one_main
             ON regulation_documents (regulation_id)
             WHERE document_role = 'main'"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_documents');
        Schema::dropIfExists('regulations');
        Schema::dropIfExists('regulation_types');
    }
};
