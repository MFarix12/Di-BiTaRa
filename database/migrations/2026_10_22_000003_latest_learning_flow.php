<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempt_questions', fn (Blueprint $t) => $t->unsignedTinyInteger('audio_plays')->default(0));
        Schema::create('answer_submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attempt_question_id')->constrained('attempt_questions')->restrictOnDelete();
            $t->uuid('submission_key')->unique();
            $t->json('payload');
            $t->boolean('is_correct');
            $t->string('input_mode',24)->default('pointer');
            $t->timestamp('created_at')->useCurrent();
        });
        // Historical totals stay unchanged. New journeys use the current timing policy.
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_submissions');
        Schema::table('attempt_questions', fn (Blueprint $t) => $t->dropColumn('audio_plays'));
    }
};
