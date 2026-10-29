<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arabic_skill_sessions', function (Blueprint $table) {
            $table->string('practice_set_key', 100)->nullable();
            $table->string('practice_set_title')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('arabic_skill_sessions', fn (Blueprint $table) => $table->dropColumn(['practice_set_key', 'practice_set_title']));
    }
};
