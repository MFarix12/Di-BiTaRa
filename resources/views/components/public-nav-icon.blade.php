@props(['name'])
<svg class="public-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($name)
@case('home')<path d="m3 10 9-7 9 7M5 9v12h5v-7h4v7h5V9"/>@break
@case('about')<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/>@break
@case('play')<path d="M7 7h10a4 4 0 0 1 4 3l1 7a2 2 0 0 1-3 2l-4-3H9l-4 3a2 2 0 0 1-3-2l1-7a4 4 0 0 1 4-3Z"/><path d="M7 10v4M5 12h4M16 11h.01M19 13h.01"/>@break
@case('adventure')<path d="M4 21V5M4 6c5-5 11 5 16 0v10c-5 5-11-5-16 0"/>@break
@case('code')<path d="M3 5h18v5a2 2 0 0 0 0 4v5H3v-5a2 2 0 0 0 0-4Z"/><path d="M9 5v3m0 3v2m0 3v3M13 9h4M13 14h3"/>@break
@case('admin')<path d="m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6Z"/><circle cx="12" cy="10" r="2"/><path d="M8 17v-1a4 4 0 0 1 8 0v1"/>@break
@endswitch
</svg>
