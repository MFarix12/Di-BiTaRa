<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_journeys', function (Blueprint $t) { $t->unsignedTinyInteger('flow_version')->default(1); });
        Schema::table('attempt_questions', function (Blueprint $t) {
            $t->unsignedInteger('audio_help_plays')->default(0);
            $t->json('draft_payload')->nullable();
            $t->timestamp('draft_saved_at')->nullable();
        });
        Schema::create('journey_clocks', function (Blueprint $t) {
            $t->id(); $t->foreignId('student_journey_id')->unique()->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('elapsed_ms')->default(0);
            $t->timestamp('started_at', 3); $t->timestamp('last_seen_at', 3)->nullable();
            $t->timestamp('finished_at', 3)->nullable();
            $t->boolean('is_partial')->default(false);
        });
        Schema::create('challenge_launches', function (Blueprint $t) {
            $t->id(); $t->foreignId('student_journey_id')->constrained()->cascadeOnDelete();
            $t->foreignId('activity_id')->constrained();
            $t->timestamp('countdown_started_at', 3)->nullable(); $t->timestamp('started_at', 3)->nullable();
            $t->unique(['student_journey_id','activity_id']);
        });
        Schema::create('learning_help_events', function (Blueprint $t) {
            $t->id(); $t->foreignId('attempt_question_id')->constrained()->cascadeOnDelete();
            $t->uuid('request_key')->unique(); $t->string('kind', 30); $t->timestamp('created_at')->useCurrent();
        });
        // Only active journeys adopt the new flow. Completed historical results are immutable.
        DB::table('student_journeys')->where('status','active')->update(['flow_version'=>2]);
    }
    public function down(): void
    {
        Schema::dropIfExists('learning_help_events'); Schema::dropIfExists('challenge_launches'); Schema::dropIfExists('journey_clocks');
        Schema::table('attempt_questions', fn (Blueprint $t) => $t->dropColumn(['audio_help_plays','draft_payload','draft_saved_at']));
        Schema::table('student_journeys', fn (Blueprint $t) => $t->dropColumn('flow_version'));
    }
};
