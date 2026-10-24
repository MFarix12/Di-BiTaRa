@php
    $isAdmin = auth()->user()->role === 'admin';
    $items = $isAdmin ? [
        ['admin.dashboard','▦','Papan Pemuka'], ['admin.contents','▤','Kandungan'], ['admin.notes','▣','Nota'],
        ['admin.quizzes','✈','Kuiz & Aktiviti'], ['admin.questions','?','Soalan & Jawapan'], ['admin.instructions','☷','Arahan'],
        ['admin.audio','♫','Audio'], ['admin.media','▧','Media'], ['admin.zone3.preview','▷','Pratonton Pelajar'],
        ['admin.codes','🎫','Kod Akses'], ['admin.sessions','♙','Keputusan / Sesi'], ['admin.audit','◷','Log Audit'],
    ] : [];
@endphp
<aside id="workspace-sidebar" class="workspace-sidebar {{ $isAdmin ? 'is-admin' : 'is-student' }}" :class="{'drawer-open':sidebarOpen}" aria-label="{{ $isAdmin ? 'Navigasi Pentadbir' : 'Navigasi Pelajar' }}">
    <button class="drawer-close" @click="sidebarOpen=false" type="button" aria-label="Tutup menu sisi">×</button>
    <a class="sidebar-brand" href="{{ route($isAdmin ? 'admin.dashboard' : 'student.dashboard') }}"><x-brand-logo :decorative="true"/><span>Di-BITaRa<small class="brand-subtitle">Bijak Imbuhan & Tatabahasa Ruang Angkasa</small><small>{{ $isAdmin ? 'Pentadbir' : 'Juruterbang Ilmu' }}</small></span></a>
    <button class="collapse-toggle" type="button" @click="collapsed=!collapsed" :aria-expanded="!collapsed" aria-controls="sidebar-menu" :aria-label="collapsed ? 'Kembangkan menu sisi' : 'Kecilkan menu sisi'">⇄ <span class="menu-label">Kecilkan menu</span></button>
    <nav id="sidebar-menu" aria-label="Menu {{ $isAdmin ? 'Pentadbir' : 'Pelajar' }}">
        @foreach($items as [$name,$icon,$label])
        @php($studioActive=request()->routeIs('admin.studio.*') && (['notes'=>'admin.notes','activities'=>'admin.quizzes','questions'=>'admin.questions','instructions'=>'admin.instructions','audio'=>'admin.audio','media'=>'admin.media'][request()->route('module')]??null)===$name)
        @php($itemActive=request()->routeIs($name) || $studioActive || ($name==='admin.zone3.preview' && request()->routeIs('admin.activity.preview')))
        <a href="{{ route($name) }}" title="{{ $label }}" class="{{ $itemActive ? 'selected' : '' }}" @if($itemActive) aria-current="page" @endif><span class="nav-icon" aria-hidden="true"><x-admin-nav-icon :name="$name"/></span><span class="menu-label">{{ $label }}</span></a>
        @endforeach
    </nav>
    <div class="sidebar-art"><span class="sidebar-control-dot" aria-hidden="true"></span><p>Pusat Kawalan<br><strong>Di-BITaRa</strong></p></div>
</aside>
