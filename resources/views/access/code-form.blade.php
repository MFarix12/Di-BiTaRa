<form method="post" action="{{ route('access.start') }}" class="form-stack access-code-form">@csrf
<label>Masukkan Kod Pengembaraan<input name="code" value="{{ old('code') }}" required maxlength="20" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="DBR-T4-XXXXXX"></label>
@error('code')<p class="notice error" role="alert">{{ $message }}</p>@enderror
<button class="btn btn-primary">✈ Mula Pengembaraan</button><p class="muted">Dapatkan kod daripada guru.</p>
</form>
