<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('attempt_questions',function(Blueprint $table){$table->timestamp('meaning_acknowledged_at')->nullable();});
        // Existing correct work is historical, not an unacknowledged new popup.
        DB::table('attempt_questions')->whereIn('id',DB::table('responses')->where('is_correct',true)->select('attempt_question_id'))->update(['meaning_acknowledged_at'=>now()]);
    }
    public function down(): void { Schema::table('attempt_questions',fn(Blueprint $table)=>$table->dropColumn('meaning_acknowledged_at')); }
};
