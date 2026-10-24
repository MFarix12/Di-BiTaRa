<x-layout title="Maklumat Pelajar">
<section class="learner-details-scene"><article class="access-entry-card learner-details-card">
<span class="access-ticket" aria-hidden="true">🎫<i>✓</i></span>
<p class="learner-step">KOD SAH · TAHUN {{ auth()->user()->school_year }}</p>
<h1>Kenali Juruterbang!</h1><p>Isi nama dan kelas anda.</p>
<x-feedback/>
<form method="post" action="{{ route('student.details.save') }}" class="form-stack">
@csrf
<label for="learner-name">Nama Pelajar<input id="learner-name" name="display_name" value="{{ old('display_name') }}" maxlength="100" required autocomplete="name" placeholder="Nama anda" autofocus></label>
<label for="learner-class">Kelas<input id="learner-class" name="class_name" value="{{ old('class_name') }}" maxlength="50" required autocomplete="off" placeholder="Contoh: {{ auth()->user()->school_year }} Cemerlang"></label>
<button class="btn btn-primary">Seterusnya <span aria-hidden="true">✈</span></button>
</form>
</article></section>
</x-layout>
