<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  foreach(DB::table('questions')->where('type','sentence_order')->get() as $q){
   $answer=json_decode(DB::table('question_solutions')->where('question_id',$q->id)->value('answer_key')??'{}',true);
   $raw=['type'=>'sentence_order','config'=>json_decode($q->public_config,true)?:[],'answer'=>$answer];
   $fixed=\App\Services\SentencePunctuation::normalize($raw);
   if($fixed['config']!==$raw['config'])DB::table('questions')->where('id',$q->id)->update(['public_config'=>json_encode($fixed['config'],JSON_UNESCAPED_UNICODE)]);
  }
 }
 public function down(): void { /* Typographic correction does not change grading or historical results. */ }
};
