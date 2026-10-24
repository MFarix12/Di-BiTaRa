<?php

use App\Http\Controllers\AuthController;
use App\Services\StudentOverview;
use App\Services\PassportOverview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/tentang', 'about')->name('about');
Route::view('/capai-pembelajaran', 'adventure')->name('adventure');
Route::redirect('/pengembaraan','/capai-pembelajaran');
Route::view('/cara-bermain', 'help')->name('play');
Route::view('/bantuan', 'help')->name('help');
Route::view('/privasi', 'privacy')->name('privacy');
Route::view('/syarat-penggunaan', 'terms')->name('terms');
Route::get('/masukkan-kod', [\App\Http\Controllers\AccessSessionController::class,'enter'])->name('access.enter');
Route::post('/masukkan-kod', [\App\Http\Controllers\AccessSessionController::class,'start'])->block(10,10)->name('access.start');
Route::middleware('guest:web')->group(function () {
    Route::view('/log-masuk-admin','auth.login',['adminLogin'=>true])->name('admin.login');
    Route::post('/log-masuk-admin',[AuthController::class,'login'])->middleware('throttle:15,1')->name('admin.login.submit');
});
Route::post('/log-keluar', [AuthController::class, 'logout'])->middleware(['auth:web','role:admin'])->name('logout');
Route::get('/papan-pemuka', function () {
    abort_unless(auth()->user()->status === 'active', 403);
    if (auth()->user()->role === 'admin') { return redirect()->route('admin.dashboard'); }
    abort_unless(auth()->user()->role === 'pelajar', 403);
    return redirect()->route('student.dashboard');
})->middleware(['auth','auth.session'])->name('dashboard');

