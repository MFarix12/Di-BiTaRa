<?php

namespace App\Http\Controllers;

use App\Models\LearnerSession;
use App\Services\ContinuousAdventure;
use App\Services\LearnerIdentity;
use App\Services\LearningEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AccessSessionController extends Controller
{
    public function enter(Request $r)
    {
        if (Auth::guard('web')->user()?->role==='admin') { return redirect()->route('admin.dashboard'); }
        if (\App\Services\LearnerIdentity::isSession($r->user())) return redirect()->route($r->user()->hasStudentDetails()?'student.activities':'student.details');
        return view('access.enter');
    }

    public function start(Request $r, LearningEngine $engine)
    {
        if (LearnerIdentity::isSession($r->user())) { return redirect()->route($r->user()->hasStudentDetails() ? 'student.activities' : 'student.details'); }
        abort_if(Auth::guard('web')->check(),403);
        $limit='access-code:'.$r->ip();
        if (RateLimiter::tooManyAttempts($limit,20)) { throw ValidationException::withMessages(['code'=>'Terlalu banyak kod tidak sah. Cuba sebentar lagi.']); }
        $data=$r->validate(['code'=>'required|string|max:20']);
        $code=DB::table('access_codes')->where('code',Str::upper(trim($data['code'])))->first();
        $error=!$code ? 'Kod tidak sah. Sila semak semula kod yang diberikan oleh guru.' : ($code->status!=='active' ? 'Kod tidak lagi aktif. Sila dapatkan kod daripada guru.' : ($code->expires_at && now()->gte($code->expires_at) ? 'Tempoh penggunaan kod telah tamat. Sila hubungi guru.' : null));
        if ($error) { RateLimiter::hit($limit,60); throw ValidationException::withMessages(['code'=>$error]); }
        if (!DB::table('learning_releases')->where('status','published')->exists()) { throw ValidationException::withMessages(['code'=>'Misi sedang disediakan oleh guru. Cuba lagi sebentar nanti.']); }
        RateLimiter::clear($limit);
        [$session,$token]=DB::transaction(function () use ($code,$engine) {
            $fresh=DB::table('access_codes')->where('id',$code->id)->lockForUpdate()->first();
            abort_unless($fresh->status==='active' && (!$fresh->expires_at || now()->lt($fresh->expires_at)),422,'Kod tidak lagi tersedia.');
            $release=DB::table('learning_releases')->where('school_year',$fresh->school_year)->where('status','published')->when($fresh->learning_release_id,fn($q)=>$q->where('id',$fresh->learning_release_id))->latest('id')->first();
            if(!$release) throw ValidationException::withMessages(['code'=>'Set soalan tahun ini belum tersedia. Sila hubungi guru.']);
            if(!$fresh->learning_release_id) { DB::table('access_codes')->where('id',$fresh->id)->update(['learning_release_id'=>$release->id]);$fresh->learning_release_id=$release->id; }
            $token=bin2hex(random_bytes(32));
            $s=LearnerSession::create(['access_code_id'=>$fresh->id,'school_year'=>$fresh->school_year,'learning_release_id'=>$fresh->learning_release_id,'public_id'=>(string)Str::uuid(),'token_hash'=>hash('sha256',$token)]);
            $engine->journey($s);return [$s,$token];
        });
        $r->session()->regenerate();$r->session()->put('learner_token',$token);
        return redirect()->route('student.details')->cookie('dibitara_resume',$token,43200,'/',null,$r->isSecure(),true,false,'lax');
    }

    public function details(Request $r)
    {
        if ($r->user()->hasStudentDetails()) { return redirect()->route('student.activities',['zon'=>1]); }
        return view('access.details');
    }

    public function saveDetails(Request $r)
    {
        if ($r->user()->hasStudentDetails()) { return redirect()->route('student.activities'); }
        $data=$r->validate([
            'display_name'=>['required','string','max:100'],
            'class_name'=>['required','string','max:50'],
        ],[
            'display_name.required'=>'Masukkan nama anda.',
            'display_name.max'=>'Nama mestilah tidak melebihi 100 aksara.',
            'class_name.required'=>'Masukkan kelas anda.',
            'class_name.max'=>'Kelas mestilah tidak melebihi 50 aksara.',
        ]);
        $r->user()->update(['display_name'=>trim($data['display_name']),'class_name'=>trim($data['class_name'])]);
        return redirect()->route('student.activities',['zon'=>1]);
    }

    public function end(Request $r, LearningEngine $engine, ContinuousAdventure $flow)
    {
        $r->validate(['confirmed'=>'accepted']);$s=$r->user();abort_unless(LearnerIdentity::isSession($s),403);
        DB::transaction(function () use ($s,$engine,$flow) {
            $j=$engine->journey($s);$clock=$flow->touch($j);
            if ($clock && !$clock->finished_at) { DB::table('journey_clocks')->where('id',$clock->id)->update(['finished_at'=>now(),'last_seen_at'=>null]); }
            if ($j->status!=='completed') { DB::table('student_journeys')->where('id',$j->id)->update(['status'=>'archived']); }
            DB::table('learner_sessions')->where('id',$s->id)->update(['ended_at'=>now(),'updated_at'=>now()]);
        });
        $r->session()->forget('learner_token');$r->session()->regenerate();Auth::forgetGuards();
        return redirect()->route('home')->with('status','Sesi telah tamat. Terima kasih, juruterbang!')->withoutCookie('dibitara_resume');
    }
}
