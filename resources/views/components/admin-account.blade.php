<div class="admin-account" x-data="{ open: false }" @keydown.escape.stop="if (open) { open = false; $refs.trigger.focus() }" @click.outside="open = false">
    <button type="button" class="account-chip admin-account-trigger" x-ref="trigger" @click="open = !open" :aria-expanded="open" aria-controls="admin-account-dropdown" aria-label="Menu profil Pentadbir">
        <x-admin-avatar :decorative="true"/>
        <span><strong>{{ auth()->user()->display_name }}</strong><small>Pentadbir Sistem</small></span>
        <span class="admin-account-chevron" aria-hidden="true" :class="{'is-open': open}">⌄</span>
    </button>
    <nav id="admin-account-dropdown" class="admin-account-dropdown" aria-label="Akaun Pentadbir" x-show="open" x-cloak x-transition.origin.top.right><div class="account-brand"><x-brand-logo :decorative="true"/><strong>Di-BITaRa<small>Pusat Pengurusan</small></strong></div>
        <a href="{{ route('admin.profile') }}" @if(request()->routeIs('admin.profile')) aria-current="page" @endif><span aria-hidden="true">♙</span> Profil</a>
        <a href="{{ route('admin.settings') }}" @if(request()->routeIs('admin.settings')) aria-current="page" @endif><span aria-hidden="true">⚙</span> Tetapan</a>
        <button type="button" class="admin-logout-action" @click="open = false; $refs.logoutDialog.showModal()"><span aria-hidden="true">↪</span> Log Keluar</button>
    </nav>
    <x-logout-dialog id="admin-logout" description="Anda boleh log masuk semula untuk meneruskan pengurusan Di-BITaRa."/>
</div>
