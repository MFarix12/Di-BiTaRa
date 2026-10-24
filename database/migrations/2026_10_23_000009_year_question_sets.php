<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  foreach (['learning_releases','activities','access_codes','learner_sessions'] as $table) {
   Schema::table($table,fn(Blueprint $t)=>$t->unsignedTinyInteger('school_year')->default(4)->index());
  }
  Schema::table('access_codes',fn(Blueprint $t)=>$t->foreignId('learning_release_id')->nullable()->constrained('learning_releases')->restrictOnDelete());
  Schema::table('learner_sessions',fn(Blueprint $t)=>$t->foreignId('learning_release_id')->nullable()->constrained('learning_releases')->restrictOnDelete());
  // Preserve the actual release of every historical session, rather than selecting a new one.
  foreach(DB::table('student_journeys')->whereNotNull('learner_session_id')->get() as $j) {
   DB::table('learner_sessions')->where('id',$j->learner_session_id)->update(['learning_release_id'=>$j->learning_release_id]);
  }
  $release=DB::table('learning_releases')->where('status','published')->latest('id')->value('id');
  if($release) DB::table('access_codes')->update(['learning_release_id'=>$release]);
 }
 public function down(): void { /* Preserve session provenance and question sets. */ }
};
