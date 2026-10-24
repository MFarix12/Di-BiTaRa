# Pelaksanaan dokumentasi lengkap v2 — 22 Oktober 2026

Sumber utama: `Dokumentasi_Lengkap_Sistem_Di-BiTaRa.pdf` (8 halaman), dibaca sepenuhnya sebelum pelaksanaan. Soalan sebenar kekal daripada `Soalan Kuiz.pdf`; dokumen lengkap turut mengesahkan tiga jawapan Imlak.

## Perbezaan dan fail terlibat

1. Akaun Pelajar diganti kod akses dan sesi sebenar, tanpa mencipta akaun tersembunyi dalam users. Migration, LearnerSession, LearnerIdentity, pengesahan sesi, AccessSessionController serta routes/web.php.
2. Semua kemajuan dimiliki sesi dan kod: LearningEngine, StudentOverview, PassportOverview, DepartureReadiness, DeparturePreparation, Tutorial, MediaController dan StudentCertificateController. Rekod akaun lama dikekalkan sebagai sejarah.
3. UI awam menggunakan Masukkan Kod. Pendaftaran, login, lupa kata laluan dan profil akaun Pelajar dikeluarkan daripada flow. Top-navigation/footer/home/help serta navbar sesi dikemas kini. Pentadbir kekal login berasingan.
4. Kod sah terus ke Zon 1: nama/kelas pilihan, avatar berdiri penuh, pengesahan, cap, kapal terbang dan terus nota Zon 2. Tutorial berstatus pada sesi dan menyasarkan perjalanan, cap, aktiviti, audio serta tamat sesi.
5. Satu jam sahaja: bermula selepas countdown pertama Zon 3, tidak reset semasa cabaran/zon/transition, berhenti selepas refleksi. Countdown setiap cabaran Zon 3/4; Zon 1/2/5 tiada countdown. Sesi kod mengikut had tiga audio tanpa bantuan audio tambahan. Keputusan ini menggantikan pengecualian terdahulu yang bercanggah dengan sumber terkini.
6. Pentadbir: AccessCodeController dan paparan pengurusan kod, keputusan sesi, penggunaan, tamat tempoh, laporan dan audit. CRUD kandungan, rakaman/pratonton audio, penerbitan, avatar dan media kekal.
7. Pasport dan sijil menunjukkan identiti sesi. Tamat Sesi menutup akses peranti tanpa memadam keputusan. Kunci zon, pemilikan nota/jawapan/draf/audio/sijil dan pemisahan Pentadbir disemak pada pelayan.

## Urutan pelaksanaan

Baca sumber → petakan perbezaan → skema tambahan dan identiti sesi → akses kod Pentadbir/Pelajar → servis dan UI sesi → selaraskan timer/audio/tutorial → migrasi sejarah selamat → ujian end-to-end dan semakan pelayar desktop/telefon → dokumentasi akhir.

Tiada reset pangkalan data, akaun, snapshot, jawapan atau sijil sejarah. Migrasi diuji pada pangkalan data QA berasingan sebelum diaplikasikan setempat.

## Pengesahan akhir — 22 Oktober 2026

- `php artisan test --compact`: **46 ujian lulus, 1,137 assertions**.
- `npm run build`: berjaya.
- Ujian pelayar menggunakan pangkalan data QA berasingan: kod → Zon 1/avatar → nota/audio Zon 2 → semua 26 soalan → refleksi → lima cap → sijil → Tamat Sesi → keputusan kekal dalam Pentadbir.
- Salah pada cubaan pertama direkodkan berasingan; had audio tiga kali, countdown setiap cabaran, masa tunggal hingga refleksi dan rekod sesi berlainan disahkan.
- Desktop dan telefon 390px disemak tanpa limpahan mendatar. Kamera/hand detection fizikal belum diuji dengan peranti; laluan alternatif tanpa kamera disahkan.
- Pangkalan data utama dinaik taraf selepas sandaran SQLite; semua lajur/rekod asal bagi akaun, perjalanan, jawapan, cap, sijil dan snapshot dibandingkan tanpa kehilangan data.
- Halaman utama, Masukkan Kod dan Log Masuk Pentadbir di `http://localhost:8000` memberi HTTP 200. Kod dan sesi ujian hanya dicipta dalam QA.
- Bukti UI: [Pasport lengkap](screenshots/access-session-passport.png), [keputusan Pentadbir](screenshots/access-session-admin-results.png), [telefon](screenshots/access-session-mobile.png).

