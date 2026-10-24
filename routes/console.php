<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

Artisan::command('dibitara:admin {username?} {--email=} {--name=Pentadbir Di-BITaRa} {--generate}', function () {
    $username = Str::lower($this->argument('username') ?: $this->ask('Kod pengguna Admin'));
    if (! preg_match('/^[a-z0-9_-]{3,50}$/', $username) || User::withTrashed()->where('username', $username)->exists()) {
        $this->error('Kod pengguna tidak sah atau telah digunakan.'); return 1;
    }
    $password = $this->option('generate') ? Str::password(20) : $this->secret('Kata laluan Admin (minimum 12 aksara)');
    if (strlen((string) $password) < 12) { $this->error('Kata laluan terlalu pendek.'); return 1; }
    $user = new User;
    $user->forceFill(['username' => $username, 'display_name' => $this->option('name'), 'email' => $this->option('email'), 'password' => Hash::make($password), 'role' => 'admin', 'status' => 'active'])->save();
    foreach (['Kapten Awan','Kapten Bintang','Kapten Suria','Kapten Bulan'] as $i => $name) {
        if (DB::table('avatars')->where('code', 'pilot-'.($i+1))->exists()) { continue; }
        $file = public_path('images/pilot-'.($i+1).'.svg');
        $media = DB::table('media_files')->insertGetId(['kind' => 'image', 'title' => $name, 'disk' => 'brand', 'path' => 'images/pilot-'.($i+1).'.svg', 'original_name' => basename($file), 'mime_type' => 'image/svg+xml', 'size_bytes' => filesize($file), 'source' => 'generated', 'status' => 'ready', 'created_by' => $user->id, 'alt_text' => $name, 'checksum' => hash_file('sha256', $file)]);
        DB::table('avatars')->insert(['code' => 'pilot-'.($i+1), 'name' => $name, 'media_file_id' => $media, 'sort_order' => $i]);
    }
    app(\App\Services\AvatarCatalogue::class)->install($user->id);
    DB::table('audit_logs')->insert(['admin_user_id' => $user->id, 'action' => 'create', 'entity_type' => 'users', 'entity_id' => $user->id, 'request_id' => (string) Str::uuid(), 'after_data' => json_encode(['role' => 'admin', 'source' => 'console'])]);
    $this->info('Akaun Admin diwujudkan: '.$username);
    if ($this->option('generate')) { $this->line('Kata laluan sekali paparan: '.$password); }
})->purpose('Wujudkan akaun Admin melalui terminal terkawal');

Artisan::command('dibitara:install-content', function () {
    $admin = User::where('role','admin')->where('status','active')->firstOrFail();
    $id = app(\App\Services\StarterCurriculum::class)->install($admin->id);
    app(\App\Services\StarterCurriculum::class)->activateForStudents($id);
    $this->info('Kurikulum sumber sedia. Keluaran #'.$id);
})->purpose('Pasang nota dan soalan PDF sumber tanpa menimpa suntingan sedia ada');

Artisan::command('dibitara:refresh-certificate-branding', function () {
    $updated = 0;
    $records = DB::table('certificates')->where('status', 'ready')->whereNotNull('media_file_id')->get();
    foreach ($records as $certificate) {
        $media = DB::table('media_files')->where('id', $certificate->media_file_id)->where('mime_type', 'application/pdf')->whereNull('deleted_at')->first();
        if (! $media || ! in_array($media->disk, ['private', 'local'], true)) continue;
        $disk = \Illuminate\Support\Facades\Storage::disk($media->disk);
        if (! $disk->exists($media->path) || str_contains($disk->get($media->path), '/DiBITaRaTextV1')) continue;
        $date = \Carbon\Carbon::parse($certificate->issued_at)->timezone('Asia/Kuala_Lumpur')->format('d/m/Y');
        $pdf = app(\App\Services\CertificatePdf::class)->generate($certificate->recipient_name, $certificate->certificate_number, $date);
        $path = 'certificates/branding-v4/'.$certificate->id.'.pdf';
        if (! $disk->put($path, $pdf)) throw new \RuntimeException('Sijil baharu gagal disimpan. Fail asal dikekalkan.');
        DB::table('media_files')->where('id', $media->id)->update(['path'=>$path, 'size_bytes'=>strlen($pdf), 'checksum'=>hash('sha256', $pdf)]);
        $updated++;
    }
    $this->info($updated.' sijil diselaraskan. Nama, nombor, tarikh dan kemajuan dikekalkan.');
})->purpose('Selaraskan logo sijil sedia ada sambil mengekalkan fail asal');

Artisan::command('dibitara:install-avatars', function () {
    $admin = User::where('role','admin')->firstOrFail();
    app(\App\Services\AvatarCatalogue::class)->install($admin->id);
    $this->info('Koleksi avatar sedia. Pilihan avatar lama dikekalkan.');
})->purpose('Pasang koleksi avatar penerbangan dan ruang angkasa');

Artisan::command('dibitara:install-year-sets', function () {
    $admin=User::where('role','admin')->where('status','active')->firstOrFail();
    foreach([5,6] as $year){
        $id=app(\App\Services\YearCurriculum::class)->install($admin->id,$year);
        $this->info('Tahun '.$year.' sedia. Keluaran #'.$id);
    }
})->purpose('Pasang set PDF Tahun 5 dan Tahun 6 tanpa menukar sesi atau set Tahun 4');

Artisan::command('dibitara:personalize-certificates',function(){
 $n=0;
 foreach(DB::table('certificates')->where('status','ready')->whereNotNull('media_file_id')->get() as $cert){
  $j=DB::table('student_journeys')->find($cert->student_journey_id);if(!$j || !$j->learner_session_id)continue;
  $s=\App\Models\LearnerSession::find($j->learner_session_id);$m=DB::table('media_files')->find($cert->media_file_id);if(!$s || !$m || !in_array($m->disk,['private','local']))continue;
  $disk=\Illuminate\Support\Facades\Storage::disk($m->disk);if(!$disk->exists($m->path) || str_contains($disk->get($m->path),'/DiBITaRaSessionV3'))continue;
  $pdf=app(\App\Services\CertificatePdf::class)->generate($cert->recipient_name,$cert->certificate_number,\Carbon\Carbon::parse($cert->issued_at)->timezone('Asia/Kuala_Lumpur')->format('d/m/Y'),['year'=>$s->school_year,'class'=>$s->class_name??'—','seconds'=>$j->total_duration_seconds??0]);
  $path='certificates/session-v3/'.$cert->id.'.pdf';if(!$disk->put($path,$pdf))throw new \RuntimeException('Sijil gagal disimpan; fail asal dikekalkan.');
  DB::table('media_files')->where('id',$m->id)->update(['path'=>$path,'size_bytes'=>strlen($pdf),'checksum'=>hash('sha256',$pdf)]);$n++;
 }
 $this->info($n.' sijil diselaraskan; nombor, tarikh, markah dan cap asal dikekalkan.');
});
