<x-layout :title="$activity['title']" :zone="4"><section class="mission-page"><header class="mission-heading"><div><span>ZON 4 · Cabaran Kecemasan</span><h1>{{ $activity['title'] }}</h1></div></header>
<article class="meaning-stage"><span aria-hidden="true">📖</span><h2>Maksud Peribahasa</h2><p>Baca maksud, kemudian teruskan.</p></article>
<div x-data="{read:false}" x-init="$refs.meaning.showModal()"><dialog x-ref="meaning" class="meaning-dialog zone-confirm-dialog" aria-labelledby="meaning-heading" @cancel.prevent>
<span class="meaning-icon" aria-hidden="true">💡</span><h2 id="meaning-heading">Maksud Peribahasa</h2><blockquote>{{ $meaning['sentence'] }}</blockquote><p class="meaning-copy">{{ $meaning['meaning'] }}</p>
<form method="post" action="{{ route('student.meaning',$record->id) }}">@csrf<input type="hidden" name="question_id" value="{{ $meaningQuestion->id }}"><label class="meaning-read"><input type="checkbox" name="read" value="1" required x-model="read">Saya telah membaca maksud.</label><button class="btn btn-primary" :disabled="!read">Seterusnya ›</button></form>
</dialog></div></section></x-layout>
