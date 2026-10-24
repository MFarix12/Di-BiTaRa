<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Auth};
use App\Models\{User,LearnerSession};
use App\Services\StarterCurriculum;
class PersistentSessionMusicTest extends TestCase {
 use RefreshDatabase;
 public function test_music_shell_starts_only_after_details_and_survives_completion_until_session_exit(): void {
  $this->seed();$admin=User::factory()->create(['role'=>'admin']);app(StarterCurriculum::class)->install($admin->id);
  DB::table('access_codes')->insert(['code'=>'DBR-T4-MUSIC1','name'=>'Audio QA','school_year'=>4,'status'=>'active','created_by'=>$admin->id]);
  $this->withHeader('Sec-Fetch-Dest','document')->get('/masukkan-kod')->assertOk()->assertDontSee('data-session-audio-shell',false);
  $this->post(route('access.start'),['code'=>'DBR-T4-MUSIC1'])->assertRedirect(route('student.details'));
  $this->get(route('student.details'))->assertOk()->assertDontSee('data-session-audio-shell',false)->assertDontSee('data-journey-music-toggle',false);
  $this->post(route('student.details.save'),['display_name'=>'Aina','class_name'=>'4 Gemilang'])->assertRedirect();
  $this->get(route('student.activities'))->assertOk()->assertSee('data-session-audio-shell',false)->assertSee('data-session-frame',false);
  $this->withHeader('Sec-Fetch-Dest','iframe')->get(route('student.activities'))->assertOk()->assertDontSee('data-session-frame',false)->assertSee('data-journey-music-toggle',false);
  $this->withHeader('Sec-Fetch-Dest','document')->get(route('student.achievements'))->assertOk()->assertSee('data-session-frame',false);
  $s=LearnerSession::firstOrFail();DB::table('learner_sessions')->where('id',$s->id)->update(['completed_at'=>now()]);Auth::forgetGuards();
  $this->withHeader('Sec-Fetch-Dest','iframe')->get(route('student.achievements'))->assertOk()->assertSee('data-journey-music-toggle',false);
  $this->post(route('student.session.end'),['confirmed'=>1])->assertRedirect('/');
  $this->withHeader('Sec-Fetch-Dest','document')->get('/')->assertOk()->assertDontSee('data-session-audio-shell',false)->assertDontSee('data-journey-music-toggle',false);
 }
}
