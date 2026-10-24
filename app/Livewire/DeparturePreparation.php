<?php

namespace App\Livewire;

use App\Services\AvatarCatalogue;
use App\Services\DepartureReadiness;
use App\Services\LearningEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DeparturePreparation extends Component
{
    public ?int $avatar_id = null;

    private function guard(): void
    {
        if (\App\Services\LearnerIdentity::isSession(auth()->user())) { abort_unless(auth()->user()->hasStudentDetails(),403); }
        abort_unless(auth()->user()?->role === 'pelajar' && auth()->user()?->status === 'active', 403);
    }

    public function mount(): void
    {
        $this->guard();
        $this->avatar_id = \App\Services\LearnerIdentity::profile(auth()->user())?->avatar_id;
    }

    public function confirm()
    {
        $this->guard();
        $existingJourney=app(LearningEngine::class)->journey(auth()->user());
        if ($existingJourney) { app(\App\Services\ContinuousAdventure::class)->assertCurrent($existingJourney,app(LearningEngine::class)->zone($existingJourney,1)['id']); }
        $data = $this->validate([
            'avatar_id'=>['required','integer',Rule::exists('avatars','id')->where('is_active',true)->whereNull('deleted_at')],
        ],['avatar_id.required'=>'Pilih satu avatar dahulu.','avatar_id.exists'=>'Avatar ini tidak tersedia. Pilih avatar lain.']);
        DB::transaction(function () use ($data) {
            $user = auth()->user();
            DB::table(\App\Services\LearnerIdentity::isSession($user)?'learner_sessions':'users')->where('id',$user->id)->lockForUpdate()->first();
            \App\Services\LearnerIdentity::updateProfile($user,[
                'avatar_id'=>$data['avatar_id'],'avatar_confirmed_at'=>now(),'updated_at'=>now(),
            ]);
            $engine = app(LearningEngine::class);
            $journey = $engine->journey($user);
            if ($journey) {
                DB::table('student_journeys')->where('id',$journey->id)->lockForUpdate()->first();
                $engine->refresh($journey);
            }
        });
        session()->flash('status','Avatar anda telah dipilih! Anda kini bersedia untuk memulakan pengembaraan.');
        $j=app(LearningEngine::class)->journey(auth()->user());
        return app(\App\Services\ContinuousAdventure::class)->enabled($j) ? redirect()->route('student.transition',1) : redirect()->route('student.activities',['zon'=>1]);
    }

    public function render()
    {
        $avatars = DB::table('avatars')->where('is_active',true)->whereNull('deleted_at')->orderBy('sort_order')->get();
        return view('livewire.departure-preparation',[
            'avatars'=>$avatars,
            'roles'=>$avatars->map(fn ($avatar)=>AvatarCatalogue::details($avatar)['role'])->unique()->sort()->values(),
            'preview'=>$avatars->firstWhere('id',$this->avatar_id),
            'departure'=>app(DepartureReadiness::class)->for(auth()->user()),
            'stampEarned'=>DB::table('passport_stamps')->join('student_journeys','student_journeys.id','=','passport_stamps.student_journey_id')
                ->join('zones','zones.id','=','passport_stamps.zone_id')->where('student_journeys.'.\App\Services\LearnerIdentity::key(auth()->user()),auth()->id())->where('zones.sequence',1)->exists(),
        ]);
    }
}
