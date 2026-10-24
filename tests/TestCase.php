<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function makeLearner(array $values=[]): \App\Models\LearnerSession
    {
        $admin=\App\Models\User::where('role','admin')->first() ?? \App\Models\User::factory()->create(['role'=>'admin']);
        $code=\Illuminate\Support\Facades\DB::table('access_codes')->insertGetId(['code'=>'DBR-'.strtoupper(\Illuminate\Support\Str::random(8)),'name'=>'Sesi Ujian','status'=>'active','created_by'=>$admin->id,'created_at'=>now(),'updated_at'=>now()]);
        unset($values['username'],$values['password'],$values['role']);
        return \App\Models\LearnerSession::create($values+['public_id'=>(string)\Illuminate\Support\Str::uuid(),'token_hash'=>hash('sha256',random_bytes(32)),'access_code_id'=>$code,'display_name'=>'Murid Bahasa','class_name'=>'4 Cemerlang']);
    }
}