Route::middleware([\App\Http\Middleware\RequireLearnerSession::class, \App\Http\Middleware\PrivateStudentPages::class])->group(function () {
    Route::get('/maklumat-pelajar',[\App\Http\Controllers\AccessSessionController::class,'details'])->name('student.details');
    Route::post('/maklumat-pelajar',[\App\Http\Controllers\AccessSessionController::class,'saveDetails'])->block(10,10)->name('student.details.save');
    Route::get('/tutorial', fn () => redirect()->route('student.profile',['tutorial'=>1]))->name('tutorial');
    Route::post('/sesi/tamat',[\App\Http\Controllers\AccessSessionController::class,'end'])->block(10,10)->name('student.session.end');
    Route::get('/pelajar', fn () => redirect()->route('student.activities'))->name('student.dashboard');
    Route::view('/profil-sesi','student.session-profile')->name('student.profile');
    Route::get('/pasport-pencapaian', fn (PassportOverview $overview) => view('student.achievements', $overview->for(auth()->user())))->name('student.achievements');
    Route::get('/pasport', fn () => redirect(route('student.achievements').'#pasport'))->name('student.passport');
    Route::get('/kemajuan', fn () => redirect(route('student.achievements').'#ringkasan'))->name('student.progress');
    Route::get('/kuiz-aktiviti', [\App\Http\Controllers\AdventureController::class,'catalog'])->name('student.activities');
    Route::get('/zon/{sequence}/mula', [\App\Http\Controllers\AdventureController::class,'launch'])->whereNumber('sequence')->name('student.zone.launch');
    Route::post('/zon/{sequence}/bersedia', [\App\Http\Controllers\AdventureController::class,'prepare'])->whereNumber('sequence')->block(10,10)->name('student.zone.prepare');
    Route::post('/zon/{sequence}/mula', [\App\Http\Controllers\AdventureController::class,'start'])->whereNumber('sequence')->block(10,10)->name('student.zone.start');
    Route::get('/zon/{sequence}/nota/{note}', [\App\Http\Controllers\AdventureController::class,'note'])->whereNumber(['sequence','note'])->name('student.note');
    Route::post('/zon/{sequence}/nota/{note}', [\App\Http\Controllers\AdventureController::class,'read'])->whereNumber(['sequence','note'])->block(10,10)->name('student.note.read');
    Route::post('/aktiviti/{activity}/mula', [\App\Http\Controllers\AdventureController::class,'begin'])->whereNumber('activity')->block(10,10)->name('student.activity.begin');
    Route::get('/misi/{attempt}', [\App\Http\Controllers\AdventureController::class,'attempt'])->whereNumber('attempt')->name('student.attempt');
    Route::post('/misi/{attempt}/jawapan', [\App\Http\Controllers\AdventureController::class,'answer'])->whereNumber('attempt')->block(10,10)->name('student.answer');
    Route::post('/misi/{attempt}/maksud',[\App\Http\Controllers\AdventureController::class,'meaning'])->whereNumber('attempt')->block(10,10)->name('student.meaning');
    Route::post('/misi/{attempt}/audio', [\App\Http\Controllers\AdventureController::class,'dictationAudio'])->whereNumber('attempt')->block(10,10)->name('student.dictation.audio');
    Route::get('/perjalanan/teruskan/{sequence}', [\App\Http\Controllers\AdventureController::class,'transition'])->whereNumber('sequence')->name('student.transition');
    Route::post('/perjalanan/jam', [\App\Http\Controllers\AdventureSupportController::class,'heartbeat'])->name('student.clock');
    Route::post('/misi/{attempt}/draf', [\App\Http\Controllers\AdventureSupportController::class,'draft'])->whereNumber('attempt')->name('student.draft');
    Route::get('/ulang-kaji', [\App\Http\Controllers\ReviewController::class,'index'])->name('student.review');
    Route::match(['get','post'],'/ulang-kaji/aktiviti/{activity}', [\App\Http\Controllers\ReviewController::class,'activity'])->whereNumber('activity')->name('student.review.activity');
    Route::get('/ulang-kaji/nota/{note}', [\App\Http\Controllers\ReviewController::class,'note'])->whereNumber('note')->name('student.review.note');
    Route::get('/ulang-kaji/audio/{media}', [\App\Http\Controllers\ReviewController::class,'audio'])->whereNumber('media')->name('student.review.audio');
    Route::post('/perjalanan/selesai/dilihat',function(\Illuminate\Http\Request $r,\App\Services\LearningEngine $engine){
        $journey=$engine->journey($r->user());abort_unless($journey?->status==='completed',409);
        DB::table('student_journeys')->where('id',$journey->id)->whereNull('completion_seen_at')->update(['completion_seen_at'=>now()]);
        return redirect()->route('student.achievements');
    })->block(10,10)->name('student.completion.seen');
    Route::post('/refleksi', [\App\Http\Controllers\AdventureController::class,'reflect'])->block(10,10)->name('student.reflection');
    Route::get('/notifikasi', [\App\Http\Controllers\AdventureController::class,'notifications'])->name('student.notifications');
    Route::post('/notifikasi/baca', [\App\Http\Controllers\AdventureController::class,'markNotifications'])->name('student.notifications.read');
    Route::get('/ganjaran', fn () => redirect(route('student.achievements').'#lencana'))->name('student.rewards');
    Route::get('/sijil', fn () => redirect(route('student.achievements').'#sijil'))->name('student.certificates');
    Route::get('/sijil/{certificate}/pratonton', [\App\Http\Controllers\StudentCertificateController::class,'show'])->whereNumber('certificate')->name('student.certificate.preview');
    Route::get('/sijil/{certificate}/muat-turun', [\App\Http\Controllers\StudentCertificateController::class,'show'])->whereNumber('certificate')->name('student.certificate.download');
    Route::post('/sijil/{certificate}/cuba-semula', [\App\Http\Controllers\StudentCertificateController::class,'retry'])->whereNumber('certificate')->block(10,10)->name('student.certificate.retry');
});

