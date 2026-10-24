<?php

namespace App\Http\Controllers;

use App\Services\Audit;
use App\Services\Publication;
use App\Services\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudioController extends Controller
{
    public function zonePreview(Request $r)
    {
        $data=$r->validate(['zon'=>'nullable|integer|min:1|max:5']);
        $release=DB::table('learning_releases')->where('status','published')->latest('id')->first();
        $zones=$release ? \App\Services\ZoneStructure::normalize(json_decode($release->manifest,true))['zones'] : [];
        return view('admin.zone-preview',['zones'=>$zones,'sequence'=>(int)($data['zon'] ?? 1)]);
    }
    public const MODULES = ['notes' => ['learning_contents', 'Nota'], 'activities' => ['activities', 'Kuiz & Aktiviti'], 'questions' => ['questions', 'Soalan & Jawapan'], 'instructions' => ['instructions', 'Arahan'], 'audio' => ['media_files', 'Audio'], 'media' => ['media_files', 'Media']];

    private function definition(string $module): array
    {
        abort_unless(isset(self::MODULES[$module]), 404);

        return self::MODULES[$module];
    }

    public function index(Request $r, string $module)
    {
        [$table,$title] = $this->definition($module);
        $rows = DB::table($table)->whereNull('deleted_at')->when($module === 'audio', fn ($q) => $q->where('kind', 'audio'))->when($module === 'media', fn ($q) => $q->where('kind', 'image'))->when($r->filled('q'), fn ($q) => $q->where($table === 'questions' ? 'prompt' : 'title', 'like', '%'.$r->string('q').'%'))->latest('id')->paginate(15)->withQueryString();

        return view('admin.studio-index', compact('module', 'title', 'rows') + ['releases' => DB::table('learning_releases')->latest('id')->limit(5)->get()]);
    }

    public function form(Request $r, string $module, ?int $id = null)
    {
        [$table,$title] = $this->definition($module);
        $row = $id ? DB::table($table)->whereNull('deleted_at')->find($id) : null;
        abort_if($id && ! $row, 404);
        if ($row && in_array($module, ['audio', 'media'])) {
            abort_unless($row->kind === ($module === 'audio' ? 'audio' : 'image'), 404);
        }
        $q = $module === 'questions' && $row ? app(QuestionBank::class)->snapshot($row) : null;

        return view('admin.studio-form', compact('module', 'title', 'row', 'q') + ['zones' => DB::table('zones')->when($module === 'activities', fn ($query) => $query->whereIn('sequence', [3,4,5]))->orderBy('sequence')->get(), 'activities' => DB::table('activities')->whereNull('deleted_at')->get(), 'instructions' => DB::table('instructions')->whereNull('deleted_at')->get(), 'audios' => DB::table('media_files')->where('kind', 'audio')->whereNull('deleted_at')->where('status', 'ready')->get(), 'images' => DB::table('media_files')->where('kind', 'image')->where('disk', 'private')->whereNull('deleted_at')->where('status', 'ready')->get()]);
    }

    public function save(Request $r, string $module, ?int $id = null)
    {
        [$table] = $this->definition($module);

        return DB::transaction(function () use ($r, $module, $id, $table) {
            $before = $id ? DB::table($table)->whereNull('deleted_at')->lockForUpdate()->find($id) : null;
            abort_if($id && ! $before, 404);
            if ($before && isset($before->revision) && (int) $r->input('revision') !== $before->revision) {
                throw ValidationException::withMessages(['revision' => 'Kandungan telah berubah. Buka semula versi terkini sebelum menyimpan.']);
            }
            $data = $r->validate(['title' => ($module === 'questions' ? 'nullable' : 'required').'|string|max:180', 'status' => 'required|in:draft,published,inactive', 'sort_order' => 'nullable|integer|min:0|max:65535']);
            if (in_array($module, ['notes', 'activities'])) {
                $data += $r->validate(['zone_id' => 'required|integer|exists:zones,id', 'instruction_id' => 'nullable|integer|exists:instructions,id']);
            }
            if (in_array($module, ['notes', 'instructions'])) {
                $data += $r->validate(['body' => 'required|string|max:20000']);
                $data['body'] = strip_tags($data['body']);
            }
            if ($module === 'instructions') {
                unset($data['sort_order']);
            }
            if ($module === 'notes') {
                $data['kind'] = 'note';
                $data['is_required'] = (int) DB::table('zones')->where('id', $data['zone_id'])->value('sequence') === 1 ? false : $r->boolean('is_required');
            }
            if ($module === 'activities') {
                $data += $r->validate(['school_year'=>'required|integer|in:4,5,6','kind' => 'required|in:quiz,activity', 'passing_percent' => 'required|numeric|between:1,100']);
                $sequence = (int) DB::table('zones')->where('id', $data['zone_id'])->value('sequence');
                if (in_array($sequence, [1, 2], true)) {
                    throw ValidationException::withMessages(['zone_id' => 'Zon 1 hanya persediaan profil/avatar. Zon 2 menggunakan nota dan audio sahaja.']);
                }
                if ($data['kind'] === 'quiz' && ! in_array($sequence, [3, 4], true)) {
                    throw ValidationException::withMessages(['kind' => 'Kuiz hanya dibenarkan di Zon 3 dan Zon 4.']);
                }
                $data['passing_percent'] = 100;
                $data['is_required'] = $r->boolean('is_required');
                $settings = $r->validate(['description' => 'nullable|string|max:1000', 'minutes' => 'required|integer|between:1,120']);
                $data['settings'] = json_encode($settings, JSON_UNESCAPED_UNICODE);
            }
            $question = null;
            if ($module === 'questions') {
                unset($data['title']);
                $data += $r->validate(['activity_id' => 'required|integer|exists:activities,id', 'prompt' => 'required|string|max:5000', 'type' => 'required|in:'.implode(',', array_keys(QuestionBank::TYPES)), 'max_marks' => 'required|integer|between:1,1000', 'feedback_correct' => 'nullable|string|max:2000', 'feedback_incorrect' => 'nullable|string|max:2000', 'items' => 'nullable|array|max:30', 'items.*' => 'nullable|string|max:255', 'mappings' => 'nullable|array|max:30', 'mappings.*' => 'nullable|in:imbuhan,sendi', 'correct' => 'nullable|string|max:5000', 'options_text' => 'nullable|string|max:5000', 'order' => 'nullable|string|max:200', 'audio_id' => 'nullable|integer|exists:media_files,id','require_copy'=>'nullable|boolean','blank_choices'=>'nullable|string|max:2000','full_sentence'=>'nullable|string|max:1000','sentence'=>'required_if:type,sentence_order|nullable|string|max:1000','is_proverb'=>'nullable|boolean','meaning'=>'required_if:is_proverb,1|nullable|string|max:2000']);
                $options = [];
                $config = [];
                $answer = $r->input('correct');
                if ($data['type'] === 'true_false') {
                    $options = ['betul' => 'Betul', 'salah' => 'Salah'];
                }
                if ($data['type'] === 'objective') {
                    foreach (preg_split('/\R/u', trim($r->input('options_text', ''))) as $i => $text) {
                        if (trim($text) !== '') {
                            $options[(string) ($i + 1)] = trim($text);
                        }
                    }
                }
                if (in_array($data['type'], ['drag_drop', 'sentence_order'])) {
                    $items = [];
                    $mapping = [];
                    foreach ($r->input('items', []) as $i => $text) {
                        if (trim((string) $text) !== '') {
                            $key = 'i'.($i + 1);
                            $items[$key] = trim($text);
                            $mapping[$key] = $r->input('mappings.'.$i, 'imbuhan');
                        }
                    }
                    $config = ['items' => $items];
                    if ($data['type'] === 'drag_drop') {
                        $config['targets'] = ['imbuhan' => 'Imbuhan di-', 'sendi' => 'Kata Sendi di'];
                        $answer = $mapping;
                    } else {
                        $answer = array_map(fn ($n) => 'i'.trim($n), explode(',', $r->input('order', '')));
                    }
                }
                if ($data['type']==='fill_blank' && $r->boolean('require_copy')) {
                    $config=['require_copy'=>true,'choices'=>array_values(array_filter(array_map('trim',preg_split('/\R/u',$r->input('blank_choices','')))))];
                    $answer=['blank'=>$r->input('correct'),'sentence'=>$r->input('full_sentence')];
                }
                if ($data['type']==='dictation') { $config['max_audio_plays']=3; }
                $question = ['prompt' => $data['prompt'], 'type' => $data['type'], 'max_marks' => (int) $data['max_marks'], 'config' => $config, 'options' => collect($options)->map(fn ($text, $key) => ['key' => (string) $key, 'text' => $text])->values()->all(), 'answer' => ['value' => $answer], 'audio_id' => $r->input('audio_id') ? (int) $r->input('audio_id') : null];
                if ($data['type']==='sentence_order') {
                    $question['answer']['sentence']=$r->input('sentence');
                    $question['answer']['meaning']=$r->input('meaning');
                    $question['config']['is_proverb']=$r->boolean('is_proverb');
                }
                $zoneId = DB::table('activities')->whereNull('deleted_at')->where('id', $data['activity_id'])->value('zone_id');
                abort_unless($zoneId, 422);
                if ($data['status'] === 'published') {
                    app(QuestionBank::class)->validate($question, (int) DB::table('zones')->where('id', $zoneId)->value('sequence'));
                }
                $data['public_config'] = json_encode($config, JSON_UNESCAPED_UNICODE);
                foreach (['items', 'mappings', 'correct', 'options_text', 'order', 'audio_id','require_copy','blank_choices','full_sentence','sentence','meaning','is_proverb'] as $key) {
                    unset($data[$key]);
                }
            }
            if (in_array($module, ['audio', 'media'])) {
                $r->validate(['file' => ($id ? 'nullable' : 'required').'|file|max:10240|mimetypes:'.($module === 'audio' ? 'audio/mpeg,audio/wav,audio/x-wav,audio/webm,video/webm,audio/ogg,audio/mp4,video/mp4' : 'image/jpeg,image/png,image/webp'), 'alt_text' => 'nullable|string|max:255', 'license_note' => 'nullable|string|max:1000']);
                unset($data['sort_order']);
                $data['kind'] = $module === 'audio' ? 'audio' : 'image';
                $data['status'] = 'ready';
                $data['alt_text'] = $r->input('alt_text');
                $data['license_note'] = $r->input('license_note');
                if ($r->hasFile('file')) {
                    $file = $r->file('file');
                    $bytes = $file->get();
                    $mime = $file->getMimeType();
                    if ($module === 'media' && ! @getimagesize($file->getPathname())) {
                        throw ValidationException::withMessages(['file' => 'Imej tidak sah.']);
                    }
                    $data += ['disk' => 'private', 'path' => $file->store('media', 'private'), 'original_name' => basename($file->getClientOriginalName()), 'mime_type' => $mime, 'size_bytes' => $file->getSize(), 'checksum' => hash('sha256', $bytes), 'created_by' => auth()->id()];
                    // Replacing a file creates a new media record; published releases keep their original bytes.
                    if ($id) {
                        $data['parent_media_id'] = $id;
                        $id = null;
                        $before = null;
                    }
                }
            }
            if (in_array($module, ['notes', 'activities', 'instructions'])) {
                $data['updated_by'] = auth()->id();
                if (! $id) {
                    $data['created_by'] = auth()->id();
                }
            }
            if (isset($before->revision)) {
                $data['revision'] = $before->revision + 1;
            }$data['updated_at'] = now();
            $saved = $id ?? DB::table($table)->insertGetId($data);
            if ($id) {
                DB::table($table)->where('id', $id)->update($data);
            }
            if ($question) {
                DB::table('question_solutions')->updateOrInsert(['question_id' => $saved], ['answer_key' => json_encode($question['answer'], JSON_UNESCAPED_UNICODE), 'normalization' => '{}']);
                DB::table('answer_options')->where('question_id', $saved)->delete();
                foreach ($question['options'] as $o) {
                    DB::table('answer_options')->insert(['question_id' => $saved, 'option_key' => $o['key'], 'text' => $o['text'], 'is_correct' => $o['key'] === ($question['answer']['value'] ?? null)]);
                }
            }
            if (in_array($module, ['questions', 'notes', 'instructions'])) {
                $r->validate(['audio_id' => 'nullable|integer|exists:media_files,id']);
                $audioId = $r->integer('audio_id');
                if ($audioId) {
                    abort_unless(DB::table('media_files')->where('id', $audioId)->where('kind', 'audio')->where('status', 'ready')->whereNull('deleted_at')->exists(), 422);
                }
                $link = match ($module) {
                    'questions' => 'question_audio','notes' => 'content_media','instructions' => 'instruction_audio'
                };
                $fk = match ($module) {
                    'questions' => 'question_id','notes' => 'learning_content_id','instructions' => 'instruction_id'
                };
                DB::table($link)->where($fk, $saved)->when($module === 'notes', fn ($q) => $q->where('purpose', 'audio'))->delete();
                if ($audioId) {
                    DB::table($link)->insert([$fk => $saved, 'media_file_id' => $audioId] + ($module === 'notes' ? ['purpose' => 'audio'] : []));
                }
            }
            if ($module === 'notes') {
                $r->validate(['image_id' => 'nullable|integer|exists:media_files,id']);
                $image = $r->integer('image_id');
                if ($image) {
                    abort_unless(DB::table('media_files')->where('id', $image)->where('kind', 'image')->where('status', 'ready')->whereNull('deleted_at')->exists(), 422);
                }
                DB::table('content_media')->where('learning_content_id', $saved)->where('purpose', 'illustration')->delete();
                if ($image) {
                    DB::table('content_media')->insert(['learning_content_id' => $saved, 'media_file_id' => $image, 'purpose' => 'illustration']);
                }
            }
            Audit::record($before ? 'update' : 'create', $table, $saved, ['title' => $data['title'] ?? $data['prompt'], 'status' => $data['status']]);

            return redirect()->route('admin.studio.index', ['module' => $module])->with('status', 'Disimpan. Terbitkan keluaran baharu untuk menjadikannya tersedia kepada perjalanan baharu.');
        });
    }

    public function delete(Request $r, string $module, int $id)
    {
        [$table] = $this->definition($module);
        DB::transaction(function () use ($table, $id) {
            $row = DB::table($table)->whereNull('deleted_at')->lockForUpdate()->find($id);
            abort_unless($row, 404);
            DB::table($table)->where('id', $id)->update(['deleted_at' => now(), 'status' => 'inactive', 'updated_at' => now()]);
            Audit::record('delete', $table, $id, ['title' => $row->title ?? $row->prompt]);
        });

        return redirect()->route('admin.studio.index', ['module' => $module])->with('status', 'Kandungan dipadam daripada pengurusan kandungan. Salinan keluaran dan sejarah Pelajar dikekalkan.');
    }

    public function publish(Request $r, Publication $publication)
    {
        $data=$r->validate(['school_year'=>'required|integer|in:4,5,6']);
        $publication->publish($r->user()->id,(int)$data['school_year']);

        return back()->with('status', 'Keluaran baharu diterbitkan. Perjalanan sedia ada mengekalkan versi masing-masing.');
    }

    public function preview(Request $r, int $activity, Publication $publication)
    {
        $a = DB::table('activities')->whereNull('deleted_at')->find($activity);
        abort_unless($a, 404);
        $snapshot = $publication->activity($a);
        $snapshot['sequence'] = (int) DB::table('zones')->where('id', $a->zone_id)->value('sequence');
        // Include draft questions in the Admin-only preview.
        $snapshot['questions'] = DB::table('questions')->where('activity_id', $a->id)->whereNull('deleted_at')->orderBy('sort_order')->orderBy('id')->get()->map(fn ($q) => app(QuestionBank::class)->snapshot($q))->all();
        $position = max(0, min(count($snapshot['questions']) - 1, (int) $r->query('soalan', 0)));
        $raw = $snapshot['questions'][$position] ?? null;
        $feedback = null;
        if ($r->isMethod('post') && $raw) {
            $feedback = app(QuestionBank::class)->grade($raw,$r->input('answer')) ? 'Jawapan betul.' : 'Belum tepat. Jawapan: '.json_encode($raw['answer']['value'],JSON_UNESCAPED_UNICODE);
        }

        return view('student.mission',['activity' => $snapshot, 'question' => $raw ? app(QuestionBank::class)->publicQuestion($raw) : null, 'preview' => true, 'position' => $position, 'feedback' => $feedback, 'previewId' => $activity]);
    }
}
