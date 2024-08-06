<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->string('main_title');
            $table->text('sub_title');
            $table->string('our_features_title');
            $table->string('first_feature_title');
            $table->text('first_feature_content');
            $table->string('second_feature_title');
            $table->text('second_feature_content');
            $table->string('third_feature_title');
            $table->text('third_feature_content');
            $table->string('last_section_title');
            $table->text('last_section_content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
