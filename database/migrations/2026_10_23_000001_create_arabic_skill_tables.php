<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arabic_skill_banks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title');
            $table->string('subtitle');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('is_active')->default(true);
            $table->json('source_metadata');
            $table->unsignedInteger('content_revision')->default(1);
            $table->char('import_checksum', 64)->nullable();
            $table->string('import_version', 80)->nullable();
            $table->timestamps();
        });
        Schema::create('arabic_skill_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained('arabic_skill_banks')->restrictOnDelete();
            $table->string('source_id', 100)->unique();
            $table->unsignedSmallInteger('number');
            $table->string('title');
            $table->unsignedSmallInteger('sort_order');
            $table->unsignedInteger('content_revision')->default(1);
            $table->char('import_checksum', 64)->nullable();
            $table->string('import_version', 80)->nullable();
            $table->timestamps();
            $table->unique(['bank_id', 'number'], 'as_model_number_unique');
            $table->unique(['id', 'bank_id'], 'as_model_bank_unique');
        });
        Schema::create('arabic_skill_questions', function (Blueprint $table) {
            $table->id();
            $table->string('source_id', 100)->unique();
            $table->foreignId('bank_id')->constrained('arabic_skill_banks')->restrictOnDelete();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedSmallInteger('number')->nullable();
            $table->unsignedSmallInteger('context_group_number')->nullable();
            $table->string('skill');
            $table->enum('type', ['multiple_choice', 'true_false', 'matching']);
            $table->text('prompt');
            $table->json('options');
            $table->json('matching_items');
            $table->json('answer');
            $table->text('explanation');
            $table->text('hint')->nullable();
            $table->json('scoring');
            $table->unsignedTinyInteger('max_score')->default(2);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('content_revision')->default(1);
            $table->char('import_checksum', 64)->nullable();
            $table->string('import_version', 80)->nullable();
            $table->timestamps();
            $table->foreign(['model_id', 'bank_id'], 'as_question_model_fk')->references(['id', 'bank_id'])->on('arabic_skill_models')->restrictOnDelete();
            $table->unique(['bank_id', 'number'], 'as_question_number_unique');
            $table->unique(['id', 'bank_id'], 'as_question_bank_unique');
            $table->index(['bank_id', 'is_active', 'type'], 'as_active_questions_index');
        });
        Schema::create('arabic_skill_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('bank_id')->constrained('arabic_skill_banks')->restrictOnDelete();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->enum('mode', ['practice', 'model']);
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->unsignedSmallInteger('current_position')->default(1);
            $table->unsignedSmallInteger('selected_question_count');
            $table->unsignedSmallInteger('available_points');
            $table->unsignedSmallInteger('earned_points')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamps();
            $table->foreign(['model_id', 'bank_id'], 'as_session_model_fk')->references(['id', 'bank_id'])->on('arabic_skill_models')->restrictOnDelete();
            $table->unique(['id', 'bank_id'], 'as_session_bank_unique');
            $table->index(['user_id', 'bank_id', 'status', 'mode', 'model_id'], 'as_session_owner_index');
        });
        Schema::create('arabic_skill_session_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedSmallInteger('position');
            $table->json('content_snapshot');
            $table->json('response')->nullable();
            $table->unsignedTinyInteger('earned_points')->nullable();
            $table->enum('result', ['correct', 'partial', 'incorrect', 'skipped'])->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->foreign(['session_id', 'bank_id'], 'as_item_session_fk')->references(['id', 'bank_id'])->on('arabic_skill_sessions')->restrictOnDelete();
            $table->foreign(['question_id', 'bank_id'], 'as_item_question_fk')->references(['id', 'bank_id'])->on('arabic_skill_questions')->restrictOnDelete();
            $table->unique(['session_id', 'position'], 'as_session_position_unique');
            $table->unique(['session_id', 'question_id'], 'as_session_question_unique');
            $table->index(['question_id', 'submitted_at', 'id'], 'as_latest_submission_index');
        });
    }

    public function down(): void
    {
        foreach (['arabic_skill_session_questions', 'arabic_skill_sessions', 'arabic_skill_questions', 'arabic_skill_models', 'arabic_skill_banks'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
