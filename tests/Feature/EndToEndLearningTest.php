<?php

namespace Tests\Feature;

use App\Http\Controllers\StudioController;
use App\Models\User;
use App\Services\CertificatePdf;
use App\Services\LearningEngine;
use App\Services\QuestionBank;
use App\Services\StarterCurriculum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndLearningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        // Preserve coverage for historical version-one journeys; version two has its own end-to-end suite.
        config(['learning.flow_version'=>1]);
        $this->seed();
        Storage::fake('private');
        for ($i = 1; $i <= 5; $i++) {
            Storage::disk('private')->put('starter-audio/imlak-'.$i.'.mp3', 'test-audio');
        }
        $this->admin = User::factory()->create(['role' => 'admin']);
        $media = DB::table('media_files')->insertGetId(['kind'=>'image','title'=>'Avatar ujian','disk'=>'brand','path'=>'images/pilot-1.svg','original_name'=>'pilot-1.svg','mime_type'=>'image/svg+xml','size_bytes'=>1,'checksum'=>str_repeat('a',64),'status'=>'ready','created_by'=>$this->admin->id]);
        $avatar = DB::table('avatars')->insertGetId(['code'=>'pilot-1','name'=>'Kapten Awan','media_file_id'=>$media]);
        $this->student = User::factory()->create();
        DB::table('student_profiles')->insert(['user_id' => $this->student->id, 'onboarding_status' => 'completed', 'avatar_id' => $avatar, 'avatar_confirmed_at'=>now()]);
        $this->actingAs($this->admin,'web');
        app(StarterCurriculum::class)->install($this->admin->id);
        $this->actingAs($this->student);
    }

    private function startZone(int $sequence): void
    {
        if (! in_array($sequence, [3,4,5], true)) {
            $this->get(route('student.zone.launch', $sequence))->assertRedirect();
            $this->post(route('student.zone.start', $sequence))->assertRedirect();
            return;
        }
        $this->get(route('student.zone.launch', $sequence))->assertOk();
        $this->post(route('student.zone.prepare',$sequence))->assertOk()->assertSee('data-seconds="3"',false);
        $this->travel(3)->seconds();
        $this->post(route('student.zone.start', $sequence))->assertRedirect();
    }

    private function completeNotes(int $sequence): void
    {
        $engine = app(LearningEngine::class);
        $j = $engine->journey($this->student);
        foreach ($engine->zone($j, $sequence)['notes'] as $note) {
            $engine->readNote($this->student, $sequence, $note['id']);
        }
    }

    private function openZoneThree(): void
    {
        $this->completeNotes(1);
        $this->startZone(2);
        $this->completeNotes(2);
        $this->startZone(3);
    }

    private function finishActivity(int $activity): int
    {
        $response = $this->post(route('student.activity.begin', $activity))->assertRedirect();
        $id = (int) basename($response->headers->get('Location'));
        foreach (DB::table('attempt_questions')->where('attempt_id', $id)->orderBy('position')->get() as $q) {
            $answer = json_decode($q->grading_snapshot, true)['answer']['value'];
            $this->post(route('student.answer', $id), ['question_id' => $q->id, 'answer' => $answer, 'score' => 9999, 'passed' => true])->assertRedirect();
        }
        $this->get(route('student.attempt', $id))->assertOk()->assertSee('Misi Berjaya');

        return $id;
    }

    public function test_new_curriculum_activation_preserves_historical_times_attempts_and_release_snapshots(): void
    {
        $latest=DB::table('learning_releases')->first();$row=(array)$latest;unset($row['id']);$row['version']='legacy-test';$row['title']='Keluaran lama';
        $oldRelease=DB::table('learning_releases')->insertGetId($row);
        $oldId=DB::table('student_journeys')->insertGetId(['user_id'=>$this->student->id,'learning_release_id'=>$oldRelease,'total_duration_seconds'=>888]);
        $oldJourney=DB::table('student_journeys')->find($oldId);$engine=app(LearningEngine::class);$engine->refresh($oldJourney);
        DB::table('student_journeys')->where('id',$oldId)->update(['total_duration_seconds'=>888]);
        $oldZone=$engine->zone($oldJourney,2);DB::table('student_progress')->where('student_journey_id',$oldId)->where('zone_id',$oldZone['id'])->update(['duration_seconds'=>132]);
        $a=$engine->zone($oldJourney,3)['activities'][0];$attempt=DB::table('attempts')->insertGetId(['student_journey_id'=>$oldId,'activity_id'=>$a['id'],'attempt_no'=>1,'activity_snapshot'=>json_encode($a+['sequence'=>3]),'max_score'=>60]);
        app(StarterCurriculum::class)->activateForStudents($latest->id);app(StarterCurriculum::class)->activateForStudents($latest->id);
        $this->assertDatabaseHas('student_journeys',['id'=>$oldId,'status'=>'archived','total_duration_seconds'=>888]);
        $this->assertDatabaseHas('student_progress',['student_journey_id'=>$oldId,'zone_id'=>$oldZone['id'],'duration_seconds'=>132]);
        $this->assertDatabaseHas('attempts',['id'=>$attempt]);$this->assertSame($latest->manifest,DB::table('learning_releases')->find($oldRelease)->manifest);
        $this->assertSame(1,DB::table('student_journeys')->where('user_id',$this->student->id)->where('learning_release_id',$latest->id)->count());
        $this->assertSame($latest->id,$engine->journey($this->student)->learning_release_id);
    }

    public function test_admin_cannot_restore_quizzes_outside_zone_three_and_four(): void
    {
        $this->actingAs($this->admin,'web');
        $data=['title'=>'Kuiz tersalah zon','school_year'=>4,'kind'=>'quiz','passing_percent'=>80,'minutes'=>5,'status'=>'published'];
        foreach ([1,2,5] as $sequence) {
            $this->post(route('admin.studio.store','activities'),$data+['zone_id'=>DB::table('zones')->where('sequence',$sequence)->value('id')])->assertSessionHasErrors($sequence===5?'kind':'zone_id');
        }
        $this->assertSame(0,DB::table('activities')->where('title','Kuiz tersalah zon')->count());
    }

    public function test_admin_crud_publication_snapshot_and_preview_do_not_change_student_work(): void
    {
        $engine = app(LearningEngine::class);
        $j = $engine->journey($this->student);
        $before = $engine->manifest($j);
        $a = $before['zones'][2]['activities'][0];
        $this->actingAs($this->admin,'web');
        foreach (['notes', 'activities', 'questions', 'instructions', 'audio', 'media'] as $module) {
            $this->get(route('admin.studio.index', $module))->assertOk();
            $this->get(route('admin.studio.create', $module))->assertOk();
        }
        $this->post(route('admin.studio.store', 'notes'), ['title' => 'Nota Tambahan', 'body' => 'Nota khas yang selamat.', 'zone_id' => $before['zones'][1]['id'], 'status' => 'draft', 'sort_order' => 3])->assertRedirect();
        $note = DB::table('learning_contents')->where('title', 'Nota Tambahan')->first();
        $this->put(route('admin.studio.update', ['module' => 'notes', 'id' => $note->id]), ['title' => 'Nota Disunting', 'body' => 'Kandungan baharu.', 'zone_id' => $note->zone_id, 'status' => 'published', 'sort_order' => 3, 'revision' => 1])->assertRedirect();
        $this->get(route('admin.studio.edit', ['module' => 'notes', 'id' => $note->id]))->assertOk()->assertSee('Nota Disunting');
        $this->post(route('admin.publish'),['school_year'=>4])->assertRedirect();
        $this->assertDatabaseCount('learning_releases', 2);
        $this->assertSame($before, $engine->manifest($j));
        $this->get(route('admin.activity.preview', $a['id']))->assertOk()->assertSee('Mod Pratonton Pentadbir');
        $this->post(route('admin.activity.preview', $a['id']), ['answer' => 'betul'])->assertOk()->assertSee('Jawapan betul');
        $this->assertDatabaseCount('attempts', 0);
        $this->delete(route('admin.studio.delete', ['module' => 'notes', 'id' => $note->id]))->assertRedirect();
        $this->assertNotNull(DB::table('learning_contents')->find($note->id)->deleted_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'delete', 'entity_id' => $note->id]);
        $this->actingAs($this->student,'web')->post(route('admin.studio.store', 'notes'), [])->assertForbidden();
    }

    public function test_admin_question_editor_validates_zone_rules_and_upload_rejects_executable(): void
    {
        $a = DB::table('activities')->where('zone_id', DB::table('zones')->where('sequence', 4)->value('id'))->first();
        $this->actingAs($this->admin,'web');
        $data = ['activity_id' => $a->id, 'prompt' => 'Ayat tambahan: Roti itu ____.', 'type' => 'fill_blank', 'max_marks' => 10, 'correct' => 'dimakan', 'require_copy'=>1,'blank_choices'=>"dimakan\ndi makan",'full_sentence'=>'Roti itu dimakan.', 'status' => 'published', 'sort_order' => 3];
        $this->post(route('admin.studio.store', 'questions'), $data)->assertRedirect();
        $q = DB::table('questions')->where('prompt', $data['prompt'])->first();
        $this->assertNotNull($q);
        $this->get(route('admin.studio.edit', ['module' => 'questions', 'id' => $q->id]))->assertOk()->assertSee('dimakan');
        $this->post(route('admin.studio.store', 'questions'), array_replace($data, ['type' => 'true_false', 'correct' => 'betul']))->assertSessionHasErrors('question');
        $this->post(route('admin.studio.store', 'audio'), ['title' => 'Fail jahat', 'status' => 'published', 'file' => UploadedFile::fake()->create('jahat.php', 10, 'application/x-httpd-php')])->assertSessionHasErrors('file');
        DB::table('questions')->where('type', 'sentence_order')->update(['status' => 'draft']);
        $this->post(route('admin.publish'),['school_year'=>4])->assertSessionHasErrors('publication');
        $this->assertDatabaseCount('learning_releases', 1);
    }

    public function test_all_studio_content_modules_save_update_delete_and_keep_media_versions(): void
    {
        $this->actingAs($this->admin,'web');
        $zone = DB::table('zones')->where('sequence', 3)->value('id');
        foreach (['instructions' => ['title' => 'Arahan Ujian', 'body' => 'Baca dengan teliti.', 'status' => 'published'],
            'activities' => ['title' => 'Aktiviti Ujian', 'zone_id' => $zone, 'school_year'=>4,'kind' => 'activity', 'passing_percent' => 80, 'minutes' => 5, 'status' => 'draft', 'sort_order' => 0],
            'audio' => ['title' => 'Audio Ujian', 'status' => 'published', 'file' => UploadedFile::fake()->createWithContent('arahan.mp3', file_get_contents(resource_path('audio/imlak-1.mp3')))],
            'media' => ['title' => 'Imej Ujian', 'status' => 'published', 'file' => UploadedFile::fake()->createWithContent('imej.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='))],
        ] as $module => $data) {
            $this->post(route('admin.studio.store', $module), $data)->assertSessionHasNoErrors()->assertRedirect();
            $table = StudioController::MODULES[$module][0];
            $row = DB::table($table)->where('title', $data['title'])->first();
            $this->assertNotNull($row);
            $this->get(route('admin.studio.edit', ['module' => $module, 'id' => $row->id]))->assertOk();
            $edit = $data;
            unset($edit['file']);
            $edit['revision'] = $row->revision ?? 1;
            $edit['title'] .= ' Disunting';
            $this->put(route('admin.studio.update', ['module' => $module, 'id' => $row->id]), $edit)->assertSessionHasNoErrors()->assertRedirect();
            $this->assertDatabaseHas($table, ['id' => $row->id, 'title' => $edit['title']]);
            if ($module === 'audio') {
                $replacement = $edit + ['file' => UploadedFile::fake()->createWithContent('baharu.mp3', file_get_contents(resource_path('audio/imlak-2.mp3')))];
                $this->put(route('admin.studio.update', ['module' => $module, 'id' => $row->id]), $replacement)->assertSessionHasNoErrors()->assertRedirect();
                $new = DB::table('media_files')->where('parent_media_id', $row->id)->first();
                $this->assertNotNull($new);
                $this->assertNotSame($row->path, $new->path);
                Storage::disk('private')->assertExists($row->path);
            }
            $this->delete(route('admin.studio.delete', ['module' => $module, 'id' => $row->id]))->assertRedirect();
            $this->assertNotNull(DB::table($table)->find($row->id)->deleted_at);
        }
    }

    public function test_text_grading_preserves_di_word_boundaries_and_order_is_exact(): void
    {
        $bank = app(QuestionBank::class);
        $q = ['type' => 'dictation', 'answer' => ['value' => 'Roti itu dimakan di kantin.']];
        $this->assertTrue($bank->grade($q, '  ROTI itu dimakan di kantin  '));
        $this->assertFalse($bank->grade($q, 'Roti itu di makan dikantin.'));
        $this->assertFalse($bank->grade($q, ['Roti itu dimakan di kantin.']));
        $this->assertFalse($bank->grade(['type' => 'sentence_order', 'answer' => ['value' => ['i3', 'i1', 'i4', 'i2']]], ['i3', 'i1', 'i2', 'i4']));
        $this->assertFalse($bank->grade(['type' => 'drag_drop', 'answer' => ['value' => ['i1' => 'imbuhan', 'i2' => 'sendi']]], ['i1' => 'imbuhan']));
    }

    public function test_latest_pdf_words_orders_and_dictation_are_exact_and_install_is_repeatable(): void
    {
        $release=app(StarterCurriculum::class)->install($this->admin->id);$this->assertDatabaseCount('learning_releases',1);
        $engine=app(LearningEngine::class);$j=$engine->journey($this->student);$zones=$engine->manifest($j)['zones'];
        $this->assertCount(0,$zones[0]['notes']);$this->assertCount(6,$zones[2]['activities'][0]['questions']);$this->assertCount(6,$zones[2]['activities'][1]['questions']);
        $this->assertSame(['dijawab','di dapur','dimasak','di padang','dicuci','di kantin','ditutup','di kedai','dipukul','di bilik'],array_values($zones[2]['activities'][2]['questions'][0]['config']['items']));
        $this->assertSame(['i3','i4','i1','i2','i5'],$zones[3]['activities'][0]['questions'][0]['answer']['value']);
        $this->assertSame('Bagai dihiris sembilu.',$zones[3]['activities'][0]['questions'][1]['answer']['sentence']);
        $this->assertSame(['di rumah','bagai dihiris sembilu','ukur baju di badan sendiri'],array_map(fn($q)=>$q['answer']['value'],$zones[4]['activities'][0]['questions']));
        foreach ([1,2] as $n) { $this->assertFalse($zones[$n-1]['has_countdown']);$this->assertFalse($zones[$n-1]['has_timer']); }
        foreach ([3,4,5] as $n) { $this->assertTrue($zones[$n-1]['has_countdown']);$this->assertTrue($zones[$n-1]['has_timer']); }
    }

    public function test_copy_sentence_requires_both_correct_spelling_and_full_sentence_and_admin_can_edit_it(): void
    {
        $engine=app(LearningEngine::class);$j=$engine->journey($this->student);$q=$engine->zone($j,4)['activities'][1]['questions'][0];$bank=app(\App\Services\QuestionBank::class);
        $this->assertFalse($bank->grade($q,'ditulis'));
        $this->assertFalse($bank->grade($q,['blank'=>'ditulis','sentence'=>'Buku itu di tulis oleh saya.']));
        $this->assertFalse($bank->grade($q,['blank'=>'di tulis','sentence'=>'Buku itu ditulis oleh saya.']));
        $this->assertTrue($bank->grade($q,['blank'=>'ditulis','sentence'=>'Buku itu ditulis oleh saya.']));
        $row=DB::table('questions')->find($q['id']);
        $this->actingAs($this->admin,'web')->get(route('admin.studio.edit',['module'=>'questions','id'=>$q['id']]))->assertOk()->assertSee('Ayat lengkap yang betul');
        $this->put(route('admin.studio.update',['module'=>'questions','id'=>$q['id']]),['activity_id'=>$row->activity_id,'prompt'=>$row->prompt,'type'=>'fill_blank','max_marks'=>10,'correct'=>'ditulis','require_copy'=>1,'blank_choices'=>"ditulis\ndi tulis",'full_sentence'=>'Buku itu ditulis oleh saya.','status'=>'published','revision'=>$row->revision,'sort_order'=>1])->assertRedirect()->assertSessionHasNoErrors();
        $updated=$bank->snapshot(DB::table('questions')->find($q['id']));$this->assertTrue($bank->grade($updated,['blank'=>'ditulis','sentence'=>'Buku itu ditulis oleh saya.']));
    }

}
