<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseLearningFeatures extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('courses', 'source_type')) Schema::table('courses', function (Blueprint $table) {
            $table->string('source_type', 40)->default('manual')->after('tags');
            $table->string('source_key', 191)->nullable()->after('source_type');
            $table->string('content_hash', 64)->nullable()->after('source_key');
            $table->timestamp('generated_at')->nullable()->after('content_hash');
            $table->string('content_status', 20)->default('published')->after('generated_at');
            $table->index(['created_by', 'source_key']);
            $table->index(['content_status', 'source_type']);
        });

        if (!Schema::hasColumn('course_items', 'content')) Schema::table('course_items', function (Blueprint $table) {
            $table->text('content')->nullable()->after('description');
            $table->string('source_type', 40)->default('manual')->after('content');
            $table->string('source_key', 191)->nullable()->after('source_type');
            $table->string('content_hash', 64)->nullable()->after('source_key');
            $table->timestamp('generated_at')->nullable()->after('content_hash');
            $table->string('content_status', 20)->default('published')->after('generated_at');
            $table->index(['course_id', 'source_key']);
            $table->index(['content_status', 'source_type']);
        });

        if (!Schema::hasColumn('user_progress', 'mastery_status')) Schema::table('user_progress', function (Blueprint $table) {
            $table->string('mastery_status', 20)->default('not_started')->after('status');
            $table->decimal('mastery_score', 5, 2)->default(0)->after('mastery_status');
            $table->timestamp('started_at')->nullable()->after('last_accessed_at');
            $table->timestamp('review_due_at')->nullable()->after('started_at');
            $table->index(['user_id', 'mastery_status', 'review_due_at']);
        });

        if (!Schema::hasTable('user_courses')) Schema::create('user_courses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('course_id');
            $table->string('title', 255);
            $table->string('status', 20)->default('planned');
            $table->text('goal')->nullable();
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('order_index')->default(0);
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
            $table->index(['user_id', 'status']);
            $table->index(['course_id', 'progress_percent']);
        });

        if (!Schema::hasTable('course_quizzes')) Schema::create('course_quizzes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('course_item_id');
            $table->decimal('passing_score', 5, 2)->default(70);
            $table->integer('attempts_allowed')->nullable();
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->unique('course_item_id');
        });

        if (!Schema::hasTable('course_quiz_questions')) Schema::create('course_quiz_questions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quiz_id');
            $table->string('question_type', 20)->default('single');
            $table->text('question');
            $table->text('explanation')->nullable();
            $table->decimal('points', 6, 2)->default(1);
            $table->float('order_index')->default(0);
            $table->string('source_type', 40)->default('manual');
            $table->timestamps();
            $table->index(['quiz_id', 'order_index']);
        });

        if (!Schema::hasTable('course_quiz_options')) Schema::create('course_quiz_options', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('question_id');
            $table->string('option_key', 20);
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->float('order_index')->default(0);
            $table->timestamps();
            $table->index(['question_id', 'order_index']);
        });

        if (!Schema::hasTable('course_quiz_attempts')) Schema::create('course_quiz_attempts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quiz_id');
            $table->integer('user_id');
            $table->integer('user_course_id')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->integer('correct_count')->default(0);
            $table->integer('total_count')->default(0);
            $table->boolean('passed')->default(false);
            $table->text('answers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'quiz_id', 'created_at']);
        });

        if (!Schema::hasTable('course_review_items')) Schema::create('course_review_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('user_course_id');
            $table->integer('course_item_id');
            $table->string('status', 20)->default('due');
            $table->integer('review_count')->default(0);
            $table->integer('interval_days')->default(1);
            $table->decimal('last_score', 5, 2)->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'user_course_id', 'course_item_id']);
            $table->index(['user_id', 'status', 'next_review_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_review_items');
        Schema::dropIfExists('course_quiz_attempts');
        Schema::dropIfExists('course_quiz_options');
        Schema::dropIfExists('course_quiz_questions');
        Schema::dropIfExists('course_quizzes');
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'mastery_status', 'review_due_at']);
            $table->dropColumn(['mastery_status', 'mastery_score', 'started_at', 'review_due_at']);
        });
        Schema::table('course_items', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'source_key']);
            $table->dropIndex(['content_status', 'source_type']);
            $table->dropColumn(['content', 'source_type', 'source_key', 'content_hash', 'generated_at', 'content_status']);
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['created_by', 'source_key']);
            $table->dropIndex(['content_status', 'source_type']);
            $table->dropColumn(['source_type', 'source_key', 'content_hash', 'generated_at', 'content_status']);
        });
    }
}
