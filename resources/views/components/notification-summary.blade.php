@php($latestNotifications=\App\Services\LearnerIdentity::notifications(auth()->user())->latest('id')->limit(3)->get())
@forelse($latestNotifications as $notification)<p><strong>{{ $notification->title }}</strong><br>{{ $notification->body }}</p>@empty<p>Tiada notifikasi baharu.</p>@endforelse<a href="{{ route('student.notifications') }}">Lihat Semua Notifikasi ›</a>
