<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepartureReadiness
{
    public function for(User|\App\Models\LearnerSession $user): array
    {
        $profile = LearnerIdentity::profile($user);
        $avatar = $profile?->avatar_id ? DB::table('avatars')->where('is_active', true)->whereNull('deleted_at')->find($profile->avatar_id) : null;
        $checks = [
            'account' => $user->role === 'pelajar' && $user->status === 'active' && trim($user->username) !== '',
            'profile' => $profile !== null && trim($user->display_name) !== '' && (!LearnerIdentity::isSession($user) || $user->hasStudentDetails()),
            'avatar' => $avatar !== null && ($profile->avatar_confirmed_at ?? null) !== null,
        ];
        return ['checks' => $checks, 'avatar' => $avatar, 'ready' => ! in_array(false, $checks, true), 'completed' => count(array_filter($checks))];
    }
}
