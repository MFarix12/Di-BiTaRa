<x-layout title="Ulang Kaji">
<section class="studio-page review-page"><header class="studio-heading"><h1>📖 Jom Ulang Kaji!</h1><p>Latih lagi. Markah, cap dan masa asal kekal.</p></header>
<div class="learning-note-grid">@foreach($zones as $zone)<article class="studio-panel"><h2>Zon {{ $zone['sequence'] }} · {{ $zone['name'] }}</h2>
@if($zone['sequence']===1)<p>✓ Persediaan lengkap</p>@endif
@foreach($zone['notes'] as $n)<p><a class="btn btn-outline" href="{{ route('student.review.note',$n['id']) }}">📖 {{ $n['title'] }}</a></p>@endforeach
@foreach($zone['activities'] as $a)<p><a class="btn btn-primary" href="{{ route('student.review.activity',$a['id']) }}">{{ $a['title'] }} ›</a></p>@endforeach
</article>@endforeach</div><a class="btn btn-outline" href="{{ route('student.achievements') }}">🏅 Pasport & Pencapaian</a></section>
</x-layout>
