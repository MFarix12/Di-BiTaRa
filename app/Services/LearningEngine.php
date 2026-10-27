<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearningEngine
{
    public function journey(User|\App\Models\LearnerSession $user): ?object
    {
        if (LearnerIdentity::isSession($user)) {
            return DB::transaction(function () use ($user) {
                $session=DB::table('learner_sessions')->where('id',$user->id)->lockForUpdate()->first();
                $existing=LearnerIdentity::journeys($user)->first();
                if ($existing) { return $existing; }
                $release=DB::table('learning_releases')->where('status','published')->where('school_year',4)->when($session->learning_release_id,fn($q)=>$q->where('id',$session->learning_release_id))->latest('id')->first();
                if (!$release) { return null; }
                $id=DB::table('student_journeys')->insertGetId(['learner_session_id'=>$user->id,'learning_release_id'=>$release->id,'flow_version'=>3]);
                $journey=DB::table('student_journeys')->find($id);$this->refresh($journey);return $journey;
            });
        }
        $existing = DB::table('student_journeys')->where('user_id', $user->id)->whereIn('status', ['active', 'completed'])->latest('id')->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user) {
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
            $existing = DB::table('student_journeys')->where('user_id', $user->id)->whereIn('status', ['active', 'completed'])->latest('id')->first();
            if ($existing) {
                return $existing;
            }
            $release = DB::table('learning_releases')->where('status', 'published')->latest('id')->first();
            if (! $release) {
                return null;
            }
            DB::table('student_journeys')->insertOrIgnore(['user_id' => $user->id, 'learning_release_id' => $release->id, 'flow_version'=>(int)config('learning.flow_version',2)]);
            $journey = DB::table('student_journeys')->where('user_id', $user->id)->where('learning_release_id', $release->id)->first();
            $this->refresh($journey);

            return $journey;
        });
    }

    public function manifest(object $journey): array
    {
        $release = DB::table('learning_releases')->find($journey->learning_release_id);
        abort_unless($release && $release->status === 'published', 403, 'Keluaran pembelajaran ini tidak tersedia. Sila hubungi Pentadbir.');

        $manifest=ZoneStructure::normalize(json_decode($release->manifest, true, 512, JSON_THROW_ON_ERROR));
        if (!app(ContinuousAdventure::class)->enabled($journey)) { foreach ($manifest['zones'] as &$z) { $z['has_countdown']=ZoneStructure::timed($z['sequence']); } unset($z); }
        foreach ($manifest['zones'] as &$zone) {
            foreach ($zone['activities'] as &$activity) {
                $activity['questions']=array_map([SentencePunctuation::class,'normalize'],$activity['questions']);
            }
            unset($activity);
        }
        unset($zone);
        return $manifest;
    }

    public function zone(object $journey, int $sequence): array
    {
        $z = collect($this->manifest($journey)['zones'])->firstWhere('sequence', $sequence);
        abort_unless($z, 404);

        return $z;
    }

    public function access(object $journey, int $zoneId): void
    {
        app(ContinuousAdventure::class)->assertCurrent($journey,$zoneId);
        $p = DB::table('student_progress')->where('student_journey_id', $journey->id)->where('zone_id', $zoneId)->first();
        abort_unless($p && $p->status !== 'locked', 403, 'Selesaikan zon sebelumnya untuk membuka zon ini.');
    }

    public function notify(User|\App\Models\LearnerSession $owner, string $key, string $title, string $body): void
    {
        DB::table('student_notifications')->insertOrIgnore([LearnerIdentity::key($owner) => $owner->id, 'dedupe_key' => $key, 'title' => $title, 'body' => $body, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function readNote(User|\App\Models\LearnerSession $user, int $sequence, int $id): void
    {
        DB::transaction(function () use ($user, $sequence, $id) {
            $journey = $this->journey($user);
            abort_unless($journey, 404);
            DB::table('student_journeys')->where('id', $journey->id)->lockForUpdate()->first();
            $z = $this->zone($journey, $sequence);
            $this->access($journey, $z['id']);
            app(ZoneClock::class)->assertStarted($journey, $z['id']);
            abort_unless(collect($z['notes'])->contains('id', $id), 404);
            DB::table('content_completions')->insertOrIgnore(['student_journey_id' => $journey->id, 'learning_content_id' => $id, 'completed_at' => now()->format('Y-m-d H:i:s.v')]);
            DB::table('student_journeys')->where('id', $journey->id)->update(['last_zone_id' => $z['id'], 'last_content_id' => $id, 'updated_at' => now()]);
            $this->refresh($journey);
        });
    }

    public function begin(User|\App\Models\LearnerSession $user, int $activityId): int
    {
        return DB::transaction(function () use ($user, $activityId) {
            $journey = $this->journey($user);
            abort_unless($journey, 404);
            DB::table('student_journeys')->where('id', $journey->id)->lockForUpdate()->first();
            $activity = null;
            $zone = null;
            foreach ($this->manifest($journey)['zones'] as $z) {
                foreach ($z['activities'] as $a) {
                    if ($a['id'] === $activityId) {
                        $activity = $a;
                        $zone = $z;
                    }
                }
            }
            abort_unless($activity, 404);
            $this->access($journey, $zone['id']);
            app(ZoneClock::class)->assertStarted($journey, $zone['id']);
            foreach ($zone['activities'] as $previous) {
                if ($previous['id'] === $activityId) { break; }
                if ($previous['is_required']) {
                    abort_unless(DB::table('attempts')->where('student_journey_id',$journey->id)->where('activity_id',$previous['id'])->where('passed',true)->where('status','completed')->exists(),403,'Selesaikan cabaran sebelumnya dahulu.');
                }
            }
            if (app(ContinuousAdventure::class)->enabled($journey)) { abort_unless(app(ContinuousAdventure::class)->ready($journey,$activityId),403,'Tunggu kira detik cabaran tamat.'); }
            $existing = DB::table('attempts')->where('student_journey_id', $journey->id)->where('activity_id', $activityId)->where('status', 'in_progress')->first();
            if ($existing) {
                return $existing->id;
            }
            $no = 1 + (int) DB::table('attempts')->where('student_journey_id', $journey->id)->where('activity_id', $activityId)->max('attempt_no');
            $snapshot = $activity;
            unset($snapshot['questions']);
            $snapshot['sequence'] = $zone['sequence'];
            $id = DB::table('attempts')->insertGetId(['student_journey_id' => $journey->id, 'activity_id' => $activityId, 'attempt_no' => $no, 'activity_snapshot' => json_encode($snapshot), 'max_score' => array_sum(array_column($activity['questions'], 'max_marks'))]);
            foreach ($activity['questions'] as $index => $q) {
                DB::table('attempt_questions')->insert(['attempt_id' => $id, 'question_id' => $q['id'], 'position' => $index + 1, 'question_snapshot' => json_encode(app(QuestionBank::class)->publicQuestion($q)), 'grading_snapshot' => json_encode($q), 'max_marks' => $q['max_marks']]);
            }
            DB::table('game_sessions')->insert(['attempt_id' => $id, 'mode' => $zone['sequence'] === 3 ? 'cockpit' : ($zone['sequence'] === 4 ? 'emergency' : 'lesson'), 'checkpoint' => '{}']);
            DB::table('student_journeys')->where('id', $journey->id)->update(['last_zone_id' => $zone['id'], 'last_activity_id' => $activityId, 'updated_at' => now()]);

            return $id;
        });
    }

    public function ownedAttempt(User|\App\Models\LearnerSession $user, int $id): object
    {
        $attempt = DB::table('attempts')->join('student_journeys', 'student_journeys.id', '=', 'attempts.student_journey_id')->where('attempts.id', $id)->where('student_journeys.'.LearnerIdentity::key($user), $user->id)->select('attempts.*')->first();
        abort_unless($attempt, 404);

        return $attempt;
    }

    public function answer(User|\App\Models\LearnerSession $user, int $id, int $questionId, mixed $payload, string $mode, ?string $submissionKey = null): void
    {
        DB::transaction(function () use ($user, $id, $questionId, $payload, $mode, $submissionKey) {
            $attempt = $this->ownedAttempt($user, $id);
            DB::table('student_journeys')->where('id', $attempt->student_journey_id)->lockForUpdate()->first();
            $attempt = DB::table('attempts')->where('id', $id)->lockForUpdate()->first();
            $journey = DB::table('student_journeys')->find($attempt->student_journey_id);
            abort_if($journey->status==='archived',409,'Sila gunakan set soalan terkini.');
            $zone = $this->zone($journey, json_decode($attempt->activity_snapshot, true)['sequence']);
            $this->access($journey,$zone['id']);
            abort_unless(collect($zone['activities'])->contains('id', $attempt->activity_id), 404);
            app(ZoneClock::class)->assertStarted($journey, $zone['id']);
            $q = DB::table('attempt_questions')->where('attempt_id', $id)->where('id', $questionId)->first();
            abort_unless($q, 404);
            if (DB::table('responses')->where('attempt_question_id', $q->id)->where('is_correct',true)->exists()) {
                return;
            }
            abort_unless($attempt->status === 'in_progress', 409);
            abort_if($this->pendingMeaning($attempt),409,'Baca maksud peribahasa dahulu.');
            $pending = DB::table('attempt_questions')->where('attempt_id', $id)->whereNotIn('id', DB::table('responses')->where('is_correct',true)->select('attempt_question_id'))->orderBy('position')->first();
            abort_unless($pending?->id === $q->id, 409);
            $snapshot = json_decode($q->grading_snapshot, true);
            $correct = app(QuestionBank::class)->grade($snapshot, $payload);
            $feedback = $correct ? '' : 'Jawapan kurang tepat, sila cuba lagi.';
            session()->flash('quiz_retry',!$correct);
            $submissionKey ??= (string) Str::uuid();
            if (DB::table('answer_submissions')->where('submission_key',$submissionKey)->exists()) { return; }
            DB::table('answer_submissions')->insert(['attempt_question_id'=>$q->id,'submission_key'=>$submissionKey,'payload'=>json_encode(['value'=>$payload]),'is_correct'=>$correct,'input_mode'=>$mode]);
            DB::table('attempt_questions')->where('id',$q->id)->update(['draft_payload'=>$correct ? null : json_encode(['value'=>$payload]),'draft_saved_at'=>$correct ? null : now()]);
            DB::table('responses')->updateOrInsert(['attempt_question_id'=>$q->id],['idempotency_key'=>$submissionKey,'payload'=>json_encode(['value'=>$payload]),'is_correct'=>$correct,'awarded_marks'=>$correct ? $q->max_marks : 0,'input_mode'=>$mode,'submitted_at'=>now(),'feedback'=>$feedback]);
            $this->syncAttempt($user,$attempt,$q->position,$mode);
        });
    }


    public function resumeMeaningPolicy(User|\App\Models\LearnerSession $user,int $id): void
    {
        DB::transaction(function()use($user,$id){
            $a=$this->ownedAttempt($user,$id);
            DB::table('student_journeys')->where('id',$a->student_journey_id)->lockForUpdate()->first();
            $a=DB::table('attempts')->where('id',$id)->lockForUpdate()->first();
            if($a->status!=='in_progress' || $this->pendingMeaning($a)) return;
            $questions=DB::table('attempt_questions')->where('attempt_id',$id);
            if(!$questions->exists() || (clone $questions)->whereNotIn('id',DB::table('responses')->where('is_correct',true)->select('attempt_question_id'))->exists()) return;
            $this->syncAttempt($user,$a,0,'pointer');
        });
    }

    public function pendingMeaning(object $attempt): ?object
    {
        if ($attempt->status!=='in_progress') return null;
        $journey=DB::table('student_journeys')->find($attempt->student_journey_id);
        if ((int)$journey->flow_version<3 || (int)json_decode($attempt->activity_snapshot,true)['sequence']!==4) return null;
        foreach(DB::table('attempt_questions')->where('attempt_id',$attempt->id)->whereNull('meaning_acknowledged_at')->whereIn('id',DB::table('responses')->where('is_correct',true)->select('attempt_question_id'))->orderBy('position')->get() as $q){
            $raw=json_decode($q->grading_snapshot,true);
            if(SentenceMeaning::for($raw)) return $q;
        }
        return null;
    }

    public function acknowledgeMeaning(User|\App\Models\LearnerSession $user,int $id,int $questionId): void
    {
        DB::transaction(function()use($user,$id,$questionId){
            $attempt=$this->ownedAttempt($user,$id);
            $journey=DB::table('student_journeys')->where('id',$attempt->student_journey_id)->lockForUpdate()->first();
            $attempt=DB::table('attempts')->where('id',$id)->lockForUpdate()->first();
            abort_if($journey->status==='archived',409);
            $zone=$this->zone($journey,4);$this->access($journey,$zone['id']);
            $q=DB::table('attempt_questions')->where('attempt_id',$id)->find($questionId);abort_unless($q,404);
            if($q->meaning_acknowledged_at) return;
            abort_unless($this->pendingMeaning($attempt)?->id===$q->id,409);
            DB::table('attempt_questions')->where('id',$q->id)->update(['meaning_acknowledged_at'=>now()]);
            $this->syncAttempt($user,$attempt,$q->position,'pointer');
        });
    }

    private function syncAttempt(User|\App\Models\LearnerSession $user,object $attempt,int $position,string $mode): void
    {
        $id=$attempt->id;
        $score=(int)DB::table('responses')->whereIn('attempt_question_id',DB::table('attempt_questions')->where('attempt_id',$id)->select('id'))->sum('awarded_marks');
        $remaining=DB::table('attempt_questions')->where('attempt_id',$id)->whereNotIn('id',DB::table('responses')->where('is_correct',true)->select('attempt_question_id'))->count();
        if($this->pendingMeaning($attempt)) $remaining++;
        $changes=['score'=>$score,'updated_at'=>now()];
        if(!$remaining){
            $policy=json_decode($attempt->activity_snapshot,true);$percent=$attempt->max_score?100*$score/$attempt->max_score:0;$passed=$percent>=(float)$policy['passing_percent'];
            $changes+=['status'=>'completed','completed_at'=>now(),'percentage'=>round($percent,2),'passed'=>$passed];
            $this->notify($user,'attempt-'.$id,'Aktiviti diselesaikan',$policy['title'].' — '.$score.' / '.$attempt->max_score.($passed?' · Lulus':' · Cuba semula'));
        }
        DB::table('attempts')->where('id',$id)->update($changes);
        DB::table('game_sessions')->where('attempt_id',$id)->update(['phase'=>$remaining?'navigation':'landed','checkpoint'=>json_encode(['answered'=>$position]),'input_mode'=>$mode,'last_saved_at'=>now()]);
        $this->refresh(DB::table('student_journeys')->find($attempt->student_journey_id));
    }

    public function refresh(object $journey): void
    {
        $manifest = $this->manifest($journey);
        $previous = true;
        foreach ($manifest['zones'] ?? [] as $z) {
            $notes = collect($z['notes'])->where('is_required', true)->pluck('id');
            $activities = collect($z['activities'])->where('is_required', true)->pluck('id');
            $count = DB::table('content_completions')->where('student_journey_id', $journey->id)->whereIn('learning_content_id', $notes)->count();
            $count += DB::table('attempts')->where('student_journey_id', $journey->id)->whereIn('activity_id', $activities)->where('status', 'completed')->where('passed', true)->distinct()->count('activity_id');
            $required = $notes->count() + $activities->count() + ($z['sequence'] === 5 ? 1 : 0);
            if ($z['sequence'] === 5) {
                $count += DB::table('reflections')->where('student_journey_id', $journey->id)->where('zone_id', $z['id'])->exists() ? 1 : 0;
            }
            if ($z['sequence'] === 1) {
                $readiness = app(DepartureReadiness::class)->for(LearnerIdentity::owner($journey));
                $required = 3;
                $count = DB::table('passport_stamps')->where('student_journey_id', $journey->id)->where('zone_id', $z['id'])->exists() ? 3 : $readiness['completed'];
            }
            $complete = $previous && $required > 0 && $count === $required;
            $score = DB::table('attempts')->where('student_journey_id', $journey->id)->where('status', 'completed')->whereIn('activity_id', $activities)->select('activity_id', DB::raw('MAX(score) as best'))->groupBy('activity_id')->get()->sum('best');
            $completedAt = DB::table('student_progress')->where('student_journey_id', $journey->id)->where('zone_id', $z['id'])->value('completed_at');
            DB::table('student_progress')->updateOrInsert(['student_journey_id' => $journey->id, 'zone_id' => $z['id']], ['status' => ! $previous ? 'locked' : ($complete ? 'completed' : ($count ? 'in_progress' : 'available')), 'required_units' => $required, 'completed_units' => $count, 'progress_percent' => $required ? 100 * $count / $required : 0, 'best_score' => $score, 'completed_at' => $complete ? ($completedAt ?? now()) : null, 'updated_at' => now()]);
            if ($complete) {
                app(ZoneClock::class)->finish($journey, $z['id']);
                $added = DB::table('passport_stamps')->insertOrIgnore(['student_journey_id' => $journey->id, 'zone_id' => $z['id'], 'title_snapshot' => $z['stamp_title']]);
                if ($added) {
                    $this->notify(LearnerIdentity::owner($journey), 'stamp-'.$journey->id.'-'.$z['id'], 'Cap Zon '.$z['sequence'].' diperoleh', $z['stamp_title'].($z['sequence'] < 5 ? ' · Zon seterusnya telah dibuka.' : ' · Pengembaraan anda lengkap!'));
                }
            } $previous = $complete;
        }
        app(ZoneClock::class)->total($journey);
        if (count($manifest['zones'] ?? []) === 5 && $previous) {
            DB::table('student_journeys')->where('id', $journey->id)->whereNull('completed_at')->update(['status' => 'completed', 'completed_at' => now()]);
            if ($journey->learner_session_id) { DB::table('learner_sessions')->where('id',$journey->learner_session_id)->whereNull('completed_at')->update(['completed_at'=>now()]); }
            $this->certificate($journey);
        }
    }

    public function certificate(object $journey): void
    {
        $journey=DB::table('student_journeys')->find($journey->id);
        $existing = DB::table('certificates')->where('student_journey_id', $journey->id)->first();
        if ($existing?->status === 'ready') {
            return;
        }
        $user = LearnerIdentity::owner($journey);
        $number = 'DBR-'.str_pad($journey->id, 8, '0', STR_PAD_LEFT);
        $recipient = $existing?->recipient_name ?? $user->display_name;
        try {
            $pdf = app(CertificatePdf::class)->generate($recipient, $number, \Carbon\Carbon::parse($journey->completed_at ?? now())->timezone('Asia/Kuala_Lumpur')->format('d/m/Y'), ['year'=>4,'class'=>$user->class_name??'','seconds'=>$journey->total_duration_seconds??0]);
            $path = 'certificates/'.$number.'.pdf';
            if (! Storage::disk('private')->put($path, $pdf)) {
                throw new \RuntimeException('PDF storage failed.');
            }
        } catch (\Throwable $e) {
            DB::table('certificates')->updateOrInsert(['student_journey_id' => $journey->id], ['certificate_number' => $number, 'recipient_name' => $recipient, 'status' => 'failed', 'failure_reason' => 'Fail sijil belum dapat disimpan. Sila cuba semula.']);

            return;
        }
        $media = DB::table('media_files')->insertGetId(['kind' => 'certificate', 'title' => 'Sijil '.$number, 'path' => $path, 'disk' => 'private', 'original_name' => $number.'.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => strlen($pdf), 'checksum' => hash('sha256', $pdf), 'status' => 'ready', 'created_by' => LearnerIdentity::isSession($user) ? DB::table('access_codes')->where('id',$user->access_code_id)->value('created_by') : $user->id]);
        DB::table('certificates')->updateOrInsert(['student_journey_id' => $journey->id], ['certificate_number' => $number, 'recipient_name' => $recipient, 'media_file_id' => $media, 'status' => 'ready', 'failure_reason' => null, 'issued_at' => now()]);
        $this->notify($user, 'certificate-'.$journey->id, 'Sijil anda telah tersedia', 'Lihat atau muat turun sijil melalui Pasport & Pencapaian.');
    }
}
