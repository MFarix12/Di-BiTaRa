<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LearnerSession;
use App\Services\LearningEngine;
use App\Services\StarterCurriculum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccessSessionTest extends TestCase
{
    use RefreshDatabase;
    private User $admin;
    protected function setUp(): void
    {
        parent::setUp();$this->seed();Storage::fake('private');
        $this->admin=User::factory()->create(['role'=>'admin']);
        app(\App\Services\AvatarCatalogue::class)->install($this->admin->id);
        app(StarterCurriculum::class)->install($this->admin->id);
        DB::table('access_codes')->insert(['code'=>'DBR-TEST1234','name'=>'Ujian Kelas','status'=>'active','created_by'=>$this->admin->id,'created_at'=>now(),'updated_at'=>now()]);
    }

    private function enter(): LearnerSession
    {
        $this->post(route('access.start'),['code'=>'dbr-test1234'])->assertRedirect(route('student.details'));
        $this->post(route('student.details.save'),['display_name'=>'Aina','class_name'=>'4 Cemerlang'])->assertRedirect('/kuiz-aktiviti?zon=1');
        $this->get('/kuiz-aktiviti?zon=1')->assertOk()->assertSee('Mari Meneroka')->assertSee('Kod Disahkan')->assertDontSee('ID Pelajar')->assertDontSee('Log Keluar')->assertDontSee('Kapten Awan')->assertDontSee('Pembantu Rio')->assertSee('Pembantu Juruterbang');
        return LearnerSession::firstOrFail();
    }

    public function test_code_creates_real_session_without_creating_an_account_and_preserves_scope(): void
    {
        $before=User::count();$s=$this->enter();$this->assertSame($before,User::count());$this->assertSame('Aina',$s->display_name);
        $j=DB::table('student_journeys')->where('learner_session_id',$s->id)->first();$this->assertNull($j->user_id);$this->assertSame(3,$j->flow_version);
        $this->get('/pelajar')->assertRedirect(route('student.activities'));
        $this->get('/profil-sesi')->assertOk()->assertDontSee('<footer',false)->assertSee('Tamat Sesi')->assertSee('Profil Anda')->assertDontSee('Lihat Tutorial Semula')->assertDontSee('Notifikasi')->assertDontSee('Papan Pemuka');
        $this->get('/pasport-pencapaian')->assertOk()->assertSee('Kod / Sesi')->assertDontSee('Profil Saya');
        $this->get('/admin')->assertRedirect('/log-masuk-admin');
        $this->assertDatabaseCount('student_profiles',0);
    }

    public function test_details_are_required_before_zones_and_avatar_cannot_change_identity(): void
    {
        $this->post(route('access.start'),['code'=>'DBR-TEST1234'])->assertRedirect(route('student.details'));
        $session=LearnerSession::firstOrFail();
        $this->get('/kuiz-aktiviti?zon=1')->assertRedirect(route('student.details'));
        $this->get('/pasport-pencapaian')->assertRedirect(route('student.details'));
        $this->get(route('student.details'))->assertOk()->assertSee('Nama Pelajar')->assertSee('Kelas');
        $this->post(route('student.details.save'),['display_name'=>' ','class_name'=>' '])->assertSessionHasErrors(['display_name','class_name']);
        $this->post(route('student.details.save'),['display_name'=>str_repeat('A',101),'class_name'=>str_repeat('B',51)])->assertSessionHasErrors(['display_name','class_name']);
        $this->assertNull($session->fresh()->class_name);
        $this->post(route('student.details.save'),['display_name'=>'  Aina  ','class_name'=>'  4 Cemerlang  '])->assertRedirect('/kuiz-aktiviti?zon=1');
        $this->assertDatabaseHas('learner_sessions',['id'=>$session->id,'access_code_id'=>$session->access_code_id,'display_name'=>'Aina','class_name'=>'4 Cemerlang']);
        $this->get('/kuiz-aktiviti?zon=1')->assertOk()->assertDontSee('departure-identity-fields',false)->assertDontSee('wire:model="display_name"',false);
        $this->post(route('student.details.save'),['display_name'=>'Nama Lain','class_name'=>'5 Lain'])->assertRedirect(route('student.activities'));
        \Livewire\Livewire::test(\App\Livewire\DeparturePreparation::class)->set('avatar_id',DB::table('avatars')->value('id'))->call('confirm')->assertRedirect(route('student.transition',1));
        $this->assertDatabaseHas('learner_sessions',['id'=>$session->id,'display_name'=>'Aina','class_name'=>'4 Cemerlang']);
        $this->assertDatabaseCount('passport_stamps',1);
        $this->assertDatabaseCount('learner_sessions',1);
    }

    public function test_old_account_entry_routes_are_removed_and_public_links_use_codes(): void
    {
        foreach(['/daftar','/daftar/berjaya','/log-masuk','/pulihkan-akaun','/profil','/tetapan'] as $url){$this->get($url)->assertNotFound();}
        foreach(['/','/tentang','/cara-bermain','/capai-pembelajaran','/masukkan-kod','/log-masuk-admin'] as $url){$this->get($url)->assertOk()->assertSee('Masukkan Kod')->assertDontSee('Daftar Pelajar')->assertDontSee('Log Masuk Pelajar');}
    }

    public function test_public_code_form_is_only_on_dedicated_centered_page(): void
    {
        $home=$this->get('/')->assertOk()->assertDontSee('access-code-form')->assertDontSee('name="code"',false);
        // Public navigation remains the path from the introduction to the dedicated code page.
        $home->assertSee(route('access.enter'));
        $this->get('/masukkan-kod')->assertOk()->assertSee('access-entry-card')->assertSee('Masukkan Kod')->assertSee('Mula Pengembaraan')->assertSee('Dapatkan kod daripada guru.')->assertDontSee('auth-intro')->assertDontSee('Jom mulakan pengembaraan!')->assertDontSee('Kembali ke Halaman Utama');
    }

    public function test_invalid_disabled_and_expired_codes_never_create_sessions(): void
    {
        $this->post(route('access.start'),['code'=>'TIADA'])->assertSessionHasErrors('code');
        DB::table('access_codes')->update(['status'=>'inactive']);$this->post(route('access.start'),['code'=>'DBR-TEST1234'])->assertSessionHasErrors('code');
        DB::table('access_codes')->update(['status'=>'active','expires_at'=>now()->subMinute()]);$this->post(route('access.start'),['code'=>'DBR-TEST1234'])->assertSessionHasErrors('code');
        $this->assertDatabaseCount('learner_sessions',0);$this->assertDatabaseCount('student_journeys',0);
    }

    public function test_complete_code_session_keeps_one_wall_clock_real_questions_stamps_and_certificate(): void
    {
        $s=$this->enter();$this->actingAs($s,'learner');$engine=app(LearningEngine::class);$j=$engine->journey($s);
        \Livewire\Livewire::test(\App\Livewire\Tutorial::class)->call('finish',true);
        $this->assertDatabaseHas('learner_sessions',['id'=>$s->id,'onboarding_status'=>'skipped']);
        \Livewire\Livewire::test(\App\Livewire\DeparturePreparation::class)->set('avatar_id',DB::table('avatars')->value('id'))->call('confirm')->assertRedirect(route('student.transition',1));
        $this->get('/kuiz-aktiviti')->assertRedirect(route('student.note',['sequence'=>2,'note'=>$engine->zone($j,2)['notes'][0]['id']]));
        $this->post(route('student.note.read',['sequence'=>2,'note'=>$engine->zone($j,2)['notes'][0]['id']]),['confirmed'=>1])->assertRedirect(route('student.transition',2));
        $this->get(route('student.zone.launch',1))->assertForbidden();$this->assertDatabaseCount('journey_clocks',0);
        $start=null;
        foreach([3,4,5] as $sequence){
            $zone=$engine->zone($j,$sequence);
            foreach($zone['activities'] as $activity){
                $this->get(route('student.zone.launch',$sequence))->assertOk();
                $launch=$this->post(route('student.zone.prepare',$sequence),['target'=>$activity['id']]);
                if($sequence<5){$launch->assertOk();$this->travel(3)->seconds();$launch=$this->post(route('student.zone.start',$sequence),['target'=>$activity['id']]);}
                $launch->assertRedirect();$attempt=(int)basename($launch->headers->get('Location'));
                $this->assertDatabaseCount('journey_clocks',1);$stamp=DB::table('journey_clocks')->value('started_at');$start??=$stamp;$this->assertSame($start,$stamp);
                foreach(DB::table('attempt_questions')->where('attempt_id',$attempt)->orderBy('position')->get() as $q){
                    $raw=json_decode($q->grading_snapshot,true);
                    if($sequence===5 && $q->position===1){
                        for($i=0;$i<3;$i++)$this->post(route('student.dictation.audio',$attempt),['question_id'=>$q->id])->assertOk();
                        $this->post(route('student.dictation.audio',$attempt),['question_id'=>$q->id,'help'=>true,'request_key'=>(string)\Illuminate\Support\Str::uuid()])->assertStatus(429);
                    }
                    $this->travel(2)->seconds();
                    $this->post(route('student.answer',$attempt),['question_id'=>$q->id,'answer'=>$raw['answer']['value']])->assertRedirect();
                    if($sequence===4 && \App\Services\SentenceMeaning::for($raw))$this->post(route('student.meaning',$attempt),['question_id'=>$q->id,'read'=>1])->assertRedirect();
                }
            }
        }
        // Offline/closed intervals are included by the latest document's continuous timer.
        $this->travel(2)->minutes();$this->post(route('student.clock'),['pause'=>true])->assertOk();
        $this->post('/refleksi',['emotion'=>'sangat_seronok'])->assertRedirect(route('student.achievements'));
        $j=DB::table('student_journeys')->find($j->id);$this->assertSame('completed',$j->status);$this->assertGreaterThanOrEqual(120,$j->total_duration_seconds);
        $this->assertDatabaseCount('passport_stamps',5);$this->assertDatabaseCount('certificates',1);$this->assertSame(260,DB::table('responses')->sum('awarded_marks'));
        $this->get('/pasport-pencapaian')->assertOk()->assertSee('Aina')->assertSee('Masa Keseluruhan Pengembaraan');
        $this->post(route('student.clock'))->assertOk();
        $this->get('/pasport-pencapaian')->assertOk()->assertSee('journey-finished-dialog');
        $this->post(route('student.completion.seen'))->assertRedirect(route('student.achievements'));
        $this->get('/pasport-pencapaian')->assertOk()->assertDontSee('journey-finished-dialog');

        $cert=DB::table('certificates')->first();$this->get(route('student.certificate.preview',$cert->id))->assertOk()->assertHeader('Content-Type','application/pdf');
        $before=DB::table('responses')->get()->toJson();$this->get(route('student.review'))->assertOk();$this->assertSame($before,DB::table('responses')->get()->toJson());
        $this->post(route('student.session.end'),['confirmed'=>1])->assertRedirect('/');$this->assertNotNull(LearnerSession::find($s->id)->ended_at);
        $this->get('/pelajar')->assertRedirect(route('access.enter'));$this->assertDatabaseCount('certificates',1);$this->assertDatabaseCount('passport_stamps',5);
        $this->assertSame(1,User::count());
    }

    public function test_access_code_admin_crud_reports_and_audit_are_admin_only(): void
    {
        $this->get(route('admin.codes'))->assertRedirect('/log-masuk-admin');
        $this->actingAs($this->admin,'web')->get(route('admin.codes'))->assertOk();
        $this->post(route('admin.codes.store'),['name'=>'Bahasa Melayu Tahun 4','status'=>'active','school_year'=>4])->assertRedirect();
        $code=DB::table('access_codes')->latest('id')->first();$this->assertStringStartsWith('DBR-',$code->code);
        $this->put(route('admin.codes.update',$code->id),['name'=>'Sesi Baharu','status'=>'inactive','expires_at'=>'2026-12-31T14:00'])->assertRedirect();
        $this->assertDatabaseHas('access_codes',['id'=>$code->id,'status'=>'inactive','expires_at'=>'2026-12-31 06:00:00']);
        $this->get(route('admin.sessions'))->assertOk();$this->get('/admin/laporan')->assertNotFound();
        $this->assertDatabaseHas('audit_logs',['entity_type'=>'access_codes','entity_id'=>$code->id,'action'=>'update']);
    }

    public function test_same_code_sessions_are_isolated_and_reentry_does_not_duplicate_active_session(): void
    {
        $first=$this->enter();$token=session('learner_token');
        $this->assertNotSame($token,$first->token_hash);
        $this->post(route('access.start'),['code'=>'DBR-TEST1234'])->assertRedirect(route('student.activities'));
        $this->assertDatabaseCount('learner_sessions',1);
        $other=$this->makeLearner();
        DB::table('learner_sessions')->where('id',$other->id)->update(['access_code_id'=>$first->access_code_id]);
        $j=app(LearningEngine::class)->journey($first);
        app(LearningEngine::class)->notify($first,'secret-notification','Rahsia Sesi Pertama','Hanya sesi ini.');
        $this->app['session']->forget('learner_token');
        $this->actingAs($other,'learner')->get('/notifikasi')->assertOk()->assertDontSee('Rahsia Sesi Pertama');
        $this->get('/pasport-pencapaian')->assertOk()->assertDontSee($first->public_id);
        $this->assertNotSame($j->id,app(LearningEngine::class)->journey($other)->id);
        $this->post(route('student.session.end'),['confirmed'=>1])->assertRedirect('/');
        $this->assertNull($first->fresh()->ended_at);
    }

    public function test_admin_previews_all_zones_without_creating_sessions_attempts_or_awards(): void
    {
        $this->actingAs($this->admin,'web');
        for($i=1;$i<=5;$i++)$this->get(route('admin.zone3.preview',['zon'=>$i]))->assertOk()->assertSee('Pratonton sahaja');
        $this->assertDatabaseCount('learner_sessions',0);$this->assertDatabaseCount('attempts',0);$this->assertDatabaseCount('passport_stamps',0);
    }
}
