<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LearnerIdentity;

class RequireLearnerSession
{
    public function handle(Request $r,Closure $next)
    {
        if (!LearnerIdentity::isSession($r->user()) || $r->user()->ended_at) { $r->session()->flash('status','Masukkan kod untuk memulakan sesi.'); return new \Illuminate\Http\RedirectResponse(route('access.enter')); }
        if (!$r->user()->hasStudentDetails() && !$r->routeIs('student.details','student.details.save','student.session.end')) {
            return redirect()->route('student.details');
        }
        return $next($r);
    }
}
