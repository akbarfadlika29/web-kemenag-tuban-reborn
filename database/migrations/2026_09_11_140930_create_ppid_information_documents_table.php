<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppid_information_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('information_id')
                ->constrained('ppid_informations')
                ->cascadeOnDelete();

            $table->foreignId('media_id')
                ->constrained('media')
                ->restrictOnDelete();

            $table->string('title')
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->string('version', 50)
                ->nullable();

            $table->string('document_status', 30)
                ->default('active');

            $table->date('document_date')
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_primary')
                ->default(false);

            $table->timestamps();

            $table->unique([
                'information_id',
                'media_id',
            ]);

            $table->index([
                'information_id',
                'sort_order',
            ]);

            $table->index('document_status');
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ppid_information_documents'
        );
    }
};