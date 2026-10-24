<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('learning_contents')->whereIn('zone_id', DB::table('zones')->where('sequence', 1)->select('id'))->update(['is_required' => false]);
            foreach (DB::table('student_journeys')->get() as $journey) {
                if (DB::table('learning_releases')->where('id', $journey->learning_release_id)->value('status') !== 'published') { continue; }
                DB::table('student_journeys')->where('id', $journey->id)->lockForUpdate()->first();
                app(\App\Services\LearningEngine::class)->refresh($journey);
            }
        });
    }

    public function down(): void
    {
        // Awarded stamps and profile preparation are retained.
    }
};
