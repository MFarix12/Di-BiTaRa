@props(['name' => 'user'])
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes->class(['registration-icon']) }}>
@switch($name)
@case('lock')<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>@break
@case('student')<path d="m2 8 10-5 10 5-10 5-10-5Zm4 3v6c4 3 8 3 12 0v-6M22 8v8"/>@break
@default<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2Z"/>
@endswitch
</svg>
