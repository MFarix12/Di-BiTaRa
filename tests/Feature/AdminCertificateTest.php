<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Storage};
use App\Models\User;
use App\Services\{LearningEngine,SessionCertificates};
class AdminCertificateTest extends TestCase {
 use RefreshDatabase;
 private User $admin;
 protected function setUp(): void {parent::setUp();$this->seed();Storage::fake('private');$this->admin=User::factory()->create(['role'=>'admin']);}
 private function ready(string $name='Aina',int $year=5): array {
  $s=$this->makeLearner(['display_name'=>$name,'class_name'=>$year.' Gemilang','school_year'=>$year,'completed_at'=>now()]);
  $release=DB::table('learning_releases')->insertGetId(['version'=>uniqid('cert-'),'title'=>'Ujian','manifest'=>'{}','scoring_policy'=>'{}','checksum'=>str_repeat('a',64)]);
  $j=DB::table('student_journeys')->insertGetId(['learner_session_id'=>$s->id,'learning_release_id'=>$release,'status'=>'completed','completed_at'=>now(),'total_duration_seconds'=>790]);
  foreach(DB::table('zones')->whereBetween('sequence',[1,5])->get() as $z){
   DB::table('student_progress')->insert(['student_journey_id'=>$j,'zone_id'=>$z->id,'status'=>'completed','required_units'=>1,'completed_units'=>1,'completed_at'=>now()]);
   DB::table('passport_stamps')->insert(['student_journey_id'=>$j,'zone_id'=>$z->id,'title_snapshot'=>'Cop']);
   if($z->sequence===5)DB::table('reflections')->insert(['student_journey_id'=>$j,'zone_id'=>$z->id,'emotion'=>'happy']);
  }
  app(LearningEngine::class)->certificate(DB::table('student_journeys')->find($j));
  $cert=DB::table('certificates')->where('student_journey_id',$j)->first();
  return [$s,$j,$cert];
 }
 public function test_admin_gets_the_existing_official_pdf_and_preview(): void {
  [$s,$j,$c]=$this->ready();$media=DB::table('media_files')->find($c->media_file_id);$bytes=Storage::disk('private')->get($media->path);
  $this->actingAs($this->admin,'web')->get(route('admin.sessions'))->assertOk()->assertSee('Lihat Sijil')->assertSee('Pilih sijil Aina');
  $this->get(route('admin.certificates.preview',$c->id))->assertOk()->assertSee('5 Gemilang')->assertSee('Muat Turun PDF')->assertViewHas('preview',fn($v)=>count($v['lines'])===10 && str_starts_with($v['frame'],'data:image/jpeg;base64,'));
  $pdf=$this->get(route('admin.certificates.download',$c->id))->assertOk()->assertDownload(app(SessionCertificates::class)->filename($c));
  $this->assertSame($bytes,$pdf->streamedContent());
  $this->get(route('admin.certificates.file',$c->id))->assertOk()->assertHeader('Content-Disposition','inline; filename="'.app(SessionCertificates::class)->filename($c).'"');
  foreach(['Aina','5 Gemilang','Tahun 5','pengembaraan pembelajaran Di-BITaRa.'] as $word)$this->assertStringContainsString($word,$bytes);
  foreach(['Status:','Masa Keseluruhan:','00:13:10'] as $word)$this->assertStringNotContainsString($word,$bytes);
 }
 public function test_auth_and_completion_rules_are_checked_on_every_request(): void {
  [$s,$j,$c]=$this->ready();
  $this->get(route('admin.certificates.download',$c->id))->assertRedirect('/log-masuk-admin');
  $this->actingAs($s,'learner')->get(route('admin.certificates.preview',$c->id))->assertRedirect('/log-masuk-admin');
  $this->actingAs($this->admin,'web');
  DB::table('reflections')->where('student_journey_id',$j)->delete();
  $this->get(route('admin.certificates.download',$c->id))->assertNotFound();
  $this->post(route('admin.certificates.bundle'),['scope'=>'selected','sessions'=>[$s->id]])->assertSessionHasErrors('certificates');
  [$s2,$j2,$c2]=$this->ready('Belum Tamat');DB::table('learner_sessions')->where('id',$s2->id)->update(['completed_at'=>null]);
  $this->get(route('admin.certificates.preview',$c2->id))->assertNotFound();
  [$s3,$j3,$c3]=$this->ready('Zon Belum Selesai');DB::table('student_progress')->where('student_journey_id',$j3)->limit(1)->update(['status'=>'in_progress']);
  $this->get(route('admin.certificates.download',$c3->id))->assertNotFound();
 }
 public function test_zip_uses_filters_or_selection_and_contains_identical_pdfs(): void {
  [$a,$j,$c]=$this->ready('Aina',5);[$b,$jb,$cb]=$this->ready('Bala',6);
  $this->actingAs($this->admin,'web');
  $filtered=$this->post(route('admin.certificates.bundle'),['scope'=>'filtered','year'=>5])->assertOk();
  $zip=$filtered->streamedContent();$name=app(SessionCertificates::class)->filename($c);
  $this->assertStringContainsString($name,$zip);$this->assertStringNotContainsString(app(SessionCertificates::class)->filename($cb),$zip);
  $header=unpack('Vsig/vversion/vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vsize/vname/vextra',substr($zip,0,30));
  $pdf=gzinflate(substr($zip,30+$header['name']+$header['extra'],$header['compressed']));
  $media=DB::table('media_files')->find($c->media_file_id);$this->assertSame(Storage::disk('private')->get($media->path),$pdf);
  $both=$this->post(route('admin.certificates.bundle'),['scope'=>'selected','sessions'=>[$a->id,$b->id]])->assertOk()->streamedContent();
  $this->assertStringContainsString($name,$both);$this->assertStringContainsString(app(SessionCertificates::class)->filename($cb),$both);
  $this->post(route('admin.certificates.bundle'),['scope'=>'selected','year'=>5,'sessions'=>[$b->id]])->assertUnprocessable();
  $this->post(route('admin.certificates.bundle'),['scope'=>'filtered','name'=>'Tiada'])->assertSessionHasErrors('certificates');
  $this->postJson(route('admin.certificates.bundle'),['scope'=>'selected'])->assertUnprocessable();
 }
 public function test_missing_or_unsafe_files_are_never_offered(): void {
  [$s,$j,$c]=$this->ready();$media=DB::table('media_files')->find($c->media_file_id);
  $this->actingAs($this->admin,'web');Storage::disk('private')->delete($media->path);
  $this->get(route('admin.certificates.download',$c->id))->assertNotFound();
  $this->get(route('admin.sessions'))->assertOk()->assertSee('Sijil belum tersedia')->assertDontSee('Lihat Sijil');
  DB::table('media_files')->where('id',$media->id)->update(['path'=>'../outside.pdf']);
  $this->get(route('admin.certificates.download',$c->id))->assertNotFound();
 }
}
