<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $admin = DB::table('users')->where('role', 'admin')->first();
        if ($admin) {
            app(\App\Services\AvatarCatalogue::class)->install($admin->id);
        }
    }

    public function down(): void
    {
        // Keep avatar selections and media intact on rollback.
    }
};
