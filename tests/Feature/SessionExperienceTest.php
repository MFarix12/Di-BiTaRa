<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Auth};
use App\Models\{User,LearnerSession};
use App\Services\{StarterCurriculum,SessionInsights,CertificatePdf};
class SessionExperienceTest extends TestCase {
 use RefreshDatabase;
 public function test_same_browser_can_resume_but_ended_and_forged_tokens_cannot(): void {
  $this->seed();$admin=User::factory()->create(['role'=>'admin']);app(StarterCurriculum::class)->install($admin->id);
  DB::table('access_codes')->insert(['code'=>'DBR-T4-RESUME','name'=>'Sambung','school_year'=>4,'created_by'=>$admin->id,'status'=>'active']);
  $this->post(route('access.start'),['code'=>'DBR-T4-RESUME'])->assertRedirect(route('student.details'))->assertCookie('dibitara_resume');
  $token=session('learner_token');$s=LearnerSession::firstOrFail();$journey=DB::table('student_journeys')->first();
  session()->flush();Auth::forgetGuards();
  $this->withCookie('dibitara_resume',$token)->get(route('access.enter'))->assertRedirect(route('student.details'));
  $this->assertSame($token,session('learner_token'));$this->assertDatabaseCount('learner_sessions',1);$this->assertDatabaseCount('student_journeys',1);
  $this->post(route('student.details.save'),['display_name'=>'Aina','class_name'=>'4 Maju'])->assertRedirect();
  $this->get('/profil-sesi')->assertOk()->assertSee('Tahun 4 | 4 Maju');
  $this->post(route('student.session.end'),['confirmed'=>1])->assertRedirect('/')->assertCookieExpired('dibitara_resume');
  session()->flush();Auth::forgetGuards();$this->withCookie('dibitara_resume',$token)->get('/masukkan-kod')->assertOk();$this->assertNull(session('learner_token'));
  session()->flush();Auth::forgetGuards();$this->withCookie('dibitara_resume',str_repeat('a',64))->get('/masukkan-kod')->assertOk();$this->assertNull(session('learner_token'));
 }
 public function test_ranking_is_per_code_completed_only_and_shared_when_tied(): void {
  $this->seed();$a=$this->makeLearner(['completed_at'=>now()]);$b=$this->makeLearner(['completed_at'=>now()]);$c=$this->makeLearner(['completed_at'=>now()]);$d=$this->makeLearner();
  DB::table('learner_sessions')->whereIn('id',[$b->id,$c->id,$d->id])->update(['access_code_id'=>$a->access_code_id]);
  $release=DB::table('learning_releases')->insertGetId(['version'=>'rank','title'=>'Ujian','manifest'=>'{}','scoring_policy'=>'{}','checksum'=>str_repeat('a',64)]);
  foreach([[$a,100,90],[$b,120,100],[$c,100,90],[$d,260,10]] as [$s,$score,$seconds]){
   $j=DB::table('student_journeys')->insertGetId(['learner_session_id'=>$s->id,'learning_release_id'=>$release,'total_duration_seconds'=>$seconds]);
   DB::table('student_progress')->insert(['student_journey_id'=>$j,'zone_id'=>DB::table('zones')->where('sequence',3)->value('id'),'required_units'=>1,'completed_units'=>1,'best_score'=>$score,'status'=>'completed']);
  }
  $r=app(SessionInsights::class)->rankings([$a->access_code_id]);$this->assertSame(1,$r[$b->id]);$this->assertSame(2,$r[$a->id]);$this->assertSame(2,$r[$c->id]);$this->assertArrayNotHasKey($d->id,$r);
  $insights=app(SessionInsights::class)->dashboard();$this->assertSame(3,$insights['Sesi Selesai']);$this->assertSame('106.7',$insights['Purata Markah']);
  $this->actingAs(User::where('role','admin')->first(),'web')->get('/admin')->assertOk()->assertSee('Purata Markah')->assertSee('Sesi Selesai');
 }
 public function test_certificate_includes_year_class_duration_and_completion(): void {
  $pdf=app(CertificatePdf::class)->generate('Aina','DBR-1','23/10/2026',['year'=>5,'class'=>'5 Gemilang','seconds'=>790]);
  foreach(['Tahun 5','5 Gemilang','pengembaraan pembelajaran Di-BITaRa.','Aina'] as $text)$this->assertStringContainsString($text,$pdf);
  foreach(['Status:','Masa Keseluruhan:','00:13:10'] as $text)$this->assertStringNotContainsString($text,$pdf);
 }
}