Route::prefix('admin')->name('admin.')->middleware(['auth:web', 'auth.session', 'role:admin'])->group(function () {
    Route::get('/', function () {
        $counts = collect(['Nota' => 'learning_contents', 'Aktiviti' => 'activities', 'Soalan' => 'questions', 'Audio' => 'media_files'])
            ->map(fn ($table, $label) => DB::table($table)->whereNull('deleted_at')->when($label === 'Audio', fn ($q) => $q->where('kind','audio'))->count());
        $counts->put('Kod Aktif',DB::table('access_codes')->where('status','active')->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->count());
        $counts->put('Kandungan Aktif',DB::table('learning_contents')->whereNull('deleted_at')->where('status','published')->count());
        $counts->put('Sesi',DB::table('learner_sessions')->count());
        $zoneCounts = DB::table('zones')->orderBy('sequence')->get()->map(function ($zone) {
            $zone->content_count = DB::table('learning_contents')->where('zone_id',$zone->id)->whereNull('deleted_at')->count();
            return $zone;
        });
        return view('admin.dashboard', ['insights'=>app(\App\Services\SessionInsights::class)->dashboard(), 'counts' => $counts, 'zoneCounts'=>$zoneCounts, 'logs' => DB::table('audit_logs')->latest('id')->limit(5)->get()]);
    })->name('dashboard');
    Route::get('/kod-akses',[\App\Http\Controllers\AccessCodeController::class,'index'])->name('codes');
    Route::post('/kod-akses',[\App\Http\Controllers\AccessCodeController::class,'save'])->name('codes.store');
    Route::put('/kod-akses/{code}',[\App\Http\Controllers\AccessCodeController::class,'save'])->whereNumber('code')->name('codes.update');
    Route::get('/sesi/muat-turun/{format}',[\App\Http\Controllers\AccessCodeController::class,'export'])->whereIn('format',['xlsx','pdf'])->name('sessions.export');
    Route::get('/sesi',[\App\Http\Controllers\AccessCodeController::class,'results'])->name('sessions');
    Route::get('/studio/{module}',[\App\Http\Controllers\StudioController::class,'index'])->name('studio.index');
    Route::get('/studio/{module}/baharu',[\App\Http\Controllers\StudioController::class,'form'])->name('studio.create');
    Route::post('/studio/{module}',[\App\Http\Controllers\StudioController::class,'save'])->name('studio.store');
    Route::get('/studio/{module}/{id}',[\App\Http\Controllers\StudioController::class,'form'])->whereNumber('id')->name('studio.edit');
    Route::put('/studio/{module}/{id}',[\App\Http\Controllers\StudioController::class,'save'])->whereNumber('id')->name('studio.update');
    Route::delete('/studio/{module}/{id}',[\App\Http\Controllers\StudioController::class,'delete'])->whereNumber('id')->name('studio.delete');
    Route::post('/terbit',[\App\Http\Controllers\StudioController::class,'publish'])->block(15,15)->name('publish');
    Route::match(['get','post'],'/pratonton/aktiviti/{activity}',[\App\Http\Controllers\StudioController::class,'preview'])->whereNumber('activity')->name('activity.preview');
    foreach(['contents'=>['kandungan','notes'],'notes'=>['nota','notes'],'quizzes'=>['kuiz','activities'],'questions'=>['soalan','questions'],'instructions'=>['arahan','instructions'],'audio'=>['audio','audio'],'media'=>['media','media']] as $name=>[$path,$module]) {
        Route::get('/'.$path,[\App\Http\Controllers\StudioController::class,'index'])->defaults('module',$module)->name($name);
    }
    Route::get('/kuiz/baharu',[\App\Http\Controllers\StudioController::class,'form'])->defaults('module','activities')->name('quizzes.create');
    Route::view('/profil', 'admin.profile')->name('profile');
    Route::view('/tetapan', 'admin.settings')->name('settings');
    Route::get('/pengguna',fn()=>view('admin.users',['users'=>DB::table('users')->whereNull('deleted_at')->select('id','display_name','username','role','status','last_login_at')->paginate(20)]))->name('users');
    Route::post('/pengguna/{user}/status',function(int $user){
        DB::transaction(function()use($user){$row=DB::table('users')->where('role','pelajar')->whereNull('deleted_at')->lockForUpdate()->find($user);abort_unless($row,404);$status=$row->status==='active'?'inactive':'active';DB::table('users')->where('id',$user)->update(['status'=>$status,'updated_at'=>now()]);\App\Services\Audit::record('status','users',$user,['status'=>$status]);});
        return back()->with('status','Status Pelajar dikemas kini.');
    })->whereNumber('user')->name('user.status');
    Route::get('/laporan',fn()=>view('admin.reports',['journeys'=>DB::table('student_journeys')->leftJoin('users','users.id','=','student_journeys.user_id')->leftJoin('learner_sessions','learner_sessions.id','=','student_journeys.learner_session_id')->select('student_journeys.*')->selectRaw("COALESCE(learner_sessions.display_name,users.display_name) as display_name")->latest('student_journeys.id')->paginate(15,['*'],'perjalanan'),'progress'=>DB::table('student_progress')->join('student_journeys','student_journeys.id','=','student_progress.student_journey_id')->leftJoin('users','users.id','=','student_journeys.user_id')->leftJoin('learner_sessions','learner_sessions.id','=','student_journeys.learner_session_id')->join('zones','zones.id','=','student_progress.zone_id')->select('student_progress.*','zones.name')->selectRaw("COALESCE(learner_sessions.display_name,users.display_name) as display_name")->orderBy('users.id')->paginate(30)]))->name('reports');
    Route::get('/pratonton/zon-3', [\App\Http\Controllers\StudioController::class,'zonePreview'])->name('zone3.preview');
    Route::get('/audit', fn () => view('admin.audit', ['logs' => DB::table('audit_logs')->leftJoin('users','users.id','=','audit_logs.admin_user_id')->select('audit_logs.*','users.display_name')->orderByDesc('audit_logs.id')->paginate(20)]))->name('audit');
});

Route::get('/media/{media}',[\App\Http\Controllers\MediaController::class,'show'])->middleware([\App\Http\Middleware\RequireMediaIdentity::class])->whereNumber('media')->name('media.show');
