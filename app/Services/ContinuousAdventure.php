<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContinuousAdventure
{
    public function enabled(?object $journey): bool { return (int) ($journey->flow_version ?? 1) >= 2; }

    public function current(object $journey): ?array
    {
        foreach (app(LearningEngine::class)->manifest($journey)['zones'] as $zone) {
            if (DB::table('student_progress')->where('student_journey_id',$journey->id)->where('zone_id',$zone['id'])->value('status') !== 'completed') { return $zone; }
        }
        return null;
    }

    public function assertCurrent(object $journey, int $zoneId): void
    {
        if ($this->enabled($journey)) { abort_unless(($this->current($journey)['id'] ?? null) === $zoneId, 403, 'Zon ini sudah selesai atau masih terkunci. Teruskan perjalanan semasa.'); }
    }

    public function pending(object $journey, array $zone): ?array
    {
        return collect($zone['activities'])->first(fn ($a) => ! DB::table('attempts')->where('student_journey_id',$journey->id)->where('activity_id',$a['id'])->where('status','completed')->where('passed',true)->exists());
    }

    public function ready(object $journey, int $activityId): bool
    {
        return DB::table('challenge_launches')->where('student_journey_id',$journey->id)->where('activity_id',$activityId)->whereNotNull('started_at')->exists();
    }

    public function prepare(object $journey, array $zone, int $activityId): void
    {
        $this->assertCurrent($journey,$zone['id']);
        abort_unless($this->pending($journey,$zone)['id'] === $activityId,403,'Selesaikan cabaran semasa dahulu.');
        DB::table('challenge_launches')->insertOrIgnore(['student_journey_id'=>$journey->id,'activity_id'=>$activityId,'countdown_started_at'=>now()->format('Y-m-d H:i:s.v')]);
    }

    public function start(object $journey, array $zone, int $activityId): void
    {
        DB::transaction(function () use ($journey,$zone,$activityId) {
            DB::table('student_journeys')->where('id',$journey->id)->lockForUpdate()->first();
            $this->assertCurrent($journey,$zone['id']);
            abort_unless(($this->pending($journey,$zone)['id'] ?? null) === $activityId,403);
            $launch=DB::table('challenge_launches')->where('student_journey_id',$journey->id)->where('activity_id',$activityId)->first();
            if ($launch?->started_at) { return; }
            if (in_array($zone['sequence'],[3,4],true) && (!$launch?->countdown_started_at || Carbon::parse($launch->countdown_started_at)->addSeconds(3)->isFuture())) {
                throw ValidationException::withMessages(['countdown'=>'Tunggu kira detik tamat.']);
            }
            DB::table('challenge_launches')->updateOrInsert(['student_journey_id'=>$journey->id,'activity_id'=>$activityId],['started_at'=>now()->format('Y-m-d H:i:s.v')]);
            DB::table('student_progress')->where('student_journey_id',$journey->id)->where('zone_id',$zone['id'])->whereNull('timing_started_at')->update(['timing_started_at'=>now()->format('Y-m-d H:i:s.v')]);
            $this->startClock($journey);
        });
    }

    private function startClock(object $journey): void
    {
        // A resumed pre-update attempt receives an explicitly partial clock, never fabricated historical time.
        DB::table('journey_clocks')->insertOrIgnore(['student_journey_id'=>$journey->id,'started_at'=>now()->format('Y-m-d H:i:s.v'),'last_seen_at'=>now()->format('Y-m-d H:i:s.v'),'is_partial'=>DB::table('attempts')->where('student_journey_id',$journey->id)->exists()]);
    }

    public function touch(object $journey, bool $pause = false): ?object
    {
        return DB::transaction(function () use ($journey,$pause) {
            DB::table('student_journeys')->where('id',$journey->id)->lockForUpdate()->first();
            $clock=DB::table('journey_clocks')->where('student_journey_id',$journey->id)->first();
            if (!$clock || $clock->finished_at) { return $clock; }
            if ((int)$journey->flow_version===3) {
                DB::table('journey_clocks')->where('id',$clock->id)->update(['elapsed_ms'=>max(0,(int)Carbon::parse($clock->started_at)->diffInMilliseconds(now())),'last_seen_at'=>now()->format('Y-m-d H:i:s.v')]);
                return DB::table('journey_clocks')->find($clock->id);
            }
            $delta=$clock->last_seen_at ? max(0,(int)Carbon::parse($clock->last_seen_at)->diffInMilliseconds(now())) : 0;
            // Five-second heartbeats lease at most fifteen seconds. Offline/closed gaps are not billed.
            $elapsed=$clock->elapsed_ms+($delta<=15000 ? $delta : 0);
            DB::table('journey_clocks')->where('id',$clock->id)->update(['elapsed_ms'=>$elapsed,'last_seen_at'=>$pause ? null : now()->format('Y-m-d H:i:s.v')]);
            return DB::table('journey_clocks')->find($clock->id);
        });
    }

    public function finish(object $journey): void
    {
        $clock=$this->touch($journey);
        if (!$clock || $clock->finished_at) { return; }
        DB::table('journey_clocks')->where('id',$clock->id)->update(['finished_at'=>now()->format('Y-m-d H:i:s.v'),'last_seen_at'=>null]);
        DB::table('student_journeys')->where('id',$journey->id)->update(['total_duration_seconds'=>intdiv($clock->elapsed_ms,1000)]);
    }
}
