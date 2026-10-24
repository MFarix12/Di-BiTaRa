@if(!app(\App\Services\ContinuousAdventure::class)->enabled(app(\App\Services\LearningEngine::class)->journey(auth()->user())))
@props(['sequence'])
@php
    $timedJourney = \Illuminate\Support\Facades\DB::table('student_journeys')->where('user_id', auth()->id())->whereIn('status', ['active','completed'])->latest('id')->first();
    $timedZone = $timedJourney ? collect(app(\App\Services\LearningEngine::class)->manifest($timedJourney)['zones'])->firstWhere('sequence', (int) $sequence) : null;
    $clockRow = $timedZone ? \Illuminate\Support\Facades\DB::table('student_progress')->where('student_journey_id', $timedJourney->id)->where('zone_id', $timedZone['id'])->first() : null;
    $elapsed = $clockRow?->timing_started_at ? max(0, (int) \Carbon\Carbon::parse($clockRow->timing_started_at)->diffInSeconds(now())) : null;
@endphp
@if(\App\Services\ZoneStructure::timed((int) $sequence) && $clockRow?->timing_started_at)
<div class="zone-timer" role="timer" aria-live="off" @if($clockRow->duration_seconds === null) data-zone-timer data-elapsed="{{ $elapsed }}" @endif>
    <span aria-hidden="true">⏱</span><span>Masa Zon {{ $sequence }}</span>
    <strong data-timer-value>{{ \App\Services\ZoneClock::format($clockRow->duration_seconds ?? $elapsed) }}</strong>
    <small>{{ $clockRow->duration_seconds !== null ? '✓ Selesai' : 'Misi sedang berjalan' }}{{ $clockRow->timing_is_partial ? ' · Masa sambungan' : '' }}</small>
</div>
@endif

@endif
