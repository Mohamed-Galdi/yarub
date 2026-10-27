<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $version = DB::selectOne('SELECT VERSION() AS version')->version;
        if (DB::getDriverName() !== 'mysql' || stripos($version, 'MariaDB') !== false
            || ! preg_match('/^\d+\.\d+\.\d+/', $version, $match)
            || version_compare($match[0], '8.0.16', '<')) {
            throw new RuntimeException('Video library requires verified MySQL 8.0.16+ with enforced CHECK constraints.');
        }

        Schema::create('library_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('qudrat_category_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('arabic_skill_bank_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('disk', 80)->default('video_library');
            $table->string('video_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->enum('cover_origin', ['generated', 'custom'])->nullable();
            $table->decimal('duration_seconds', 10, 3)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('byte_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->json('codec_metadata')->nullable();
            $table->enum('processing_state', ['pending', 'processing', 'ready', 'failed'])->default('pending');
            $table->text('processing_error')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['processing_state', 'is_visible', 'published_at', 'id'], 'library_videos_eligibility_index');
            $table->index(['created_at', 'id']);
        });
        // MySQL 8.0.16+ enforces CHECK constraints. Do not deploy on older servers.
        DB::statement('ALTER TABLE library_videos ADD CONSTRAINT library_videos_exactly_one_type CHECK ((qudrat_category_id IS NOT NULL AND arabic_skill_bank_id IS NULL) OR (qudrat_category_id IS NULL AND arabic_skill_bank_id IS NOT NULL))');

        Schema::create('video_library_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->foreignId('public_example_video_id')->nullable()->constrained('library_videos')->nullOnDelete();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE video_library_settings ADD CONSTRAINT video_library_settings_singleton CHECK (id = 1)');
        DB::table('video_library_settings')->insert(['id' => 1, 'public_example_video_id' => null]);
    }

    public function down(): void
    {
        Schema::dropIfExists('video_library_settings');
        Schema::dropIfExists('library_videos');
    }
};
