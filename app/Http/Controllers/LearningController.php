<?php

namespace App\Http\Controllers;

use App\Services\LearningEngine;
use App\Services\StudentOverview;
use App\Services\ZoneClock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningController extends Controller
{
    public function launch(Request $r, int $sequence, LearningEngine $engine, ZoneClock $clock)
    {
        $journey = $engine->journey($r->user());
        abort_unless($journey, 404);
        $zone = $engine->zone($journey, $sequence);
        $target = $r->validate(['next' => 'nullable|in:catalog,note,activity,attempt', 'target' => 'nullable|integer|min:1']);
        if ($clock->ready($journey, $zone['id']) && ($target['next'] ?? '') !== 'activity') {
            return $this->destination($r, $engine, $zone, $target);
        }
        return view('student.zone-briefing', compact('zone','target'));
    }

    public function prepare(Request $r, int $sequence, LearningEngine $engine, ZoneClock $clock)
    {
        $journey=$engine->journey($r->user());
        abort_unless($journey,404);
        $zone=$engine->zone($journey,$sequence);
        $target=$r->validate(['next'=>'nullable|in:catalog,note,activity,attempt','target'=>'nullable|integer|min:1','input_mode'=>'nullable|in:pointer,keyboard']);
        $clock->prepare($journey,$zone);
        if ($clock->ready($journey,$zone['id'])) { return $this->destination($r,$engine,$zone,$target); }
        if (isset($target['input_mode'])) { $r->session()->put('zone_input_mode.'.$sequence,$target['input_mode']); }
        return view('student.zone-launch',compact('zone','target'));
    }

    public function start(Request $r, int $sequence, LearningEngine $engine, ZoneClock $clock)
    {
        $journey = $engine->journey($r->user());
        abort_unless($journey, 404);
        $zone = $engine->zone($journey, $sequence);
        $target = $r->validate(['next' => 'nullable|in:catalog,note,activity,attempt', 'target' => 'nullable|integer|min:1']);
        $clock->start($journey, $zone);
        return $this->destination($r, $engine, $zone, $target);
    }

    private function destination(Request $r, LearningEngine $engine, array $zone, array $target)
    {
        $id = (int) ($target['target'] ?? 0);
        if (($target['next'] ?? '') === 'note') {
            abort_unless(collect($zone['notes'])->contains('id', $id), 404);
            return redirect()->route('student.note', ['sequence' => $zone['sequence'], 'note' => $id]);
        }
        if (($target['next'] ?? '') === 'activity') {
            abort_unless(collect($zone['activities'])->contains('id', $id), 404);
            return redirect()->route('student.attempt', $engine->begin($r->user(), $id));
        }
        if (($target['next'] ?? '') === 'attempt') {
            $attempt = $engine->ownedAttempt($r->user(), $id);
            abort_unless(json_decode($attempt->activity_snapshot, true)['sequence'] === $zone['sequence'], 404);
            return redirect()->route('student.attempt', $id);
        }
        return redirect()->route('student.activities', ['zon' => $zone['sequence']]);
    }

    public function catalog(Request $r, LearningEngine $engine, StudentOverview $overview)
    {
        $value = $r->query('zon', '1');
        abort_unless(is_scalar($value) && ctype_digit((string) $value) && (int) $value >= 1 && (int) $value <= 5, 404);
        $sequence = (int) $value;
        $journey = $engine->journey($r->user());
        $data = $overview->for($r->user());
        $zone = $journey ? $engine->zone($journey, $sequence) : null;

        return view('student.learning-catalog', $data + ['selectedSequence' => $sequence, 'zone' => $zone, 'zoneOpen' => $zone && ($data['progress']->get($zone['id'])?->status !== 'locked'), 'notesDone' => $journey ? DB::table('content_completions')->where('student_journey_id', $journey->id)->pluck('learning_content_id')->all() : []]);
    }

    public function note(Request $r, int $sequence, int $note, LearningEngine $engine)
    {
        $journey = $engine->journey($r->user());
        abort_unless($journey, 404);
        $zone = $engine->zone($journey, $sequence);
        $engine->access($journey, $zone['id']);
        $content = collect($zone['notes'])->firstWhere('id', $note);
        abort_unless($content, 404);
        if (! app(ZoneClock::class)->ready($journey, $zone['id'])) {
            return redirect()->route('student.zone.launch', ['sequence' => $sequence, 'next' => 'note', 'target' => $note]);
        }

        return view('student.note', compact('zone', 'content'));
    }

    public function read(Request $r, int $sequence, int $note, LearningEngine $engine)
    {
        if ($sequence===2) { $r->validate(['confirmed'=>'accepted']); }
        $engine->readNote($r->user(), $sequence, $note);

        return redirect()->route('student.activities', ['zon' => $sequence])->with('status', 'Nota telah ditandakan selesai.');
    }

    public function begin(Request $r, int $activity, LearningEngine $engine)
    {
        $journey = $engine->journey($r->user());
        abort_unless($journey, 404);
        $zone = collect($engine->manifest($journey)['zones'])->first(fn ($z) => collect($z['activities'])->contains('id', $activity));
        abort_unless($zone, 404);
        if (! app(ZoneClock::class)->ready($journey, $zone['id'])) {
            return redirect()->route('student.zone.launch', ['sequence' => $zone['sequence'], 'next' => 'activity', 'target' => $activity]);
        }
        return redirect()->route('student.attempt', ['attempt' => $engine->begin($r->user(), $activity)]);
    }

    public function attempt(Request $r, int $attempt, LearningEngine $engine)
    {
        $record = $engine->ownedAttempt($r->user(), $attempt);
        $activity = json_decode($record->activity_snapshot, true);
        if ($meaningQuestion=$engine->pendingMeaning($record)) {
            $meaning=\App\Services\SentenceMeaning::for(json_decode($meaningQuestion->grading_snapshot,true));
            return view('student.meaning',compact('record','activity','meaningQuestion','meaning'));
        }
        $journey = DB::table('student_journeys')->find($record->student_journey_id);
        if ($journey->status === 'archived') {
            return redirect()->route('student.activities')->with('status','Set soalan telah dikemas kini. Sila gunakan aktiviti terkini.');
        }
        $zone = $engine->zone($journey, $activity['sequence']);
        if ($record->status!=='completed' && ! app(ZoneClock::class)->ready($journey, $zone['id'])) {
            return redirect()->route('student.zone.launch', ['sequence' => $zone['sequence'], 'next' => 'attempt', 'target' => $attempt]);
        }
        abort_unless(collect($zone['activities'])->contains('id', $record->activity_id), 404);
        $activity['kind'] = collect($zone['activities'])->firstWhere('id', $record->activity_id)['kind'];
        $questions = DB::table('attempt_questions')->where('attempt_id', $attempt)->orderBy('position')->get();
        $responses = DB::table('responses')->whereIn('attempt_question_id', $questions->pluck('id'))->get()->keyBy('attempt_question_id');
        $current = $questions->first(fn ($q) => ! $responses->get($q->id)?->is_correct);
        $question = $current ? app(\App\Services\QuestionBank::class)->publicQuestion(\App\Services\SentencePunctuation::normalize(json_decode($current->grading_snapshot,true))) : null;
        $review = $questions->filter(fn ($q) => $responses->get($q->id)?->is_correct)->map(function ($q) use ($responses) {
            $raw = json_decode($q->grading_snapshot, true);
            $answer = $raw['answer']['value'];
            if ($raw['type'] === 'sentence_order') {
                $answer = $raw['answer']['sentence'] ?? implode(' ', array_map(fn ($key) => $raw['config']['items'][$key], $answer));
            } elseif (in_array($raw['type'], ['objective', 'true_false'], true)) {
                $answer = collect($raw['options'])->firstWhere('key', $answer)['text'] ?? 'Lihat pilihan jawapan';
            }if ($raw['type'] === 'drag_drop') {
                $answer = collect($answer)->map(fn ($target, $key) => $raw['config']['items'][$key].' → '.$raw['config']['targets'][$target])->all();
            }

return ['prompt' => $raw['prompt'], 'response' => $responses[$q->id], 'answer' => $answer,'meaning'=>$raw['answer']['meaning'] ?? null];
        })->values();

        return view('student.mission', compact('record', 'activity', 'questions', 'responses', 'current', 'question', 'review') + ['preview' => false]);
    }

    public function answer(Request $r, int $attempt, LearningEngine $engine)
    {
        $data = $r->validate(['question_id' => 'required|integer', 'answer' => 'required', 'input_mode' => 'nullable|in:pointer,keyboard','submission_key'=>'nullable|uuid']);
        abort_if(strlen(json_encode($data['answer'])) > 20000, 422);
        $engine->answer($r->user(), $attempt, $data['question_id'], $data['answer'], $data['input_mode'] ?? 'pointer', $data['submission_key'] ?? null);

        $record=$engine->ownedAttempt($r->user(),$attempt);
        if ($record->status==='completed' && $record->passed) {
            $sequence=(int)json_decode($record->activity_snapshot,true)['sequence'];
            if ($sequence===5) { return redirect()->route('student.activities',['zon'=>5]); }
            $zone=$engine->zone(DB::table('student_journeys')->find($record->student_journey_id),$sequence);
            if (DB::table('student_progress')->where('student_journey_id',$record->student_journey_id)->where('zone_id',$zone['id'])->value('status')==='completed') {
                return redirect()->route('student.activities',['zon'=>$sequence])->with('zone_complete',$sequence);
            }
        }
        return redirect()->route('student.attempt', ['attempt' => $attempt]);
    }

    public function dictationAudio(Request $r, int $attempt, LearningEngine $engine)
    {
        $data=$r->validate(['question_id'=>'required|integer','help'=>'nullable|boolean','request_key'=>'required_if:help,1|nullable|uuid']);
        $mediaId=DB::transaction(function () use ($r,$attempt,$engine,$data) {
            $a=$engine->ownedAttempt($r->user(),$attempt);
            DB::table('attempts')->where('id',$attempt)->lockForUpdate()->first();
            $journey=DB::table('student_journeys')->find($a->student_journey_id);
            abort_if($journey->status==='archived',409);
            $zone=$engine->zone($journey,5);
            app(ZoneClock::class)->assertStarted($journey,$zone['id']);
            $q=DB::table('attempt_questions')->where('attempt_id',$attempt)->whereNotIn('id',DB::table('responses')->where('is_correct',true)->select('attempt_question_id'))->orderBy('position')->first();
            abort_unless($a->status==='in_progress' && $q && $q->id===(int)$data['question_id'],403);
            $snapshot=json_decode($q->question_snapshot,true);
            abort_unless($snapshot['type']==='dictation' && $snapshot['audio_id'],404);
            $available=DB::table('media_files')->where('id',$snapshot['audio_id'])->where('status','ready')->first();
            abort_unless($available && \Illuminate\Support\Facades\Storage::disk($available->disk)->exists($available->path),404);
            if ($q->audio_plays>=3) {
                abort_unless((int)$journey->flow_version<3 && !empty($data['help']),429,(int)$journey->flow_version===3 ? 'Audio telah didengar tiga kali. Tulis jawapan anda.' : 'Tiga kali selesai. Tekan Bantuan Audio untuk dengar lagi.');
                return [$snapshot['audio_id'],3,0];
            }
            DB::table('attempt_questions')->where('id',$q->id)->increment('audio_plays');
            return [$snapshot['audio_id'],$q->audio_plays+1,0];
        });
        $m=DB::table('media_files')->where('id',$mediaId[0])->where('status','ready')->first();
        abort_unless($m && \Illuminate\Support\Facades\Storage::disk($m->disk)->exists($m->path),404);
        return response()->file(\Illuminate\Support\Facades\Storage::disk($m->disk)->path($m->path),['Content-Type'=>$m->mime_type,'Cache-Control'=>'private, no-store','X-Audio-Plays'=>(string)$mediaId[1],'X-Audio-Help'=>(string)$mediaId[2]]);
    }

    public function reflect(Request $r, LearningEngine $engine)
    {
        $data = $r->validate(['emotion' => 'required|in:gembira,sangat_seronok,seronok,biasa,mencabar,sukar', 'note' => 'nullable|string|max:500']);
        DB::transaction(function () use ($r, $engine, $data) {
            $j = $engine->journey($r->user());
            abort_unless($j, 404);
            DB::table('student_journeys')->where('id', $j->id)->lockForUpdate()->first();
            if ($j->status==='completed') { return; }
            $z = $engine->zone($j, 5);
            $engine->access($j, $z['id']);
            app(ZoneClock::class)->assertStarted($j, $z['id']);
            foreach (collect($z['activities'])->where('is_required', true) as $a) {
                abort_unless(DB::table('attempts')->where('student_journey_id', $j->id)->where('activity_id', $a['id'])->where('passed', true)->where('status', 'completed')->exists(), 403, 'Lengkapkan Imlak terlebih dahulu.');
            }
            DB::table('reflections')->updateOrInsert(['student_journey_id' => $j->id, 'zone_id' => $z['id']], $data + ['submitted_at' => now()]);
            $engine->refresh($j);
        });

        return redirect()->route('student.achievements')->with('status', 'Tahniah! Pengembaraan anda telah selesai. Cap Zon 5 diperoleh.')->with('journey_finished',true);
    }

    public function notifications(Request $r)
    {
        return view('student.notifications', ['notifications' => \App\Services\LearnerIdentity::notifications($r->user())->latest('id')->paginate(20)]);
    }

    public function markNotifications(Request $r)
    {
        \App\Services\LearnerIdentity::notifications($r->user())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('status','Notifikasi ditandakan telah dibaca.');
    }
}
