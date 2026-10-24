@props(['name'])
@php($paths=[
'admin.dashboard'=>'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
'admin.contents'=>'M4 4h16v16H4z M8 8h8 M8 12h8 M8 16h5',
'admin.notes'=>'M4 3h12l4 4v14H4z M16 3v5h4 M8 12h8 M8 16h6',
'admin.quizzes'=>'M5 7h14l3 11-4 2-4-5h-4l-4 5-4-2z M6 10v5 M3.5 12.5h5 M17 11h.01 M19 14h.01',
'admin.questions'=>'M9 8a3 3 0 1 1 5 2c-2 1-2 2-2 3 M12 17h.01 M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20',
'admin.instructions'=>'M9 5h12 M9 12h12 M9 19h12 M3 5h1 M3 12h1 M3 19h1',
'admin.audio'=>'M10 4v13a3 3 0 1 1-3-3h3 M10 4l10-2v12a3 3 0 1 1-3-3h3',
'admin.media'=>'M3 3h18v18H3z M3 17l6-6 4 4 3-3 5 5 M16 7h.01',
'admin.zone3.preview'=>'M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7 M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6',
'admin.codes'=>'M3 5h18v5a2 2 0 0 0 0 4v5H3v-5a2 2 0 0 0 0-4z M15 8v8',
'admin.sessions'=>'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8 M18 4a4 4 0 0 1 0 7 M22 21v-2a4 4 0 0 0-3-4',
'admin.audit'=>'M4 4h16v17H4z M8 3h8v4H8z M8 11h8 M8 15h6',
])
<svg {{ $attributes->class(['admin-nav-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['admin.contents'] }}"/></svg>
