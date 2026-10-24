<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StarterCurriculum
{
    public const TITLE = 'Kurikulum PDF terkini Di-BITaRa — 22 Oktober 2026';

    public function activateForStudents(int $release): void
    {
        DB::transaction(function () use ($release) {
            $engine=app(LearningEngine::class);
            foreach (DB::table('student_journeys')->whereNotNull('user_id')->where('learning_release_id','!=',$release)->whereIn('status',['active','completed'])->distinct()->pluck('user_id') as $userId) {
                DB::table('users')->where('id',$userId)->lockForUpdate()->first();
                if (DB::table('student_journeys')->where('user_id',$userId)->where('learning_release_id',$release)->exists()) { continue; }
                DB::table('student_journeys')->where('user_id',$userId)->where('status','active')->update(['status'=>'archived']);
                $id=DB::table('student_journeys')->insertGetId(['user_id'=>$userId,'learning_release_id'=>$release,'flow_version'=>(int)config('learning.flow_version',2)]);
                $engine->refresh(DB::table('student_journeys')->find($id));
                $engine->notify(\App\Models\User::findOrFail($userId),'curriculum-'.$release,'Set soalan baharu tersedia','Teruskan dengan set terkini. Rekod percubaan dan sijil lama disimpan.');
            }
        });
    }

    public function install(int $admin): int
    {
        if ($existing = DB::table('learning_releases')->whereIn('title',[self::TITLE,str_replace('Di-BITaRa','Di-BiTaRa',self::TITLE)])->first()) { return $existing->id; }
        return DB::transaction(function () use ($admin) {
            // Retire the old published catalogue, retaining every source row and snapshot.
            DB::table('activities')->where('status','published')->update(['status'=>'inactive']);
            DB::table('learning_contents')->where('status','published')->update(['status'=>'inactive']);
            $zones = DB::table('zones')->orderBy('sequence')->get()->keyBy('sequence');
            $audio = function (string $name,string $title) use ($admin): int {
                $path='latest-20261022/'.$name.'.mp3';
                $source=resource_path('audio/'.$path);
                if (!is_file($source)) { throw new \RuntimeException('Audio belum tersedia: '.$path); }
                $bytes=file_get_contents($source);
                if (!Storage::disk('private')->put($path,$bytes)) { throw new \RuntimeException('Audio tidak dapat disimpan.'); }
                return DB::table('media_files')->insertGetId(['kind'=>'audio','title'=>$title,'path'=>$path,'original_name'=>$name.'.mp3','mime_type'=>'audio/mpeg','size_bytes'=>strlen($bytes),'checksum'=>hash('sha256',$bytes),'source'=>'tts','license_note'=>'Suara sintetik Bahasa Melayu; kandungan daripada dokumen rujukan pengguna.','status'=>'ready','created_by'=>$admin]);
            };
            $briefing=$audio('taklimat','Penerangan Imbuhan di- dan Kata Sendi di');
            $note=DB::table('learning_contents')->insertGetId(['zone_id'=>$zones[2]->id,'title'=>'Dua pola ejaan: rapat dan terpisah','body'=>'Imbuhan di- ditulis rapat dengan kata kerja pasif: ditulis, dimasak, dihantar. Kata sendi di ditulis terpisah untuk tempat: di sekolah, di rumah, di padang. Baca contoh dan dengar penerangan.','kind'=>'note','is_required'=>true,'status'=>'published','created_by'=>$admin,'updated_by'=>$admin]);
            DB::table('content_media')->insert(['learning_content_id'=>$note,'media_file_id'=>$briefing,'purpose'=>'audio']);
            $activity=function (int $z,string $title,string $kind,int $order) use ($zones,$admin) {
                return DB::table('activities')->insertGetId(['zone_id'=>$zones[$z]->id,'title'=>$title,'kind'=>$kind,'is_required'=>true,'passing_percent'=>100,'sort_order'=>$order,'settings'=>json_encode(['source'=>'Soalan Kuiz.pdf','curriculum'=>'pdf-20261022','minutes'=>10,'retry_until_correct'=>true]),'status'=>'published','created_by'=>$admin,'updated_by'=>$admin]);
            };
            $question=function (int $activity,string $type,string $prompt,mixed $answer,array $config=[],array $options=[],?int $audio=null,string $feedback='Hebat! Jawapan anda betul!',array $solution=[]) {
                $id=DB::table('questions')->insertGetId(['activity_id'=>$activity,'type'=>$type,'prompt'=>$prompt,'public_config'=>json_encode($config,JSON_UNESCAPED_UNICODE),'max_marks'=>10,'sort_order'=>DB::table('questions')->where('activity_id',$activity)->count()+1,'status'=>'published','feedback_correct'=>$feedback,'feedback_incorrect'=>'Hampir betul. Cuba sekali lagi!']);
                DB::table('question_solutions')->insert(['question_id'=>$id,'answer_key'=>json_encode(['value'=>$answer]+$solution,JSON_UNESCAPED_UNICODE),'normalization'=>'{"case_fold":true,"preserve_word_boundaries":true}']);
                foreach ($options as $key=>$text) { DB::table('answer_options')->insert(['question_id'=>$id,'option_key'=>$key,'text'=>$text,'is_correct'=>$key===$answer]); }
                if ($audio) { DB::table('question_audio')->insert(['question_id'=>$id,'media_file_id'=>$audio]); }
            };
            foreach ([['Imbuhan di-',['ditulis','di baca','dimasak','di buang','dihantar','di sapu']],['Kata Sendi di',['di sekolah','dirumah','di padang','dipejabat','di taman','dikelas']]] as $group=>[$label,$words]) {
                $a=$activity(3,'Cabaran '.($group+1).' — Betul / Salah: '.$label,'quiz',$group+1);
                foreach ($words as $i=>$word) { $question($a,'true_false','Adakah ejaan “'.$word.'” betul?',$i%2===0?'betul':'salah',[],['betul'=>'Betul','salah'=>'Salah']); }
            }
            $a=$activity(3,'Cabaran 3 — Tarik dan Lepas','activity',3);
            $items=[];$mapping=[];
            foreach (['dijawab','di dapur','dimasak','di padang','dicuci','di kantin','ditutup','di kedai','dipukul','di bilik'] as $i=>$word) { $key='i'.($i+1);$items[$key]=$word;$mapping[$key]=str_starts_with($word,'di ')?'sendi':'imbuhan'; }
            $question($a,'drag_drop','Tarik setiap kad ke kategori yang betul.',$mapping,['items'=>$items,'targets'=>['imbuhan'=>'Imbuhan di-','sendi'=>'Kata Sendi di']]);
            $a=$activity(4,'Cabaran 1 — Susun Ayat','activity',1);
            foreach ([
                [['di','badan','Ukur','baju','sendiri.'],['i3','i4','i1','i2','i5'],'Ukur baju di badan sendiri.','Berbelanja mengikut kemampuan sendiri.'],
                [['sembilu.','Bagai','di','hiris'],['i2','i3','i4','i1'],'Bagai dihiris sembilu.','Hati berasa sangat sedih atau pedih.'],
                [['roti','Saya makan','di kantin.'],['i2','i1','i3'],'Saya makan roti di kantin.','Saya sedang makan roti. Tempatnya ialah kantin.'],
                [['Murid-murid','di sekolah.','belajar'],['i1','i3','i2'],'Murid-murid belajar di sekolah.','Sekolah ialah tempat murid-murid belajar.'],
                [['oleh','Karangan','Kapten Adam.','ditulis'],['i2','i4','i1','i3'],'Karangan ditulis oleh Kapten Adam.','Kapten Adam ialah orang yang menulis karangan itu.'],
            ] as [$words,$order,$sentence,$meaning]) {
                $items=[];foreach ($words as $i=>$word) { $items['i'.($i+1)]=$word; }
                $question($a,'sentence_order','Susun perkataan atau frasa menjadi ayat lengkap.',$order,['items'=>$items,'is_proverb'=>in_array($sentence,['Ukur baju di badan sendiri.','Bagai dihiris sembilu.'],true)],[],null,'Hebat! '.$sentence.($meaning?' Maksud: '.$meaning:''),['sentence'=>$sentence,'meaning'=>$meaning]);
            }
            $a=$activity(4,'Cabaran 2 — Isi Tempat Kosong dan Salin Ayat','activity',2);
            foreach ([
                ['Buku itu ____ (tulis) oleh saya.','ditulis','di tulis','Buku itu ditulis oleh saya.'],
                ['Buah epal itu ____ (makan) oleh saya.','dimakan','di makan','Buah epal itu dimakan oleh saya.'],
                ['Kami berkumpul ____ (dewan) sekolah.','di dewan','didewan','Kami berkumpul di dewan sekolah.'],
                ['Amir makan nasi ____ (kantin).','di kantin','dikantin','Amir makan nasi di kantin.'],
                ['Kami bermain bola ____ (padang).','di padang','dipadang','Kami bermain bola di padang.'],
            ] as $i=>[$prompt,$blank,$other,$sentence]) {
                $question($a,'fill_blank',$prompt,['blank'=>$blank,'sentence'=>$sentence],['require_copy'=>true,'choices'=>$i%2?[$other,$blank]:[$blank,$other]]);
            }
            $a=$activity(5,'Imlak Akhir — Dengar dan Tulis','activity',1);
            foreach (['di rumah','bagai dihiris sembilu','ukur baju di badan sendiri'] as $i=>$text) {
                $question($a,'dictation','Dengar audio. Tulis perkataan atau ayat yang didengar.',$text,['max_audio_plays'=>3],[],$audio('imlak-'.($i+1),'Audio Imlak '.($i+1)));
            }
            $id=app(Publication::class)->publish($admin);
            DB::table('learning_releases')->where('id',$id)->update(['title'=>self::TITLE]);
            return $id;
        });
    }
}
