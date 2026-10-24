<x-layout title="Keputusan / Sesi" admin><section class="studio-page"><header class="studio-heading"><h1>Keputusan / Sesi</h1><p>Semak dan muat turun keputusan Pelajar. Tarikh mengikut waktu Malaysia.</p></header><x-feedback/>
<section class="studio-panel session-filter-panel" tabindex="0" aria-label="Toolbar tapisan keputusan sesi"><form method="get" action="{{ route('admin.sessions') }}" class="session-results-filters">
<label>Kod Pengembaraan<select name="code"><option value="">Semua kod</option>@foreach($codes as $code)<option value="{{ $code->id }}" @selected(($filters['code']??'')==$code->id)>{{ $code->code }} · {{ $code->name }}</option>@endforeach</select></label>
<label>Nama Pelajar<input name="name" value="{{ $filters['name']??'' }}" maxlength="100" placeholder="Cari nama"></label>
<label>Kelas<input name="class" value="{{ $filters['class']??'' }}" maxlength="50" placeholder="Semua kelas"></label>
<label>Tahun / Darjah<select name="year"><option value="">Semua tahun</option>@foreach([4,5,6] as $year)<option value="{{ $year }}" @selected(($filters['year']??'')==$year)>Tahun {{ $year }}</option>@endforeach</select></label>
<label>Status<select name="status"><option value="">Semua status</option>@foreach(['active'=>'Dalam Perjalanan','completed'=>'Selesai','ended'=>'Ditamatkan Awal'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach</select></label>
<label>Tarikh Mula — Dari<input type="date" name="from" value="{{ $filters['from']??'' }}"></label><label>Tarikh Mula — Hingga<input type="date" name="to" value="{{ $filters['to']??'' }}"></label>
<div class="session-filter-actions"><button class="btn btn-primary">Tapis Keputusan</button><a class="btn btn-outline" href="{{ route('admin.sessions') }}">Kosongkan Filter</a></div>
</form></section>
<p class="muted">Ranking bagi sesi selesai dalam kod yang sama: markah tertinggi, betul cubaan pertama, kemudian masa terpendek. Kedudukan seri dikongsi; kelajuan bukan satu-satunya ukuran pencapaian.</p><div class="session-export-toolbar"><div><strong>{{ $sessions->total() }} sesi</strong><small>{{ $filters ? 'Muat turun semua data yang sepadan dengan filter.' : 'Muat turun semua keputusan sesi.' }}</small></div><div class="studio-actions"><a class="btn btn-outline" href="{{ route('admin.sessions.export',array_merge($filters,['format'=>'xlsx'])) }}">Muat Turun Excel</a><a class="btn btn-primary" href="{{ route('admin.sessions.export',array_merge($filters,['format'=>'pdf'])) }}">Muat Turun PDF</a></div></div>
<form method="post" action="{{ route('admin.certificates.bundle') }}" x-data="{selected:[]}">
@csrf
@foreach($filters as $key=>$value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
<div class="studio-panel certificate-bulk-actions">
<button class="btn btn-primary" type="submit" name="scope" value="filtered">Muat Turun Semua Sijil Pelajar Selesai (ZIP)</button>
<button class="btn btn-outline" type="submit" name="scope" value="selected" :disabled="selected.length===0">Muat Turun Sijil Dipilih (ZIP) <span x-text="selected.length ? '('+selected.length+')' : ''"></span></button>
<small>Semua sijil mengikut tapisan semasa, termasuk halaman lain. Pilihan individu hanya pada halaman ini. Hanya sijil yang layak dan tersedia disertakan.</small>
<label class="certificate-selection"><input type="checkbox" aria-label="Pilih semua sijil pada halaman ini" @change="selected=$event.target.checked ? @js($certificates->keys()->map(fn($id)=>(string)$id)->values()->all()) : []" :checked="selected.length>0 && selected.length===@js($certificates->count())">Pilih semua sijil pada halaman ini</label>
</div>
<div class="studio-panel table-scroll"><table class="session-results-table"><thead><tr><th scope="col">Pilih Sijil</th>@foreach(\App\Services\SessionResults::HEADERS as $header)<th>{{ $header }}</th>@endforeach<th scope="col">Sijil Pelajar</th></tr></thead><tbody>
@forelse($rows as $row)
@php($learnerSession=$sessions->items()[$loop->index])
@php($certificate=$certificates->get($learnerSession->id))
<tr><td>@if($certificate)<input type="checkbox" name="sessions[]" value="{{ $learnerSession->id }}" x-model="selected" aria-label="Pilih sijil {{ $learnerSession->display_name }}">@else<span aria-label="Sijil tidak tersedia">—</span>@endif</td>
@foreach($row as $index=>$value)<td>@if($index===11)<x-admin-status :status="['Selesai'=>'completed','Ditamatkan Awal'=>'inactive','Dalam Perjalanan'=>'in_progress'][$value]" :label="$value"/>@else{{ $value }}@endif</td>@endforeach
<td>@if($certificate)<div class="session-certificate-actions"><a class="btn btn-outline btn-small" href="{{ route('admin.certificates.preview',$certificate->id) }}">Lihat Sijil</a><a class="btn btn-primary btn-small" href="{{ route('admin.certificates.download',$certificate->id) }}">Muat Turun Sijil</a></div>@else<small>{{ $learnerSession->completed_at ? 'Sijil belum tersedia' : 'Lengkapkan lima zon dan refleksi dahulu' }}</small>@endif</td></tr>
@empty<tr><td colspan="17"><img src="{{ asset('images/pilot-mascot.png') }}" alt="" width="45" height="55" style="object-fit:contain;vertical-align:middle;margin-right:12px">Belum ada keputusan di sini. Cuba ubah tapisan atau tunggu murid memulakan misi.</td></tr>@endforelse
</tbody></table>{{ $sessions->links() }}</div></form></section></x-layout>