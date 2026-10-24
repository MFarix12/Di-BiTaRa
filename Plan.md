# Pelan Sistem Di-BITaRa

Versi aktif: Dokumentasi Lengkap Sistem v2, 22 Oktober 2026. Nama rasmi **Di-BITaRa — Bijak Imbuhan & Tatabahasa Ruang Angkasa**.

## Sumber utama

[Dokumentasi lengkap 8 halaman](docs/references/Dokumentasi_Lengkap_Sistem_Di-BiTaRa.pdf) menggantikan flow akaun terdahulu. SHA-256: `d5f9d822583f0999f4d91515b39b5a524ae375170656f5104a93d25a1bfeb011`.
[Soalan sebenar dan skema](docs/references/Soalan%20Kuiz.pdf) digunakan tanpa placeholder; pemetaan tepat dalam [flow aktif](docs/references/latest-learning-flow.md). Dokumen sejarah dalam docs/history tidak menentukan pelaksanaan semasa.

## Prinsip dan skop

Bahasa Melayu sepenuhnya, teks ringkas, visual ceria dan mesra murid. Logo rasmi, navy/biru, kuning/emas dan putih; penerbangan, ruang angkasa, awan, bintang dan avatar berdiri penuh. Top navbar Pelajar; sidebar Pentadbir. Desktop, laptop, tablet dan telefon.

Pelajar tidak mempunyai akaun. Kod daripada Pentadbir → pengesahan → sesi baharu → Zon 1 hingga 5 → tamat. Nama/kelas pilihan, tiada kata laluan, pendaftaran, profil akaun atau log keluar akaun. Pentadbir kekal log masuk biasa. Semua kemajuan dimiliki sesi dan kod, bukan users.

## Modul awam dan navigasi

Utama | Tentang | Cara Bermain | Capai Pembelajaran | Masukkan Kod | Log Masuk Pentadbir. Setiap satu halaman berasingan, active state jelas dan hamburger responsif. Footer hanya halaman umum: logo, nama penuh, pautan cepat, bantuan/privasi/syarat serta hak cipta 2026.

## Kod akses dan sesi

Pentadbir mencipta kod rawak unik, nama aktiviti, status aktif/tidak aktif dan tamat tempoh pilihan (input waktu Malaysia, simpan UTC). Kemas kini direkod Audit Log. Paparkan bilangan penggunaan dan keputusan setiap sesi. Kod tidak sah, dinyahaktifkan atau tamat tempoh ditolak dengan mesej Melayu. Had cubaan gagal tidak mengehadkan bilangan kemasukan kod sah daripada IP sekolah yang sama.

Satu kod boleh menghasilkan banyak sesi bebas. Token rahsia 256-bit dalam sesi Laravel, hash dalam learner_sessions; tidak dipaparkan dalam URL/HTML/laporan. Sesi aktif sama tidak diduplikasi oleh penghantaran semula. Tamat Sesi memerlukan pengesahan dan tidak memadam keputusan. Kod tidak memberi akses kepada keputusan sesi lain.

## Perjalanan satu hala

| Zon | Syarat dan aktiviti |
|---|---|
| 1 Balai Pelepasan | Nama/kelas pilihan, avatar, pratonton, Mari Meneroka, pengesahan OK, cap |
| 2 Taklimat Penerbangan | Nota visual/audio automatik, Sudah Baca dan Teruskan, cap |
| 3 Simulasi Penerbangan | 6 Betul/Salah imbuhan → markah → 6 Betul/Salah kata sendi → markah → 10 kad klasifikasi → markah zon/cap |
| 4 Cabaran Kecemasan | Kamera pilihan atau klik/sentuh/papan kekunci; 5 Susun Ayat → markah → 5 Isi Tempat Kosong + salin ayat → markah zon/cap |
| 5 Balai Ketibaan & Imlak | Popup ketibaan → tiga Imlak → refleksi lima emoji → jam berhenti → cap akhir/pencapaian/sijil |

Countdown 3 → 2 → 1 → Mula sebelum setiap cabaran Zon 3 dan 4 sahaja. Jam tunggal bermula selepas countdown pertama Zon 3 dan diteruskan tanpa reset hingga refleksi Zon 5. Tiada timer berasingan setiap zon. Tiga kali audio Imlak, tanpa audio tambahan. Salah kekal pada soalan semasa. Peribahasa menunjukkan maksud selepas betul; isi kosong menyemak ejaan serta ayat lengkap.

