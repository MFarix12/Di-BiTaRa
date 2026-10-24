@php($steps=[
    ['zones','✈','Perjalanan Anda','Mula di Zon 1. Selesaikan zon mengikut urutan.'],
    ['activities','🎮','Kuiz & Aktiviti','Baca nota. Jawab cabaran satu demi satu.'],
    ['profile','🧑‍✈️','Avatar Anda','Pilih avatar di Zon 1. Tekan Mari Meneroka.'],
    ['passport','🏅','Pasport & Pencapaian','Lihat cap, lencana dan sijil anda.'],
    ['activities','🔊','Dengar dan Cuba','Tekan Dengar untuk audio. Cuba lagi jika belum betul.'],
    ['end-session','🏁','Tamat Pengembaraan','Lihat pencapaian. Tekan Tamat Sesi apabila selesai.'],
])
@php([$target,$icon,$heading,$description]=$steps[$step-1])
<div data-guided-tour data-active="{{ $active ? '1' : '0' }}" data-step="{{ $step }}" data-target="{{ $target }}">
    <dialog class="guided-tour-shell" wire:ignore.self aria-labelledby="guided-tour-title" aria-describedby="guided-tour-description" aria-modal="true">
        <div class="guided-tour-shade shade-top" aria-hidden="true"></div><div class="guided-tour-shade shade-left" aria-hidden="true"></div><div class="guided-tour-shade shade-right" aria-hidden="true"></div><div class="guided-tour-shade shade-bottom" aria-hidden="true"></div>
        <div class="guided-tour-spotlight" aria-hidden="true"></div>
        <section class="guided-tour-card">
            <button type="button" class="guided-tour-close" wire:click="finish(true)" data-tour-dismiss aria-label="Tutup tutorial" wire:loading.attr="disabled">×</button>
            <div class="guided-tour-top"><span class="guided-tour-icon" aria-hidden="true">{{ $icon }}</span><span class="guided-tour-count">{{ $step }} / 6</span></div>
            <div aria-live="polite" aria-atomic="true"><p class="guided-tour-kicker">JOM KENALI Di-BITaRa!</p><h2 id="guided-tour-title">{{ $heading }}</h2><p id="guided-tour-description">{{ $description }}</p></div>
            <p class="guided-tour-mobile-hint" hidden>Pada telefon, buka menu ☰.</p>
            <div class="guided-tour-dots" role="img" aria-label="Langkah {{ $step }} daripada 6">@foreach($steps as $i=>$item)<span class="{{ $i+1===$step ? 'current' : ($i+1<$step ? 'done' : '') }}"></span>@endforeach</div>
            <div class="guided-tour-actions"><button type="button" wire:click="move(-1)" class="btn btn-outline" @disabled($step===1) wire:loading.attr="disabled">Sebelumnya</button>@if($step<6)<button type="button" wire:click="move(1)" class="btn btn-primary" data-tour-next autofocus wire:loading.attr="disabled">Seterusnya ›</button>@else<button type="button" wire:click="finish" class="btn btn-primary" data-tour-next autofocus wire:loading.attr="disabled">Selesai ✓</button>@endif</div>
            <button type="button" class="guided-tour-skip" wire:click="finish(true)" wire:loading.attr="disabled">Langkau</button>
        </section>
    </dialog>
</div>
