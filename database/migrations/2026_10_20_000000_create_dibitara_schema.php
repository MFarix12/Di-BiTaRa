<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('display_name', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 254)->nullable()->unique();
            $table->string('password', 255);
            $table->string('role', 16)->default('pelajar');
            $table->string('status', 16)->default('active');
            $table->string('remember_token', 100)->nullable();
            $table->dateTime('last_login_at', 3)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->softDeletes('deleted_at', 3);
            $table->index(['role', 'status', 'deleted_at']);
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16);
            $table->string('title', 180);
            $table->string('disk', 40)->default('private');
            $table->string('path', 512);
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('source', 20)->default('upload');
            $table->text('license_note')->nullable();
            $table->char('checksum', 64);
            $table->string('status', 16)->default('processing');
            $table->string('failure_reason', 255)->nullable();
            $table->unsignedBigInteger('parent_media_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->softDeletes('deleted_at', 3);
            $table->unique(['disk', 'path']);
            $table->foreign('parent_media_id')->references('id')->on('media_files')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('avatars', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 100);
            $table->unsignedBigInteger('media_file_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->softDeletes('deleted_at', 3);
            $table->foreign('media_file_id')->references('id')->on('media_files')->restrictOnDelete();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('avatar_id')->nullable();
            $table->string('aircraft_color', 16)->default('yellow');
            $table->string('onboarding_status', 16)->default('pending');
            $table->unsignedTinyInteger('onboarding_step')->default(1);
            $table->dateTime('onboarding_completed_at', 3)->nullable();
            $table->boolean('reduced_motion')->default(0);
            $table->boolean('audio_enabled')->default(1);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('avatar_id')->references('id')->on('avatars')->nullOnDelete();
        });

        Schema::create('account_recovery_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->char('token_hash', 64)->unique();
            $table->string('kind', 16)->default('email_reset');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('expires_at', 3)->nullable();
            $table->dateTime('used_at', 3)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name', 120);
            $table->unsignedTinyInteger('sequence')->unique();
            $table->text('description')->nullable();
            $table->string('stamp_title', 100);
            $table->unsignedBigInteger('stamp_media_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('stamp_media_id')->references('id')->on('media_files')->restrictOnDelete();
        });

        Schema::create('instructions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('body');
            $table->string('screen_key', 60)->nullable();
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('revision')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->softDeletes('deleted_at', 3);
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('learning_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('instruction_id')->nullable();
            $table->string('title', 180);
            $table->string('kind', 20)->default('note');
            $table->longText('body');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(1);
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('revision')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->softDeletes('deleted_at', 3);
            $table->index(['zone_id', 'status', 'deleted_at', 'sort_order']);
            $table->foreign('zone_id')->references('id')->on('zones')->restrictOnDelete();
            $table->foreign('instruction_id')->references('id')->on('instructions')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('instruction_id')->nullable();
            $table->string('title', 180);
            $table->string('kind', 24)->default('quiz');
            $table->decimal('passing_percent', 5, 2)->default(80.00);
            $table->boolean('is_required')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('settings');
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('revision')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->softDeletes('deleted_at', 3);
            $table->index(['zone_id', 'status', 'deleted_at', 'sort_order']);
            $table->foreign('zone_id')->references('id')->on('zones')->restrictOnDelete();
            $table->foreign('instruction_id')->references('id')->on('instructions')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->text('prompt');
            $table->string('type', 24)->default('objective');
            $table->unsignedSmallInteger('max_marks')->default(10);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('public_config');
            $table->text('feedback_correct')->nullable();
            $table->text('feedback_incorrect')->nullable();
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('revision')->default(1);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->softDeletes('deleted_at', 3);
            $table->index(['activity_id', 'status', 'deleted_at', 'sort_order']);
            $table->foreign('activity_id')->references('id')->on('activities')->restrictOnDelete();
        });

        Schema::create('answer_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('option_key', 40);
            $table->text('text');
            $table->boolean('is_correct')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['question_id', 'option_key']);
            $table->foreign('question_id')->references('id')->on('questions')->restrictOnDelete();
        });

        Schema::create('question_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('item_key', 40);
            $table->string('text', 255);
            $table->unsignedBigInteger('media_file_id')->nullable();
            $table->unsignedTinyInteger('max_uses')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['question_id', 'item_key']);
            $table->foreign('question_id')->references('id')->on('questions')->restrictOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->restrictOnDelete();
        });

        Schema::create('question_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('target_key', 40);
            $table->string('label', 100);
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['question_id', 'target_key']);
            $table->foreign('question_id')->references('id')->on('questions')->restrictOnDelete();
        });

        Schema::create('question_solutions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id')->unique();
            $table->json('answer_key');
            $table->json('normalization');
            $table->string('scoring_mode', 20)->default('all_or_nothing');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('question_id')->references('id')->on('questions')->restrictOnDelete();
        });

        Schema::create('content_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('learning_content_id');
            $table->unsignedBigInteger('media_file_id');
            $table->string('purpose', 20)->default('illustration');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['learning_content_id', 'media_file_id', 'purpose']);
            $table->foreign('learning_content_id')->references('id')->on('learning_contents')->restrictOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->restrictOnDelete();
        });

        Schema::create('question_audio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('media_file_id');
            $table->string('purpose', 20)->default('prompt');
            $table->text('transcript')->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['question_id', 'purpose']);
            $table->foreign('question_id')->references('id')->on('questions')->restrictOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->restrictOnDelete();
        });

        Schema::create('instruction_audio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instruction_id')->unique();
            $table->unsignedBigInteger('media_file_id');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('instruction_id')->references('id')->on('instructions')->restrictOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->restrictOnDelete();
        });

        Schema::create('learning_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 40)->unique();
            $table->string('title', 180);
            $table->string('status', 16)->default('draft');
            $table->json('manifest');
            $table->json('scoring_policy');
            $table->char('checksum', 64);
            $table->dateTime('published_at', 3)->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('published_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('student_journeys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('learning_release_id');
            $table->string('status', 16)->default('active');
            $table->dateTime('started_at', 3)->useCurrent();
            $table->dateTime('completed_at', 3)->nullable();
            $table->unsignedBigInteger('last_zone_id')->nullable();
            $table->unsignedBigInteger('last_content_id')->nullable();
            $table->unsignedBigInteger('last_activity_id')->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['user_id', 'learning_release_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('learning_release_id')->references('id')->on('learning_releases')->restrictOnDelete();
            $table->foreign('last_zone_id')->references('id')->on('zones')->restrictOnDelete();
            $table->foreign('last_content_id')->references('id')->on('learning_contents')->restrictOnDelete();
            $table->foreign('last_activity_id')->references('id')->on('activities')->restrictOnDelete();
        });

        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_journey_id');
            $table->unsignedBigInteger('activity_id');
            $table->unsignedInteger('attempt_no');
            $table->string('status', 20)->default('in_progress');
            $table->string('input_mode', 24)->default('pointer');
            $table->json('activity_snapshot');
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('max_score');
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('passed')->default(0);
            $table->dateTime('started_at', 3)->useCurrent();
            $table->dateTime('completed_at', 3)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['student_journey_id', 'activity_id', 'attempt_no']);
            $table->index(['student_journey_id', 'activity_id', 'status', 'score']);
            $table->foreign('student_journey_id')->references('id')->on('student_journeys')->restrictOnDelete();
            $table->foreign('activity_id')->references('id')->on('activities')->restrictOnDelete();
        });

        Schema::create('attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedSmallInteger('position');
            $table->json('question_snapshot');
            $table->json('grading_snapshot');
            $table->unsignedSmallInteger('max_marks');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['attempt_id', 'question_id']);
            $table->unique(['attempt_id', 'position']);
            $table->foreign('attempt_id')->references('id')->on('attempts')->restrictOnDelete();
            $table->foreign('question_id')->references('id')->on('questions')->restrictOnDelete();
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_question_id')->unique();
            $table->char('idempotency_key', 36)->unique();
            $table->json('payload');
            $table->boolean('is_correct')->nullable();
            $table->unsignedSmallInteger('awarded_marks')->default(0);
            $table->string('input_mode', 24)->default('pointer');
            $table->dateTime('submitted_at', 3)->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('attempt_question_id')->references('id')->on('attempt_questions')->restrictOnDelete();
        });

        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id')->unique();
            $table->string('mode', 20)->default('cockpit');
            $table->string('phase', 24)->default('briefing');
            $table->json('checkpoint');
            $table->string('input_mode', 24)->default('pointer');
            $table->json('calibration_settings')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->dateTime('last_saved_at', 3)->useCurrent();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('attempt_id')->references('id')->on('attempts')->restrictOnDelete();
        });

        Schema::create('content_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_journey_id');
            $table->unsignedBigInteger('learning_content_id');
            $table->dateTime('completed_at', 3)->useCurrent();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['student_journey_id', 'learning_content_id']);
            $table->foreign('student_journey_id')->references('id')->on('student_journeys')->restrictOnDelete();
            $table->foreign('learning_content_id')->references('id')->on('learning_contents')->restrictOnDelete();
        });

        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_journey_id');
            $table->unsignedBigInteger('zone_id');
            $table->string('status', 20)->default('locked');
            $table->unsignedSmallInteger('required_units');
            $table->unsignedSmallInteger('completed_units')->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->unsignedInteger('best_score')->default(0);
            $table->dateTime('completed_at', 3)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['student_journey_id', 'zone_id']);
            $table->foreign('student_journey_id')->references('id')->on('student_journeys')->restrictOnDelete();
            $table->foreign('zone_id')->references('id')->on('zones')->restrictOnDelete();
        });

        Schema::create('passport_stamps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_journey_id');
            $table->unsignedBigInteger('zone_id');
            $table->string('title_snapshot', 100);
            $table->dateTime('awarded_at', 3)->useCurrent();
            $table->unique(['student_journey_id', 'zone_id']);
            $table->foreign('student_journey_id')->references('id')->on('student_journeys')->restrictOnDelete();
            $table->foreign('zone_id')->references('id')->on('zones')->restrictOnDelete();
        });

        Schema::create('reflections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_journey_id');
            $table->unsignedBigInteger('zone_id');
            $table->string('emotion', 24);
            $table->string('note', 500)->nullable();
            $table->dateTime('submitted_at', 3)->useCurrent();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->unique(['student_journey_id', 'zone_id']);
            $table->foreign('student_journey_id')->references('id')->on('student_journeys')->restrictOnDelete();
            $table->foreign('zone_id')->references('id')->on('zones')->restrictOnDelete();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_journey_id')->unique();
            $table->string('certificate_number', 60)->unique();
            $table->string('recipient_name', 100);
            $table->unsignedBigInteger('media_file_id')->nullable();
            $table->string('status', 16)->default('pending');
            $table->dateTime('qualified_at', 3)->useCurrent();
            $table->dateTime('issued_at', 3)->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrent();
            $table->foreign('student_journey_id')->references('id')->on('student_journeys')->restrictOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->restrictOnDelete();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->string('action', 40);
            $table->string('entity_type', 60);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->char('request_id', 36);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->index(['created_at', 'id']);
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id', 'created_at']);
            $table->foreign('admin_user_id')->references('id')->on('users')->restrictOnDelete();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('reflections');
        Schema::dropIfExists('passport_stamps');
        Schema::dropIfExists('student_progress');
        Schema::dropIfExists('content_completions');
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('responses');
        Schema::dropIfExists('attempt_questions');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('student_journeys');
        Schema::dropIfExists('learning_releases');
        Schema::dropIfExists('instruction_audio');
        Schema::dropIfExists('question_audio');
        Schema::dropIfExists('content_media');
        Schema::dropIfExists('question_solutions');
        Schema::dropIfExists('question_targets');
        Schema::dropIfExists('question_items');
        Schema::dropIfExists('answer_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('learning_contents');
        Schema::dropIfExists('instructions');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('account_recovery_tokens');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('avatars');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('users');
    }
};
