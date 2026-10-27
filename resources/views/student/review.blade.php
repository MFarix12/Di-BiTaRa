<x-layout title="Ulang Kaji">
<section class="review-page revision-room">
<header class="revision-welcome">
<div class="revision-welcome-copy"><span class="revision-eyebrow">✦ RUANG ILMU ANDA · TAHUN 4</span><h1>Ulang Kaji</h1><p>Jom ulang kaji apa yang telah anda pelajari!</p><div class="revision-welcome-chips"><span>📚 Baca</span><span>🎧 Dengar</span><span>🎮 Cuba lagi</span></div><small>Markah, cap dan masa asal anda kekal.</small></div>
<div class="revision-guide"><span class="revision-guide-bubble">Pilih satu misi. Jom belajar bersama!</span><img src="{{ asset('images/avatars/navigator-luna.png') }}" alt="Navigator membawa buku panduan" width="200" height="300"><span class="revision-guide-star" aria-hidden="true">✦</span></div>
</header>
<div class="revision-heading"><div><span class="revision-eyebrow">PILIH SUDUT BELAJAR</span><h2>Nak ulang kaji apa hari ini?</h2></div><a class="btn btn-outline" href="{{ route('student.achievements') }}">🏅 Pasport & Pencapaian</a></div>
<div class="revision-grid">
@foreach($zones as $zone)
@if(in_array((int)$zone['sequence'],[2,3,4,5]))
@php($presentation=match((int)$zone['sequence']){2=>['📖','Nota Pembelajaran','Ingat semula. Rapat atau jarak?','Baca Semula'],3=>['💡','Imbuhan di- & Kata Sendi di','Pilih ejaan dan tempat yang tepat.','Cuba Semula'],4=>['🧩','Susun Ayat & Isi Tempat Kosong','Bina ayat. Lengkapkan cerita!','Cuba Semula'],5=>['🎧','Imlak','Pasang telinga. Dengar dan tulis!','Dengar & Cuba']})
<article class="revision-card revision-zone-{{ $zone['sequence'] }}">
<div class="revision-card-top"><span class="revision-card-icon" aria-hidden="true">{{ $presentation[0] }}</span><span class="revision-zone-badge">ZON {{ $zone['sequence'] }}</span></div>
<h3>{{ $presentation[1] }}</h3><p>{{ $presentation[2] }}</p>
<div class="revision-resource-list">
@forelse($zone['notes'] as $n)
<a class="revision-resource" href="{{ route('student.review.note',$n['id']) }}"><span><small>{{ $presentation[3] }}</small><strong>{{ $n['title'] }}</strong></span><b aria-hidden="true">↗</b></a>
@php($audioIds=\Illuminate\Support\Facades\DB::table('media_files')->whereIn('id',$n['media_ids'] ?? [])->where('kind','audio')->where('status','ready')->pluck('id'))
@foreach($audioIds as $audioId)<details class="revision-audio"><summary>🎧 Dengar Audio Nota</summary><audio controls preload="none" src="{{ route('student.review.audio',$audioId) }}" aria-label="Audio nota {{ $n['title'] }}"></audio></details>@endforeach
@empty @endforelse
@foreach($zone['activities'] as $a)
<a class="revision-resource" href="{{ route('student.review.activity',$a['id']) }}"><span><small>{{ $presentation[3] }}</small><strong>{{ $a['title'] }}</strong></span><b aria-hidden="true">↗</b></a>
@endforeach
@if(empty($zone['notes']) && empty($zone['activities']))<div class="revision-empty"><span aria-hidden="true">☁</span>Belum ada bahan untuk sudut ini.</div>@endif
</div>
<footer class="revision-card-footer"><span aria-hidden="true">✦</span> Belajar ikut rentak anda</footer>
</article>
@endif
@endforeach
</div>
<div class="revision-encouragement"><span aria-hidden="true">✈</span><p><strong>Sedikit latihan, semakin yakin!</strong> Pilih kad dan cuba seberapa kali yang anda mahu.</p></div>
</section>
</x-layout>
