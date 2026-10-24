# Di-BiTaRa

**Bijak Imbuhan & Tatabahasa Ruang Angkasa** — aplikasi pembelajaran Bahasa Melayu dengan dua peranan, Pelajar dan Admin. Laravel 12, Livewire 3, Alpine.js, Vite dan MediaPipe setempat.

## Menjalankan sistem

```powershell
cd C:\laragon\www\Di-BiTaRa
.\scripts\run.ps1 serve
```

Buka [Di-BiTaRa](http://localhost:8000). Terminal perlu kekal berjalan. Pangkalan data setempat menggunakan SQLite dalam `database/database.sqlite`; `.env` dan kata laluan tidak disimpan dalam sumber.

Untuk pemasangan baharu selepas Composer/npm dan `.env` tersedia:

```powershell
.\scripts\run.ps1 migrate
.\scripts\run.ps1 admin
.\scripts\run.ps1 content
.\scripts\run.ps1 build
.\scripts\run.ps1 serve
```

Arahan `content` memasang kurikulum sumber sekali sahaja, termasuk fail audio Imlak yang disertakan dalam `resources/audio`. Ia tidak menimpa suntingan Admin apabila dijalankan semula. Jangan gunakan `migrate:fresh` terhadap data diperlukan. Akaun Admin setempat sedia ada dikekalkan; tiada kata laluan Admin dalam README. `admin` mewujudkan akaun baharu melalui prompt terminal terkawal.

## Aliran Pelajar

Daftar Maklumat Akaun → Pendaftaran Berjaya → Log Masuk → Papan Pemuka dengan tutorial popup kali pertama → Kuiz & Aktiviti → Zon 1–5 → Refleksi → Pasport & Pencapaian serta Sijil.

- Pendaftaran akaun tanpa avatar, ID unik, kata laluan minimum 8 aksara dan kod pemulihan sekali guna.
- Tutorial popup enam langkah, boleh dilangkau/diulang; status disimpan merentas sesi.
- Navbar Pelajar: **Papan Pemuka | Kuiz & Aktiviti | Pasport & Pencapaian**. Tiada Peta Zon.
- Dropdown Pelajar: **Profil, Log Keluar**. Tetapan, Kemajuan Saya dan Sijil Digital tidak lagi menjadi menu berasingan. Kemajuan pada dashboard; sijil dalam Pasport & Pencapaian.
- Nota dengan tindakan Sudah Baca; aktiviti boleh disambung pada soalan belum dijawab.
- Skor disahkan pelayan, penghantaran idempoten dan skor terbaik dikekalkan. Semua nota/aktiviti wajib perlu diselesaikan untuk cap dan membuka zon seterusnya.
- Lima cap dan refleksi melayakkan sijil PDF peribadi. Kegagalan simpan PDF tidak membatalkan kemajuan; butang cuba semula tersedia.
- Notifikasi sebenar untuk aktiviti selesai, cap dan sijil; status dibaca disimpan.

## Kandungan sumber

| Zon | Kandungan awal |
|---|---|
| 1 | Nota persediaan dan kuiz asas 5 soalan |
| 2 | Nota dua pola ejaan dan kuiz kefahaman 5 soalan |
| 3 | 20 ejaan Betul / Salah dan satu aktiviti pengelasan 20 frasa, berdasarkan Soalan Zon 3.pdf halaman 1 |
| 4 | 5 Susun Ayat dan 5 Isi Tempat Kosong, berdasarkan Soalan Zon 4.pdf halaman 1–3 |
| 5 | 5 Imlak dengan audio sintetik Bahasa Melayu tempatan dan refleksi |

Jumlah awal: **5 nota, 6 aktiviti, 36 soalan dan 5 fail audio**. Aktiviti pengelasan 20 frasa dinilai sebagai satu soalan keseluruhan. Ayat Imlak berasaskan ayat latihan Zon 4; audio menggunakan suara sintetik ms-MY-YasminNeural dan boleh diganti Admin. Audio dimainkan daripada storan tempatan; perkhidmatan TTS tidak diperlukan semasa Pelajar belajar.

Soalan, frasa dan susunan jawapan daripada PDF sumber dikekalkan. Kandungan Zon 1–2 ialah bahan asas tambahan kerana PDF yang diberikan khusus untuk Zon 3–4. Ketepatan ejaan rapat/terpisah disemak; konteks masa menggunakan pada.

## Studio Admin

Menu Kandungan/Nota, Kuiz & Aktiviti, Soalan & Jawapan, Arahan, Audio dan Media menyokong Tambah, Lihat, Kemas Kini dan Padam. Borang soalan mempunyai pilihan jenis, blok frasa, kategori/jawapan, markah, audio dan maklum balas; Admin tidak perlu menyunting kod.

- Rakaman mikrofon dengan pratonton atau muat naik fail audio; had rakaman 120 saat dan muat naik 10 MB. Rakaman hanya dihantar apabila Simpan ditekan.
- Imej JPEG/PNG/WebP, audio MP3/WAV/WebM/Ogg/MP4; fail disahkan pelayan. Imej/audio boleh dipaut kepada nota; audio kepada soalan/arahan.
- Status Draf, Sedia untuk terbit dan Tidak Aktif. Terbitkan Keluaran menyemak lima zon, jenis soalan, kunci dan media Imlak sebelum menyimpan manifest.
- Pratonton menggunakan renderer aktiviti yang sama tanpa menulis percubaan/cap Pelajar.
- Setiap perjalanan terikat kepada keluaran immutable. Sunting/padam studio tidak menukar jawapan dan fail bagi perjalanan lama. Penggantian media mencipta rekod baharu.
- Dashboard menggunakan kiraan sebenar; Pengguna menyokong aktif/nyahaktif Pelajar; Laporan memaparkan kemajuan; Audit Log membaca peristiwa pengurusan.

## Kamera dan pengesanan tangan

Webcam Zon 4 ialah pilihan. Model MediaPipe dan WASM disimpan dalam `public/models` serta `public/mediapipe`; landmark diproses pada peranti, tanpa rakaman atau muat naik video. Telunjuk mengawal penuding dan cubitan memilih blok/kategori. Klik, sentuhan serta papan kekunci sentiasa tersedia. Trek kamera dihentikan apabila keluar, tab disembunyikan atau borang dihantar.

Kamera/mikrofon memerlukan HTTPS atau localhost dan izin pengguna. Ujian automatik serta pelayar mengesahkan aliran alternatif; pengesanan dengan tangan sebenar dan rakaman mikrofon sebenar masih memerlukan UAT peranti. Jangan anggap itu telah diuji hanya kerana model tersedia.

## Ujian dan bukti

```powershell
.\scripts\run.ps1 test
.\scripts\run.ps1 build
```

Semakan terakhir: **44 ujian, 1,075 assertions**, meliputi perjalanan lima zon hingga sijil, ketepatan kandungan sumber, pengasingan peranan/pemilik, pemarkahan, retry tanpa ganjaran berganda, kegagalan PDF, CRUD semua modul studio, penggantian media serta snapshot keluaran. Ujian menggunakan SQLite berasingan dan storan palsu. Semakan UI menggunakan pangkalan data QA berasingan; tiada kemajuan ujian dimasukkan ke akaun sebenar.

Dalam pelayar: log masuk Pelajar/Admin, Betul/Salah, pengelasan frasa, Susun Ayat, main balik audio Imlak, simpan soalan Admin, log keluar dan susun atur telefon telah disemak. Bukti berada dalam `docs/screenshots/e2e-*`. Sijil PDF telah dirender dan disemak.

Sistem tersedia untuk penggunaan setempat. Sebelum pelancaran awam: uji MySQL/MariaDB, peranti kamera/mikrofon sebenar, prestasi serentak, sandaran/pemulihan serta semakan guru. Pemulihan akaun asas menggunakan kod sandaran; aliran emel dan PIN bukan kaedah tambahan yang tersedia. Fail PDF menggunakan fon Latin terbina dalam; nama di luar set Latin ditransliterasikan.

Rujukan terperinci: [Plan.md](Plan.md), [Module.md](Module.md), [Database.md](Database.md), [Prototype.md](Prototype.md), [aset visual](docs/Visual-Assets.md).



### Navigasi akaun Admin — 21 Oktober 2026

Sidebar Admin hanya mengandungi Dashboard, Kandungan, Nota, Kuiz & Aktiviti, Soalan & Jawapan, Arahan, Audio, Media, Pratonton Pelajar, Pengguna, Laporan dan Audit Log. Profil, Tetapan dan Log Keluar berada dalam dropdown profil di kanan atas, dibuka melalui nama, avatar atau ikon dropdown. Log Keluar membuka modal “Adakah anda pasti mahu log keluar?” dengan butang Batal dan Log Keluar. Batal/Escape mengekalkan sesi dan mengembalikan fokus ke pencetus; pengesahan menghantar POST dengan CSRF dan menamatkan sesi. Dropdown dan modal menyokong desktop, tablet dan telefon. Ketetapan ini mengatasi senarai navigasi Admin terdahulu.


### Penyelarasan dropdown Pelajar — 21 Oktober 2026

Dropdown Pelajar hanya memaparkan Profil dan Log Keluar. Gaya kad, ikon, jarak, hover navy/kuning dan sudut bulat disamakan dengan Admin. Kedua-dua peranan berkongsi komponen modal pengesahan; Pelajar menggunakan mesej “Adakah anda pasti mahu log keluar?” dan “Anda boleh log masuk semula untuk meneruskan pembelajaran Di-BiTaRa.” Butang Batal atau Escape mengekalkan sesi dan memulangkan fokus; Log Keluar menamatkan sesi melalui POST CSRF. Tiada Tetapan, Kemajuan Saya, Sijil Digital, Bantuan atau Tutorial dalam dropdown Pelajar. Ini mengatasi senarai menu akaun Pelajar terdahulu; laluan bantuan/tutorial kekal tersedia.


### Bahasa Melayu dan paparan mesra murid — 21 Oktober 2026

Semua label sistem menggunakan Bahasa Melayu: Papan Pemuka, Pentadbir, Tetapan, Profil, Log Keluar, Hantar, Seterusnya, Sebelumnya, Markah, Kemajuan, Pencapaian, Kuiz dan Log Audit. Laluan, nama kelas serta nilai dalaman pangkalan data tidak perlu diterjemah; nilai tersebut dipadankan kepada label Melayu sebelum dipaparkan. Nama pengguna dan kandungan yang ditaip pengguna dikekalkan.

Arahan murid dipendekkan dengan ikon mengikut jenis soalan, bar kemajuan, butang sekurang-kurangnya 48 px, pilihan perasaan bergambar dan maklum balas warna/simbol. Panduan tambahan serta kawalan tangan pilihan boleh dibuka apabila diperlukan. Kawalan audio menggunakan Dengar, Jeda dan Ulang; mesej medan wajib dan dialog pengesahan turut menggunakan Bahasa Melayu. Soalan, ejaan dan kunci jawapan sumber Zon 3/4 serta audio Imlak dikekalkan. Pentadbir hendaklah menulis kandungan baharu dalam Bahasa Melayu dan menggunakan arahan pendek. Ketetapan ini mengatasi istilah paparan terdahulu.

Pengesahan: 44 ujian / 1,075 assertions; semakan pelayar untuk susun ayat, maklum balas markah, audio Imlak, medan wajib, dialog pengesahan, Log Audit dan telefon 390 px. Semakan menggunakan data QA berasingan.


### Logo rasmi dan footer awam — 21 Oktober 2026

Logo rasmi ialah salinan asal lampiran pengguna di `public/images/official-logo.png`; bentuk, tulisan dalam karya dan ketelusan asal dikekalkan. Nama teks UI kekal Di-BiTaRa dan Bijak Imbuhan & Tatabahasa Ruang Angkasa. Komponen `brand-logo` digunakan pada navigasi awam/Pelajar, menu sisi Pentadbir, halaman utama, log masuk, pendaftaran, dropdown, pasport dan pratonton sijil. Avatar peribadi kekal berasingan daripada logo rasmi. Palet serta peraturan responsif dikumpulkan dalam `resources/css/branding.css`.

Footer hanya dirender untuk tetamu melalui `@guest` pada layout bersama. Tiada footer pada mana-mana halaman selepas log masuk, termasuk jika pengguna membuka halaman umum ketika masih bersesi. Footer mengandungi logo besar, pautan pantas, sokongan, awan, bintang, kapal terbang dan hak cipta 2026. Pautan Privasi dan Syarat Penggunaan menuju halaman sebenar; label Log Masuk Pentadbir mengekalkan Bahasa Melayu.

Sijil PDF membenamkan logo, menggunakan salinan JPEG khusus PDF di `resources/branding/certificate-logo.jpg` tanpa memerlukan GD semasa berjalan. Arahan `php artisan dibitara:refresh-certificate-branding` boleh dijalankan semula dengan selamat: hanya sijil lama yang belum mempunyai logo dijana ke fail baharu; fail asal, nama, nombor dan tarikh anugerah dikekalkan.

Pengesahan: 44 ujian / 1,075 assertions, build Vite, semakan pelayar desktop/telefon serta render sijil PDF. Ketetapan ini mengatasi footer atau penjenamaan terdahulu.


### Latar khas lima zon — 21 Oktober 2026

Lima PNG baharu dijana menggunakan image_gen terbina dalam dan disimpan di `public/images/zones/`: `zone-1-departure.png` (balai pelepasan), `zone-2-briefing.png` (bilik taklimat), `zone-3-cockpit.png` (kokpit dan landasan), `zone-4-emergency.png` (latihan kecemasan mesra murid) dan `zone-5-arrival.png` (ketibaan, kejayaan dan imlak). Gaya navy/biru/emas/putih dikekalkan; elemen utama diletakkan di sisi supaya ruang tengah lapang untuk UI.

`config/zone-backgrounds.php` memetakan zon kepada gambar. Layout menerima nombor zon untuk katalog, nota dan misi; hanya latar zon dipilih dimuat awal. Kad kandungan kekal legap/cerah untuk kebolehbacaan. Zon 3 menggunakan kokpit baharu; aset latar lama dikekalkan. Penyesuaian telefon menggunakan latar tanpa lekatan tetap dan susun atur asal responsif. Tiada perubahan pada kandungan soalan, pemarkahan, syarat buka zon atau footer awam sahaja.

Set prompt sebenar: `docs/visuals/zone-background-prompts.md`. Semakan pelayar bagi kelima-lima zon, nota Zon 2, misi Zon 3 dan telefon 390 px selesai. Build dan 44 ujian / 1,075 assertions lulus.


### Struktur akhir zon, kuiz dan masa — 21 Oktober 2026

| Zon | Kandungan | Kuiz | Kira detik | Timer |
| --- | --- | --- | --- | --- |
| 1 — Balai Pelepasan | Pengenalan, objektif, persediaan | Tidak | Tidak | Tidak |
| 2 — Taklimat Penerbangan | Nota, konsep, contoh, audio | Tidak | Ya | Ya |
| 3 — Simulasi Penerbangan | Betul / Salah, Seret dan Lepas | Ya | Ya | Ya |
| 4 — Cabaran Kecemasan | Susun Ayat, Isi Tempat Kosong | Ya | Ya | Ya |
| 5 — Balai Ketibaan & Imlak | Aktiviti Imlak dan refleksi akhir | Tidak | Tidak | Tidak |

Zon 1 dan 2 dibuka terus tanpa popup kira detik atau timer. Zon 3, 4 dan 5 menggunakan 3 → 2 → 1 → Mula sebelum mula kali pertama. Memilih tab hanya melihat katalog; kandungan sebenar zon bermasa dikunci sehingga POST mula diterima selepas tiga saat. Persediaan akaun, profil dan avatar melengkapkan Zon 1; nota wajib melengkapkan Zon 2; Zon 3 dan 4 memerlukan nota serta semua aktiviti wajib lulus. Zon 5 memerlukan nota, Imlak dan refleksi untuk sijil.

Timer bermula selepas kira detik dan berhenti sebaik syarat zon dipenuhi; Zon 2 berhenti selepas nota wajib terakhir. Masa diteruskan merentas halaman/sesi, tidak direset atau ditimpa oleh aktiviti ulangan. Pasport hanya memaparkan Masa Zon 2, Masa Zon 3, Masa Zon 4 dan **Jumlah Masa Keseluruhan = Zon 3 + Zon 4 + Zon 5**. Jumlah tersedia sebaik ketiga-tiga rekod lengkap, tanpa menunggu Zon 5. Tiada markah penalti berdasarkan masa. Masa lama yang tidak dapat disahkan tidak direka; masa sambungan dilabel dan dikecualikan daripada jumlah penuh.

Dasar bersama: `ZoneStructure` (timer/countdown 2–4, kuiz 3–4). Penerbitan dan CRUD Pentadbir menolak aktiviti soalan pada Zon 1/2 serta kuiz pada Zon 5. Kurikulum baharu mempunyai 6 aktiviti dan 36 soalan sumber (Zon 3: 20 Betul/Salah + satu klasifikasi; Zon 4: 5 susun ayat + 5 isi kosong; Zon 5: 5 Imlak). Syarat Pakar Kuiz ialah lulus 2 kuiz berbeza.

Migrasi `2026_10_21_000002_correct_zone_structure` mengarkib kuiz lama Zon 1/2 tanpa memadam cubaan/jawapan sejarah, mengelaskan Imlak sebagai aktiviti, mengosongkan semua masa/countdown Zon 1/5 dan mengira semula kemajuan serta jumlah Zon 2–4. Masa Zon 2 lama diselaraskan dengan waktu nota wajib terakhir jika boleh disahkan. Snapshot terbit asal dikekalkan untuk audit; pembacaan manifest menggunakan dasar struktur semasa. Jangan anggap kuiz lama dalam snapshot sebagai syarat zon aktif.

Pengesahan: 50 ujian / 1,184 assertions lulus dan build Vite berjaya. Ujian meliputi perjalanan lima zon, Zon 1/5 tanpa timer/countdown, countdown tiga saat tepat, Zon 2 tamat melalui nota, jumlah 2–4, pemilikan, rekod lama, migrasi berulang dan larangan CRUD yang tidak sah. Ini menggantikan keseluruhan struktur masa/kuiz terdahulu.


### Perincian perjalanan pelajar — Balai Pelepasan hingga Imlak

**Bersedia → Belajar → Uji Kefahaman → Aplikasi → Imlak & Tamat**

Zon 1 ialah persediaan identiti: akaun aktif berdaftar (ID), maklumat diri (nama dan profil), serta avatar juruterbang aktif yang telah dipilih. Pendaftaran mencipta profil tanpa avatar. Selepas log masuk, Balai Pelepasan menyediakan maklumat diri, grid dan pratonton avatar serta butang pengesahan Sedia untuk Berlepas. Apabila ketiga-tiga syarat lengkap, cap Zon 1 diberi dan Zon 2 dibuka. Membaca nota sahaja tidak boleh melengkapkan Zon 1. Zon 1 tiada nota, kuiz, countdown atau timer. Mengubah profil/arkib avatar kemudian tidak menarik balik cap yang telah diperoleh.

Zon 2 memfokuskan nota visual dan audio konsep di-/di tanpa countdown atau timer. Sudah Baca dan Teruskan membuka pengesahan OK sebelum cap Zon 2 dan Zon 3 dibuka.

Zon 3 ialah simulasi kokpit dengan Betul/Salah serta Seret dan Lepas sahaja, berdasarkan Soalan Zon 3.pdf. Zon 4 ialah aplikasi bahasa melalui Susun Ayat serta Isi Tempat Kosong berdasarkan Soalan Zon 4.pdf; kamera/pengesanan tangan pilihan dan klik/sentuh/papan kekunci kekal tersedia. Kedua-duanya menggunakan countdown dan timer, berhenti apabila semua syarat wajib dan kelulusan dipenuhi.

Zon 5 mempunyai popup ketibaan, countdown dan timer. Tiga soalan Imlak mesti betul sebelum refleksi lima emoji. Timer berhenti selepas refleksi, cap akhir diberikan dan sijil dijana. Jumlah masa = Zon 3 + Zon 4 + Zon 5.

`DepartureReadiness` ialah sumber semakan persediaan. Simpan profil mengemas kini kemajuan dalam transaksi. Migrasi 000003 menjadikan nota Zon 1 pilihan dan menyelaraskan kemajuan tanpa menarik balik cap lama. Penerbitan tidak lagi mewajibkan nota wajib pada Zon 1. Pengesahan: build berjaya; **51 ujian / 1,198 assertions lulus**, termasuk profil tanpa avatar tidak boleh membuka Zon 2 walaupun nota dibaca, simpan avatar membuka zon dan masa tidak dicipta bagi Zon 1.


### Koleksi avatar berbilang peranan — 21 Oktober 2026

Tambah 11 ilustrasi baharu (jumlah 15 bersama empat avatar lama): Juruterbang Cerdas, Kapten Suraya, Pembantu Rio, Kru Maya, Kru Danish, Jurutera Nadia, Pegawai Hakim, Angkasawan Aisyah, Saintis Arif, Navigator Luna dan Pegawai Faris. Pakaian, rambut, tona kulit, aksesori, pose dan peranan berbeza; gaya chibi navy/emas/putih konsisten. PNG asli 1024×1536 beralpha dijana menggunakan image_gen terbina dalam, disalin ke `public/images/avatars/` tanpa menimpa aset lama. Prompt lengkap disimpan dalam `docs/visuals/avatar-catalogue-prompts.md`.

Katalog `config/avatar-catalog.php`, perkhidmatan `AvatarCatalogue` dan migrasi 000004 menambah metadata `gender`/`role_label` serta rekod media/avatars secara idempoten. `php artisan dibitara:install-avatars` boleh memasang katalog semula; arahan penciptaan Pentadbir turut memasangnya bagi pemasangan baharu. Pilihan avatar Pelajar sedia ada tidak berubah.

Grid Zon 1 mempunyai penapis Lelaki/Perempuan/Semua serta Jawatan, tanda pilihan, keadaan tiada hasil dan grid responsif. Pratonton Zon 1 memaparkan imej sebenar pilihan. Komponen pilot-avatar menyokong setiap imej baharu di navigasi, pasport, profil dan Balai Pelepasan menggunakan imej berdiri penuh. Pengesahan pelayan masih menolak avatar tidak aktif/dipadam atau ID tidak sah.

Pengesahan: 53 ujian / 1,252 assertions lulus; build Vite berjaya. Semakan QA desktop/telefon mengesahkan penapis, tiada hasil, pemilihan/simpan profil, pratonton sepadan dan tiada limpahan mendatar pada 390 px. Bukti utama: docs/screenshots/avatar-registration-preview.png dan avatar-registration-mobile.png.


### Avatar berdiri penuh — 22 Oktober 2026

Semua 15 avatar menggunakan pose berdiri penuh. Empat Kapten lama mendapat ilustrasi baharu; Angkasawan Aisyah kini berdiri dengan kedua-dua kaki berpijak. Identiti, jawatan, pakaian dan pilihan Pelajar dikekalkan. Semua imej menggunakan bingkai seragam, `object-fit: contain` dan penjajaran tengah/bawah tanpa pelarasan individu atau potongan. Komponen bersama meliputi pratonton Zon 1, profil, navigasi, pasport dan Balai Pelepasan. Migrasi 2026_10_22_000001 menyelaraskan media tanpa menukar ID avatar. Fail asal dikekalkan.

Aset: `public/images/avatars/*-standing.png`. Prompt: `docs/visuals/avatar-standing-prompts.md` (image_gen terbina dalam). Pengesahan: 53 ujian / 1,267 assertions lulus; build Vite berjaya. Semakan desktop dan telefon 390 px mengesahkan 15 imej dimuat, tiada pemotongan atau limpahan mendatar. Bukti: `docs/screenshots/avatar-standing-desktop.png` dan `avatar-standing-mobile.png`.


### Aliran akaun dan avatar terkini — 22 Oktober 2026

Pendaftaran Akaun → Pendaftaran Berjaya → Log Masuk → Tutorial / Papan Pemuka → Zon 1 (Maklumat Diri → Pilih Avatar → Pratonton → Sedia untuk Berlepas) → Cap Zon 1 → Zon 2 Dibuka. Tiada avatar dipilih atau disimpan ketika daftar. GET/POST /daftar/avatar dan pemilihan di Profil dibuang. Komponen DeparturePreparation menyimpan pilihan hanya selepas pengesahan; StudentProfile tidak boleh mengubah avatar. avatar_confirmed_at (nullable) merekod pengesahan dalam student_profiles. Migrasi 2026_10_22_000002 mengekalkan avatar lama sebagai disahkan tanpa menarik balik cap. Pilihan tidak aktif/dipadam ditolak pelayan. Zon 1 tiada timer atau countdown. Bahagian ini menggantikan semua aliran pendaftaran tiga langkah terdahulu dalam dokumen ini.

### Tutorial popup berpandu — 22 Oktober 2026

Pelajar log masuk ke /pelajar. Komponen Tutorial membuka modal native hanya untuk onboarding_status=pending, di atas Papan Pemuka sebenar. Enam langkah ialah Papan Pemuka, Kuiz & Aktiviti, Zon Pembelajaran, Pasport & Pencapaian, Notifikasi dan Profil. guided-tour.js mengukur sasaran sebenar, menempatkan kad dalam viewport dan menggelapkan bahagian lain menggunakan empat panel. Pada telefon, menu tersembunyi menyorot hamburger; sasaran zon ialah tajuk kad Zon 1 supaya kad panduan tidak menindih sasaran. Paparan menyokong perubahan saiz dan reduced motion.

Status/kemajuan disimpan dalam student_profiles.onboarding_status/onboarding_step/onboarding_completed_at. Selesai menyimpan completed; Langkau, tutup dan Escape menyimpan skipped. Ulang tutorial bermula pada langkah 1 dan tidak menukar kemajuan zon/markah/avatar. Menutup ulang tutorial tidak menurunkan status completed. /tutorial kini memaparkan Papan Pemuka dengan popup ulangan, bukan skrin arahan berasingan. Pautan Lihat Tutorial Semula tersedia di Profil dan Bantuan. Dropdown kekal Profil/Log Keluar. Native dialog memerangkap fokus, menghalang klik pada latar dan memulangkan fokus apabila ditutup.

Pengesahan: 56 ujian / 1,295 assertions lulus; build Vite berjaya. Semakan QA desktop/telefon mengesahkan auto-buka, keenam-enam sorotan, Sebelumnya/Seterusnya, sambung selepas muat semula, Escape, status kekal selepas tutup, ulangan dari Profil dan Selesai. Bukti: docs/screenshots/guided-tutorial-desktop.png, guided-tutorial-mobile.png, guided-tutorial-mobile-zones.png dan guided-tutorial-profile.png. Bahagian ini menggantikan tutorial halaman penuh terdahulu.

## Rujukan muktamad Kuiz & Aktiviti — PDF 22 Oktober 2026

Rujuk docs/references/latest-learning-flow.md serta salinan PDF asal dalam docs/references/. Bahagian ini menggantikan semua flow/soalan terdahulu yang bercanggah. Pengguna mengesahkan Zon 2 TIADA countdown atau timer. Hanya Zon 3/4/5 menggunakan countdown selepas Faham, timer dan jumlah masa 3+4+5.

Zon 1 tiada nota/kuiz: maklumat diri → avatar → Mari Meneroka → OK → cap dan buka Zon 2. Zon 2 nota visual/audio → Sudah Baca dan Teruskan → OK → cap dan buka Zon 3. Zon 3 ialah 6+6 Betul/Salah dan 10 kad Tarik & Lepas (satu soalan pengelasan). Zon 4 ialah 5 Susun Ayat serta 5 Isi Tempat Kosong dengan semakan pilihan ejaan DAN salinan ayat lengkap; maksud dua peribahasa muncul selepas betul. Zon 5 ialah popup ketibaan, tiga Imlak sebenar (di rumah; bagai dihiris sembilu; ukur baju di badan sendiri), had tiga kali audio setiap soalan, refleksi lima emoji, cap akhir dan sijil. Timer Zon 5 berhenti selepas refleksi dihantar.

Salah tidak membuka soalan seterusnya. Pelajar membetulkan soalan sama sehingga betul; sejarah setiap penghantaran disimpan, markah idempoten dan kunci jawapan tidak bocor sebelum betul. Cabaran dalam satu zon juga berurutan. Migrasi 000003 pada 22 Oktober menambah answer_submissions dan attempt_questions.audio_plays. Pemasangan kandungan menambah keluaran dan perjalanan baharu, mengarkibkan percubaan aktif versi lama sambil mengekalkan semua jawapan, masa, markah, cap, snapshot serta sijil sejarah. Pasport memaparkan sijil lama juga. Jumlah masa lama tidak dikosongkan.

Katalog baharu mengandungi 26 soalan: 12 Betul/Salah, 1 pengelasan 10 kad, 5 Susun Ayat, 5 Isi Tempat Kosong dan 3 Imlak. Semua aset audio Bahasa Melayu tersedia setempat; semua kandungan menyokong CRUD dan penerbitan Admin.
