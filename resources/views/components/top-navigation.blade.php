<header class="site-header global-nav public-nav-redesign">
    <a class="brand" href="{{ route('home') }}" aria-label="Di-BITaRa - Halaman Utama">
        <x-brand-logo :decorative="true"/>
        <span>Di-<strong>BITaRa</strong><small>Bijak Imbuhan & Tatabahasa Ruang Angkasa</small></span>
    </a>
    <button class="mobile-menu" x-ref="publicMenuToggle" type="button" @click="menu=!menu" :aria-expanded="menu" aria-controls="navigation" aria-label="Buka menu" :aria-label="menu ? 'Tutup menu' : 'Buka menu'"><span></span><span></span><span></span></button>
    <nav id="navigation" :class="menu ? 'is-open' : ''" aria-label="Navigasi utama" @click.outside="if (!$event.target.closest('.mobile-menu')) menu=false" @keydown.escape.stop="menu=false; $refs.publicMenuToggle.focus()" @click="if ($event.target.closest('a')) menu=false">
        @foreach(['home'=>'Utama','about'=>'Tentang','play'=>'Cara Bermain','adventure'=>'Capai Pembelajaran'] as $page=>$label)
        <a class="{{ request()->routeIs($page) || ($page === 'play' && request()->routeIs('help')) ? 'active' : '' }}" @if(request()->routeIs($page) || ($page === 'play' && request()->routeIs('help'))) aria-current="page" @endif href="{{ route($page) }}"><x-public-nav-icon :name="$page"/><span>{{ $label }}</span></a>
        @endforeach
        @guest
            <a class="btn btn-small btn-primary {{ request()->routeIs('access.enter') ? 'active' : '' }}" @if(request()->routeIs('access.enter')) aria-current="page" @endif href="{{ route('access.enter') }}"><x-public-nav-icon name="code"/><span>Masukkan Kod</span></a>
            <a class="btn btn-small btn-admin-login {{ request()->routeIs('admin.login') ? 'active' : '' }}" @if(request()->routeIs('admin.login')) aria-current="page" @endif href="{{ route('admin.login') }}"><x-public-nav-icon name="admin"/><span>Log Masuk Pentadbir</span></a>
        @else
            <a href="{{ route('dashboard') }}">Papan Pemuka</a>
            
            <form action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-small btn-outline">Log Keluar</button></form>
        @endguest
    </nav>
</header>

