<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppid_informations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('ppid_categories')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('classification', 30);

            $table->text('excerpt')
                ->nullable();

            $table->longText('description')
                ->nullable();

            /*
             * Metadata administratif.
             */
            $table->string('information_holder')
                ->nullable();

            $table->string('person_in_charge')
                ->nullable();

            $table->string('information_form', 30)
                ->nullable();

            $table->string('information_format')
                ->nullable();

            $table->string('publication_media')
                ->nullable();

            $table->unsignedInteger('retention_period')
                ->nullable();

            $table->string('retention_unit', 20)
                ->nullable();

            $table->string('availability', 30)
                ->default('online');

            $table->string('access_level', 30)
                ->default('public');

            $table->string('document_number')
                ->nullable();

            $table->date('document_date')
                ->nullable();

            $table->date('effective_date')
                ->nullable();

            $table->timestamp('last_reviewed_at')
                ->nullable();

            $table->text('legal_basis')
                ->nullable();

            $table->text('notes')
                ->nullable();

            /*
             * Periode/tahun informasi.
             */
            $table->unsignedSmallInteger('year')
                ->nullable();

            /*
             * Publikasi CMS.
             */
            $table->string('status', 30)
                ->default('draft');

            $table->timestamp('published_at')
                ->nullable();

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedBigInteger('view_count')
                ->default(0);

            /*
             * SEO.
             */
            $table->string('meta_title')
                ->nullable();

            $table->text('meta_description')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('unit_id');
            $table->index('classification');
            $table->index('year');
            $table->index('status');
            $table->index('published_at');
            $table->index('is_featured');
            $table->index('availability');
            $table->index('access_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_informations');
    }
};