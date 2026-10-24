<?php

namespace Tests\Feature;

use App\Livewire\Tutorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class GuidedTutorialTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $status = 'pending'): \App\Models\LearnerSession
    {
        $student = $this->makeLearner(['onboarding_status'=>$status]);
        return $student;
    }

    public function test_first_login_opens_dashboard_guide_and_dismissal_stays_saved_after_next_login(): void
    {
        $this->seed();
        $student = $this->student();
        $other = $this->student();
        $this->actingAs($student,'learner');
        $this->get('/kuiz-aktiviti')->assertOk()->assertSee('data-active="1"',false)->assertSee('guided-tour-shell')->assertSee('data-tour="zones"',false);
        Livewire::actingAs($student,'learner')->test(Tutorial::class)->assertSet('active',true)->call('move',1)->assertSet('step',2)->call('move',-1)->assertSet('step',1)->call('finish',true)->assertSet('active',false)->assertNoRedirect();
        $this->assertDatabaseHas('learner_sessions',['id'=>$student->id,'onboarding_status'=>'skipped']);
        $this->assertDatabaseHas('learner_sessions',['id'=>$other->id,'onboarding_status'=>'pending','onboarding_step'=>1]);
        $this->get('/kuiz-aktiviti')->assertSee('data-active="0"',false);
    }

    public function test_completion_and_replay_keep_learning_data_and_completed_status(): void
    {
        $this->seed();
        $student = $this->student();
        $tour = Livewire::actingAs($student,'learner')->test(Tutorial::class);
        for ($i=0;$i<8;$i++) { $tour->call('move',1); }
        $tour->assertSet('step',6)->assertSee('Selesai')->call('finish')->assertSet('active',false)->assertNoRedirect();
        $completedAt = DB::table('learner_sessions')->where('id',$student->id)->value('onboarding_completed_at');
        $this->assertNotNull($completedAt);
        $this->actingAs($student,'learner')->get('/bantuan')->assertOk()->assertSee('Lihat Tutorial Semula');
        $this->get('/tutorial')->assertRedirect(route('student.profile',['tutorial'=>1]));
        $this->get('/profil-sesi?tutorial=1')->assertOk()->assertSee('Profil Anda')->assertSee('data-active="1"',false)->assertDontSee('tutorial-layout');
        Livewire::actingAs($student,'learner')->test(Tutorial::class,['replay'=>true])->assertSet('step',1)->call('move',1)->call('finish',true)->assertSet('active',false);
        $this->assertDatabaseHas('learner_sessions',['id'=>$student->id,'onboarding_status'=>'completed','onboarding_completed_at'=>$completedAt,'onboarding_step'=>6,'avatar_id'=>null]);
        $this->assertDatabaseCount('passport_stamps',0);
        $this->assertDatabaseCount('attempts',0);
        $this->get('/kuiz-aktiviti')->assertSee('data-active="0"',false);
    }

    public function test_pending_tour_resumes_and_non_students_cannot_use_it(): void
    {
        $this->seed();
        $student = $this->student();
        Livewire::actingAs($student,'learner')->test(Tutorial::class)->call('move',1)->call('move',1)->assertSet('step',3);
        Livewire::actingAs($student,'learner')->test(Tutorial::class)->assertSet('step',3)->assertSet('active',true);
        $admin = User::factory()->create(['role'=>'admin']);
        Livewire::actingAs($admin,'web')->test(Tutorial::class)->assertForbidden();
        $this->actingAs($admin,'web')->get('/tutorial')->assertRedirect('/masukkan-kod');
        $student->forceFill(['ended_at'=>now()])->save();
        Livewire::actingAs($student,'learner')->test(Tutorial::class)->assertForbidden();
    }
}

