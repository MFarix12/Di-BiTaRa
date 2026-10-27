<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PassportOverview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PassportAchievementsTest extends TestCase
{
    use RefreshDatabase;

    private function journey(\App\Models\LearnerSession $user): int
    {
        $release = DB::table('learning_releases')->insertGetId(['version'=>'v-'.$user->id,'title'=>'Keluaran ujian','manifest'=>'{}','scoring_policy'=>'{}','checksum'=>str_repeat('a',64)]);
        return DB::table('student_journeys')->insertGetId(['learner_session_id'=>$user->id,'learning_release_id'=>$release]);
    }

    public function test_unified_page_and_legacy_redirects_use_the_same_destination(): void
    {
        $this->seed();
        $student = $this->makeLearner();
        $this->actingAs($student,'learner')->get('/pasport-pencapaian')->assertOk()->assertSee('Ringkasan Kemajuan Saya')->assertSee('Cop Pengembaraan Zon')->assertSee('Lencana Pencapaian Saya')->assertDontSee('Pencapaian Terkini')->assertSee('Masa Pengembaraan Saya')->assertDontSee('Bantuan Digunakan')->assertDontSee('pa-barcode',false)->assertSee('Sijil Saya')->assertSee('Juruterbang Baharu');
        foreach (['pasport'=>'pasport','kemajuan'=>'ringkasan','ganjaran'=>'lencana','sijil'=>'sijil'] as $path=>$anchor) {
            $this->get('/'.$path)->assertRedirect(route('student.achievements').'#'.$anchor);
        }
        $this->assertDatabaseCount('passport_stamps',0);
        $this->assertDatabaseCount('certificates',0);
        $this->assertSame(0,app(PassportOverview::class)->for($student)['badgeCount']);
    }

    public function test_badges_and_history_are_derived_only_from_current_students_journey(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role'=>'admin']);
        $student = $this->makeLearner();
        $other = $this->makeLearner();
        $journey = $this->journey($student);
        $otherJourney = $this->journey($other);
        foreach (DB::table('zones')->orderBy('sequence')->limit(3)->get() as $zone) {
            DB::table('passport_stamps')->insert(['student_journey_id'=>$journey,'zone_id'=>$zone->id,'title_snapshot'=>$zone->name]);
        }
        for ($i=0;$i<5;$i++) {
            $note = DB::table('learning_contents')->insertGetId(['title'=>'Nota ujian','body'=>'Isi nota','kind'=>'note','created_by'=>$admin->id,'updated_by'=>$admin->id]);
            DB::table('content_completions')->insert(['student_journey_id'=>$journey,'learning_content_id'=>$note]);
        }
        $activity = DB::table('activities')->insertGetId(['zone_id'=>DB::table('zones')->where('sequence',3)->value('id'),'title'=>'Kuiz ujian','settings'=>'{}','created_by'=>$admin->id,'updated_by'=>$admin->id]);
        foreach ([$journey,$otherJourney] as $index=>$ownerJourney) {
            DB::table('attempts')->insert(['student_journey_id'=>$ownerJourney,'activity_id'=>$activity,'attempt_no'=>1,'status'=>'completed','activity_snapshot'=>json_encode(['kind'=>'quiz','sequence'=>3,'title'=>$index ? 'Rahsia Pelajar Lain' : 'Kuiz Saya']),'max_score'=>100,'score'=>50,'passed'=>false,'completed_at'=>now()]);
        }
        $data = app(PassportOverview::class)->for($student);
        $this->assertSame(3,$data['badgeCount']);
        $this->assertSame(4,$data['level']);
        $this->assertSame('Penjelajah Bahasa',$data['rank']);
        $this->assertSame(0,$data['score']); // History does not add attempt marks to the best-score aggregate.
        $this->actingAs($student,'learner')->get('/pasport-pencapaian')->assertOk()->assertDontSee('Kuiz Saya')->assertDontSee('Rahsia Pelajar Lain');
    }

    public function test_certificate_files_require_owner_ready_status_and_private_pdf(): void
    {
        Storage::fake('private');
        $student = $this->makeLearner();
        $other = $this->makeLearner();
        $journey = $this->journey($student);
        $media = DB::table('media_files')->insertGetId(['kind'=>'document','title'=>'Sijil','disk'=>'private','path'=>'certificates/owned.pdf','original_name'=>'owned.pdf','mime_type'=>'application/pdf','size_bytes'=>15,'checksum'=>str_repeat('b',64),'status'=>'ready','created_by'=>DB::table('access_codes')->where('id',$student->access_code_id)->value('created_by')]);
        Storage::disk('private')->put('certificates/owned.pdf','%PDF-1.4 sample');
        $certificate = DB::table('certificates')->insertGetId(['student_journey_id'=>$journey,'certificate_number'=>'DBR-001','recipient_name'=>$student->display_name,'status'=>'ready','media_file_id'=>$media,'issued_at'=>now()]);
        $this->get('/sijil/'.$certificate.'/pratonton')->assertRedirect('/masukkan-kod');
        $this->actingAs($student,'learner')->get('/sijil/'.$certificate.'/pratonton')->assertOk()->assertHeader('Content-Type','application/pdf')->assertHeader('Content-Disposition','inline; filename="sijil-DBR-001.pdf"');
        $this->get('/sijil/'.$certificate.'/muat-turun')->assertOk()->assertHeader('Content-Disposition','attachment; filename="sijil-DBR-001.pdf"');
        $this->app['session']->flush();
        $this->actingAs($other,'learner')->get('/sijil/'.$certificate.'/pratonton')->assertNotFound();
        $this->app['session']->flush();
        DB::table('certificates')->where('id',$certificate)->update(['status'=>'pending']);
        $this->actingAs($student,'learner')->get('/sijil/'.$certificate.'/muat-turun')->assertNotFound();
    }
    public function test_level_is_fixed_to_session_year_without_any_progress(): void
    {
        $this->seed();
        foreach([4] as $year){
            $s=$this->makeLearner(['school_year'=>$year]);
            $this->assertSame($year,app(PassportOverview::class)->for($s)['level']);
            $this->actingAs($s,'learner')->get('/pasport-pencapaian')->assertOk()->assertSee('Tahap 4')->assertDontSee('Tahap meningkat');
        }
    }
}
