<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->string('gender', 16)->nullable();
            $table->string('role_label', 80)->nullable();
        });
        DB::table('avatars')->whereIn('code',['pilot-1','pilot-2'])->update(['gender'=>'male','role_label'=>'Kapten']);
        DB::table('avatars')->whereIn('code',['pilot-3','pilot-4'])->update(['gender'=>'female','role_label'=>'Kapten']);
        $admin = DB::table('users')->where('role','admin')->first();
        if ($admin) { app(\App\Services\AvatarCatalogue::class)->install($admin->id); }
    }

    public function down(): void
    {
        Schema::table('avatars', fn (Blueprint $table) => $table->dropColumn(['gender','role_label']));
    }
};
