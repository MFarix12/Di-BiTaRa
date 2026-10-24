@props(['detailed'=>false])
<div class="pub-zones {{ $detailed ? 'pub-zones-detailed' : '' }}">@foreach([
['✈','Balai Pelepasan','Pilih avatar. Sedia untuk terbang!','Bersedia'],
['📖','Taklimat Penerbangan','Baca, dengar dan kenali di- serta di.','Belajar'],
['🎮','Simulasi Penerbangan','Betul / Salah dan Tarik dan Lepas.','Cuba misi'],
['⚡','Cabaran Kecemasan','Susun Ayat dan Isi Tempat Kosong.','Bina ayat'],
['🎧','Balai Ketibaan & Imlak','Dengar, tulis dan raikan kejayaan!','Imlak & tamat']
] as $i=>$zone)<article class="pub-card pub-zone"><div class="pub-zone-picture"><img src="{{ asset(config('zone-backgrounds.'.($i+1).'.image')) }}" alt="" loading="lazy" width="400" height="225"><span class="pub-zone-index">{{ $i+1 }}</span><span class="pub-zone-sticker" aria-hidden="true">{{ $zone[0] }}</span></div><div class="pub-zone-copy"><span class="pub-kicker">ZON {{ $i+1 }} · {{ $zone[3] }}</span><h3>{{ $zone[1] }}</h3><p>{{ $zone[2] }}</p>@if($detailed)<details class="pub-zone-discover"><summary>Teroka misi <span aria-hidden="true">＋</span></summary><p>{{ ['Sahkan avatar untuk mendapatkan cap pertama.','Lengkapkan nota wajib untuk membuka Zon 3.','Kelaskan perkataan kepada Imbuhan di- atau Kata Sendi di.','Susun perkataan dan lengkapkan ayat yang betul.','Lengkapkan tiga imlak dan refleksi untuk cap akhir.'][$i] }}</p></details>@else<a class="pub-zone-link" href="{{ route('adventure') }}#zon-{{ $i+1 }}">Jom teroka <span aria-hidden="true">→</span></a>@endif</div><span id="zon-{{ $i+1 }}" class="pub-zone-anchor" aria-hidden="true"></span></article>@endforeach</div>
