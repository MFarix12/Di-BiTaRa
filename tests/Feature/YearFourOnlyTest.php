<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use App\Services\{StarterCurriculum,RetireOtherYears};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Storage};

class YearFourOnlyTest extends TestCase
{
    use RefreshDatabase;
    public function test_codes_and_publication_need_no_year_selection_and_reject_other_years(): void
    {
        $this->seed();Storage::fake('private');$admin=User::factory()->create(['role'=>'admin']);
        app(StarterCurriculum::class)->install($admin->id);$this->actingAs($admin);
        $this->post(route('admin.codes.store'),['name'=>'Misi Bahasa','status'=>'active'])->assertRedirect()->assertSessionHasNoErrors();
        $code=DB::table('access_codes')->latest('id')->first();
        $this->assertSame(4,$code->school_year);$this->assertStringStartsWith('DBR-T4-',$code->code);
        foreach([5,6] as $year)$this->post(route('admin.codes.store'),['name'=>'Tidak sah','status'=>'active','school_year'=>$year])->assertSessionHasErrors('school_year');
        $this->get(route('admin.codes'))->assertOk()->assertDontSee('name="school_year"',false);
        $this->get(route('admin.sessions'))->assertOk()->assertDontSee('name="year"',false);
        $this->post(route('admin.publish'))->assertRedirect()->assertSessionHasNoErrors();
    }
    public function test_database_disallows_other_years_even_outside_http(): void
    {
        $this->seed();$admin=User::factory()->create(['role'=>'admin']);
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('access_codes')->insert(['code'=>'DBR-T5-BLOCK','name'=>'Blocked','school_year'=>5,'created_by'=>$admin->id]);
    }
    public function test_retirement_removes_related_records_without_touching_year_four(): void
    {
        $this->seed();$admin=User::factory()->create(['role'=>'admin']);
        DB::unprepared('DROP TRIGGER access_codes_year_four_INSERT');
        DB::unprepared('DROP TRIGGER learner_sessions_year_four_INSERT');
        $keep=DB::table('access_codes')->insertGetId(['code'=>'DBR-T4-KEEP','name'=>'Keep','created_by'=>$admin->id]);
        $old=DB::table('access_codes')->insertGetId(['code'=>'DBR-T6-OLD','name'=>'Old','school_year'=>6,'created_by'=>$admin->id]);
        DB::table('learner_sessions')->insert(['access_code_id'=>$old,'school_year'=>6,'public_id'=>(string)\Illuminate\Support\Str::uuid(),'token_hash'=>hash('sha256','old')]);
        app(RetireOtherYears::class)->run();
        $this->assertDatabaseHas('access_codes',['id'=>$keep]);$this->assertDatabaseMissing('access_codes',['id'=>$old]);
        $this->assertSame(0,DB::table('learner_sessions')->where('school_year','<>',4)->count());
        $this->assertSame([],DB::select('PRAGMA foreign_key_check'));
    }
}
