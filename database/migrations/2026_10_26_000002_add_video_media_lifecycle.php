<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_videos', function (Blueprint $table) {
            $table->uuid('processing_token')->nullable()->unique();
            $table->timestamp('processing_started_at')->nullable();
        });
        Schema::create('video_media_operations', function (Blueprint $table) {
            $table->uuid('request_key')->primary();
            $table->foreignId('video_id')->nullable()->constrained('library_videos')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 20);
            $table->string('state', 20)->default('pending');
            $table->uuid('token')->nullable();
            $table->timestamps();
            $table->index(['state', 'updated_at']);
        });
        Schema::create('video_asset_cleanups', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->timestamp('available_after')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_asset_cleanups');
        Schema::dropIfExists('video_media_operations');
        Schema::table('library_videos', function (Blueprint $table) {
            $table->dropUnique(['processing_token']);
            $table->dropColumn(['processing_token', 'processing_started_at']);
        });
    }
};
