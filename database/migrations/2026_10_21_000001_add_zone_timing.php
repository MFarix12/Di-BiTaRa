<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->dateTime('countdown_started_at', 3)->nullable();
            $table->dateTime('timing_started_at', 3)->nullable();
            $table->dateTime('timing_finished_at', 3)->nullable();
            $table->unsignedBigInteger('duration_seconds')->nullable();
            $table->boolean('timing_is_partial')->default(false);
        });
        Schema::table('student_journeys', function (Blueprint $table) {
            $table->unsignedBigInteger('total_duration_seconds')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_progress', fn (Blueprint $table) => $table->dropColumn(['countdown_started_at', 'timing_started_at', 'timing_finished_at', 'duration_seconds', 'timing_is_partial']));
        Schema::table('student_journeys', fn (Blueprint $table) => $table->dropColumn('total_duration_seconds'));
    }
};
