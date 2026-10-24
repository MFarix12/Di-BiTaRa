@props(['decorative'=>false])
<img {{ $attributes->class(['pilot-portrait','admin-official-avatar']) }} src="{{ asset('images/admin-officer-avatar.png') }}" alt="{{ $decorative ? '' : 'Pentadbir / Pegawai Kawalan Di-BITaRa' }}" width="1024" height="1536" decoding="async">
