<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qudrat_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('is_published')->default(false);
            $table->char('import_checksum', 64)->nullable();
            $table->string('import_version', 80)->nullable();
            $table->timestamps();
        });

        Schema::create('qudrat_passages', function (Blueprint $table) {
            $table->id();
            $table->string('external_key', 100)->unique();
            $table->foreignId('category_id')->constrained('qudrat_categories')->restrictOnDelete();
            $table->text('text');
            $table->unsignedSmallInteger('sort_order');
            $table->char('import_checksum', 64)->nullable();
            $table->string('import_version', 80)->nullable();
            $table->timestamps();
            $table->unique(['id', 'category_id'], 'qudrat_passage_category_unique');
        });

        Schema::create('qudrat_questions', function (Blueprint $table) {
            $table->id();
            $table->string('external_key', 100)->unique();
            $table->foreignId('category_id')->constrained('qudrat_categories')->restrictOnDelete();
            $table->unsignedBigInteger('passage_id')->nullable();
            $table->text('prompt');
            $table->text('hint')->nullable();
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('sort_order');
            $table->enum('correct_option_key', ['a', 'b', 'c', 'd']);
            $table->boolean('is_published')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->char('import_checksum', 64)->nullable();
            $table->string('import_version', 80)->nullable();
            $table->timestamps();
            $table->foreign(['passage_id', 'category_id'], 'qudrat_question_passage_fk')
                ->references(['id', 'category_id'])->on('qudrat_passages')->restrictOnDelete();
            $table->index(['category_id', 'is_published', 'archived_at', 'sort_order'], 'qudrat_bank_index');
        });

        Schema::create('qudrat_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('qudrat_questions')->restrictOnDelete();
            $table->enum('local_key', ['a', 'b', 'c', 'd']);
            $table->text('text');
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
            $table->unique(['question_id', 'local_key'], 'qudrat_option_key_unique');
        });

        Schema::create('qudrat_practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('qudrat_categories')->restrictOnDelete();
            $table->enum('mode', ['practice', 'review'])->default('practice');
            $table->enum('status', ['active', 'completed', 'closed'])->default('active');
            $table->unsignedSmallInteger('current_position')->default(1);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('closure_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'category_id', 'status'], 'qudrat_session_owner_index');
            $table->unique(['id', 'user_id'], 'qudrat_session_user_unique');
        });

        Schema::create('qudrat_session_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('qudrat_practice_sessions')->restrictOnDelete();
            $table->foreignId('question_id')->constrained('qudrat_questions')->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->enum('draft_option_key', ['a', 'b', 'c', 'd'])->nullable();
            $table->timestamp('hint_requested_at')->nullable();
            $table->enum('availability', ['available', 'restricted'])->default('available');
            $table->json('content_snapshot');
            $table->timestamps();
            $table->unique(['session_id', 'question_id'], 'qudrat_session_question_unique');
            $table->unique(['session_id', 'position'], 'qudrat_session_position_unique');
            $table->unique(['id', 'session_id', 'question_id'], 'qudrat_item_identity_unique');
        });

        Schema::create('qudrat_session_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_question_id')->unique();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('question_id');
            $table->enum('selected_option_key', ['a', 'b', 'c', 'd']);
            $table->boolean('is_correct');
            $table->boolean('used_hint')->default(false);
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->foreign(['session_question_id', 'session_id', 'question_id'], 'qudrat_answer_item_fk')
                ->references(['id', 'session_id', 'question_id'])->on('qudrat_session_questions')->restrictOnDelete();
            $table->foreign(['session_id', 'user_id'], 'qudrat_answer_owner_fk')
                ->references(['id', 'user_id'])->on('qudrat_practice_sessions')->restrictOnDelete();
            $table->index(['user_id', 'question_id', 'id'], 'qudrat_first_answer_index');
        });
    }

    public function down(): void
    {
        foreach (['qudrat_session_answers', 'qudrat_session_questions', 'qudrat_practice_sessions',
            'qudrat_options', 'qudrat_questions', 'qudrat_passages', 'qudrat_categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
