<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionResults
{
    public const HEADERS=['Bil.','Kod Pengembaraan','Kod Sesi','Nama Pelajar','Kelas','Tahun / Darjah','Jawatan Avatar','Zon Selesai','Markah Keseluruhan','Betul Cubaan Pertama','Masa Keseluruhan Pengembaraan','Status Sesi','Tarikh dan Masa Mula','Tarikh dan Masa Tamat','Ranking Dalam Kod'];

    public function filters(Request $r): array
    {
        return array_filter($r->validate([
            'code'=>'nullable|integer|exists:access_codes,id','name'=>'nullable|string|max:100',
            'class'=>'nullable|string|max:50','from'=>'nullable|date_format:Y-m-d','to'=>array_filter(['nullable','date_format:Y-m-d',$r->filled('from')?'after_or_equal:from':null]),
            'status'=>'nullable|in:completed,ended,active','year'=>'nullable|integer|in:4,5,6',
        ]),fn($v)=>$v!==null && $v!=='');
    }

    public function query(array $filters)
    {
        $q=DB::table('learner_sessions as s')->join('access_codes as c','c.id','=','s.access_code_id')
            ->leftJoin('avatars as av','av.id','=','s.avatar_id')
            ->leftJoin('student_journeys as j','j.learner_session_id','=','s.id')
            ->leftJoin('journey_clocks as clock','clock.student_journey_id','=','j.id')
            ->select('s.*','c.code','c.name as code_name','av.role_label as avatar_role','j.id as journey_id','j.flow_version','clock.elapsed_ms','clock.started_at as clock_started_at','clock.finished_at as clock_finished_at');
        if(isset($filters['year']))$q->where('s.school_year',$filters['year']);
        if(isset($filters['code']))$q->where('c.id',$filters['code']);
        // instr performs a literal substring match: user %/_ characters never act as SQL wildcards.
        foreach(['name'=>'display_name','class'=>'class_name'] as $key=>$column){
            if(isset($filters[$key]))$q->whereRaw('instr(lower(s.'.$column.'), lower(?)) > 0',[$filters[$key]]);
        }
        if(isset($filters['from']))$q->where('s.created_at','>=',Carbon::parse($filters['from'],'Asia/Kuala_Lumpur')->startOfDay()->utc());
        if(isset($filters['to']))$q->where('s.created_at','<',Carbon::parse($filters['to'],'Asia/Kuala_Lumpur')->addDay()->startOfDay()->utc());
        if(($filters['status']??null)==='completed')$q->whereNotNull('s.completed_at');
        if(($filters['status']??null)==='ended')$q->whereNull('s.completed_at')->whereNotNull('s.ended_at');
        if(($filters['status']??null)==='active')$q->whereNull('s.completed_at')->whereNull('s.ended_at');
        return $q->orderByDesc('s.id');
    }

    public function rows(iterable $sessions, Carbon $at, int $offset=0): array
    {
        $sessions=collect($sessions);$ranks=app(SessionInsights::class)->rankings($sessions->pluck('access_code_id')->unique()->all());$journeys=$sessions->pluck('journey_id')->filter()->all();
        $progress=DB::table('student_progress')->whereIn('student_journey_id',$journeys)->get()->groupBy('student_journey_id');
        $firstQuestions=DB::table('attempt_questions as aq')->join('attempts as a','a.id','=','aq.attempt_id')->whereIn('a.student_journey_id',$journeys)
            ->selectRaw('MIN(aq.id) as id')->groupBy('a.student_journey_id','aq.question_id');
        $firstSubmissions=DB::table('answer_submissions')->selectRaw('MIN(id) as id')->groupBy('attempt_question_id');
        $metrics=DB::table('attempt_questions as aq')->join('attempts as a','a.id','=','aq.attempt_id')
            ->join('answer_submissions as sub','sub.attempt_question_id','=','aq.id')->whereIn('aq.id',$firstQuestions)->whereIn('sub.id',$firstSubmissions)
            ->selectRaw('a.student_journey_id, COUNT(*) as answered, SUM(CASE WHEN sub.is_correct = 1 THEN 1 ELSE 0 END) as first_correct')->groupBy('a.student_journey_id')->get()->keyBy('student_journey_id');
        $zoneNumbers=DB::table('zones')->pluck('sequence','id');
        return $sessions->map(function($s,$i)use($progress,$metrics,$at,$offset,$zoneNumbers,$ranks){
            $p=$progress->get($s->journey_id,collect());$m=$metrics->get($s->journey_id);
            $seconds=$s->elapsed_ms===null?null:($s->clock_finished_at || (int)$s->flow_version<3?intdiv($s->elapsed_ms,1000):max(0,(int)Carbon::parse($s->clock_started_at)->diffInSeconds($at,false)));
            $zones=$p->where('status','completed')->pluck('zone_id');
            $sequences=$zones->map(fn($id)=>$zoneNumbers->get($id))->filter()->sort()->values()->all();
            return [
                $offset+$i+1,$s->code,strtoupper(substr($s->public_id,0,8)),$s->display_name,$s->class_name?:'—','Tahun '.$s->school_year,$s->avatar_role?:'Belum dipilih',
                $sequences?'Zon '.implode(', ',$sequences):'Belum ada',(int)$p->sum('best_score'),($m->first_correct??0).' / '.($m->answered??0),
                $seconds===null?'Belum bermula':sprintf('%02d:%02d:%02d',intdiv($seconds,3600),intdiv($seconds%3600,60),$seconds%60),
                $s->completed_at?'Selesai':($s->ended_at?'Ditamatkan Awal':'Dalam Perjalanan'),
                $this->date($s->created_at),$this->date($s->completed_at??$s->ended_at),$ranks[$s->id]??'Belum selesai',
            ];
        })->all();
    }
    private function date(?string $value): string {return $value?Carbon::parse($value)->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s'):'—';}
    public function summary(array $filters): string
    {
        $labels=['year'=>'Tahun','code'=>'Kod','name'=>'Nama','class'=>'Kelas','from'=>'Dari','to'=>'Hingga','status'=>'Status'];
        $values=['completed'=>'Selesai','ended'=>'Ditamatkan Awal','active'=>'Dalam Perjalanan'];$parts=[];
        foreach($filters as $key=>$value){if($key==='code')$value=DB::table('access_codes')->where('id',$value)->value('code');$parts[]=$labels[$key].': '.($values[$value]??$value);}
        return $parts?implode(' · ',$parts):'Semua sesi';
    }
}
