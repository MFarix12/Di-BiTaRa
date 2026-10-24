<?php

namespace Tests\Feature;

use App\Livewire\DeparturePreparation;
use App\Models\User;
use App\Services\ContinuousAdventure;
use App\Services\LearningEngine;
use App\Services\LearningMetrics;
use App\Services\StarterCurriculum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ContinuousAdventureTest extends TestCase
{
    use RefreshDatabase;
    private \App\Models\LearnerSession $student;
    private User $admin;
    private LearningEngine $engine;

    protected function setUp(): void
    {
        parent::setUp(); $this->seed(); Storage::fake('private');
        $this->admin=User::factory()->create(['role'=>'admin']);
        app(\App\Services\AvatarCatalogue::class)->install($this->admin->id);
        app(StarterCurriculum::class)->install($this->admin->id);
        $this->student=$this->makeLearner(['avatar_id'=>DB::table('avatars')->value('id'),'avatar_confirmed_at'=>now(),'onboarding_status'=>'completed']);
        $this->actingAs($this->student,'learner'); $this->engine=app(LearningEngine::class);
    }

    private function journey(): object { return $this->engine->journey($this->student); }

    private function openThree(): object
    {
        $j=$this->journey(); $zone=$this->engine->zone($j,2);
        foreach ($zone['notes'] as $n) { $this->post(route('student.note.read',['sequence'=>2,'note'=>$n['id']]),['confirmed'=>1])->assertRedirect(route('student.transition',2)); }
        return $j;
    }

    private function start(int $sequence): int
    {
        $j=$this->journey();$zone=$this->engine->zone($j,$sequence);$pending=app(ContinuousAdventure::class)->pending($j,$zone);
        $this->get(route('student.zone.launch',$sequence))->assertOk()->assertDontSee('Guna Kamera')->assertDontSee('Tanpa Kamera');
        $res=$this->post(route('student.zone.prepare',$sequence),['target'=>$pending['id']]);
        if ($sequence<5) { $res->assertOk()->assertSee('data-seconds="3"',false);$this->travel(3)->seconds();$res=$this->post(route('student.zone.start',$sequence),['target'=>$pending['id']]); }
        $res->assertRedirect();
        return (int)basename($res->headers->get('Location'));
    }

    private function finish(int $id): void
    {
        foreach (DB::table('attempt_questions')->where('attempt_id',$id)->orderBy('position')->get() as $q) {
            $this->travel(1)->seconds();$this->post(route('student.clock'))->assertOk();
            $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>json_decode($q->grading_snapshot,true)['answer']['value'],'submission_key'=>(string)Str::uuid()])->assertRedirect();
            if(\App\Services\SentenceMeaning::for(json_decode($q->grading_snapshot,true)))$this->post(route('student.meaning',$id),['question_id'=>$q->id,'read'=>1])->assertRedirect();
        }
    }

    private function reach(int $sequence): void
    {
        $this->openThree();
        for ($z=3;$z<$sequence;$z++) { while(app(ContinuousAdventure::class)->pending($this->journey(),$this->engine->zone($this->journey(),$z))) { $this->finish($this->start($z)); } }
    }

    public function test_meaning_is_a_persistent_server_gate_and_last_ack_completes_challenge_once(): void
    {
        $this->reach(4);$id=$this->start(4);
        $this->get(route('student.attempt',$id))->assertOk()->assertDontSee('data-camera-start',false)->assertDontSee('Kawalan Tangan');
        $this->post(route('student.answer',$id),['question_id'=>DB::table('attempt_questions')->where('attempt_id',$id)->value('id'),'answer'=>['i1'],'input_mode'=>'hand'])->assertSessionHasErrors('input_mode');
        $questions=DB::table('attempt_questions')->where('attempt_id',$id)->orderBy('position')->get();
        foreach($questions as $i=>$q){
            $raw=json_decode($q->grading_snapshot,true);
            $this->post(route('student.meaning',$id),['question_id'=>$q->id,'read'=>1])->assertStatus(409);
            $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>$raw['answer']['value']])->assertRedirect();
            if (!\App\Services\SentenceMeaning::for($raw)) {
                $this->assertNull($this->engine->pendingMeaning($this->engine->ownedAttempt($this->student,$id)));
                $this->get(route('student.attempt',$id))->assertOk()->assertDontSee('meaning-heading')->assertDontSee('Hebat! Jawapan betul.');
                continue;
            }
            $this->get(route('student.attempt',$id))->assertOk()->assertSee('Maksud Peribahasa')->assertSee(e($raw['answer']['meaning']),false)->assertDontSee('Hebat! Jawapan betul.');
            if(isset($questions[$i+1]))$this->post(route('student.answer',$id),['question_id'=>$questions[$i+1]->id,'answer'=>json_decode($questions[$i+1]->grading_snapshot,true)['answer']['value']])->assertStatus(409);
            $this->post(route('student.meaning',$id),['question_id'=>$q->id])->assertSessionHasErrors('read');
            $this->post(route('student.meaning',$id),['question_id'=>$q->id,'read'=>1])->assertRedirect();
            $this->assertNotNull(DB::table('attempt_questions')->find($q->id)->meaning_acknowledged_at);
            $this->post(route('student.meaning',$id),['question_id'=>$q->id,'read'=>1])->assertRedirect();
        }
        $this->assertDatabaseHas('attempts',['id'=>$id,'status'=>'completed','score'=>50]);$this->assertSame(5,DB::table('responses')->whereIn('attempt_question_id',$questions->pluck('id'))->count());
        DB::table('attempts')->where('id',$id)->update(['status'=>'in_progress','completed_at'=>null]);
        $this->engine->resumeMeaningPolicy($this->student,$id);
        $this->assertDatabaseHas('attempts',['id'=>$id,'status'=>'completed','score'=>50]);
    }

    public function test_drag_cards_have_no_dropdown_history_panel_or_side_clock(): void
    {
        $this->openThree();$this->finish($this->start(3));$this->finish($this->start(3));$id=$this->start(3);
        $html=$this->get(route('student.attempt',$id))->assertOk()->assertSee('data-category-value',false)->assertSee('word-color-5')->assertDontSee('<select',false)->assertDontSee('Jawapan Anda')->assertDontSee('journey-clock-side')->getContent();
        $this->assertLessThan(strpos($html,'</header>'),strpos($html,'data-journey-clock'));
    }

    public function test_focused_challenge_has_automatic_countdown_retry_feedback_and_no_dashboard_escape(): void
    {
        $this->openThree();$j=$this->journey();$zone=$this->engine->zone($j,3);$pending=app(ContinuousAdventure::class)->pending($j,$zone);
        $this->get(route('student.zone.launch',3))->assertOk()->assertDontSee('>Kembali<',false);
        $this->post(route('student.zone.prepare',3),['target'=>$pending['id']])->assertOk()->assertSee('data-countdown-form',false)->assertDontSee('data-countdown-submit',false)->assertDontSee('countdown-cancel',false)->assertDontSee('Mula Zon 3 ✈');
        $this->post(route('student.zone.start',3),['target'=>$pending['id']])->assertSessionHasErrors('countdown');
        $this->assertDatabaseCount('journey_clocks',0);$this->travel(3)->seconds();
        $response=$this->post(route('student.zone.start',3),['target'=>$pending['id']])->assertRedirect();$id=(int)basename($response->headers->get('Location'));
        $q=DB::table('attempt_questions')->where('attempt_id',$id)->orderBy('position')->first();$raw=json_decode($q->grading_snapshot,true);$wrong=$raw['answer']['value']==='true'?'false':'true';
        $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>$wrong])->assertRedirect();
        $page=$this->get(route('student.attempt',$id))->assertOk()->assertDontSee('data-rejected-answer=',false)->assertSee('Jawapan kurang tepat, sila cuba lagi.')->assertSee('nav-journey-clock')->assertDontSee('Papan Pemuka')->assertDontSee('Notifikasi');
        $this->assertDatabaseHas('responses',['attempt_question_id'=>$q->id,'is_correct'=>false,'feedback'=>'Jawapan kurang tepat, sila cuba lagi.']);
        $this->get('/pelajar')->assertRedirect(route('student.activities'));$this->get('/kuiz-aktiviti')->assertRedirect(route('student.attempt',$id));
        $clock=DB::table('journey_clocks')->value('started_at');$this->travel(5)->seconds();
        $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>$raw['answer']['value']])->assertRedirect();
        $this->get(route('student.attempt',$id))->assertOk()->assertDontSee('Hebat! Jawapan betul.');
        $this->assertSame($clock,DB::table('journey_clocks')->value('started_at'));$this->assertDatabaseCount('journey_clocks',1);
    }

    public function test_continuous_five_zone_journey_and_review_do_not_change_original_results(): void
    {
        DB::table('learner_sessions')->where('id',$this->student->id)->update(['avatar_id'=>null,'avatar_confirmed_at'=>null]);
        $j=$this->journey();$this->student->refresh();$this->assertSame(3,$j->flow_version);
        $this->get('/kuiz-aktiviti')->assertOk()->assertSee('Mari Meneroka');
        $this->get(route('student.zone.launch',2))->assertForbidden();
        Livewire::actingAs($this->student,'learner')->test(DeparturePreparation::class)->set('avatar_id',DB::table('avatars')->value('id'))->call('confirm')->assertRedirect(route('student.transition',1));
        $this->get(route('student.transition',1))->assertOk()->assertSee('data-adventure-transition',false);
        $this->get('/kuiz-aktiviti?zon=2')->assertRedirect(route('student.note',['sequence'=>2,'note'=>$this->engine->zone($j,2)['notes'][0]['id']]));
        $this->get(route('student.zone.launch',1))->assertForbidden();
        $this->openThree();
        for($z=3;$z<=5;$z++) {
            while(app(ContinuousAdventure::class)->pending($j,$this->engine->zone($j,$z))) { $this->finish($id=$this->start($z)); }
            if($z<5) {
                $this->get(route('student.transition',$z))->assertOk()->assertSee('Cap pasport diperoleh');
                $this->get(route('student.attempt',$id))->assertForbidden();
                $this->get(route('student.zone.launch',$z))->assertForbidden();
            }
        }
        $this->get('/kuiz-aktiviti?zon=5')->assertOk()->assertSee('Refleksi Hari Ini');
        $this->assertDatabaseCount('journey_clocks',1);$this->assertDatabaseCount('challenge_launches',6);
        $this->assertNull(DB::table('journey_clocks')->first()->finished_at);
        $this->post('/refleksi',['emotion'=>'seronok'])->assertRedirect(route('student.achievements'));
        $clock=DB::table('journey_clocks')->first();$this->assertNotNull($clock->finished_at);$this->assertGreaterThan(0,$clock->elapsed_ms);
        $j=DB::table('student_journeys')->find($j->id);$this->assertSame('completed',$j->status);$this->assertSame(intdiv($clock->elapsed_ms,1000),$j->total_duration_seconds);
        $this->assertDatabaseCount('passport_stamps',5);$this->assertDatabaseCount('certificates',1);
        $this->get('/pasport-pencapaian')->assertOk()->assertSee('Betul Cubaan Pertama')->assertSee('Ulang Kaji');
        $before=['attempts'=>DB::table('attempts')->get()->toJson(),'responses'=>DB::table('responses')->get()->toJson(),'submissions'=>DB::table('answer_submissions')->get()->toJson(),'clock'=>DB::table('journey_clocks')->get()->toJson(),'certificates'=>DB::table('certificates')->get()->toJson()];
        $this->get(route('student.review'))->assertOk()->assertSee('Jom Ulang Kaji');
        $activity=$this->engine->zone($j,5)['activities'][0];$raw=$activity['questions'][0];
        $this->get(route('student.review.activity',$activity['id']))->assertOk()->assertSee('Mod Ulang Kaji')->assertDontSee('data-journey-clock',false);
        $this->post(route('student.review.activity',$activity['id']),['answer'=>$raw['answer']['value']])->assertRedirect();
        $this->get(route('student.review.audio',$raw['audio_id']))->assertOk();
        $proverb=$this->engine->zone($j,4)['activities'][0];
        $this->post(route('student.review.activity',$proverb['id']),['answer'=>$proverb['questions'][0]['answer']['value']])->assertOk()->assertSee('Maksud Peribahasa')->assertDontSee('Hebat! Jawapan betul.');
        $this->travel(1)->hours();$this->post(route('student.clock'))->assertOk();$this->post('/refleksi',['emotion'=>'sukar'])->assertRedirect();
        foreach($before as $table=>$json){$name=['clock'=>'journey_clocks','submissions'=>'answer_submissions'][$table]??$table;$this->assertSame($json,DB::table($name)->get()->toJson());}
        $this->assertSame('seronok',DB::table('reflections')->value('emotion'));
    }

    public function test_single_clock_continues_during_closed_or_offline_intervals_without_resetting(): void
    {
        $this->openThree();$this->start(3);
        $this->travel(5)->seconds();$this->post(route('student.clock'))->assertJsonPath('seconds',5);
        $this->travel(2)->seconds();$this->post(route('student.clock'),['pause'=>true])->assertJsonPath('seconds',7)->assertJsonPath('paused',false);
        $this->travel(1)->hours();$this->post(route('student.clock'))->assertJsonPath('seconds',3607);
        $this->post(route('student.clock'))->assertJsonPath('seconds',3607);
        $this->assertDatabaseCount('journey_clocks',1);
    }

    public function test_every_challenge_requires_its_own_countdown_and_cannot_be_skipped(): void
    {
        $j=$this->openThree();$zone=$this->engine->zone($j,3);$first=$zone['activities'][0];$second=$zone['activities'][1];
        $this->post(route('student.zone.prepare',3),['target'=>$second['id']])->assertForbidden();
        $this->post(route('student.activity.begin',$second['id']))->assertForbidden();
        $this->travelTo(now()->startOfSecond());
        $this->post(route('student.zone.prepare',3),['target'=>$first['id']])->assertOk();
        $this->travel(2999)->milliseconds();$this->post(route('student.zone.start',3))->assertSessionHasErrors('countdown');
        $this->travel(1)->milliseconds();$res=$this->post(route('student.zone.start',3))->assertRedirect();$id=(int)basename($res->headers->get('Location'));
        $this->finish($id);$this->get(route('student.attempt',$id))->assertOk()->assertSee('Hebat! Misi selesai!')->assertSee(route('student.zone.prepare',3),false);
        $this->post(route('student.activity.begin',$second['id']))->assertRedirect(route('student.zone.launch',3));
        $this->post(route('student.zone.start',3))->assertSessionHasErrors('countdown');
        $before=DB::table('journey_clocks')->value('started_at');$this->start(3);$this->assertSame($before,DB::table('journey_clocks')->value('started_at'));
        $this->get(route('student.review'))->assertForbidden();
    }

    public function test_draft_ownership_restore_and_first_attempt_assistance_metrics(): void
    {
        $this->openThree();$id=$this->start(3);$q=DB::table('attempt_questions')->where('attempt_id',$id)->orderBy('position')->first();
        $this->postJson(route('student.draft',$id),['question_id'=>$q->id,'answer'=>'salah'])->assertOk();
        $this->get(route('student.attempt',$id))->assertOk()->assertSee('data-draft=',false)->assertSee('Kemajuan disimpan automatik',false);
        $future=DB::table('attempt_questions')->where('attempt_id',$id)->orderBy('position')->skip(1)->first();
        $this->postJson(route('student.draft',$id),['question_id'=>$future->id,'answer'=>'betul'])->assertForbidden();
        $this->postJson('/misi/'.$id.'/bantuan',['question_id'=>$q->id,'request_key'=>(string)Str::uuid()])->assertNotFound();
        $this->assertDatabaseCount('learning_help_events',0);
        $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>'salah'])->assertRedirect();
        $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>'betul'])->assertRedirect();
        $m=app(LearningMetrics::class)->for($this->journey()->id);$this->assertSame(0,$m['first_correct']);$this->assertSame(1,$m['answered']);$this->assertArrayNotHasKey('helps',$m);$this->assertSame(2,$m['submissions']);
        $this->assertSame(10,DB::table('attempts')->find($id)->score);
        $this->postJson(route('student.draft',$id),['question_id'=>$q->id,'answer'=>'salah'])->assertForbidden();
        $this->actingAs($this->makeLearner(),'learner');$this->postJson(route('student.draft',$id),['question_id'=>$future->id,'answer'=>'betul'])->assertNotFound();
        $this->actingAs($this->admin,'web')->get(route('admin.reports'))->assertOk()->assertSee('Markah Cubaan Pertama')->assertDontSee('<th>Bantuan</th>',false);
    }

    public function test_three_listens_are_strict_and_survive_reload_and_wrong_answers(): void
    {
        $this->reach(5);$id=$this->start(5);$q=DB::table('attempt_questions')->where('attempt_id',$id)->orderBy('position')->first();
        for($i=1;$i<=3;$i++){$this->post(route('student.dictation.audio',$id),['question_id'=>$q->id])->assertOk()->assertHeader('X-Audio-Plays',(string)$i);}
        $this->post(route('student.dictation.audio',$id),['question_id'=>$q->id])->assertStatus(429);
        $this->post(route('student.dictation.audio',$id),['question_id'=>$q->id,'help'=>true,'request_key'=>(string)Str::uuid()])->assertStatus(429);
        $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>'belum tepat'])->assertRedirect();
        $this->get(route('student.attempt',$id))->assertOk()->assertDontSee('Bantuan Audio');
        $this->assertDatabaseHas('attempt_questions',['id'=>$q->id,'audio_plays'=>3,'audio_help_plays'=>0]);
        $this->post(route('student.answer',$id),['question_id'=>$q->id,'answer'=>'di rumah'])->assertRedirect();
        $this->assertSame(10,DB::table('attempts')->find($id)->score);
    }
}
