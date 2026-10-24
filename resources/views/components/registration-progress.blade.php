@props(['step'=>1])
<ol class="registration-progress" aria-label="Kemajuan pendaftaran">@foreach(['Maklumat Akaun','Selesai'] as $index=>$label)<li class="{{ $step > $index+1 ? 'complete' : ($step === $index+1 ? 'current' : '') }}" @if($step === $index+1) aria-current="step" @endif><span>{{ $step > $index+1 ? '✓' : $index+1 }}</span><strong>{{ $label }}</strong></li>@endforeach</ol>
