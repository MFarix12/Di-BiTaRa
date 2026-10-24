<?php
namespace Tests\Feature;

use App\Models\User;
use App\Services\SessionResults;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionResultsExportTest extends TestCase
{
    use RefreshDatabase;
    private User $admin;
    protected function setUp(): void
    {
        parent::setUp();$this->seed();$this->admin=User::factory()->create(['role'=>'admin']);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-23 10:00:00','UTC'));
    }
    private function part(string $zip,string $wanted): string
    {
        $offset=0;
        while(substr($zip,$offset,4)==="PK\x03\x04"){
            $h=unpack('vversion/vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vsize/vname/vextra',substr($zip,$offset+4,26));
            $name=substr($zip,$offset+30,$h['name']);$begin=$offset+30+$h['name']+$h['extra'];
            $data=substr($zip,$begin,$h['compressed']);
            if($name===$wanted)return $h['method']===8?gzinflate($data):$data;
            $offset=$begin+$h['compressed'];
        }
        $this->fail('Bahagian ZIP tiada: '.$wanted);
    }
    public function test_filters_and_exports_share_real_data_without_page_limit_or_mutation(): void
    {
        $target=$this->makeLearner(['display_name'=>'Aina & <Murid>','class_name'=>'4 Cemerlang','created_at'=>'2026-10-22 16:00:00','completed_at'=>'2026-10-23 09:00:00']);
        $this->makeLearner(['display_name'=>'TIDAK TERMASUK','class_name'=>'5 Maju','created_at'=>'2026-10-22 15:59:59']);
        for($i=0;$i<24;$i++)$this->makeLearner(['display_name'=>'Murid '.$i,'class_name'=>'4 Cemerlang','created_at'=>'2026-10-23 01:00:00']);
        $before=DB::table('learner_sessions')->get()->toJson();
        $this->actingAs($this->admin,'web');
        $this->get(route('admin.sessions',['class'=>'4 Cemerlang']))->assertOk()->assertSee('25 sesi')->assertSee('Muat Turun Excel')->assertDontSee('TIDAK TERMASUK');
        $response=$this->get(route('admin.sessions.export',['format'=>'xlsx','class'=>'4 Cemerlang','page'=>2]))->assertOk()->assertDownload('Keputusan_Sesi_Di-BITaRa_2026-10-23.xlsx');
        $zip=$response->getContent();$this->assertStringStartsWith("PK\x03\x04",$zip);
        $sheet=$this->part($zip,'xl/worksheets/sheet1.xml');$dom=new \DOMDocument;$this->assertTrue($dom->loadXML($sheet));
        $this->assertStringContainsString('Aina &amp; &lt;Murid&gt;',$sheet);$this->assertStringNotContainsString('TIDAK TERMASUK',$sheet);
        $this->assertStringContainsString('A4:O29',$sheet);$this->assertStringContainsString('state="frozen"',$sheet);$this->assertStringNotContainsString('<f>',$sheet);
        $this->assertTrue($dom->loadXML($this->part($zip,'xl/styles.xml')));
        $filters=['format'=>'xlsx','code'=>$target->access_code_id,'name'=>'Aina','class'=>'4 Cemerlang','from'=>'2026-10-23','to'=>'2026-10-23','status'=>'completed'];
        $filtered=$this->get(route('admin.sessions.export',$filters))->assertOk();$xml=$this->part($filtered->getContent(),'xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('A4:O5',$xml);$this->assertStringNotContainsString('Murid 23',$xml);
        $this->get(route('admin.sessions',['to'=>'2026-10-22']))->assertOk()->assertSee('1 sesi')->assertSee('TIDAK TERMASUK');
        $pdf=$this->get(route('admin.sessions.export',array_merge($filters,['format'=>'pdf'])))->assertOk()->assertDownload('Keputusan_Sesi_Di-BITaRa_2026-10-23.pdf');
        $this->assertStringStartsWith('%PDF-',$pdf->getContent());$this->assertStringContainsString('private', $pdf->headers->get('Cache-Control'));
        $this->assertSame($before,DB::table('learner_sessions')->get()->toJson());
    }
    public function test_export_is_admin_only_validates_filters_and_empty_files_are_valid(): void
    {
        $this->get(route('admin.sessions.export',['format'=>'xlsx']))->assertRedirect('/log-masuk-admin');
        $s=$this->makeLearner();$this->actingAs($s,'learner')->get(route('admin.sessions.export',['format'=>'pdf']))->assertRedirect('/log-masuk-admin');
        $this->actingAs($this->admin,'web');
        $this->getJson(route('admin.sessions.export',['format'=>'xlsx','from'=>'2026-10-25','to'=>'2026-10-23']))->assertUnprocessable();
        $this->getJson(route('admin.sessions.export',['format'=>'pdf','status'=>'secret']))->assertUnprocessable();
        $this->get('/admin/sesi/muat-turun/csv')->assertNotFound();
        $r=$this->get(route('admin.sessions.export',['format'=>'xlsx','name'=>'TIADA']))->assertOk();
        $this->assertStringContainsString('A4:O4',$this->part($r->getContent(),'xl/worksheets/sheet1.xml'));
        $this->get(route('admin.sessions.export',['format'=>'pdf','name'=>'TIADA']))->assertOk();
    }
    public function test_row_values_duration_status_and_excel_formula_safety(): void
    {
        $s=$this->makeLearner(['display_name'=>'=HYPERLINK("https://example.invalid")','class_name'=>'4 Cemerlang','completed_at'=>'2026-10-23 09:00:00','ended_at'=>'2026-10-23 09:30:00']);
        $release=DB::table('learning_releases')->insertGetId(['version'=>'export-test','title'=>'Ujian','manifest'=>'{}','scoring_policy'=>'{}','checksum'=>str_repeat('a',64)]);
        $j=DB::table('student_journeys')->insertGetId(['learner_session_id'=>$s->id,'learning_release_id'=>$release]);
        DB::table('journey_clocks')->insert(['student_journey_id'=>$j,'started_at'=>'2026-10-23 08:00:00','finished_at'=>'2026-10-23 08:13:10','elapsed_ms'=>790000]);
        $zone=DB::table('zones')->where('sequence',3)->value('id');
        DB::table('student_progress')->insert(['student_journey_id'=>$j,'zone_id'=>$zone,'status'=>'completed','best_score'=>60,'required_units'=>1,'completed_units'=>1]);
        $report=app(SessionResults::class);$rows=$report->rows($report->query([])->get(),now());
        $this->assertSame('Zon 3',$rows[0][7]);$this->assertSame(60,$rows[0][8]);$this->assertSame('00:13:10',$rows[0][10]);$this->assertSame('Selesai',$rows[0][11]);$this->assertSame('23/10/2026 17:00:00',$rows[0][13]);
        $this->actingAs($this->admin,'web');$r=$this->get(route('admin.sessions.export',['format'=>'xlsx']))->assertOk();$sheet=$this->part($r->getContent(),'xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('=HYPERLINK',$sheet);$this->assertStringNotContainsString('<f>',$sheet);$this->assertStringContainsString('s="4" t="n"',$sheet);
    }
}
