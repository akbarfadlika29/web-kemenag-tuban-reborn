<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('editorial_state', 20)->default('draft')->index();
            $table->unsignedInteger('editorial_version')->default(0);
            $table->boolean('revision_required')->default(false);
            $table->text('rejection_reason')->nullable();
        });

        DB::table('news')->where('status', 'published')
            ->update(['editorial_state' => 'approved']);

        Schema::create('news_editorial_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->text('reason')->nullable();
            $table->unsignedInteger('version');
            $table->timestamp('created_at');
            $table->index(['news_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_editorial_events');

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['editorial_state']);
            $table->dropColumn([
                'editorial_state', 'editorial_version',
                'revision_required', 'rejection_reason',
            ]);
        });
    }
};