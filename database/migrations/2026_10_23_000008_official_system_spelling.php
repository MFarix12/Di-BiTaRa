<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Copy only: never touch answers, scores, immutable release manifests or audit history.
        foreach ([
            'learning_releases'=>['title'],
            'learning_contents'=>['title','body','description'],
            'activities'=>['title','description','instruction'],
            'zones'=>['name','description'],
            'notifications'=>['title','body','message'],
        ] as $table=>$columns) {
            if (!Schema::hasTable($table)) continue;
            $columns=array_values(array_filter($columns,fn($column)=>Schema::hasColumn($table,$column)));
            foreach(DB::table($table)->select(array_merge(['id'],$columns))->orderBy('id')->get() as $row) {
                $changes=[];
                foreach($columns as $column) {
                    if(!is_string($row->$column))continue;
                    $value=preg_replace('/Di-Bitara/i','Di-BITaRa',$row->$column);
                    if($value!==$row->$column)$changes[$column]=$value;
                }
                if($changes)DB::table($table)->where('id',$row->id)->update($changes);
            }
        }
        if(Schema::hasTable('users'))DB::table('users')->where('role','admin')->whereRaw('lower(display_name) = ?',['pentadbir di-bitara'])->update(['display_name'=>'Pentadbir Di-BITaRa']);
    }
    public function down(): void { /* Keep the corrected official spelling. */ }
};
