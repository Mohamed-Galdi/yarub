<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_seed_imports', function (Blueprint $table) {
            $table->string('identity', 64)->primary();
            $table->string('source_checksum', 64);
            $table->uuid('request_key')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_seed_imports');
    }
};
