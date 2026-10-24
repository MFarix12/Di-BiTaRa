<x-layout title="Papan Pemuka Pelajar">
<div class="flight-dashboard">
    <section data-tour="dashboard" class="fd-welcome {{ mb_strlen(auth()->user()->display_name) > 12 ? 'fd-long-name' : '' }}" aria-label="Ucapan selamat datang"><div class="fd-welcome-copy"><h1>Selamat Datang, <span>{{ auth()->user()->display_name }}!</span></h1><p>Jom sambung misi!<br>Pilih zon dan kumpul cap anda.</p></div><img src="{{ asset('images/pilot-mascot.png') }}" alt="Juruterbang Di-BITaRa" class="fd-banner-pilot"><div class="fd-speech">Ilmu hari ini,<br>kejayaan esok!</div><span class="fd-banner-star" aria-hidden="true">✦</span></section>
    <section class="fd-summary" aria-label="Ringkasan pencapaian">
        @foreach([['📖','Zon Selesai',$stamps->count().' / 5',$stamps->count()*20,'green'],['🏆','Jumlah Markah',$score,null,'gold'],['🏅','Cap Dikumpul',$stamps->count().' / 5',$stamps->count()*20,'blue'],['📜','Sijil Diperoleh',$certificateCount.' / 1',$certificateCount*100,'purple']] as [$icon,$label,$value,$amount,$color])
        <article class="fd-stat fd-{{ $color }}"><span class="fd-stat-icon" aria-hidden="true">{{ $icon }}</span><div><h2>{{ $label }}</h2><strong>{{ $value }}</strong>@if($amount !== null)<progress max="100" value="{{ $amount }}" aria-label="{{ $label }} {{ $value }}"></progress>@else<span class="fd-stat-star" aria-hidden="true">★</span>@endif</div></article>
        @endforeach
    </section>
    <div class="fd-middle">
        <section class="fd-panel fd-learning"><div class="fd-panel-heading"><h2><span aria-hidden="true">📊</span> Kemajuan Pembelajaran Saya</h2><a href="{{ route('student.achievements') }}#ringkasan">Lihat Butiran <b aria-hidden="true">›</b></a></div><div class="fd-learning-body"><div class="fd-progress-ring" style="--progress:{{ $percentage }}" role="img" aria-label="Keseluruhan kemajuan {{ $percentage }} peratus"><div><strong>{{ $percentage }}%</strong><span>Keseluruhan<br>Kemajuan</span></div></div><div class="fd-progress-list">
        @foreach($zones as $zone)
        @php($zonePercent = max(0,min(100,(int) ($progress->get($zone->id)?->progress_percent ?? 0))))
        @php($zoneStatus = $progress->get($zone->id)?->status)
        <div class="fd-progress-row fd-zone-{{ $zone->sequence }}"><span class="fd-zone-dot" aria-hidden="true"></span><strong>Zon {{ $zone->sequence }} – {{ $zone->name }}</strong><progress max="100" value="{{ $zonePercent }}" aria-label="Kemajuan Zon {{ $zone->sequence }}"></progress><b>{{ $zonePercent }}%</b><span class="fd-progress-status" aria-label="{{ $stamps->has($zone->id) ? 'Selesai' : (in_array($zoneStatus,['available','in_progress']) ? 'Dalam pembelajaran' : 'Terkunci') }}">{{ $stamps->has($zone->id) ? '✓' : (in_array($zoneStatus,['available','in_progress']) ? '▷' : '🔒') }}</span></div>
        @endforeach
        </div></div></section>
        <div class="fd-side-panels"><section class="fd-panel fd-notifications"><div class="fd-panel-heading"><h2><span aria-hidden="true">🔔</span> Notifikasi Terkini</h2><a href="{{ route('student.notifications') }}">Lihat Semua <b aria-hidden="true">›</b></a></div><div id="fd-notification-status" tabindex="-1" class="fd-notification-body"><span aria-hidden="true">ⓘ</span><div><x-notification-summary/></div></div></section>
        <section class="fd-panel fd-shortcuts"><div class="fd-panel-heading"><h2><span aria-hidden="true">ϟ</span> Pintasan Pantas</h2></div><div class="fd-shortcut-grid">@foreach([['student.activities','📖','Mulakan','Pembelajaran','green'],['student.activities','🎮','Kuiz','Interaktif','orange'],['student.rewards','🏆','Cap &','Ganjaran','purple'],['student.passport','▣','Pasport','Digital','blue']] as [$route,$icon,$first,$second,$color])<a class="fd-shortcut fd-{{ $color }}" href="{{ route($route) }}"><span aria-hidden="true">{{ $icon }}</span><strong>{{ $first }}<br>{{ $second }}</strong></a>@endforeach</div></section></div>
    </div>
    <section class="fd-panel fd-zones"><div class="fd-panel-heading"><h2><span aria-hidden="true">🚀</span> Zon Pembelajaran</h2><a href="{{ route('student.activities') }}">Lihat Semua Zon <b aria-hidden="true">›</b></a></div><div class="fd-zone-grid">
    @foreach($zones as $zone)
    @php($zonePercent = max(0,min(100,(int) ($progress->get($zone->id)?->progress_percent ?? 0))))
    @php($zoneOpen = $journey && ($stamps->has($zone->id) || in_array($progress->get($zone->id)?->status,['available','in_progress','completed'])))
    <article @if($zone->sequence===1) data-tour="zones" @endif class="fd-zone-card fd-zone-{{ $zone->sequence }}"><div class="fd-zone-card-title"><span class="zone-number-badge">{{ $zone->sequence }}</span><h3>Zon {{ $zone->sequence }}<small>{{ $zone->name }}</small></h3></div><div class="fd-zone-card-scene"><img class="fd-zone-thumbnail" src="{{ asset(config('zone-backgrounds.'.$zone->sequence.'.image')) }}" alt="{{ $zone->name }}" loading="lazy" decoding="async"><p>{{ ['Pilih dan sahkan avatar juruterbang.','Fahami konsep imbuhan di- dan kata sendi di.','Latih kefahaman melalui misi simulasi.','Uji kemahiran dengan aktiviti interaktif.','Dengar, tulis dan lengkapkan refleksi.'][$zone->sequence-1] }}</p><div class="fd-zone-card-progress"><progress max="100" value="{{ $zonePercent }}" aria-label="Kemajuan Zon {{ $zone->sequence }} {{ $zonePercent }} peratus"></progress><strong>{{ $zonePercent }}%</strong></div></div>@php($oneWay=$journey && app(\App\Services\ContinuousAdventure::class)->enabled($journey))
@if($oneWay && $progress->get($zone->id)?->status==='completed')<span class="fd-zone-action locked">✓ Selesai</span>
@elseif($oneWay && !$zoneOpen)<span class="fd-zone-action locked" aria-disabled="true">🔒 Terkunci</span>
@else<a href="{{ route('student.activities', ['zon'=>$zone->sequence]) }}" class="fd-zone-action {{ $zoneOpen ? '' : 'locked' }}" aria-label="Lihat kuiz dan aktiviti Zon {{ $zone->sequence }}">{{ $zoneOpen ? 'Lihat Aktiviti' : 'Kenali Zon' }} <b aria-hidden="true">›</b></a>@endif@unless($zoneOpen)<small class="fd-zone-availability">{{ $journey ? 'Aktiviti terkunci' : 'Misi dalam persediaan' }}</small>@endunless</article>
    @endforeach
    </div></section>
    @unless($journey)<p class="fd-preparation-note">✈ Misi sedang disediakan. Aktiviti permainan belum diterbitkan. Anda boleh <a href="{{ route('tutorial') }}">mengikuti tutorial</a> dan memilih avatar sekarang.</p>@endunless
</div>
<livewire:tutorial :replay="$replayTutorial ?? false"/>
</x-layout>