Selesai zon → animasi cop → kapal terbang besar → zon seterusnya automatik. Tiada kembali ke senarai pemilihan atau zon lama. Zon terkunci tidak boleh diklik dan semua laluan dilindungi pelayan. Reduced motion menggunakan butang teruskan. Ulang Kaji selepas tamat berasingan dan tidak mengubah rekod asal.

## Tutorial dan paparan sesi

Guided tutorial ringkas enam langkah di atas Zon 1, overlay/spotlight, Sebelumnya/Seterusnya/Langkau/Selesai. Status pada sesi; boleh lihat semula melalui Bantuan. Fokus perjalanan, avatar, aktiviti/audio, cap/pasport dan Tamat Sesi.

Navbar sesi: Kuiz & Aktiviti | Pasport & Pencapaian | Profil | Tamat Sesi. Profil ialah ringkasan sesi/avatar, bukan akaun. Tiada Papan Pemuka atau Notifikasi. Laluan lama /pelajar diarahkan kepada aktiviti semasa; logo juga menuju aktiviti semasa. Kemajuan/cap/markah/sijil berada dalam Pasport & Pencapaian. Tiada Peta Zon berasingan.

Pasport: nama/avatar/sesi, lima cap/status, lencana, pencapaian terkini, masa keseluruhan dan sijil. Kelulusan ialah penguasaan semua jawapan, bukan kelajuan. Laporan turut membezakan markah cubaan pertama dan rekod pembetulan/bantuan arahan.

## Pentadbir dan kandungan

Sidebar: Papan Pemuka, Kod Akses, Kandungan, Nota, Kuiz & Aktiviti, Soalan & Jawapan, Arahan, Audio, Media, Pratonton Pelajar, Keputusan / Sesi, Laporan, Audit Log. Dropdown atas: Profil, Tetapan, Log Keluar dengan modal pengesahan.

CRUD penuh, validasi jenis mengikut zon, upload/rakaman/pratonton audio dan penggantian berversi. Penerbitan membekukan manifest soalan, jawapan, media dan dasar pemarkahan. Sesi yang telah bermula tidak terjejas suntingan/pemadaman katalog. Pratonton lima zon/aktiviti tidak memberi cap atau mengubah jawapan Pelajar.

## Data, keselamatan dan migrasi

36 jadual domain. access_codes dan learner_sessions ditambah; student_journeys dan student_notifications mempunyai pemilik sesi nullable. user_id kekal untuk sejarah sahaja. Skema jawapan/submission/draf/cap/refleksi/sijil dikekalkan. Foreign key, transaksi, CSRF, session regeneration, token hash, had cubaan gagal, validasi fail, pemilikan sesi dan peranan Pentadbir dilaksanakan pada pelayan. Tiada kunci jawapan bocor sebelum betul.

Sandaran SQLite sebelum migrasi. Bandingkan semua lajur/rekod lama bagi akaun, perjalanan, jawapan, cap, sijil dan snapshot; jangan gunakan migrate:fresh pada data diperlukan. Account, masa dan sijil sejarah tidak dikosongkan. Ujian merangkumi perjalanan penuh, kod, tamat sesi, pemilikan, timer, audio, sumber soalan, CRUD, media, tutorial, Bahasa Melayu, branding dan layout.

## Pengesahan akhir — 22 Oktober 2026

- `php artisan test --compact`: **46 ujian lulus, 1,137 assertions**.
- `npm run build`: berjaya.
- Ujian pelayar menggunakan pangkalan data QA berasingan: kod → Zon 1/avatar → nota/audio Zon 2 → semua 26 soalan → refleksi → lima cap → sijil → Tamat Sesi → keputusan kekal dalam Pentadbir.
- Salah pada cubaan pertama direkodkan berasingan; had audio tiga kali, countdown setiap cabaran, masa tunggal hingga refleksi dan rekod sesi berlainan disahkan.
- Desktop dan telefon 390px disemak tanpa limpahan mendatar. Kamera/hand detection fizikal belum diuji dengan peranti; laluan alternatif tanpa kamera disahkan.
- Pangkalan data utama dinaik taraf selepas sandaran SQLite; semua lajur/rekod asal bagi akaun, perjalanan, jawapan, cap, sijil dan snapshot dibandingkan tanpa kehilangan data.
- Halaman utama, Masukkan Kod dan Log Masuk Pentadbir di `http://localhost:8000` memberi HTTP 200. Kod dan sesi ujian hanya dicipta dalam QA.
- Bukti UI: [Pasport lengkap](docs/screenshots/access-session-passport.png), [keputusan Pentadbir](docs/screenshots/access-session-admin-results.png), [telefon](docs/screenshots/access-session-mobile.png).



