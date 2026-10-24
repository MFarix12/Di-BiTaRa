<?php

namespace App\Services;

use App\Models\LearnerSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LearnerIdentity
{
    public static function isSession(?object $user): bool { return $user instanceof LearnerSession; }
    public static function key(object $user): string { return self::isSession($user) ? 'learner_session_id' : 'user_id'; }
    public static function journeys(object $user) { return DB::table('student_journeys')->where(self::key($user),$user->id); }
    public static function notifications(object $user) { return DB::table('student_notifications')->where(self::key($user),$user->id); }
    public static function profile(object $user): ?object { return self::isSession($user) ? $user : DB::table('student_profiles')->where('user_id',$user->id)->first(); }
    public static function owner(object $journey): User|LearnerSession { return $journey->learner_session_id ? LearnerSession::findOrFail($journey->learner_session_id) : User::findOrFail($journey->user_id); }
    public static function updateProfile(object $user,array $values): void
    {
        if (self::isSession($user)) { DB::table('learner_sessions')->where('id',$user->id)->update($values+['updated_at'=>now()]);$user->refresh(); }
        else { DB::table('student_profiles')->where('user_id',$user->id)->update($values+['updated_at'=>now()]); }
    }
}
