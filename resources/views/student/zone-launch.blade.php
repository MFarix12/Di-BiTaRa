<x-layout title="Mula Zon {{ $zone['sequence'] }}" :zone="$zone['sequence']">
    @php($alreadyStarted = !($challengeCountdown ?? false) && app(\App\Services\ZoneClock::class)->ready(app(\App\Services\LearningEngine::class)->journey(auth()->user()), $zone['id']))
    <div class="zone-launch-page">
        <dialog open class="zone-countdown" data-zone-countdown data-seconds="{{ \App\Services\ZoneStructure::timed($zone['sequence']) && ! $alreadyStarted ? 3 : 0 }}" aria-labelledby="countdown-title">
            <div class="countdown-plane" aria-hidden="true">✈</div>
            <p class="zone-countdown-label">Zon {{ $zone['sequence'] }} · {{ $zone['name'] }}</p>
            <h1 id="countdown-title">{{ $alreadyStarted ? 'Teruskan misi!' : 'Bersedia, juruterbang!' }}</h1>
            <strong class="countdown-number" data-countdown-number aria-live="assertive" aria-atomic="true">{{ \App\Services\ZoneStructure::timed($zone['sequence']) && ! $alreadyStarted ? '3' : 'Mula' }}</strong>
            <p data-countdown-caption>{{ $alreadyStarted ? 'Masa misi anda diteruskan.' : 'Jom mulakan cabaran!' }}</p>
            @error('countdown')<p role="alert">{{ $message }}</p>@enderror
            <form method="post" action="{{ route('student.zone.start', $zone['sequence']) }}" data-countdown-form>
                @csrf
                <input type="hidden" name="next" value="{{ $target['next'] ?? 'catalog' }}">
                @if(isset($target['target']))<input type="hidden" name="target" value="{{ $target['target'] }}">@endif
            </form>
            <noscript><p>Aktifkan JavaScript untuk memulakan cabaran secara automatik.</p></noscript>
        </dialog>
    </div>
</x-layout>
