@props(['eyebrow','title','description'])
@php
    $scene = request()->routeIs('about') ? 'story' : (request()->routeIs('play','help') ? 'route' : (request()->routeIs('adventure') ? 'awards' : 'welcome'));
    $guide = match ($scene) {
        'story' => ['avatars/kapten-suraya.png', 'Kapten', 'Jom buka cerita dunia bahasa!', 'Belajar di- dan di bersama saya!', 'Buka cerita kita', '📖'],
        'route' => ['avatars/navigator-luna.png', 'Navigator', 'Ikut peta. Selangkah demi selangkah!', 'Kod, avatar, misi… anda pasti boleh!', 'Sedia ikut peta?', '🧭'],
        'awards' => ['avatars/angkasawan-aisyah-standing.png', 'Angkasawan', 'Setiap usaha membawa anda lebih jauh!', 'Terus mencuba. Bintang anda menanti!', 'Raikan usaha anda', '🏅'],
        default => ['pilot-mascot.png', 'Juruterbang', 'Sedia untuk pengembaraan hebat?', 'Jom terbang ke dunia bahasa!', 'Jom berkenalan!', '✈'],
    };
@endphp
<section class="pub-hero pub-wrap hero-{{ $scene }}">
    <div class="pub-hero-copy"><span class="pub-kicker">✦ {{ $eyebrow }}</span><h1>{{ $title }}</h1><p>{{ $description }}</p>{{ $slot }}</div>
    <div class="pub-hero-art identity-card identity-{{ $scene }}" x-data="{cheer:false}">
        @if($scene === 'welcome')
            <span class="identity-stamp">TIKET KE DUNIA ILMU</span><x-plane class="pub-flying-plane"/>
            <span class="pub-cloud pub-cloud-one" aria-hidden="true">☁</span><span class="pub-cloud pub-cloud-two" aria-hidden="true">☁</span><span class="pub-spark pub-spark-one" aria-hidden="true">✦</span>
        @elseif($scene === 'story')
            <div class="story-page" aria-hidden="true"><span>BUKU PENGEMBARAAN</span><b>di- <i>&</i> di</b><div>📚 &nbsp; ✦ &nbsp; 💡</div><em>Kenali · Cuba · Teroka</em></div><span class="story-bookmark" aria-hidden="true">✦</span>
        @elseif($scene === 'route')
            <span class="identity-stamp">PELAN MISI ANDA</span>
            <svg class="route-trail" viewBox="0 0 440 440" aria-hidden="true"><path d="M65 340 C230 350 50 230 190 230 S400 235 350 100" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="9 10"/></svg>
            <ol class="route-checkpoints" aria-label="Laluan pengembaraan"><li><b>1</b> Kod</li><li><b>2</b> Avatar</li><li><b>3</b> Misi</li></ol>
        @else
            <span class="identity-stamp">GALERI KEJAYAAN</span><div class="award-orbit" aria-hidden="true"></div><span class="award-medal" aria-hidden="true">🏆</span><span class="award-star" aria-hidden="true">✦</span><div class="award-podium" aria-hidden="true">USAHA • BELAJAR • BERJAYA</div>
        @endif
        <div class="pub-speech" aria-live="polite"><span x-show="!cheer">{{ $guide[2] }}</span><span x-show="cheer" x-cloak>{{ $guide[3] }}</span></div>
        <button class="pub-character" type="button" @click="cheer=!cheer" :aria-pressed="cheer" aria-label="Sapaan {{ $guide[1] }}" title="Tekan untuk membaca sapaan"><img src="{{ asset('images/'.$guide[0]) }}" alt="{{ $guide[1] }} Di-BITaRa" width="420" height="630" fetchpriority="high"></button>
        <span class="pub-art-label">{{ $guide[5] }} Tekan karakter. {{ $guide[4] }}</span>
    </div>
</section>
