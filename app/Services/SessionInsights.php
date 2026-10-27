<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class SessionInsights
{
 public function dashboard(): array {
  $today=now()->timezone('Asia/Kuala_Lumpur')->startOfDay()->utc();
  $scores=DB::table('student_progress')->selectRaw('student_journey_id,SUM(best_score) as score')->groupBy('student_journey_id');
  $completed=DB::table('learner_sessions as s')->join('student_journeys as j','j.learner_session_id','=','s.id')->leftJoinSub($scores,'marks','marks.student_journey_id','=','j.id')->whereNotNull('s.completed_at');
  $avg=(clone $completed)->selectRaw('AVG(COALESCE(marks.score,0)) as score, AVG(j.total_duration_seconds) as seconds')->first();
  $active=DB::table('learner_sessions as s')->join('student_journeys as j','j.learner_session_id','=','s.id')->join('journey_clocks as c','c.student_journey_id','=','j.id')->whereNull('s.ended_at')->whereNull('s.completed_at')->where('c.last_seen_at','>=',now()->subMinutes(2))->count();
  return ['Kod Aktif Hari Ini'=>DB::table('access_codes')->where('status','active')->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->count(),'Murid Sedang Bermain'=>$active,'Sesi Selesai'=>DB::table('learner_sessions')->whereNotNull('completed_at')->count(),'Purata Markah'=>number_format((float)$avg->score,1),'Purata Masa'=>$avg->seconds===null?'Belum ada':sprintf('%02d:%02d:%02d',intdiv((int)$avg->seconds,3600),intdiv((int)$avg->seconds%3600,60),(int)$avg->seconds%60),'Murid Tahun 4 Hari Ini'=>DB::table('learner_sessions')->where('created_at','>=',$today)->count()];
 }
 public function rankings(array $codes): array {
  $scores=DB::table('student_progress')->selectRaw('student_journey_id,SUM(best_score) as score')->groupBy('student_journey_id');
  $firstQuestions=DB::table('attempt_questions as aq')->join('attempts as a','a.id','=','aq.attempt_id')->selectRaw('MIN(aq.id) as id')->groupBy('a.student_journey_id','aq.question_id');
  $firstSubmissions=DB::table('answer_submissions')->selectRaw('MIN(id) as id')->groupBy('attempt_question_id');
  $first=DB::table('attempt_questions as aq')->join('attempts as a','a.id','=','aq.attempt_id')->join('answer_submissions as sub','sub.attempt_question_id','=','aq.id')->whereIn('aq.id',$firstQuestions)->whereIn('sub.id',$firstSubmissions)->selectRaw('a.student_journey_id,SUM(CASE WHEN sub.is_correct=1 THEN 1 ELSE 0 END) as first_correct')->groupBy('a.student_journey_id');
  $rows=DB::table('learner_sessions as s')->join('student_journeys as j','j.learner_session_id','=','s.id')->leftJoinSub($scores,'marks','marks.student_journey_id','=','j.id')->leftJoinSub($first,'firsts','firsts.student_journey_id','=','j.id')->whereIn('s.access_code_id',$codes)->whereNotNull('s.completed_at')->select('s.id','s.access_code_id','j.total_duration_seconds')->selectRaw('COALESCE(marks.score,0) as score,COALESCE(firsts.first_correct,0) as first_correct')->orderBy('s.access_code_id')->orderByDesc('score')->orderByDesc('first_correct')->orderBy('j.total_duration_seconds')->get();
  $ranks=[];$previous=[];$rank=0;$code=null;
  foreach($rows as $r){$key=[(int)$r->score,(int)$r->first_correct,(int)$r->total_duration_seconds];if($code!==$r->access_code_id){$code=$r->access_code_id;$rank=0;$previous=[];}if($previous!==$key){$rank++;$previous=$key;}$ranks[$r->id]=$rank;}
  return $ranks;
 }
}
