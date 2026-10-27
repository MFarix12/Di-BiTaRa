@props(['title' => 'Belajar. Bermain. Berkemajuan.', 'admin' => false, 'zone' => null])
@php($zoneTheme = config('zone-backgrounds.'.(int) $zone))
<!DOCTYPE html><html lang="ms"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Di-BITaRa: pengembaraan pembelajaran imbuhan dan tatabahasa Bahasa Melayu melalui lima zon penerbangan.">
<title>{{ $title }} | Di-BITaRa — Bijak Imbuhan & Tatabahasa Ruang Angkasa</title><link rel="icon" type="image/png" href="{{ asset('images/official-logo.png') }}">
@if(request()->routeIs('access.enter'))<link rel="preload" as="image" href="{{ asset('images/access-adventure-background.png') }}">@elseif(request()->routeIs('admin.login'))<link rel="preload" as="image" href="{{ asset('images/admin-control-background.png') }}">@endif
@if($zoneTheme)<link rel="preload" as="image" href="{{ asset($zoneTheme['image']) }}">@endif
@vite(['resources/css/app.css', 'resources/css/reference-theme.css', 'resources/css/modern-theme.css', 'resources/css/branding.css', 'resources/css/admin-theme.css', 'resources/css/public-theme.css', 'resources/js/app.js']) @livewireStyles
</head><body class="modern-ui {{ request()->routeIs('home','about','play','adventure','help','access.enter','admin.login') ? 'public-nav-theme' : '' }} {{ request()->routeIs('home','about','play','adventure','help') ? 'public-redesign' : '' }} {{ auth()->check() ? 'signed-in' : 'public-page' }} {{ auth()->user()?->role === 'admin' ? 'admin-body' : '' }} {{ auth()->user()?->role === 'pelajar' ? 'student-top-layout' : '' }} {{ request()->routeIs('home') ? 'home-page' : '' }} {{ request()->routeIs('student.achievements') ? 'achievements-page' : '' }} {{ request()->routeIs('student.profile') ? 'learner-profile-page' : '' }} {{ request()->routeIs('access.enter') ? 'access-entry-page' : '' }} {{ request()->routeIs('access.enter','admin.login') ? 'public-auth-page' : '' }} {{ request()->routeIs('admin.login') ? 'admin-access-page' : '' }} {{ $zoneTheme ? 'has-zone-scene zone-scene-'.(int) $zone : '' }}" @if($zoneTheme) style="--zone-scene-image:url('{{ asset($zoneTheme['image']) }}')" data-zone-scene="{{ (int) $zone }}" @endif data-public-scene="{{ request()->routeIs('home') ? 'home' : (request()->routeIs('about') ? 'about' : (request()->routeIs('play','help') ? 'play' : (request()->routeIs('adventure') ? 'outcomes' : ''))) }}" data-admin-theme="{{ auth()->user()?->role === 'admin' ? (auth()->user()->admin_preferences['theme'] ?? 'light') : 'light' }}" data-admin-text="{{ auth()->user()?->role === 'admin' ? (auth()->user()->admin_preferences['text_size'] ?? 'normal') : 'normal' }}" data-admin-motion="{{ auth()->user()?->role === 'admin' && !(auth()->user()->admin_preferences['animations'] ?? true) ? 'off' : 'on' }}" x-data="{menu:false, sidebarOpen:false, collapsed:false}" @keydown.escape.window="menu=false; sidebarOpen=false">
<a href="#kandungan-utama" class="skip-link">Langkau ke kandungan utama</a>
@auth
@if(auth()->user()->role === 'admin')
<div class="workspace" :class="{'sidebar-collapsed':collapsed}">
    <button class="sidebar-backdrop" x-show="sidebarOpen" x-cloak @click="sidebarOpen=false" aria-label="Tutup navigasi"></button>
    <x-workspace-sidebar/>
    <div class="workspace-main">
        <header class="workspace-toolbar">
            <button type="button" class="drawer-toggle" @click="sidebarOpen=!sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="workspace-sidebar" aria-label="Buka navigasi">☰</button>
            <a class="admin-toolbar-brand" href="{{ route('admin.dashboard') }}" aria-label="Di-BITaRa - Pusat Kawalan"><x-brand-logo :decorative="true"/></a><span class="toolbar-path"><small>PUSAT KAWALAN</small><span>{{ $title }}</span></span>
            <x-admin-account/>
        </header>
        <main id="kandungan-utama" tabindex="-1">{{ $slot }}</main>
    </div>
</div>
@else
@if(\App\Services\LearnerIdentity::isSession(auth()->user()))<x-session-navigation/>@else<x-top-navigation/>@endif
<div class="student-stage"><aside class="pupil-connection-state" data-pupil-connection hidden role="status"><img src="{{ asset('images/pilot-mascot.png') }}" alt="" width="45" height="55"><span>Internet terganggu. Tunggu sambungan pulih. Draf yang sempat disimpan boleh disambung.</span></aside>
<main id="kandungan-utama" class="student-main" tabindex="-1">@if($zoneTheme && ! request()->routeIs('student.zone.launch'))<x-zone-timer :sequence="(int) $zone"/>@endif{{ $slot }}</main></div>
@endif
@else
<x-top-navigation/>
<main id="kandungan-utama" tabindex="-1">{{ $slot }}</main>
@endauth
@guest<x-public-footer/>@endguest
@livewireScripts</body></html>
