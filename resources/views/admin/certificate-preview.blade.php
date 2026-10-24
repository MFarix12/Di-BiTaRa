<x-layout title="Pratonton Sijil" admin>
<section class="studio-page"><header class="studio-heading"><h1>Sijil Pelajar</h1><p>{{ $certificate->display_name }} · Tahun {{ $certificate->school_year }} · {{ $certificate->class_name }}</p></header>
<div class="studio-panel"><div class="studio-actions"><a class="btn btn-outline" href="{{ route('admin.sessions') }}">Kembali ke Keputusan Sesi</a><a class="btn btn-primary" href="{{ route('admin.certificates.download',$certificate->id) }}">Muat Turun PDF</a></div>
<p>{{ $certificate->certificate_number }} · Tamat lima zon dan refleksi akhir.</p>
@if($preview['frame'] && $preview['lines'])
<svg class="admin-certificate-art" viewBox="0 0 842 595" role="img" aria-label="Sijil rasmi {{ $certificate->recipient_name }}"><title>Sijil Pencapaian {{ $certificate->recipient_name }}</title><image href="{{ $preview['frame'] }}" width="842" height="595"/>
@foreach($preview['lines'] as $line)<text x="{{ $line['x'] }}" y="{{ $line['y'] }}" font-size="{{ $line['size'] }}" font-family="Helvetica, Arial, sans-serif" font-weight="700" fill="#061f4f">{{ $line['text'] }}</text>@endforeach
</svg>
@else<p>Pratonton ringkas tidak tersedia untuk fail ini. Gunakan butang Muat Turun PDF untuk melihat sijil rasmi.</p>@endif
<p><a href="{{ route('admin.certificates.file',$certificate->id) }}" target="_blank" rel="noopener">Buka PDF asal dalam tab baharu ↗</a></p>
</div></section></x-layout>
