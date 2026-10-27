<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/** One-time data retirement. No question sets are reassigned to surviving sessions. */
class RetireOtherYears
{
    public function run(): void
    {
        $relations=[];
        foreach(Schema::getTableListing() as $table){
            $table=basename(str_replace('.', '/', $table));
            foreach(Schema::getForeignKeys($table) as $fk){
                if(count($fk['columns'])===1 && $fk['foreign_columns']===['id']){
                    $relations[$fk['foreign_table']][]=[$table,$fk['columns'][0]];
                }
            }
        }
        $files=[];
        $remove=function(string $table,int $id)use(&$remove,$relations,&$files){
            $row=DB::table($table)->find($id);
            if(!$row)return;
            if($table==='certificates' && $row->media_file_id)$files[]=$row->media_file_id;
            foreach($relations[$table]??[] as [$child,$column]){
                foreach(DB::table($child)->where($column,$id)->pluck('id') as $childId)$remove($child,(int)$childId);
            }
            DB::table($table)->where('id',$id)->delete();
        };
        DB::transaction(function()use($remove,$relations,&$files){
            foreach(['access_codes','learner_sessions','learning_releases','activities'] as $table){
                foreach(DB::table($table)->where('school_year','<>',4)->pluck('id') as $id)$remove($table,(int)$id);
            }
            $files=array_merge($files,DB::table('media_files')->where(fn($q)=>$q->where('path','like','tahun-5/%')->orWhere('path','like','tahun-6/%'))->pluck('id')->all());
            foreach(array_unique($files) as $id){
                $referenced=false;
                foreach($relations['media_files']??[] as [$table,$column])$referenced=$referenced||DB::table($table)->where($column,$id)->exists();
                if(!$referenced && ($media=DB::table('media_files')->find($id))){
                    Storage::disk($media->disk)->delete($media->path);
                    DB::table('media_files')->where('id',$id)->delete();
                }
            }
        });
    }
}
