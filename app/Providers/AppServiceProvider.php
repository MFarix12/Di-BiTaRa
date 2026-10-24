<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Auth::viaRequest('access-session', function ($request) {
            $token=$request->session()->get('learner_token');
            if (!is_string($token) || strlen($token)!==64) { return null; }
            return \App\Models\LearnerSession::where('token_hash',hash('sha256',$token))->whereNull('ended_at')->first();
        });
    }
}
