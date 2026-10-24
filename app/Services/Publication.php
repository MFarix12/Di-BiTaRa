<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Publication
{
    public function activity(object $a): array
    {
        $instruction = $a->instruction_id ? DB::table('instructions')->whereNull('deleted_at')->find($a->instruction_id) : null;

        return ['id' => $a->id, 'zone_id' => $a->zone_id, 'title' => $a->title, 'kind' => $a->kind, 'passing_percent' => (float) $a->passing_percent,
            'is_required' => (bool) $a->is_required, 'settings' => json_decode($a->settings, true) ?: [],
            'instruction' => $instruction?->body, 'instruction_audio_id' => $instruction ? DB::table('instruction_audio')->where('instruction_id', $instruction->id)->value('media_file_id') : null,
            'questions' => DB::table('questions')->where('activity_id', $a->id)->where('status', 'published')->whereNull('deleted_at')->orderBy('sort_order')->orderBy('id')->get()->map(fn ($q) => app(QuestionBank::class)->snapshot($q))->all()];
    }

    public function publish(int $admin, int $year = 4): int
    {
        return DB::transaction(function () use ($admin, $year) {
            abort_unless(in_array($year,[4,5,6],true),422);
            $manifest = ['school_year'=>$year,'zones' => []];
            $bank = app(QuestionBank::class);
            foreach (DB::table('zones')->orderBy('sequence')->get() as $zone) {
                $notes = DB::table('learning_contents')->where('zone_id', $zone->id)->where('status', 'published')->whereNull('deleted_at')->orderBy('sort_order')->get()->map(function ($n) {
                    return ['id' => $n->id, 'title' => $n->title, 'body' => $n->body, 'is_required' => (bool) $n->is_required, 'media_ids' => DB::table('content_media')->where('learning_content_id', $n->id)->pluck('media_file_id')->all()];
                })->all();
                $activities = DB::table('activities')->where('school_year',$year)->where('zone_id', $zone->id)->where('status', 'published')->whereNull('deleted_at')->orderBy('sort_order')->get()->map(fn ($a) => $this->activity($a))->all();
                if (($zone->sequence === 2 && ! $notes) || (in_array($zone->sequence, [3, 4, 5], true) && ! $activities)) {
                    throw ValidationException::withMessages(['publication' => 'Zon 2 memerlukan nota. Zon 3 hingga 5 memerlukan aktiviti diterbitkan.']);
                }
                if (($zone->sequence === 1 && ($notes || $activities)) || ($zone->sequence === 2 && $activities)) {
                    throw ValidationException::withMessages(['publication' => 'Zon 1 hanya persediaan profil/avatar. Zon 2 menggunakan nota dan audio tanpa kuiz.']);
                }
                foreach ($activities as $a) {
                    if ($a['kind'] === 'quiz' && ! in_array($zone->sequence, ZoneStructure::QUIZ, true)) {
                        throw ValidationException::withMessages(['publication' => 'Kuiz hanya dibenarkan di Zon 3 dan Zon 4.']);
                    }
                    if (! $a['questions']) {
                        throw ValidationException::withMessages(['publication' => 'Aktiviti '.$a['title'].' belum mempunyai soalan diterbitkan.']);
                    }
                    foreach ($a['questions'] as $q) {
                        $bank->validate($q, $zone->sequence);
                    }
                }
                if ($zone->sequence !== 1 && ! collect($notes)->where('is_required', true)->count() && ! collect($activities)->where('is_required', true)->count()) {
                    throw ValidationException::withMessages(['publication' => 'Zon '.$zone->sequence.' mesti mempunyai unit pembelajaran wajib.']);
                }
                $requiredTypes = match ($zone->sequence) {
                    3 => ['true_false', 'drag_drop'],4 => ['sentence_order', 'fill_blank'],5 => ['dictation'],default => []
                };
                $actualTypes = collect($activities)->where('is_required', true)->flatMap(fn ($a) => array_column($a['questions'], 'type'))->unique()->all();
                if (array_diff($requiredTypes, $actualTypes)) {
                    throw ValidationException::withMessages(['publication' => 'Lengkapkan semua jenis aktiviti wajib untuk Zon '.$zone->sequence.'.']);
                }
                $manifest['zones'][] = ['id' => $zone->id, 'sequence' => $zone->sequence, 'name' => $zone->name, 'stamp_title' => $zone->stamp_title, 'notes' => $notes, 'activities' => $activities];
            }
            $mediaIds = [];
            foreach ($manifest['zones'] as $z) {
                foreach ($z['notes'] as $n) {
                    $mediaIds = array_merge($mediaIds, $n['media_ids']);
                }
                foreach ($z['activities'] as $a) {
                    if ($a['instruction_audio_id']) {
                        $mediaIds[] = $a['instruction_audio_id'];
                    } foreach ($a['questions'] as $q) {
                        if ($q['audio_id']) {
                            $mediaIds[] = $q['audio_id'];
                        }
                    }
                }
            }
            $manifest['media_ids'] = array_values(array_unique($mediaIds));
            foreach ($manifest['media_ids'] as $id) {
                $m = DB::table('media_files')->whereNull('deleted_at')->where('status', 'ready')->find($id);
                if (! $m || ! Storage::disk($m->disk)->exists($m->path)) {
                    throw ValidationException::withMessages(['publication' => 'Fail media #'.$id.' tidak sedia atau tiada.']);
                }
            }
            $manifest = ZoneStructure::normalize($manifest);
            $json = json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $id = DB::table('learning_releases')->insertGetId(['school_year'=>$year,'version' => 'R-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6)), 'title' => 'Perjalanan Di-BITaRa', 'status' => 'published', 'manifest' => $json, 'scoring_policy' => json_encode(['version' => 1, 'default_pass' => 80]), 'checksum' => hash('sha256', $json), 'published_at' => now(), 'published_by' => $admin]);
            Audit::record('publish', 'learning_releases', $id, ['zones' => 5,'school_year'=>$year]);

            return $id;
        });
    }
}
