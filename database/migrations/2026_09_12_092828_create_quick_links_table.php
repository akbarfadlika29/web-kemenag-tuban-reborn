<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'quick_links',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('media_id')
                    ->nullable()
                    ->constrained('media')
                    ->nullOnDelete();

                $table
                    ->foreignId('page_id')
                    ->nullable()
                    ->constrained('pages')
                    ->nullOnDelete();

                $table
                    ->foreignId('news_category_id')
                    ->nullable()
                    ->constrained('news_categories')
                    ->nullOnDelete();

                $table->string(
                    'label',
                    150
                );

                $table->string(
                    'target_type',
                    30
                );

                $table
                    ->string(
                        'route_name',
                        255
                    )
                    ->nullable();

                $table
                    ->string(
                        'url',
                        2048
                    )
                    ->nullable();

                $table
                    ->integer('sort_order')
                    ->default(1);

                $table
                    ->boolean('open_in_new_tab')
                    ->default(false);

                $table
                    ->boolean('is_active')
                    ->default(true);

                $table->timestamps();
                $table->softDeletes();

                $table->index(
                    'target_type'
                );

                $table->index([
                    'is_active',
                    'sort_order',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'quick_links'
        );
    }
};
