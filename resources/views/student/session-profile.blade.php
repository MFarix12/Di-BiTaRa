<x-layout title="Profil Sesi">
@php($learner=auth()->user())
@php($avatar=$learner->avatar_id ? DB::table('avatars')->find($learner->avatar_id) : null)
<section class="session-profile-page"><article class="panel session-profile-card"><span class="eyebrow">✈ JURUTERBANG Di-BITaRa</span><h1>Profil Anda</h1>
@if($avatar)<x-pilot-avatar :code="$avatar->code" name="Avatar anda"/>@else<span class="profile-placeholder" aria-hidden="true">🧑‍✈️</span>@endif
<h2>{{ $learner->display_name }}</h2><dl><div><dt>Sesi</dt><dd>{{ $learner->username }}</dd></div><div><dt>Tahun</dt><dd>Tahun {{ $learner->school_year }}</dd></div><div><dt>Kelas</dt><dd>{{ $learner->class_name ?: 'Belum diisi' }}</dd></div><div><dt>Avatar</dt><dd>{{ $avatar ? \App\Services\AvatarCatalogue::details($avatar)['role'] : 'Pilih di Zon 1' }}</dd></div></dl>
<a class="btn btn-primary" href="{{ route('student.activities') }}">Teruskan Pengembaraan ✈</a>
</article></section>
@if(request()->boolean('tutorial'))<livewire:tutorial :replay="true"/>@endif
</x-layout>
