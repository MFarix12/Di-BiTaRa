<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LearnerIdentity;

class RequireMediaIdentity
{
    public function handle(Request $r,Closure $next)
    {
        abort_unless($r->user()?->status==='active' && (LearnerIdentity::isSession($r->user()) || $r->user()?->role==='admin'),403);
        return $next($r);
    }
}
