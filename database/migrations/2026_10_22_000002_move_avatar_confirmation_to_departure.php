<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', fn (Blueprint $table) => $table->timestamp('avatar_confirmed_at')->nullable());
        // Existing saved choices remain confirmed; never revoke earned stamps.
        DB::table('student_profiles')->whereNotNull('avatar_id')->update(['avatar_confirmed_at'=>now()]);
    }

    public function down(): void
    {
        Schema::table('student_profiles', fn (Blueprint $table) => $table->dropColumn('avatar_confirmed_at'));
    }
};
