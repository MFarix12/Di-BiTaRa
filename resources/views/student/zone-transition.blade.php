<x-layout title="Zon {{ $zone['sequence'] }} Selesai" :zone="$zone['sequence']">
<section class="adventure-transition" data-adventure-transition data-next="{{ route('student.zone.launch',$zone['sequence']+1) }}">
    <div class="adventure-stamp" aria-hidden="true">🏅<b>✓</b></div>
    <h1>Hebat! Zon {{ $zone['sequence'] }} Selesai!</h1>
    @if($p->best_score)<p>★ {{ $p->best_score }} markah</p>@endif
    <p>Cap pasport diperoleh!</p>
    <div class="transition-plane" aria-hidden="true"><x-plane/></div>
    <a class="btn btn-primary" href="{{ route('student.zone.launch',$zone['sequence']+1) }}">Teruskan ke Zon {{ $zone['sequence']+1 }} ✈</a>
</section>
</x-layout>
