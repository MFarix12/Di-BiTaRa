<?php

use App\Services\LearningEngine;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $learningOnly = DB::table('zones')->whereIn('sequence', [1, 2])->pluck('id');
            $untimed = DB::table('zones')->whereIn('sequence', [1, 5])->pluck('id');
            $removedActivities = DB::table('activities')->whereIn('zone_id', $learningOnly)->pluck('id');
            // Retain historic attempts and answers for audit; remove old quizzes from use.
            DB::table('activities')->whereIn('id', $removedActivities)->whereNull('deleted_at')->update(['deleted_at' => now(), 'status' => 'inactive']);
            DB::table('questions')->whereIn('activity_id', $removedActivities)->whereNull('deleted_at')->update(['deleted_at' => now(), 'status' => 'inactive']);
            DB::table('activities')->whereIn('zone_id', DB::table('zones')->where('sequence', 5)->select('id'))->update(['kind' => 'activity']);
            DB::table('student_progress')->whereIn('zone_id', $untimed)->update([
                'countdown_started_at' => null, 'timing_started_at' => null,
                'timing_finished_at' => null, 'duration_seconds' => null, 'timing_is_partial' => false,
            ]);
            foreach (DB::table('student_journeys')->get() as $journey) {
                DB::table('student_journeys')->where('id', $journey->id)->lockForUpdate()->first();
                if (DB::table('learning_releases')->where('id', $journey->learning_release_id)->value('status') !== 'published') {
                    app(\App\Services\ZoneClock::class)->total($journey);
                    continue;
                }
                // Zone 2 now ends with its last required note, not a removed quiz.
                $zone = app(LearningEngine::class)->zone($journey, 2);
                $progress = DB::table('student_progress')->where('student_journey_id', $journey->id)->where('zone_id', $zone['id'])->first();
                $required = collect($zone['notes'])->where('is_required', true)->pluck('id');
                $read = DB::table('content_completions')->where('student_journey_id', $journey->id)->whereIn('learning_content_id', $required);
                if ($progress?->timing_started_at && $required->isNotEmpty() && (clone $read)->count() === $required->count()) {
                    $end = Carbon::parse((clone $read)->max('completed_at'));
                    $start = Carbon::parse($progress->timing_started_at);
                    if ($end->greaterThanOrEqualTo($start)) {
                        DB::table('student_progress')->where('id', $progress->id)->update(['timing_finished_at' => $end->format('Y-m-d H:i:s.v'), 'duration_seconds' => (int) $start->diffInSeconds($end)]);
                    } else {
                        DB::table('student_progress')->where('id', $progress->id)->update(['timing_started_at' => null, 'timing_finished_at' => null, 'duration_seconds' => null, 'countdown_started_at' => null, 'timing_is_partial' => false]);
                    }
                }
                app(LearningEngine::class)->refresh($journey);
            }
        });
    }

    public function down(): void
    {
        // Removed timing cannot be reconstructed; historic attempts remain available for audit.
    }
};