## Penambahbaikan fokus cabaran — 22 Oktober 2026

Arahan baharu pengguna mengatasi susunan UI terdahulu. Utama hanya memperkenalkan sistem: tiada borang kod atau butang masuk kod dalam kandungan utama; pautan navigasi awam ke halaman kod khas dikekalkan. Halaman kod menggunakan satu kad berpusat, ikon tiket, medan besar dan Mula Pengembaraan. Tiada panel kiri atau butang kembali dalam kad.

Pengenalan cabaran tiada Kembali. Countdown 3 → 2 → 1 → Mula menghantar POST secara automatik, tanpa Mula Zon atau Batal; pelayan tetap menguatkuasakan tiga saat. Soalan di atas, timer dalam navbar (menggantikan timer sisi melalui enhancement terkini), tiada butang Papan Pemuka semasa aktiviti. Jawapan yang telah dihantar dan ditolak ditanda merah bersama simbol ✕; jawapan betul mendapat maklum balas hijau. Percubaan semula dan rekod cubaan pertama dikekalkan. Tiada kunci jawapan dihantar kepada klien.

Profil sesi hanya menunjukkan nama, kelas, ID sesi, avatar, tutorial semula dan pautan meneruskan zon semasa. Timer tunggal tidak dihentikan/reset semasa melihat profil atau pasport. Semua kunci zon dan pemilikan sesi kekal pada pelayan. Tiada migrasi pangkalan data diperlukan.

Pengesahan penambahbaikan fokus cabaran: **48 ujian lulus (1,186 assertions)** dan `npm run build` berjaya. Ujian pelayar QA melalui kod sebenar → avatar Zon 1 → nota Zon 2 → countdown automatik Zon 3 → salah/merah → cuba semula/betul/hijau → Profil sesi. Timer kekal berjalan dan tidak reset. Desktop 1280px serta telefon 390px disemak; tiada limpahan mendatar. Bukti di `docs/screenshots/focused-code-desktop.png`, `focused-code-mobile.png`, `focused-challenge-retry.png` dan `focused-challenge-mobile.png`. Pelayan utama localhost:8000 memaparkan kad kod baharu. Tiada data utama atau struktur database diubah untuk penambahbaikan ini.


## Enhancement rujukan PDF — versi UI terkini

Rujuk [pemetaan 10 perkara](docs/project-enhancement-implementation.md). Timer kini dalam navbar, tajuk zon/cabaran di atas dan panel Jawapan Anda dibuang. Tarik dan Lepas tanpa dropdown, kad berwarna dan input pointer/klik/papan kekunci. Canvas berpusat; papan perjalanan lima kad menggunakan latar zon sebenar. Refleksi dan penamat mempunyai gaya seragam. Maksud Susun Ayat mesti dibaca/disahkan sebelum bergerak; penamat kekal hingga disahkan. Sijil menggunakan bingkai pengguna pada preview dan PDF A4. Syarat zon, markah, cap, had audio dan timer keseluruhan dikekalkan.

Migrasi tambahan: `meaning_acknowledged_at` pada attempt_questions, `completion_seen_at` pada student_journeys. Jawapan/cap/sijil sejarah dikekalkan. Pengesahan akhir: 50 ujian lulus (1,359 assertions), build berjaya dan semakan pelayar desktop/telefon.

## Pembetulan latar halaman kod

Imej latar global body dan imej section menyebabkan latar yang sama kelihatan bersambung semula. Halaman kod kini mempunyai kelas khusus `access-entry-page`: body menggunakan warna asas sahaja, satu imej dilukis pada main dengan `background-repeat:no-repeat`, `background-size:cover`, `background-position:center` dan attachment scroll. Section telus dengan min-height diwarisi. Main minimum mengisi viewport selepas navbar dan memanjang mengikut kandungan; footer awam kekal komponen berasingan.

