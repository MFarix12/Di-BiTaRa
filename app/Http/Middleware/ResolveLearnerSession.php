<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResolveLearnerSession
{
    public function handle(Request $r, Closure $next)
    {
        if (Auth::guard('web')->user()?->role==='pelajar') { Auth::guard('web')->logout(); }
        if (!$r->session()->has('learner_token') && !Auth::guard('web')->check()) {
            $resume=$r->cookie('dibitara_resume');
            if(is_string($resume) && strlen($resume)===64 && \App\Models\LearnerSession::where('token_hash',hash('sha256',$resume))->whereNull('ended_at')->exists()) {
                $r->session()->regenerate();$r->session()->put('learner_token',$resume);
                $r->session()->flash('status','Selamat kembali! Jom sambung misi anda.');
            }
        }
        if (!$r->is('admin*','log-masuk-admin') && $r->session()->has('learner_token') && !Auth::guard('web')->check()) { Auth::shouldUse('learner'); }
        if($r->session()->has('learner_token') && !$r->cookie('dibitara_resume') && $r->user() instanceof \App\Models\LearnerSession){
            \Illuminate\Support\Facades\Cookie::queue(cookie('dibitara_resume',$r->session()->get('learner_token'),43200,'/',null,$r->isSecure(),true,false,'lax'));
        }
        return $next($r);
    }
}
