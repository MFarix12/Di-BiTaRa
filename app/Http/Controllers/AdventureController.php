<?php

namespace App\Http\Controllers;

use App\Services\ContinuousAdventure;
use App\Services\LearningEngine;
use App\Services\StudentOverview;
use App\Services\ZoneClock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdventureController extends LearningController
{
    private function context(Request $r, LearningEngine $engine): array
    {
        $j=$engine->journey($r->user()); abort_unless($j,404);
        return [$j,app(ContinuousAdventure::class)];
    }

    public function catalog(Request $r, LearningEngine $engine, StudentOverview $overview)
    {
        $j=$engine->journey($r->user()); $flow=app(ContinuousAdventure::class);
        if (!$j || !$flow->enabled($j)) { return parent::catalog($r,$engine,$overview); }
        $current=$flow->current($j);
        if (!$current) { return redirect()->route('student.review'); }
        if ($r->has('zon') && (string)$r->query('zon') !== (string)$current['sequence']) { return redirect()->route('student.activities',['zon'=>$current['sequence']]); }
        $r->query->set('zon',(string)$current['sequence']);
        if ($current['sequence']===2) {
            $done=DB::table('content_completions')->where('student_journey_id',$j->id)->pluck('learning_content_id');
            $note=collect($current['notes'])->first(fn ($n)=>!$done->contains($n['id']));
            if ($note) { return redirect()->route('student.note',['sequence'=>2,'note'=>$note['id']]); }
        }
        if ($current['sequence']>=3 && ($pending=$flow->pending($j,$current))) {
            if (!$flow->ready($j,$pending['id'])) { return redirect()->route('student.zone.launch',$current['sequence']); }
            $attempt=DB::table('attempts')->where('student_journey_id',$j->id)->where('activity_id',$pending['id'])->where('status','in_progress')->first();
            if ($attempt) { return redirect()->route('student.attempt',$attempt->id); }
        }
        return parent::catalog($r,$engine,$overview);
    }

    public function launch(Request $r, int $sequence, LearningEngine $engine, ZoneClock $clock)
    {
        [$j,$flow]=$this->context($r,$engine);
        if (!$flow->enabled($j)) { return parent::launch($r,$sequence,$engine,$clock); }
        $zone=$engine->zone($j,$sequence); $flow->assertCurrent($j,$zone['id']);
        if ($sequence<=2) { return redirect()->route('student.activities',['zon'=>$sequence]); }
        $pending=$flow->pending($j,$zone);
        if (!$pending) { return redirect()->route('student.activities',['zon'=>$sequence]); }
        if ($flow->ready($j,$pending['id'])) { return redirect()->route('student.activities',['zon'=>$sequence]); }
        $target=['next'=>'activity','target'=>$pending['id']];
        return view('student.zone-briefing',compact('zone','target')+['continuous'=>true]);
    }

    public function prepare(Request $r, int $sequence, LearningEngine $engine, ZoneClock $clock)
    {
        [$j,$flow]=$this->context($r,$engine);
        if (!$flow->enabled($j)) { return parent::prepare($r,$sequence,$engine,$clock); }
        $zone=$engine->zone($j,$sequence); $flow->assertCurrent($j,$zone['id']);
        if ($sequence<3) { return redirect()->route('student.activities',['zon'=>$sequence]); }
        $pending=$flow->pending($j,$zone); abort_unless($pending,409);
        $data=$r->validate(['target'=>'nullable|integer','input_mode'=>'nullable|in:pointer,keyboard']);
        abort_if(isset($data['target']) && (int)$data['target']!==$pending['id'],403);
        $r->session()->forget('zone_input_mode');
        $flow->prepare($j,$zone,$pending['id']);
        if ($flow->ready($j,$pending['id'])) { return redirect()->route('student.activities',['zon'=>$sequence]); }
        $target=['next'=>'activity','target'=>$pending['id']];
        return view('student.zone-launch',compact('zone','target')+['challengeCountdown'=>true]);
    }

    public function start(Request $r, int $sequence, LearningEngine $engine, ZoneClock $clock)
    {
        [$j,$flow]=$this->context($r,$engine);
        if (!$flow->enabled($j)) { return parent::start($r,$sequence,$engine,$clock); }
        $zone=$engine->zone($j,$sequence); $flow->assertCurrent($j,$zone['id']);
        if ($sequence<3) { return redirect()->route('student.activities',['zon'=>$sequence]); }
        $pending=$flow->pending($j,$zone); abort_unless($pending,409);
        $r->validate(['target'=>'nullable|integer']);
        abort_if($r->filled('target') && (int)$r->input('target')!==$pending['id'],403);
        $flow->start($j,$zone,$pending['id']);
        return redirect()->route('student.attempt',$engine->begin($r->user(),$pending['id']));
    }

    public function begin(Request $r, int $activity, LearningEngine $engine)
    {
        [$j,$flow]=$this->context($r,$engine);
        if (!$flow->enabled($j)) { return parent::begin($r,$activity,$engine); }
        $zone=$flow->current($j); abort_unless($zone && ($flow->pending($j,$zone)['id'] ?? null)===$activity,403);
        if (!$flow->ready($j,$activity)) { return redirect()->route('student.zone.launch',$zone['sequence']); }
        return redirect()->route('student.attempt',$engine->begin($r->user(),$activity));
    }

    public function read(Request $r, int $sequence, int $note, LearningEngine $engine)
    {
        $response=parent::read($r,$sequence,$note,$engine);
        [$j,$flow]=$this->context($r,$engine);
        if ($flow->enabled($j)) {
            $z=$engine->zone($j,$sequence);
            return DB::table('student_progress')->where('student_journey_id',$j->id)->where('zone_id',$z['id'])->value('status')==='completed'
                ? redirect()->route('student.transition',$sequence) : redirect()->route('student.activities');
        }
        return $response;
    }

    public function attempt(Request $r, int $attempt, LearningEngine $engine)
    {
        $a=$engine->ownedAttempt($r->user(),$attempt); $j=DB::table('student_journeys')->find($a->student_journey_id); $flow=app(ContinuousAdventure::class);
        if ($flow->enabled($j)) {
            $zone=$engine->zone($j,(int)json_decode($a->activity_snapshot,true)['sequence']);
            $flow->assertCurrent($j,$zone['id']);
        }
        if ($a->status==='in_progress') {
            $engine->resumeMeaningPolicy($r->user(),$attempt);
            $fresh=$engine->ownedAttempt($r->user(),$attempt);
            if($fresh->status==='completed') return redirect()->route('student.activities');
        }
        return parent::attempt($r,$attempt,$engine);
    }

    public function answer(Request $r, int $attempt, LearningEngine $engine)
    {
        $response=parent::answer($r,$attempt,$engine);
        $a=$engine->ownedAttempt($r->user(),$attempt); $j=DB::table('student_journeys')->find($a->student_journey_id); $flow=app(ContinuousAdventure::class);
        if (!$flow->enabled($j) || $a->status!=='completed') { return $response; }
        $sequence=(int)json_decode($a->activity_snapshot,true)['sequence'];
        if ($sequence===5) { return redirect()->route('student.activities',['zon'=>5]); }
        return ($flow->current($j)['sequence'] ?? 6)>$sequence ? redirect()->route('student.transition',$sequence) : redirect()->route('student.attempt',$attempt);
    }

    public function meaning(Request $r,int $attempt,LearningEngine $engine)
    {
        $data=$r->validate(['question_id'=>'required|integer','read'=>'accepted']);
        $engine->acknowledgeMeaning($r->user(),$attempt,(int)$data['question_id']);
        $a=$engine->ownedAttempt($r->user(),$attempt);$j=DB::table('student_journeys')->find($a->student_journey_id);
        if((app(ContinuousAdventure::class)->current($j)['sequence']??6)>4)return redirect()->route('student.transition',4);
        return redirect()->route('student.attempt',$attempt);
    }

    public function transition(Request $r, int $sequence, LearningEngine $engine)
    {
        [$j,$flow]=$this->context($r,$engine); $zone=$engine->zone($j,$sequence);
        $p=DB::table('student_progress')->where('student_journey_id',$j->id)->where('zone_id',$zone['id'])->first();
        abort_unless($sequence<5 && $p?->status==='completed' && ($flow->current($j)['sequence'] ?? 0)===$sequence+1,403);
        return view('student.zone-transition',compact('zone','p'));
    }
}