Build berjaya. QA desktop serta telefon 390 × 500 disemak; apabila mesej ralat menjadikan kandungan 807.5px tinggi, main turut 807.5px tanpa repeat atau limpahan mendatar. Bukti: `docs/screenshots/access-background-single.png` dan `access-background-tall-content.png`.

### Penyelarasan latar dengan Log Masuk Pentadbir

Kedua-dua halaman akses kini berkongsi kelas `public-auth-page`: satu imej pada body, `cover`, `no-repeat`, kedudukan `center top`, dan attachment `fixed`, mengikut konsep asal Log Masuk Pentadbir. Main dan section kad kod telus supaya tiada lapisan imej berganda. Kad kod kekal berpusat. Kaedah ini menggantikan latar pada main daripada pembetulan terdahulu. Build berjaya; semakan desktop dan telefon 390px menunjukkan tiada limpahan mendatar.

### Footer awam compact

Logo, slogan, sembilan pautan, kedua-dua tajuk column dan hak cipta dikekalkan. Gaya baharu mengecilkan logo (170px desktop/112px telefon), ikon, padding, jarak menu, hiasan awan dan bar hak cipta. Margin atas turut dikurangkan. Pada telefon, logo/slogan sebaris dan pautan dalam dua lajur dengan sasaran sentuhan minimum 44px.

Build berjaya. Semakan pelayar: desktop 1280px tinggi footer 361.8px (sebelumnya 590px, pengurangan kira-kira 39%); tablet 820px 353.3px; telefon 390px 519.6px. Kesemua 9 pautan kekal dan tiada limpahan mendatar. Bukti: `docs/screenshots/footer-compact-desktop.png` dan `footer-compact-mobile.png`.

### Reka bentuk navbar awam

Navbar awam menggunakan logo yang lebih seimbang, ikon SVG seragam, latar putih/biru lembut dan highlight aktif navy dengan aksen emas. Masukkan Kod menjadi tindakan utama emas; Log Masuk Pentadbir menjadi tindakan sekunder berbingkai. Semua enam pautan asal dikekalkan. Menu hamburger pada tablet/telefon menyokong tutup apabila memilih pautan, klik luar dan kekunci Escape dengan pemulangan fokus.

Build berjaya; NavigationAndAdminLoginTest lulus (6 ujian, 140 assertions). Semakan pelayar pada 1280px, 1200px dan 390px mengesahkan tiada limpahan mendatar serta pautan kod dan menu telefon berfungsi. Bukti: docs/screenshots/public-navbar-desktop.png dan public-navbar-mobile.png.


### Flow awal Pelajar terkini — Nama dan Kelas sebelum Zon 1

Kod → Nama Pelajar dan Kelas (wajib) → Seterusnya → Zon 1 pilih/pratonton/sahkan avatar → Mari Meneroka → cap Zon 1 → transition kapal terbang → Zon 2 hingga Zon 5. Tiada pendaftaran atau log masuk Pelajar.

GET/POST /maklumat-pelajar mengisi medan display_name dan class_name pada learner_sessions yang sudah dipautkan kepada access_code_id. Middleware menghalang akses halaman dan tindakan zon selagi maklumat belum lengkap; sesi boleh ditamatkan. Maklumat yang sudah disimpan tidak boleh ditulis semula melalui borang ini atau komponen avatar. Sesi lama yang tiada nama/kelas diminta melengkapkan maklumat tanpa memadam kemajuan. Avatar, perjalanan, cap, markah, jam dan status kekal menggunakan rekod sesi sedia ada; tiada migrasi atau reset data diperlukan.

Pengesahan: build berjaya; 51 ujian lulus (1,395 assertions). Pelayar QA mengesahkan Nama/Kelas → Zon 1 tanpa borang maklumat diri. Bukti: docs/screenshots/student-details-step.png.


### Dua latar akses berbeza

Masukkan Kod menggunakan imej lapangan terbang ceria (access-adventure-background.png); Log Masuk Pentadbir menggunakan pusat operasi (admin-control-background.png). Kedua-duanya dijana melalui built-in image_gen dan disalin ke public/images. Gaya body route khusus menggantikan perkongsian home-background terdahulu. Cover/center/no-repeat/fixed, kad legap dan preload route dikekalkan. Build berjaya; desktop dan telefon disemak. Prompt dan bukti: docs/access-background-generation.md.


