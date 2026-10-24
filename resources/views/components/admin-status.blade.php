@props(['status','label'=>null])
@php
$colors=['active'=>'success','published'=>'success','completed'=>'success','ready'=>'success','inactive'=>'danger','failed'=>'danger','error'=>'danger','expired'=>'warning','in_progress'=>'warning','pending'=>'warning','draft'=>'neutral','locked'=>'neutral','available'=>'neutral','archived'=>'neutral'];
$labels=['active'=>'Aktif','published'=>'Sedia untuk terbit','completed'=>'Selesai','ready'=>'Sedia','inactive'=>'Tidak Aktif','failed'=>'Gagal','error'=>'Ralat','expired'=>'Tamat Tempoh','in_progress'=>'Dalam Proses','pending'=>'Dalam Proses','draft'=>'Draf','locked'=>'Terkunci','available'=>'Belum Bermula','archived'=>'Diarkibkan'];
@endphp
<span class="admin-status is-{{ $colors[$status] ?? 'info' }}">{{ $label ?? $labels[$status] ?? $status }}</span>
