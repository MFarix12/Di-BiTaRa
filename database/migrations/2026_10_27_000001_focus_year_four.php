<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        app(\App\Services\RetireOtherYears::class)->run();
        // Retain year metadata for reports/certificates, but disallow any other value at DB level.
        if(DB::getDriverName()==='sqlite'){
            foreach(['access_codes','learner_sessions','activities','learning_releases'] as $table){
                foreach(['INSERT','UPDATE'] as $event){
                    DB::unprepared("CREATE TRIGGER {$table}_year_four_{$event} BEFORE {$event} ON {$table} WHEN NEW.school_year != 4 BEGIN SELECT RAISE(ABORT, 'Di-BITaRa hanya untuk Tahun 4'); END");
                }
            }
        }
    }
    public function down(): void
    {
        foreach(['access_codes','learner_sessions','activities','learning_releases'] as $table){
            foreach(['INSERT','UPDATE'] as $event)DB::unprepared("DROP TRIGGER IF EXISTS {$table}_year_four_{$event}");
        }
        // Retired content is restored only from the pre-migration backup.
    }
};
