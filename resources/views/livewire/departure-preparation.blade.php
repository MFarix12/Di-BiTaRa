<section class="departure-setup" aria-labelledby="departure-heading">
    <header class="departure-preparation"><span class="departure-setup-icon" aria-hidden="true">✈</span><div class="departure-details"><h2 id="departure-heading">Sedia untuk berlepas?</h2><p>Pilih avatar. Lihat pratonton. Pilih watak kegemaran anda!</p><div class="departure-checks">
        @foreach(['account'=>['🎫','Kod Disahkan'],'avatar'=>['🧑‍✈️','Avatar disahkan']] as $key=>[$icon,$label])
        <div class="departure-check {{ $departure['checks'][$key] ? 'is-ready' : '' }}"><span aria-hidden="true">{{ $icon }}</span><strong>{{ $label }}</strong><span aria-label="{{ $departure['checks'][$key] ? 'Lengkap' : 'Belum lengkap' }}">{{ $departure['checks'][$key] ? '✓' : '○' }}</span></div>
        @endforeach
    </div></div></header>
    @error('avatar_id')<p class="notice error" role="alert">{{ $message }}</p>@enderror
    @if($departure['ready'])<p class="notice success">{{ $stampEarned ? '🏅 Cap Zon 1 Diperoleh · Zon 2 Dibuka!' : '✓ Avatar disahkan. Anda sedia untuk berlepas!' }}</p>@if($stampEarned)<a class="btn btn-primary" href="{{ route('student.zone.launch',2) }}">Berlepas ke Zon 2 ✈</a>@endif @endif
    <form class="departure-setup-form" x-data @submit.prevent="$refs.confirmDeparture.showModal()">
        <div class="registration-avatar-layout"><fieldset x-data="{genderFilter:'all',roleFilter:'all',options:@js($avatars->map(fn($a)=>\App\Services\AvatarCatalogue::details($a))->values())}"><legend>Pilih Avatar Anda</legend><x-avatar-filters :roles="$roles"/>
            <div class="avatar-grid diverse-avatar-grid" tabindex="0" aria-label="Pilihan avatar Zon 1">
            @foreach($avatars as $avatar) @php($details=\App\Services\AvatarCatalogue::details($avatar))
                <label class="avatar-choice {{ $avatar_id===$avatar->id ? 'selected' : '' }}" wire:key="departure-avatar-{{ $avatar->id }}" x-show="(genderFilter==='all'||genderFilter===@js($details['gender'])) && (roleFilter==='all'||roleFilter===@js($details['role']))"><input type="radio" name="departure_avatar" wire:model.live.number="avatar_id" value="{{ $avatar->id }}"><x-pilot-avatar :code="$avatar->code" :name="$details['role']"/><strong>{{ $details['role'] }}</strong>@if($avatar_id===$avatar->id)<span class="departure-selection-check" aria-hidden="true">✓</span>@endif</label>
            @endforeach
            </div>
            @if($avatars->isEmpty())<p class="notice info">Avatar sedang disediakan. Sila hubungi Pentadbir.</p>@endif
            <p class="avatar-no-results" x-cloak x-show="options.filter(a=>(genderFilter==='all'||a.gender===genderFilter)&&(roleFilter==='all'||a.role===roleFilter)).length===0">Tiada pilihan ini. Cuba penapis lain.</p>
        </fieldset>
        <aside class="registration-avatar-preview" aria-live="polite"><h2>Pratonton Avatar Anda</h2><div class="registration-avatar-preview-scene">@if($preview)<x-pilot-avatar :code="$preview->code" :name="\App\Services\AvatarCatalogue::details($preview)['role']"/>@else<div class="departure-empty-avatar"><span aria-hidden="true">🧑‍✈️</span><p>Pilih satu avatar di sebelah.</p></div>@endif</div><div class="registration-avatar-preview-note"><span aria-hidden="true">★</span><p><strong>{{ $preview ? \App\Services\AvatarCatalogue::details($preview)['role'] : 'Watak Pengembaraan Anda' }}</strong></p></div><p class="departure-confirm-hint">Tekan butang untuk sahkan avatar dan dapatkan cap Zon 1.</p><button type="button" class="btn btn-primary registration-main-button" @click="$refs.confirmDeparture.showModal()" wire:loading.attr="disabled" @disabled(!$preview)><span wire:loading.remove wire:target="confirm">Mari Meneroka ✈</span><span wire:loading wire:target="confirm">Bersedia untuk terbang…</span></button></aside></div>
        <dialog x-ref="confirmDeparture" class="zone-confirm-dialog" aria-labelledby="departure-confirm-title"><span class="confirmation-icon" aria-hidden="true">✈</span><h2 id="departure-confirm-title">Avatar sudah dipilih!</h2><p>Bersedia untuk meneroka?</p><div class="public-page-actions"><button type="button" class="btn btn-outline" @click="$refs.confirmDeparture.close()">Batal</button><button type="button" class="btn btn-primary" wire:click="confirm" @click="$refs.confirmDeparture.close()" wire:loading.attr="disabled">Ya, saya pilih ini</button></div></dialog>
    </form>
</section>
