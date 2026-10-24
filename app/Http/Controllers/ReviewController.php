<?php

namespace App\Http\Controllers;

use App\Services\LearningEngine;
use App\Services\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    private function journey(Request $r, LearningEngine $engine): object
    {
        $j=$engine->journey($r->user()); abort_unless($j && $j->status==='completed',403,'Lengkapkan pengembaraan dahulu.'); return $j;
    }

    public function index(Request $r, LearningEngine $engine)
    {
        $j=$this->journey($r,$engine);
        return view('student.review',['zones'=>$engine->manifest($j)['zones']]);
    }

    public function activity(Request $r, int $activity, LearningEngine $engine, QuestionBank $bank)
    {
        $j=$this->journey($r,$engine); $snapshot=null;
        foreach ($engine->manifest($j)['zones'] as $zone) { foreach ($zone['activities'] as $a) { if ($a['id']===$activity) { $snapshot=$a+['sequence'=>$zone['sequence']]; } } }
        abort_unless($snapshot,404);
        $position=max(0,min(count($snapshot['questions'])-1,(int)$r->query('soalan',0)));
        $raw=$snapshot['questions'][$position] ?? null; $feedback=null;
        if ($r->isMethod('post') && $raw) {
            if ($bank->grade($raw,$r->input('answer'))) {
                $next=$position+1<count($snapshot['questions']) ? route('student.review.activity',['activity'=>$activity,'soalan'=>$position+1]) : route('student.review');
                if ($meaning=\App\Services\SentenceMeaning::for($raw)) return view('student.review-meaning',compact('meaning','next'));
                return redirect($next);
            }
            $feedback='Jawapan kurang tepat, sila cuba lagi.';
        }
        $question=$raw ? $bank->publicQuestion($raw) : null;
        return view('student.mission',['activity'=>$snapshot,'question'=>$question,'preview'=>true,'position'=>$position,'feedback'=>$feedback,'previewId'=>$activity,'reviewMode'=>true]);
    }

    public function note(Request $r, int $note, LearningEngine $engine)
    {
        $j=$this->journey($r,$engine);
        foreach ($engine->manifest($j)['zones'] as $zone) { foreach ($zone['notes'] as $n) { if ($n['id']===$note) { return view('student.note',['zone'=>$zone,'content'=>$n,'reviewMode'=>true]); } } }
        abort(404);
    }

    public function audio(Request $r, int $media, LearningEngine $engine)
    {
        $j=$this->journey($r,$engine); $ids=[];
        foreach ($engine->manifest($j)['zones'] as $z) {
            foreach ($z['notes'] as $n) { $ids=array_merge($ids,$n['media_ids'] ?? []); }
            foreach ($z['activities'] as $a) { foreach ($a['questions'] as $q) { $ids[]=$q['audio_id'] ?? null; } }
        }
        abort_unless(in_array($media,$ids,true),404);
        $m=\Illuminate\Support\Facades\DB::table('media_files')->where('status','ready')->find($media);
        abort_unless($m && Storage::disk($m->disk)->exists($m->path),404);
        return response()->file(Storage::disk($m->disk)->path($m->path),['Content-Type'=>$m->mime_type,'Cache-Control'=>'private, no-store']);
    }
}
