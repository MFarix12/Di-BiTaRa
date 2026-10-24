<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentOverview
{
    public function for(User|\App\Models\LearnerSession $user): array
    {
        app(LearningEngine::class)->journey($user);
        $journey = LearnerIdentity::journeys($user)
            ->whereIn('status', ['active', 'completed'])->latest('id')->first();
        $progress = $journey ? DB::table('student_progress')->where('student_journey_id', $journey->id)->get()->keyBy('zone_id') : collect();
        $stamps = $journey ? DB::table('passport_stamps')->where('student_journey_id', $journey->id)->get()->keyBy('zone_id') : collect();
        $profile = LearnerIdentity::profile($user);
        return [
            'journey' => $journey,
            'profile' => $profile,
            'avatar' => $profile?->avatar_id ? DB::table('avatars')->find($profile->avatar_id) : null,
            'zones' => DB::table('zones')->orderBy('sequence')->get(),
            'progress' => $progress,
            'stamps' => $stamps,
            'percentage' => round($progress->sum('progress_percent') / 5),
            'score' => $progress->sum('best_score'),
            'certificateCount' => DB::table('certificates')->whereIn('student_journey_id',LearnerIdentity::journeys($user)->select('id'))->where('status','ready')->count(),
        ];
    }
}
