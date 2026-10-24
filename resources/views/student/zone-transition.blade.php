<x-layout title="Zon {{ $zone['sequence'] }} Selesai" :zone="$zone['sequence']">
<section class="adventure-transition" data-adventure-transition data-next="{{ route('student.zone.launch',$zone['sequence']+1) }}">
    <div class="adventure-stamp" aria-hidden="true">🏅<b>✓</b></div>
    <h1>Hebat! Zon {{ $zone['sequence'] }} Selesai!</h1>
    @if($p->best_score)<p>★ {{ $p->best_score }} markah</p>@endif
    <p>Cap pasport diperoleh!</p>
    <div class="departure-scene" aria-hidden="true">
        <span class="departure-cloud cloud-one"></span><span class="departure-cloud cloud-two"></span>
        <span class="departure-spark spark-one">✦</span><span class="departure-spark spark-two">✧</span>
        <div class="departure-aircraft"><span class="departure-trail"></span><x-transition-aircraft/></div>
    </div>
    <p class="departure-status" role="status">Bersedia! Kita terbang ke Zon {{ $zone['sequence']+1 }}…</p>
    <noscript><p>Aktifkan JavaScript untuk meneruskan pengembaraan secara automatik.</p></noscript>
</section>
</x-layout>
