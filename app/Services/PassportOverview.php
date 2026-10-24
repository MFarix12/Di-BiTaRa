<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PassportOverview
{
    public function for(User|\App\Models\LearnerSession $user): array
    {
        $data = app(StudentOverview::class)->for($user);
        $journey = $data['journey'];
        $attempts = $journey ? DB::table('attempts')->where('student_journey_id',$journey->id)->where('status','completed')->whereNotNull('completed_at')->get() : collect();
        $attempts = $attempts->filter(fn ($attempt) => in_array((int) (json_decode($attempt->activity_snapshot, true)['sequence'] ?? 0), [3,4,5], true));
        $notes = $journey ? DB::table('content_completions')->where('student_journey_id',$journey->id)->get() : collect();
        $certificates = DB::table('certificates')->whereIn('student_journey_id',LearnerIdentity::journeys($user)->select('id'))->orderByDesc('id')->get();
        $quizPasses = $attempts->filter(fn ($row) => $row->passed && in_array((int) (json_decode($row->activity_snapshot,true)['sequence'] ?? 0), [3,4], true) && (json_decode($row->activity_snapshot,true)['kind'] ?? null) === 'quiz')->unique('activity_id')->count();
        $completedZones = $data['stamps']->count();
        $rawManifest=$journey ? json_decode(DB::table('learning_releases')->where('id',$journey->learning_release_id)->value('manifest'),true) : [];
        $requiredNotes=collect($rawManifest['zones'] ?? [])->flatMap(fn($z)=>$z['notes'] ?? [])->where('is_required',true)->pluck('id');
        $allNotesRead=$requiredNotes->isNotEmpty() ? $notes->whereIn('learning_content_id',$requiredNotes)->count()>=$requiredNotes->count() : $notes->count()>=5;
        $badges = collect([
            ['code'=>'first-note','name'=>'Langkah Pertama','icon'=>'★','color'=>'gold','condition'=>'Selesaikan 1 nota pembelajaran.','earned'=>$notes->count() >= 1],
            ['code'=>'quiz-pilot','name'=>'Pakar Kuiz','icon'=>'✈','color'=>'blue','condition'=>'Lulus 2 kuiz yang berbeza.','earned'=>$quizPasses >= 2],
            ['code'=>'note-explorer','name'=>'Pengkaji Nota','icon'=>'📖','color'=>'green','condition'=>'Baca semua nota wajib.','earned'=>$allNotesRead],
            ['code'=>'zone-explorer','name'=>'Penakluk Zon','icon'=>'◎','color'=>'red','condition'=>'Kumpulkan cap bagi 3 zon.','earned'=>$completedZones >= 3],
            ['code'=>'stamp-collector','name'=>'Pengumpul Cap','icon'=>'🏅','color'=>'purple','condition'=>'Kumpulkan kelima-lima cap zon.','earned'=>$completedZones >= 5],
            ['code'=>'mission-complete','name'=>'Misi Sempurna','icon'=>'🏆','color'=>'gold','condition'=>'Peroleh sijil perjalanan yang sedia.','earned'=>$certificates->contains('status','ready')],
        ]);
        $events = collect();
        foreach ($attempts as $attempt) {
            $snapshot = json_decode($attempt->activity_snapshot,true) ?: [];
            $events->push(['title'=>'Selesai: '.($snapshot['title'] ?? 'Aktiviti pembelajaran'),'detail'=>'Markah percubaan: '.$attempt->score.' / '.$attempt->max_score,'date'=>$attempt->completed_at,'icon'=>'🎮']);
        }
        foreach ($notes as $note) {
            $events->push(['title'=>'Nota pembelajaran diselesaikan','detail'=>'Nota #'.$note->learning_content_id,'date'=>$note->completed_at,'icon'=>'📖']);
        }
        foreach ($data['stamps'] as $stamp) {
            $events->push(['title'=>'Cap diperoleh: '.$stamp->title_snapshot,'detail'=>'Pencapaian zon disahkan','date'=>$stamp->awarded_at,'icon'=>'★']);
        }
        foreach ($certificates->where('status','ready') as $certificate) {
            if ($certificate->issued_at) {
                $events->push(['title'=>'Sijil perjalanan diperoleh','detail'=>$certificate->certificate_number,'date'=>$certificate->issued_at,'icon'=>'🏆']);
            }
        }
        return $data + [
            'badges'=>$badges, 'badgeCount'=>$badges->where('earned',true)->count(),
            'level'=>LearnerIdentity::isSession($user) ? (int) DB::table('learner_sessions')->where('id',$user->id)->value('school_year') : 4,
            'rank'=>match (true) { $completedZones >= 5=>'Kapten Bahasa', $completedZones >= 3=>'Penjelajah Bahasa', $completedZones >= 1=>'Pelatih Bahasa', default=>'Juruterbang Baharu' },
            'recentAchievements'=>$events->filter(fn ($event) => filled($event['date']))->sortByDesc('date')->take(8)->values(),
            'certificates'=>$certificates,
        ];
    }
}
