<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Import the supplied PDFs as separate, immutable published question sets. */
class YearCurriculum
{
 public function install(int $admin,int $year): int
 {
  abort_unless(in_array($year,[5,6],true),422);
  $title='Set Soalan Tahun '.$year.' — PDF 23 Oktober 2026';
  if($id=DB::table('learning_releases')->where('title',$title)->value('id'))return $id;
  return DB::transaction(function()use($admin,$year,$title){
   $data=json_decode(file_get_contents(resource_path('curriculum/tahun-'.$year.'.json')),true,512,JSON_THROW_ON_ERROR);
   $zones=DB::table('zones')->pluck('id','sequence');
   $activity=function($zone,$name,$kind,$sort)use($admin,$year,$zones){
    return DB::table('activities')->insertGetId(['zone_id'=>$zones[$zone],'school_year'=>$year,'title'=>$name,'kind'=>$kind,'is_required'=>true,'passing_percent'=>100,'sort_order'=>$sort,'settings'=>json_encode(['source'=>'Di-BITaRa_Set_Soalan_Tahun_'.$year.'.pdf','retry_until_correct'=>true]),'status'=>'published','created_by'=>$admin,'updated_by'=>$admin]);
   };
   $question=function($activity,$type,$prompt,$answer,$config=[],$options=[],$audio=null,$extra=[]) {
    $id=DB::table('questions')->insertGetId(['activity_id'=>$activity,'type'=>$type,'prompt'=>$prompt,'public_config'=>json_encode($config,JSON_UNESCAPED_UNICODE),'max_marks'=>10,'sort_order'=>DB::table('questions')->where('activity_id',$activity)->count()+1,'status'=>'published','feedback_correct'=>'','feedback_incorrect'=>'Jawapan kurang tepat, sila cuba lagi.']);
    DB::table('question_solutions')->insert(['question_id'=>$id,'answer_key'=>json_encode(['value'=>$answer]+$extra,JSON_UNESCAPED_UNICODE),'normalization'=>'{"case_fold":true,"preserve_word_boundaries":true}']);
    foreach($options as $key=>$label) DB::table('answer_options')->insert(['question_id'=>$id,'option_key'=>$key,'text'=>$label,'is_correct'=>$key===$answer]);
    if($audio)DB::table('question_audio')->insert(['question_id'=>$id,'media_file_id'=>$audio]);
   };
   foreach($data['tf'] as $group=>$words){
    $a=$activity(3,'Cabaran '.($group+1).' — Betul / Salah: '.($group?'Kata Sendi di':'Imbuhan di-'),'quiz',$group+1);
    foreach($words as $i=>$word)$question($a,'true_false','Adakah ejaan “'.$word.'” betul?',$i%2===0?'betul':'salah',[],['betul'=>'Betul','salah'=>'Salah']);
   }
   $a=$activity(3,'Cabaran 3 — Tarik dan Lepas','activity',3);$items=[];$mapping=[];
   foreach($data['drag'] as $i=>$word){$key='i'.($i+1);$items[$key]=$word;$mapping[$key]=str_starts_with($word,'di ')?'sendi':'imbuhan';}
   $question($a,'drag_drop','Tarik setiap kad ke kategori yang betul.',$mapping,['items'=>$items,'targets'=>['imbuhan'=>'Imbuhan di-','sendi'=>'Kata Sendi di']]);
   $a=$activity(4,'Cabaran 1 — Susun Ayat','activity',1);
   foreach($data['order'] as $row){
    [$words,$order,$sentence]=$row;$items=[];foreach($words as $i=>$word)$items['i'.($i+1)]=$word;
    $question($a,'sentence_order','Susun perkataan atau frasa menjadi ayat lengkap.',array_map(fn($n)=>'i'.$n,$order),['items'=>$items,'is_proverb'=>isset($row[3])],[],null,['sentence'=>$sentence,'meaning'=>$row[3]??null]);
   }
   $a=$activity(4,'Cabaran 2 — Isi Tempat Kosong dan Salin Ayat','activity',2);
   foreach($data['fill'] as $i=>[$prompt,$blank,$sentence]){
    $other=str_starts_with($blank,'di ')?str_replace('di ','di',$blank):'di '.substr($blank,2);
    $question($a,'fill_blank',$prompt,['blank'=>$blank,'sentence'=>$sentence],['require_copy'=>true,'choices'=>$i%2?[$other,$blank]:[$blank,$other]]);
   }
   $a=$activity(5,'Imlak Akhir — Dengar dan Tulis','activity',1);
   foreach($data['imlak'] as $i=>$text){
    $path='tahun-'.$year.'/imlak-'.($i+1).'.mp3';$source=resource_path('audio/'.$path);
    if(!is_file($source))throw new \RuntimeException('Audio belum tersedia: '.$path);
    $bytes=file_get_contents($source);if(!Storage::disk('private')->put($path,$bytes))throw new \RuntimeException('Audio gagal disimpan.');
    $media=DB::table('media_files')->insertGetId(['kind'=>'audio','title'=>'Tahun '.$year.' — Audio Imlak '.($i+1),'path'=>$path,'original_name'=>basename($path),'mime_type'=>'audio/mpeg','size_bytes'=>strlen($bytes),'checksum'=>hash('sha256',$bytes),'source'=>'tts','license_note'=>'Suara sintetik Bahasa Melayu; teks PDF pengguna.','status'=>'ready','created_by'=>$admin]);
    $question($a,'dictation','Dengar audio. Tulis perkataan atau ayat yang didengar.',$text,['max_audio_plays'=>3],[],$media);
   }
   $id=app(Publication::class)->publish($admin,$year);
   DB::table('learning_releases')->where('id',$id)->update(['title'=>$title]);return $id;
  });
 }
}
