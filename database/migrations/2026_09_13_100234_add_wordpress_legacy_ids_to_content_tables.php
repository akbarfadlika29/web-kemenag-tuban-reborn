<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | News
        |--------------------------------------------------------------------------
        |
        | Menyimpan ID wpjg_posts.ID dari WordPress lama.
        |
        */
        Schema::table('news', function (Blueprint $table) {
            $table->unsignedBigInteger('wordpress_post_id')
                ->nullable()
                ->unique();
        });

        /*
        |--------------------------------------------------------------------------
        | Media
        |--------------------------------------------------------------------------
        |
        | Menyimpan ID attachment dari wpjg_posts.ID.
        |
        */
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('wordpress_attachment_id')
                ->nullable()
                ->unique();
        });

        /*
        |--------------------------------------------------------------------------
        | News Categories
        |--------------------------------------------------------------------------
        |
        | Menyimpan wpjg_terms.term_id untuk taxonomy category.
        |
        */
        Schema::table('news_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('wordpress_term_id')
                ->nullable()
                ->unique();
        });

        /*
        |--------------------------------------------------------------------------
        | News Tags
        |--------------------------------------------------------------------------
        |
        | Menyimpan wpjg_terms.term_id untuk taxonomy post_tag.
        |
        */
        Schema::table('news_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('wordpress_term_id')
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('news_tags', function (Blueprint $table) {
            $table->dropUnique(['wordpress_term_id']);
            $table->dropColumn('wordpress_term_id');
        });

        Schema::table('news_categories', function (Blueprint $table) {
            $table->dropUnique(['wordpress_term_id']);
            $table->dropColumn('wordpress_term_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropUnique(['wordpress_attachment_id']);
            $table->dropColumn('wordpress_attachment_id');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['wordpress_post_id']);
            $table->dropColumn('wordpress_post_id');
        });
    }
};
