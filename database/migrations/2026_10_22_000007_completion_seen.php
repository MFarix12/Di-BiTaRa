<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('student_journeys',fn(Blueprint $table)=>$table->timestamp('completion_seen_at')->nullable());
        DB::table('student_journeys')->where('status','completed')->update(['completion_seen_at'=>now()]);
    }
    public function down(): void {Schema::table('student_journeys',fn(Blueprint $table)=>$table->dropColumn('completion_seen_at'));}
};
