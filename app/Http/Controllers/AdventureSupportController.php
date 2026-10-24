<?php

namespace App\Http\Controllers;

use App\Services\ContinuousAdventure;
use App\Services\LearningEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdventureSupportController extends Controller
{
    public function heartbeat(Request $r, LearningEngine $engine, ContinuousAdventure $flow)
    {
        $data=$r->validate(['pause'=>'nullable|boolean']); $j=$engine->journey($r->user()); abort_unless($j,404);
        $clock=$flow->enabled($j) ? $flow->touch($j,(bool)($data['pause'] ?? false)) : null;
        return response()->json(['seconds'=>$clock ? intdiv($clock->elapsed_ms,1000) : null,'paused'=>!$clock?->last_seen_at,'finished'=>(bool)$clock?->finished_at]);
    }

    private function question(Request $r, int $attempt, LearningEngine $engine): object
    {
        $a=$engine->ownedAttempt($r->user(),$attempt);
        abort_if($engine->pendingMeaning($a),409,'Baca maksud peribahasa dahulu.');
        $j=DB::table('student_journeys')->find($a->student_journey_id);
        abort_unless($j->status==='active' && $a->status==='in_progress',409);
        $zone=$engine->zone($j,(int)json_decode($a->activity_snapshot,true)['sequence']);
        $engine->access($j,$zone['id']); app(\App\Services\ZoneClock::class)->assertStarted($j,$zone['id']);
        $q=DB::table('attempt_questions')->where('attempt_id',$attempt)->whereNotIn('id',DB::table('responses')->where('is_correct',true)->select('attempt_question_id'))->orderBy('position')->first();
        abort_unless($q && $q->id===(int)$r->input('question_id'),403);
        return $q;
    }

    public function draft(Request $r, int $attempt, LearningEngine $engine)
    {
        $data=$r->validate(['question_id'=>'required|integer','answer'=>'nullable']);
        abort_if(strlen(json_encode($data['answer'] ?? null))>20000,422);
        return DB::transaction(function () use ($r,$attempt,$engine,$data) {
            $a=$engine->ownedAttempt($r->user(),$attempt);
            DB::table('student_journeys')->where('id',$a->student_journey_id)->lockForUpdate()->first();
            $q=$this->question($r,$attempt,$engine);
            DB::table('attempt_questions')->where('id',$q->id)->update(['draft_payload'=>json_encode(['value'=>$data['answer'] ?? null]),'draft_saved_at'=>now()]);
            return response()->json(['message'=>'Jawapan disimpan.']);
        });
    }

}
