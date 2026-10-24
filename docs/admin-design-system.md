# Design system Pentadbir Di-BITaRa


### Design system Pentadbir menyeluruh — 23 Oktober 2026

resources/css/admin-theme.css dimuatkan selepas tema lain dan diasingkan kepada body.admin-body. Semua halaman Pentadbir berkongsi font sistem yang jelas, heading navy, latar pusat kawalan ber-overlay lembut, kad putih solid, padding/border/radius/shadow seragam, butang biru utama dan sekunder berbingkai, focus ring jelas, jadual berjalur dengan header kontras, carian sebaris, medan borang dan placeholder yang jelas, modal serta dropdown seragam.

Sidebar menggunakan ikon SVG sekeluarga, highlight navy dengan aksen emas, hover, collapse 82px dan drawer telefon. Navbar compact memaparkan logo, tajuk halaman dan avatar/dropdown Profil/Tetapan/Log Keluar. Statistik dashboard: Nota, Aktiviti, Soalan, Audio, Kod Aktif (tidak tamat tempoh), Kandungan Aktif dan Sesi. Komponen admin-status menyelaraskan warna status pada kandungan, pengguna, kod, laporan dan keputusan sesi. Merah dikhususkan untuk Padam/ralat/tidak aktif.

Semakan QA dengan izin pengguna: dashboard, soalan, borang tambah, kandungan/nota, kuiz, arahan, audio, media, kod akses, sesi, laporan, audit, profil/tetapan; desktop 1280px, tablet 820px dan telefon 390px. Jadual telefon scroll dalam card (346px ruang, 650px kandungan), tiada limpahan halaman; drawer, collapse sidebar dan modal logout disemak. Fungsi CRUD/validasi, data perjalanan, markah dan kandungan tidak diubah.

Nisbah kontras pasangan palet utama pada putih: teks utama 13.52:1, teks sekunder 5.98:1, placeholder 4.75:1; teks putih pada butang biru 7.05:1; sidebar 11.31:1. Ini semakan palet utama, bukan audit menyeluruh setiap piksel.

Build berjaya dan 51 ujian lulus (1,432 assertions). Bukti: docs/screenshots/admin-redesign-dashboard.png, admin-redesign-table.png, admin-redesign-form.png, admin-redesign-mobile.png, admin-redesign-tablet.png, admin-redesign-modal.png.
