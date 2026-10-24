<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('access_codes', function (Blueprint $t) {
            $t->id(); $t->string('code',20)->unique(); $t->string('name',100);
            $t->string('status',16)->default('active'); $t->dateTime('expires_at',3)->nullable();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete(); $t->timestamps(3);
        });
        Schema::create('learner_sessions', function (Blueprint $t) {
            $t->id(); $t->uuid('public_id')->unique(); $t->char('token_hash',64)->unique();
            $t->foreignId('access_code_id')->constrained()->restrictOnDelete();
            $t->string('display_name',100)->default('Juruterbang'); $t->string('class_name',50)->nullable();
            $t->foreignId('avatar_id')->nullable()->constrained()->nullOnDelete(); $t->dateTime('avatar_confirmed_at',3)->nullable();
            $t->string('onboarding_status',16)->default('pending'); $t->unsignedTinyInteger('onboarding_step')->default(1); $t->dateTime('onboarding_completed_at',3)->nullable();
            $t->dateTime('completed_at',3)->nullable(); $t->dateTime('ended_at',3)->nullable(); $t->timestamps(3);
        });
        Schema::table('student_journeys', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->nullable()->change();
            $t->foreignId('learner_session_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });
        Schema::table('student_notifications', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->nullable()->change();
            $t->foreignId('learner_session_id')->nullable()->constrained()->restrictOnDelete();
        });
    }
    public function down(): void
    {
        // Session results are records of learning. Roll back application code without dropping these records.
    }
};
