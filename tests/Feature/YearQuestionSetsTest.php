<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\LearnerSession;
use App\Services\{StarterCurriculum,YearCurriculum,LearningEngine,Publication,QuestionBank,SentenceMeaning,SessionResults};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Storage,Auth};
use Tests\TestCase;
class YearQuestionSetsTest extends TestCase
{
 use RefreshDatabase;
 private User $admin;
 protected function setUp(): void {
  parent::setUp();$this->seed();Storage::fake('private');
  $this->admin=User::factory()->create(['role'=>'admin']);
  app(StarterCurriculum::class)->install($this->admin->id);
  foreach([5,6] as $year)app(YearCurriculum::class)->install($this->admin->id,$year);
 }
 public function test_each_set_uses_exact_pdf_answers_and_distinct_questions_and_audio(): void {
  $ids=[];
  foreach([4,5,6] as $year){
   $release=DB::table('learning_releases')->where('school_year',$year)->latest('id')->first();
   $manifest=json_decode($release->manifest,true);$questions=collect($manifest['zones'])->flatMap(fn($z)=>collect($z['activities'])->flatMap(fn($a)=>$a['questions']));
   foreach($questions->where('type','sentence_order') as $q){$fixed=\App\Services\SentencePunctuation::normalize($q);$order=$fixed['answer']['value'];$this->assertStringEndsWith('.',$fixed['config']['items'][end($order)]);$this->assertSame($fixed,\App\Services\SentencePunctuation::normalize($fixed));}
   $this->assertCount(26,$questions);$this->assertSame(260,$questions->sum('max_marks'));
   $this->assertSame([12,1,5,5,3],array_map(fn($t)=>$questions->where('type',$t)->count(),['true_false','drag_drop','sentence_order','fill_blank','dictation']));
   foreach($questions as $q){$this->assertTrue(app(QuestionBank::class)->grade($q,$q['answer']['value']));$this->assertNotContains($q['id'],$ids);$ids[]=$q['id'];}
   if($year>4){
    $source=json_decode(file_get_contents(resource_path('curriculum/tahun-'.$year.'.json')),true);
    $this->assertSame($source['imlak'],$questions->where('type','dictation')->pluck('answer.value')->values()->all());
    $orders=$questions->where('type','sentence_order')->values();
    foreach($orders as $i=>$q){$this->assertSame($source['order'][$i][2],$q['answer']['sentence']);$this->assertSame($i===4,SentenceMeaning::for($q)!==null);}
    foreach($questions->where('type','dictation') as $q){$media=DB::table('media_files')->find($q['audio_id']);$this->assertStringStartsWith('tahun-'.$year.'/',$media->path);$this->assertGreaterThan(1000,Storage::disk('private')->size($media->path));}
    $this->assertSame($release->id,app(YearCurriculum::class)->install($this->admin->id,$year));
   }
  }
 }
 public function test_code_year_and_release_are_locked_and_student_cannot_override_them(): void {
  $this->actingAs($this->admin,'web');
  $this->post(route('admin.codes.store'),['name'=>'Tiada tahun','status'=>'active'])->assertSessionHasErrors('school_year');
  $this->post(route('admin.codes.store'),['name'=>'Tahun tidak sah','status'=>'active','school_year'=>3])->assertSessionHasErrors('school_year');
  $this->post(route('admin.codes.store'),['name'=>'Kelas Lima','status'=>'active','school_year'=>5])->assertRedirect();
  $code=DB::table('access_codes')->latest('id')->first();$this->assertSame(5,$code->school_year);$this->assertStringStartsWith('DBR-T5-',$code->code);
  $this->put(route('admin.codes.update',$code->id),['name'=>'Ubah','status'=>'active','school_year'=>6])->assertSessionHasErrors('school_year');
  $this->assertDatabaseHas('access_codes',['id'=>$code->id,'school_year'=>5,'learning_release_id'=>$code->learning_release_id]);
  Auth::guard('web')->logout();Auth::forgetGuards();
  $this->post(route('access.start'),['code'=>$code->code,'school_year'=>6,'learning_release_id'=>1])->assertRedirect(route('student.details'));
  $s=LearnerSession::firstOrFail();$j=app(LearningEngine::class)->journey($s);$this->assertSame(5,$s->school_year);$this->assertSame($code->learning_release_id,$j->learning_release_id);
  $this->post(route('student.details.save'),['display_name'=>'Aina','class_name'=>'5 Gemilang','school_year'=>6])->assertRedirect();
  $this->assertSame(5,$s->fresh()->school_year);
  $before=app(LearningEngine::class)->manifest($j);
  $new=app(Publication::class)->publish($this->admin->id,6);$this->assertNotEquals($new,$j->learning_release_id);
  $this->assertSame($before,app(LearningEngine::class)->manifest(app(LearningEngine::class)->journey($s)));
  $first=$before['zones'][2]['activities'][0]['questions'][0];$this->assertStringContainsString('disusun',$first['prompt']);
  $foreign=json_decode(DB::table('learning_releases')->find($new)->manifest,true)['zones'][2]['activities'][0];
  try{app(LearningEngine::class)->begin($s,$foreign['id']);$this->fail('Foreign activity must be rejected');}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertContains($e->getStatusCode(),[403,404,409]);}
 }
 public function test_year_filter_is_shared_by_results_and_downloads(): void {
  foreach([4,5,6] as $year)$this->makeLearner(['display_name'=>'Murid Tahun '.$year,'school_year'=>$year]);
  $this->actingAs($this->admin,'web');
  $this->get(route('admin.sessions',['year'=>5]))->assertOk()->assertSee('1 sesi')->assertSee('Murid Tahun 5')->assertDontSee('Murid Tahun 4')->assertDontSee('Murid Tahun 6');
  $this->assertCount(1,app(SessionResults::class)->query(['year'=>5])->get());
  foreach(['xlsx','pdf'] as $format)$this->get(route('admin.sessions.export',['year'=>5,'format'=>$format]))->assertOk()->assertDownload();
  $this->getJson(route('admin.sessions',['year'=>7]))->assertUnprocessable();
 }

 public static function years(): array { return [[5],[6]]; }
 #[\PHPUnit\Framework\Attributes\DataProvider('years')]
 public function test_new_year_completes_all_zones_with_its_own_set(int $year): void {
  app(\App\Services\AvatarCatalogue::class)->install($this->admin->id);
  $release=DB::table('learning_releases')->where('school_year',$year)->latest('id')->value('id');
  DB::table('access_codes')->insert(['code'=>'DBR-T'.$year.'-QA1234','name'=>'Kelas Ujian','school_year'=>$year,'learning_release_id'=>$release,'status'=>'active','created_by'=>$this->admin->id]);
  $this->post(route('access.start'),['code'=>'DBR-T'.$year.'-QA1234'])->assertRedirect(route('student.details'));
  $this->post(route('student.details.save'),['display_name'=>'Murid PDF','class_name'=>$year.' Gemilang'])->assertRedirect();
  $s=LearnerSession::firstOrFail();$this->actingAs($s,'learner');$engine=app(LearningEngine::class);$j=$engine->journey($s);
  \Livewire\Livewire::test(\App\Livewire\DeparturePreparation::class)->set('avatar_id',DB::table('avatars')->value('id'))->call('confirm')->assertRedirect(route('student.transition',1));
  $this->post(route('student.note.read',['sequence'=>2,'note'=>$engine->zone($j,2)['notes'][0]['id']]),['confirmed'=>1])->assertRedirect();
  foreach([3,4,5] as $sequence)foreach($engine->zone($j,$sequence)['activities'] as $activity){
   $launch=$this->post(route('student.zone.prepare',$sequence),['target'=>$activity['id']]);
   if($sequence<5){$launch->assertOk();$this->travel(3)->seconds();$launch=$this->post(route('student.zone.start',$sequence),['target'=>$activity['id']]);}
   $launch->assertRedirect();$attempt=(int)basename($launch->headers->get('Location'));
   foreach(DB::table('attempt_questions')->where('attempt_id',$attempt)->orderBy('position')->get() as $q){
    $raw=json_decode($q->grading_snapshot,true);
    $this->post(route('student.answer',$attempt),['question_id'=>$q->id,'answer'=>$raw['answer']['value']])->assertRedirect();
    if(SentenceMeaning::for($raw))$this->post(route('student.meaning',$attempt),['question_id'=>$q->id,'read'=>1])->assertRedirect();
   }
  }
  $this->post('/refleksi',['emotion'=>'sangat_seronok'])->assertRedirect(route('student.achievements'));
  $this->assertDatabaseCount('passport_stamps',5);$this->assertDatabaseCount('certificates',1);$this->assertDatabaseCount('journey_clocks',1);
  $this->assertSame(260,DB::table('responses')->sum('awarded_marks'));
  $this->assertSame($year,app(\App\Services\PassportOverview::class)->for($s)['level']);
  $this->assertDatabaseHas('learner_sessions',['id'=>$s->id,'school_year'=>$year,'learning_release_id'=>$release]);
  $this->assertDatabaseHas('student_journeys',['id'=>$j->id,'status'=>'completed','learning_release_id'=>$release]);
 }
}