### Logik jawapan dan peribahasa — 23 Oktober 2026

Jawapan betul disimpan kemudian terus ke soalan seterusnya tanpa toast/banner kejayaan. Jawapan salah kekal pada soalan sama; maklum balas seragam “Jawapan kurang tepat, sila cuba lagi.” Tiada penandaan pilihan ditolak atau pendedahan jawapan. Draf, cubaan pertama, pemarkahan, penghantaran idempoten dan timer kekal.

SentenceMeaning hanya memerlukan pengakuan bacaan bagi peribahasa. Dua peribahasa sumber lama dikenal pasti melalui ayatnya; soalan baharu menggunakan config.is_proverb yang boleh diurus melalui editor Pentadbir. Maksud diwajibkan hanya bagi peribahasa. Tajuk: Maksud Peribahasa. Ayat biasa dengan penerangan lama tidak mencetuskan popup. Sesi lama yang sudah menjawab semua soalan tetapi menunggu maksud ayat biasa diselaraskan ketika disambung.

Barcode pasport dibuang. Masa Pengembaraan Saya hanya memaparkan masa keseluruhan dan Betul Cubaan Pertama (lebar penuh). Perekodan learning_help_events dan penambahan audio_help_plays dihentikan; route pencatatan bantuan dan listener frontend dibuang. Metrik/laporan Admin tidak lagi mengira bantuan. Jadual/lajur sejarah dibiarkan tanpa pemadaman data, untuk mengekalkan rekod terdahulu. Bilangan dengar audio biasa (had tiga kali bagi flow semasa) dikekalkan.

Build berjaya. Semakan visual QA: docs/screenshots/passport-no-barcode-no-help.png.

Pengesahan perubahan: keseluruhan 51 ujian lulus (1,370 assertions); selepas penambahan pop-up peribahasa dalam mod ulang kaji, 8 ujian ContinuousAdventureTest lulus (454 assertions). Mod ulang kaji mengekalkan markah, masa dan rekod asal.


### Ringkasan avatar dan cabaran tanpa kamera — 23 Oktober 2026

Kad avatar, pratonton pilihan, profil sesi dan pratonton Pentadbir memaparkan jawatan sahaja. Nama khas dikekalkan sebagai data dalaman katalog untuk keserasian rekod; tidak ditunjukkan sebagai label pilihan. Aksesibiliti imej juga menggunakan jawatan.

Zon 4 menggunakan klik/sentuh/papan kekunci sahaja. Pilihan mod kamera, panel kawalan tangan, permintaan video dan import model pengesanan tangan dibuang. Validation hanya menerima pointer/keyboard; state zone_input_mode lama dibersihkan ketika persediaan cabaran. Rekod input sejarah dikekalkan.

Pautan Lihat Tutorial Semula dibuang daripada kad profil sesi dan paparan profil lama. Tutorial automatik pengguna kali pertama dikekalkan.

Build berjaya; keseluruhan 51 ujian lulus (1,432 assertions), termasuk label avatar tanpa nama, ketiadaan kamera dan penolakan input hand.


### Kad Log Masuk Pentadbir dikemas kini

Kad menggunakan header ikon, aksen biru/emas, border radius 26px, padding dan input lebih seimbang, serta butang lebar penuh. Pautan “Anda seorang Pelajar? Masukkan Kod” dibuang. Checkbox Ingat Saya menggunakan flex dengan label sebaris dan sasaran sentuhan 44px. Nama medan/action/CSRF dan autocomplete login dikekalkan. Build berjaya; NavigationAndAdminLoginTest lulus 6 ujian (140 assertions). Pelayar desktop dan telefon 390px disemak, tanpa limpahan mendatar. Bukti: docs/screenshots/admin-login-card-refined.png dan admin-login-card-mobile.png.


### Nama rasmi Di-BITaRa

Semua teks paparan, label logo teks navbar, title browser, konfigurasi APP_NAME, mesej, kandungan lalai dan teks sijil diseragamkan kepada Di-BITaRa. Logo grafik tidak disentuh. Nama folder, pengecam teknikal PDF/command/env dan padanan keserasian kandungan lama kekal supaya pautan dan data tidak rosak.

