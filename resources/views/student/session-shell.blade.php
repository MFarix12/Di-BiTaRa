<!DOCTYPE html><html lang="ms"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pengembaraan Pelajar | Di-BITaRa</title><link rel="icon" href="{{ asset('images/official-logo.png') }}">
<style>html,body{margin:0;width:100%;height:100%;background:#092e65}iframe{display:block;width:100%;height:100dvh;border:0}</style>
@vite('resources/js/session-audio-shell.js')
</head><body><div data-session-audio-shell data-session="{{ $learner->public_id }}" data-src="{{ asset('audio/journey/adventure-loop.wav') }}" data-beep="{{ asset('audio/journey/countdown-beep.wav') }}" data-start="{{ asset('audio/journey/countdown-start.wav') }}"></div>
<iframe data-session-frame title="Pengembaraan pembelajaran Di-BITaRa" src="{{ $contentUrl }}" allow="autoplay; fullscreen"></iframe>
<noscript>Aktifkan JavaScript untuk muzik berterusan. <a href="{{ $contentUrl }}">Buka pengembaraan</a></noscript>
</body></html>
