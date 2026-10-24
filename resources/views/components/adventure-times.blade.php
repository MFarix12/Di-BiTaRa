@props(['zones', 'progress', 'journey'])
@if($journey && app(\App\Services\ContinuousAdventure::class)->enabled($journey))
@php($clock=DB::table('journey_clocks')->where('student_journey_id',$journey->id)->first())
@php($metrics=app(\App\Services\LearningMetrics::class)->for($journey->id))
<section class="pa-panel adventure-times" id="masa-pengembaraan" aria-labelledby="adventure-times-heading"><div class="pa-heading"><h2 id="adventure-times-heading">⏱ Masa Pengembaraan Saya</h2></div><div class="adventure-time-grid"><article class="adventure-time-card time-total"><h3>Masa Keseluruhan Pengembaraan</h3><strong>{{ \App\Services\ZoneClock::format($clock ? intdiv($clock->elapsed_ms,1000) : null) }}</strong><small>{{ $clock?->finished_at ? '✓ Selesai' : 'Disimpan automatik' }}</small></article><article class="adventure-time-card"><h3>🎯 Betul Cubaan Pertama</h3><strong>{{ $metrics['first_correct'] }} / {{ $metrics['answered'] }}</strong></article></div><p class="time-legacy-note">Dari Zon 3 hingga refleksi Zon 5. {{ (int)$journey->flow_version===3 ? 'Jam diteruskan sepanjang sesi hingga refleksi dihantar.' : 'Jam dijeda ketika keluar atau tiada sambungan.' }}{{ $clock?->is_partial ? ' Masa sambungan sahaja; rekod lama dikekalkan.' : '' }}</p>@if($journey->status==='completed')<a class="btn btn-primary" href="{{ route('student.review') }}">📖 Ulang Kaji</a>@endif</section>
@else
<section class="pa-panel adventure-times" id="masa-pengembaraan" aria-labelledby="adventure-times-heading">
    <div class="pa-heading"><h2 id="adventure-times-heading">⏱ Masa Pengembaraan Saya</h2></div>
    <div class="adventure-time-grid">
        @php($timedZones = $zones->whereIn('sequence', \App\Services\ZoneStructure::TIMED))
        @php($timedProgress = $progress->whereIn('zone_id', $timedZones->pluck('id')->all()))
        @foreach($timedZones as $timeZone)
        @php($timeRow = $progress->get($timeZone->id))
        <article class="adventure-time-card time-zone-{{ $timeZone->sequence }}">
            <span class="time-zone-icon" aria-hidden="true">{{ ['✈','📖','🎮','⚠','🏆'][$timeZone->sequence-1] }}</span>
            <h3>Masa Zon {{ $timeZone->sequence }}</h3>
            @if($timeRow?->duration_seconds !== null)
                <strong>{{ \App\Services\ZoneClock::format($timeRow->duration_seconds) }}</strong>
                <small>{{ $timeRow->timing_is_partial ? 'Masa sambungan sahaja' : '✓ Selesai' }}</small>
            @elseif($timeRow?->timing_started_at)
                <strong data-zone-timer data-elapsed="{{ max(0, (int) \Carbon\Carbon::parse($timeRow->timing_started_at)->diffInSeconds(now())) }}" role="timer" aria-live="off"><span data-timer-value>{{ \App\Services\ZoneClock::format(max(0, (int) \Carbon\Carbon::parse($timeRow->timing_started_at)->diffInSeconds(now()))) }}</span></strong>
                <small>Misi sedang berjalan{{ $timeRow->timing_is_partial ? ' · Masa sambungan' : '' }}</small>
            @else
                <strong>—</strong><small>{{ $timeRow?->status === 'completed' ? 'Tiada rekod masa' : ($timeRow?->status === 'locked' ? '🔒 Terkunci' : 'Belum bermula') }}</small>
            @endif
        </article>
        @endforeach
        <article class="adventure-time-card time-total"><span class="time-zone-icon" aria-hidden="true">⏱</span><h3>Jumlah Masa Keseluruhan</h3>
            <strong>{{ \App\Services\ZoneClock::format($journey?->total_duration_seconds) }}</strong>
            <small>{{ $journey?->total_duration_seconds !== null ? '✓ Zon 3, 4 dan 5 selesai' : ($timedProgress->where('status','completed')->count() === 3 ? 'Rekod masa belum lengkap' : 'Lengkapkan Zon 3, 4 dan 5') }}</small>
        </article>
    </div>
    <p class="time-legacy-note">Jumlah masa = Zon 3 + Zon 4 + Zon 5</p>
    @if($timedProgress->contains(fn ($p) => $p->timing_is_partial || ($p->status === 'completed' && $p->duration_seconds === null)))<p class="time-legacy-note">ⓘ Masa sebelum kemas kini tidak direkodkan. Masa sambungan dikira sejak anda kembali.</p>@endif
</section>

@endif
