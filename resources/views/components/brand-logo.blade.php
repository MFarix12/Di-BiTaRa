@props(['decorative' => false])
<img src="{{ asset('images/official-logo.png') }}" alt="{{ $decorative ? '' : 'Logo rasmi Di-BITaRa' }}" width="1254" height="1254" {{ $attributes->class(['official-brand-logo']) }}>
