<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LearnerIdentity;

class PersistentLearnerShell
{
    public function handle(Request $request, Closure $next)
    {
        $learner=$request->user();
        if($request->isMethod('GET') && $request->header('Sec-Fetch-Dest')==='document'
            && LearnerIdentity::isSession($learner) && !$learner->ended_at && $learner->hasStudentDetails()
            && $request->routeIs('student.activities','student.profile','student.achievements','student.zone.launch','student.transition','student.note','student.attempt','student.review','student.review.activity','student.review.note')){
            // The inner request runs the original controller and all progress/session guards.
            $request->session()->reflash();
            return response()->view('student.session-shell',['learner'=>$learner,'contentUrl'=>$request->fullUrl()]);
        }
        return $next($request);
    }
}
