<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_page_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 64);
            $table->string('visitor_key', 64);
            $table->string('kind', 10);
            $table->string('period', 10);
            $table->timestamp('created_at');

            $table->unique(
                ['page_key', 'visitor_key', 'kind', 'period'],
                'public_interaction_unique'
            );

            $table->index(
                ['page_key', 'kind'],
                'public_interaction_counts'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_page_interactions');
    }
};
