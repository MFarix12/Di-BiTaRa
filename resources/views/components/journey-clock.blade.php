@php
    $clockJourney=\App\Services\LearnerIdentity::journeys(auth()->user())->whereIn('status',['active','completed'])->latest('id')->first();
    $continuousClock=$clockJourney && app(\App\Services\ContinuousAdventure::class)->enabled($clockJourney) ? DB::table('journey_clocks')->where('student_journey_id',$clockJourney->id)->first() : null;
@endphp
@if($continuousClock)
<div class="nav-journey-clock continuous-clock" role="timer" aria-label="Masa keseluruhan pengembaraan" @unless($continuousClock->finished_at) data-journey-clock data-continuous="{{ (int)$clockJourney->flow_version===3 ? '1' : '0' }}" data-url="{{ route('student.clock') }}" data-elapsed="{{ intdiv($continuousClock->elapsed_ms,1000) }}" @endunless>
<span aria-hidden="true">⏱</span><span class="clock-caption">Masa</span><strong data-journey-time>{{ sprintf('%02d:%02d',intdiv($continuousClock->elapsed_ms,60000),intdiv($continuousClock->elapsed_ms,1000)%60) }}</strong><small class="sr-only" data-journey-status>{{ $continuousClock->finished_at ? '✓ Selesai' : 'Kemajuan disimpan automatik' }}</small>
</div>
@endif
