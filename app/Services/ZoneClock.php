<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ZoneClock
{
    public function progress(object $journey, int $zoneId): object
    {
        $p = DB::table('student_progress')->where('student_journey_id', $journey->id)->where('zone_id', $zoneId)->first();
        abort_unless($p && $p->status !== 'locked', 403, 'Selesaikan zon sebelumnya dahulu.');
        return $p;
    }

    public function ready(object $journey, int $zoneId): bool
    {
        $p = $this->progress($journey, $zoneId);
        if (app(ContinuousAdventure::class)->enabled($journey)) {
            $zone=collect(app(LearningEngine::class)->manifest($journey)['zones'])->firstWhere('id',$zoneId);
            $pending=app(ContinuousAdventure::class)->pending($journey,$zone);
            return $zone['sequence']<3 || !$pending || app(ContinuousAdventure::class)->ready($journey,$pending['id']);
        }
        return ! ZoneStructure::timed((int) DB::table('zones')->where('id', $zoneId)->value('sequence')) || $p->status === 'completed' || $p->timing_started_at !== null;
    }

    public function prepare(object $journey, array $zone): void
    {
        if (app(ContinuousAdventure::class)->enabled($journey)) { $pending=app(ContinuousAdventure::class)->pending($journey,$zone); if ($pending) { app(ContinuousAdventure::class)->prepare($journey,$zone,$pending['id']); } return; }
        DB::transaction(function () use ($journey, $zone) {
            DB::table('student_journeys')->where('id', $journey->id)->lockForUpdate()->first();
            $p = $this->progress($journey, $zone['id']);
            if ($p->status !== 'completed' && ! $p->timing_started_at && ! $p->countdown_started_at && ZoneStructure::timed($zone['sequence'])) {
                DB::table('student_progress')->where('id', $p->id)->update(['countdown_started_at' => now()->format('Y-m-d H:i:s.v')]);
            }
        });
    }

    public function start(object $journey, array $zone): void
    {
        if (app(ContinuousAdventure::class)->enabled($journey)) { $pending=app(ContinuousAdventure::class)->pending($journey,$zone); if ($pending) { app(ContinuousAdventure::class)->start($journey,$zone,$pending['id']); } return; }
        DB::transaction(function () use ($journey, $zone) {
            DB::table('student_journeys')->where('id', $journey->id)->lockForUpdate()->first();
            $p = $this->progress($journey, $zone['id']);
            if (! ZoneStructure::timed($zone['sequence']) || $p->status === 'completed' || $p->timing_started_at) {
                return;
            }
            if (ZoneStructure::timed($zone['sequence']) && (! $p->countdown_started_at || Carbon::parse($p->countdown_started_at)->addSeconds(3)->isFuture())) {
                throw ValidationException::withMessages(['countdown' => 'Tunggu kira detik tamat. Kemudian tekan Mula.']);
            }
            DB::table('student_progress')->where('id', $p->id)->update([
                'timing_started_at' => now()->format('Y-m-d H:i:s.v'),
                'timing_is_partial' => $p->completed_units > 0 || DB::table('attempts')->where('student_journey_id', $journey->id)->whereIn('activity_id', array_column($zone['activities'], 'id'))->exists(),
            ]);
        });
    }

    public function assertStarted(object $journey, int $zoneId): void
    {
        abort_unless($this->ready($journey, $zoneId), 403, 'Tekan Mula Zon dan tunggu kira detik tamat.');
    }

    // Called inside the same journey transaction that awards the zone stamp.
    public function finish(object $journey, int $zoneId): void
    {
        if (app(ContinuousAdventure::class)->enabled($journey)) { return; }
        $p = $this->progress($journey, $zoneId);
        if (ZoneStructure::timed((int) DB::table('zones')->where('id', $zoneId)->value('sequence')) && $p->timing_started_at && $p->duration_seconds === null) {
            $end = now();
            DB::table('student_progress')->where('id', $p->id)->whereNull('duration_seconds')->update([
                'timing_finished_at' => $end->format('Y-m-d H:i:s.v'),
                'duration_seconds' => max(0, (int) Carbon::parse($p->timing_started_at)->diffInSeconds($end)),
            ]);
        }
    }

    public function total(object $journey): void
    {
        if (app(ContinuousAdventure::class)->enabled($journey)) {
            if (DB::table('student_progress')->where('student_journey_id',$journey->id)->where('status','completed')->count()===5) { app(ContinuousAdventure::class)->finish($journey); }
            return;
        }
        $rows = DB::table('student_progress')->where('student_journey_id', $journey->id)->whereIn('zone_id', DB::table('zones')->whereIn('sequence', ZoneStructure::TIMED)->select('id'))->get();
        $complete = $rows->count() === 3 && $rows->every(fn ($p) => $p->status === 'completed' && $p->duration_seconds !== null && ! $p->timing_is_partial);
        DB::table('student_journeys')->where('id', $journey->id)->update(['total_duration_seconds' => $complete ? $rows->sum('duration_seconds') : null]);
    }

    public static function format(?int $seconds): string
    {
        return $seconds === null ? '—' : sprintf('%02d minit %02d saat', intdiv($seconds, 60), $seconds % 60);
    }
}
