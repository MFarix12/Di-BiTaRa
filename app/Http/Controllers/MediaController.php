<?php

namespace App\Http\Controllers;

use App\Services\LearningEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function show(Request $r, int $media, LearningEngine $engine)
    {
        $m = DB::table('media_files')->where('id', $media)->where('status', 'ready')->first();
        abort_unless($m && in_array($m->kind, ['audio', 'image']), 404);
        if ($r->user()->role !== 'admin') {
            $allowed = false;
            foreach (\App\Services\LearnerIdentity::journeys($r->user())->get() as $j) {
                $manifest = $engine->manifest($j);
                if (! in_array($media, $manifest['media_ids'] ?? [], true)) {
                    continue;
                }
                foreach ($manifest['zones'] as $z) {
                    $p = DB::table('student_progress')->where('student_journey_id', $j->id)->where('zone_id', $z['id'])->first();
                    if (! $p || $p->status === 'locked' || (\App\Services\ZoneStructure::timed($z['sequence']) && ! app(\App\Services\ZoneClock::class)->ready($j, $z['id']))) {
                        continue;
                    }
                    if (app(\App\Services\ContinuousAdventure::class)->enabled($j) && $j->status==='active' && (app(\App\Services\ContinuousAdventure::class)->current($j)['id'] ?? null)!==$z['id']) { continue; }
                    foreach ($z['notes'] as $n) {
                        if (in_array($media, $n['media_ids'], true)) {
                            $allowed = true;
                        }
                    }
                    foreach ($z['activities'] as $a) {
                        if (($a['instruction_audio_id'] ?? null) === $media) {
                            $allowed = true;
                        }foreach ($a['questions'] as $q) {
                            if (($q['audio_id'] ?? null) === $media && $q['type'] !== 'dictation') {
                                $allowed = true;
                            }
                        }
                    }
                }
            }abort_unless($allowed, 404);
        }
        $path = str_replace('\\', '/', $m->path);
        abort_if(str_starts_with($path, '/') || str_contains($path, ':') || in_array('..', explode('/', $path), true), 404);
        $disk = Storage::disk($m->disk);
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), ['Content-Type' => $m->mime_type, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
