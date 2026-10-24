<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Locked;

class Tutorial extends Component
{
    #[Locked] public int $step = 1;
    #[Locked] public bool $active = false;
    #[Locked] public bool $replay = false;

    private function guard(): void
    {
        abort_unless(auth()->user()?->role === 'pelajar' && auth()->user()?->status === 'active', 403);
    }

    public function mount(bool $replay = false): void
    {
        $this->guard();
        if (!\App\Services\LearnerIdentity::isSession(auth()->user())) { DB::table('student_profiles')->insertOrIgnore(['user_id'=>auth()->id()]); }
        $profile = \App\Services\LearnerIdentity::profile(auth()->user());
        $this->replay = $replay;
        $this->active = $replay || $profile->onboarding_status === 'pending';
        $this->step = $replay ? 1 : max(1,min(6,(int) $profile->onboarding_step));
    }

    public function move(int $direction): void
    {
        $this->guard();
        if (! $this->active) { return; }
        $this->step = max(1, min(6, $this->step + ($direction > 0 ? 1 : -1)));
        if (! $this->replay) {
            \App\Services\LearnerIdentity::updateProfile(auth()->user(),['onboarding_step' => $this->step, 'updated_at' => now()]);
        }
    }

    public function finish(bool $skip = false)
    {
        $this->guard();
        if (! $this->active) { return; }
        $profile = \App\Services\LearnerIdentity::profile(auth()->user());
        if (! ($skip && $profile?->onboarding_status === 'completed')) {
            \App\Services\LearnerIdentity::updateProfile(auth()->user(),['onboarding_status' => $skip ? 'skipped' : 'completed', 'onboarding_step'=>$this->step, 'onboarding_completed_at' => $skip ? null : now(), 'updated_at' => now()]);
        }
        $this->active = false;
    }

    public function render() { return view('livewire.tutorial'); }
}
