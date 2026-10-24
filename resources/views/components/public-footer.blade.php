<footer class="public-footer" aria-label="Pengaki Di-BITaRa">
    <div class="footer-sky" aria-hidden="true"><span class="footer-star star-one">✦</span><span class="footer-star star-two">★</span><span class="footer-star star-three">✧</span><span class="footer-planet">🪐</span><x-plane class="footer-plane"/></div>
    <div class="public-footer-grid">
        <a class="public-footer-brand" href="{{ route('home') }}" aria-label="Di-BITaRa - Halaman Utama"><x-brand-logo/><strong class="sr-only">Di-BITaRa</strong><span>Bijak Imbuhan &amp; Tatabahasa<br>Ruang Angkasa</span></a>
        <section class="public-footer-links" aria-labelledby="footer-quick-title">
            <h2 id="footer-quick-title"><span aria-hidden="true">★</span> Pautan Pantas</h2>
            <nav aria-label="Pautan pantas pengaki">
                @foreach([['home','⌂','Utama'],['about','♙','Tentang'],['play','🎮','Cara Bermain'],['adventure','▥','Capai Pembelajaran'],['access.enter','🎫','Masukkan Kod'],['admin.login','✈','Log Masuk Pentadbir']] as [$route,$icon,$label])
                <a href="{{ route($route) }}"><span class="footer-link-icon" aria-hidden="true">{{ $icon }}</span>{{ $label }}</a>
                @endforeach
            </nav>
        </section>
        <section class="public-footer-support" aria-labelledby="footer-support-title">
            <h2 id="footer-support-title"><span aria-hidden="true">★</span> Maklumat &amp; Sokongan</h2>
            <nav aria-label="Maklumat dan sokongan">
                @foreach([['help','🎧','Bantuan'],['privacy','🔒','Privasi'],['terms','▤','Syarat Penggunaan']] as [$route,$icon,$label])
                <a href="{{ route($route) }}"><span class="footer-link-icon" aria-hidden="true">{{ $icon }}</span>{{ $label }}<b aria-hidden="true">›</b></a>
                @endforeach
            </nav>
        </section>
    </div>
    <x-clouds/>
    <div class="public-footer-copyright"><span aria-hidden="true">✦</span><p>Hak Cipta Terpelihara © 2026 Di-BITaRa</p><span aria-hidden="true">✦</span></div>
</footer>