Migrasi 000008 menyelaraskan copy tersimpan tanpa mengubah jawapan, markah, manifest snapshot, audit atau identiti murid. Sandaran SQLite dibuat sebelum migrasi. Lima sijil sedia ada dijana semula ke certificates/branding-v4 dengan nama, nombor, tarikh dan fail asal dikekalkan. Keseluruhan 51 ujian lulus (1,432 assertions). Semakan pelayar mengesahkan navbar, title dan footer menggunakan ejaan rasmi.


### Latar Zon 2–4 lebih tajam

Tiga aset sedia ada digunakan sebagai sasaran edit built-in image_gen. Output 1672 × 941 menggantikan rujukan aktif 1280 × 720 melalui config/zone-backgrounds.php; sumber asal tidak dipadam. Latar dan thumbnail menggunakan aset HD yang sama. Pemandangan asal dikekalkan dengan sedikit variasi butiran penjanaan. URL aset disemak HTTP 200. Prompt dan ukuran sebenar: docs/zone-hd-generation.md.


### Identiti Pusat Kawalan Pentadbir

Aset baharu built-in image_gen: public/images/admin-workspace-background.png (1672 × 941) dan public/images/admin-officer-avatar.png (1024 × 1536, RGBA telus, full body). Latar body.admin-body dikongsi semua halaman selepas login dengan cover/center/no-repeat/fixed dan overlay biru lembut; workspace telus supaya latar muncul di belakang kad/jadual legap. Halaman login dan Pelajar menggunakan gaya asal masing-masing. Avatar komponen admin-avatar digunakan pada navbar, profil/tetapan, papan pemuka dan modal log keluar, object-fit contain untuk mengelakkan badan terpotong.

Build berjaya; 6 ujian NavigationAndAdminLoginTest lulus (140 assertions). Aset diperiksa visual serta alpha. Semakan visual melalui log masuk QA tidak diselesaikan kerana auto-review menolak penggunaan kelayakan akaun ujian tanpa arahan jelas; tiada tindakan memintas dilakukan.


### Design system Pentadbir menyeluruh — 23 Oktober 2026

resources/css/admin-theme.css dimuatkan selepas tema lain dan diasingkan kepada body.admin-body. Semua halaman Pentadbir berkongsi font sistem yang jelas, heading navy, latar pusat kawalan ber-overlay lembut, kad putih solid, padding/border/radius/shadow seragam, butang biru utama dan sekunder berbingkai, focus ring jelas, jadual berjalur dengan header kontras, carian sebaris, medan borang dan placeholder yang jelas, modal serta dropdown seragam.

Sidebar menggunakan ikon SVG sekeluarga, highlight navy dengan aksen emas, hover, collapse 82px dan drawer telefon. Navbar compact memaparkan logo, tajuk halaman dan avatar/dropdown Profil/Tetapan/Log Keluar. Statistik dashboard: Nota, Aktiviti, Soalan, Audio, Kod Aktif (tidak tamat tempoh), Kandungan Aktif dan Sesi. Komponen admin-status menyelaraskan warna status pada kandungan, pengguna, kod, laporan dan keputusan sesi. Merah dikhususkan untuk Padam/ralat/tidak aktif.

Semakan QA dengan izin pengguna: dashboard, soalan, borang tambah, kandungan/nota, kuiz, arahan, audio, media, kod akses, sesi, laporan, audit, profil/tetapan; desktop 1280px, tablet 820px dan telefon 390px. Jadual telefon scroll dalam card (346px ruang, 650px kandungan), tiada limpahan halaman; drawer, collapse sidebar dan modal logout disemak. Fungsi CRUD/validasi, data perjalanan, markah dan kandungan tidak diubah.

Nisbah kontras pasangan palet utama pada putih: teks utama 13.52:1, teks sekunder 5.98:1, placeholder 4.75:1; teks putih pada butang biru 7.05:1; sidebar 11.31:1. Ini semakan palet utama, bukan audit menyeluruh setiap piksel.

Build berjaya dan 51 ujian lulus (1,432 assertions). Bukti: docs/screenshots/admin-redesign-dashboard.png, admin-redesign-table.png, admin-redesign-form.png, admin-redesign-mobile.png, admin-redesign-tablet.png, admin-redesign-modal.png.
