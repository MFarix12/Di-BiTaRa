<x-layout title="Log Masuk Pentadbir">
<section class="auth-scene star-field">
<div class="auth-intro"><span class="pill">PANEL PENTADBIR Di-BITaRa</span><h1>Selamat datang ke Pusat Kawalan Pentadbir.</h1><p>Urus kandungan, kod akses dan sesi pembelajaran.</p><x-brand-logo class="auth-logo"/></div>
<section class="form-card admin-login-card" aria-labelledby="admin-login-title">
<header class="admin-login-heading"><span class="admin-login-emblem" aria-hidden="true"><x-public-nav-icon name="admin"/></span><div><span class="eyebrow">AKSES KHAS PENTADBIR</span><h2 id="admin-login-title">Log Masuk Pentadbir</h2></div></header>
<x-feedback/>
<form method="post" action="{{ route('admin.login.submit') }}" class="form-stack admin-login-form">
@csrf
<label for="admin-identity">ID Pentadbir / E-mel<input id="admin-identity" name="identity" value="{{ old('identity') }}" required maxlength="254" autocomplete="username" autofocus></label>
<label for="admin-password">Kata Laluan<input id="admin-password" name="password" type="password" autocomplete="current-password" required></label>
<label class="admin-remember" for="admin-remember"><input id="admin-remember" type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Ingat Saya</span></label>
<button class="btn btn-primary admin-login-submit"><span>Log Masuk Pentadbir</span><span aria-hidden="true">→</span></button>
</form>
</section></section></x-layout>
