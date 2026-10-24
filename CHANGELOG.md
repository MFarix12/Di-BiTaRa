# Release Notes

## [Unreleased](https://github.com/laravel/laravel/compare/v12.12.1...12.x)

## [v12.12.1](https://github.com/laravel/laravel/compare/v12.12.0...v12.12.1) - 2026-03-10

* [12.x] Makes imports consistent by [@nunomaduro](https://github.com/nunomaduro) in https://github.com/laravel/laravel/pull/6760

## [v12.12.0](https://github.com/laravel/laravel/compare/v12.11.2...v12.12.0) - 2026-03-09

* Update phpunit version to ^11.5.50 to address CVE by [@PerryvanderMeer](https://github.com/PerryvanderMeer) in https://github.com/laravel/laravel/pull/6746
* [12.x] Add `APP_NAME` fallback in mail config by [@apoorvdarshan](https://github.com/apoorvdarshan) in https://github.com/laravel/laravel/pull/6755
* [12.x] Neutralize DB_URL in default phpunit.xml by [@Husseinadq](https://github.com/Husseinadq) in https://github.com/laravel/laravel/pull/6761

## [v12.11.2](https://github.com/laravel/laravel/compare/v12.11.1...v12.11.2) - 2026-01-19

* [12.x] Update composer dev script to ensure no timeout by [@jackbayliss](https://github.com/jackbayliss) in https://github.com/laravel/laravel/pull/6735
* [12.x] Update jobs/cache migrations by [@jackbayliss](https://github.com/jackbayliss) in https://github.com/laravel/laravel/pull/6736
* [12.x] Remove failed jobs indexes by [@jackbayliss](https://github.com/jackbayliss) in https://github.com/laravel/laravel/pull/6739
* [12.x] Add `APP_URL` fallback in filesystems config by [@KentarouTakeda](https://github.com/KentarouTakeda) in https://github.com/laravel/laravel/pull/6742
* chore: Update outdated GitHub Actions version by [@pgoslatara](https://github.com/pgoslatara) in https://github.com/laravel/laravel/pull/6743

## [v12.11.1](https://github.com/laravel/laravel/compare/v12.11.0...v12.11.1) - 2025-12-23

* Use environment variable for `DB_SSLMODE` - Postgres by [@robsontenorio](https://github.com/robsontenorio) in https://github.com/laravel/laravel/pull/6727
* fix: ensure APP_URL does not have trailing slash in filesystem by [@msamgan](https://github.com/msamgan) in https://github.com/laravel/laravel/pull/6728

## [v12.11.0](https://github.com/laravel/laravel/compare/v12.10.1...v12.11.0) - 2025-11-25

* fix: cookies are not available for subdomains by default by [@joostdebruijn](https://github.com/joostdebruijn) in https://github.com/laravel/laravel/pull/6705
* Fix PHP 8.5 PDO Driver Specific Constant Deprecation by [@RyanSchaefer](https://github.com/RyanSchaefer) in https://github.com/laravel/laravel/pull/6710
* Ignore Laravel compiled views for Vite  by [@QistiAmal1212](https://github.com/QistiAmal1212) in https://github.com/laravel/laravel/pull/6714

## [v12.10.1](https://github.com/laravel/laravel/compare/v12.10.0...v12.10.1) - 2025-11-06

* Update schema URL in package.json by [@robinmiau](https://github.com/robinmiau) in https://github.com/laravel/laravel/pull/6701

## [v12.10.0](https://github.com/laravel/laravel/compare/v12.9.1...v12.10.0) - 2025-11-04

* Add background driver by [@barryvdh](https://github.com/barryvdh) in https://github.com/laravel/laravel/pull/6699

## [v12.9.1](https://github.com/laravel/laravel/compare/v12.9.0...v12.9.1) - 2025-10-23

* [12.x] Replace Bootcamp with Laravel Learn by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6692
* [12.x] Comment out CLI workers for fresh applications by [@timacdonald](https://github.com/timacdonald) in https://github.com/laravel/laravel/pull/6693

## [v12.9.0](https://github.com/laravel/laravel/compare/v12.8.0...v12.9.0) - 2025-10-21

**Full Changelog**: https://github.com/laravel/laravel/compare/v12.8.0...v12.9.0

## [v12.8.0](https://github.com/laravel/laravel/compare/v12.7.1...v12.8.0) - 2025-10-20

* [12.x] Makes test suite using broadcast's `null` driver by [@nunomaduro](https://github.com/nunomaduro) in https://github.com/laravel/laravel/pull/6691

## [v12.7.1](https://github.com/laravel/laravel/compare/v12.7.0...v12.7.1) - 2025-10-15

* Added `failover` driver to the `queue` config comment.  by [@sajjadhossainshohag](https://github.com/sajjadhossainshohag) in https://github.com/laravel/laravel/pull/6688

## [v12.7.0](https://github.com/laravel/laravel/compare/v12.6.0...v12.7.0) - 2025-10-14

**Full Changelog**: https://github.com/laravel/laravel/compare/v12.6.0...v12.7.0

## [v12.6.0](https://github.com/laravel/laravel/compare/v12.5.0...v12.6.0) - 2025-10-02

* Fix setup script by [@goldmont](https://github.com/goldmont) in https://github.com/laravel/laravel/pull/6682

## [v12.5.0](https://github.com/laravel/laravel/compare/v12.4.0...v12.5.0) - 2025-09-30

* [12.x] Fix type casting for environment variables in config files by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6670
* Fix CVEs affecting vite by [@faissaloux](https://github.com/faissaloux) in https://github.com/laravel/laravel/pull/6672
* Update .editorconfig to target compose.yaml by [@fredikaputra](https://github.com/fredikaputra) in https://github.com/laravel/laravel/pull/6679
* Add pre-package-uninstall script to composer.json by [@cosmastech](https://github.com/cosmastech) in https://github.com/laravel/laravel/pull/6681

## [v12.4.0](https://github.com/laravel/laravel/compare/v12.3.1...v12.4.0) - 2025-08-29

* [12.x] Add default Redis retry configuration by [@mateusjatenee](https://github.com/mateusjatenee) in https://github.com/laravel/laravel/pull/6666

## [v12.3.1](https://github.com/laravel/laravel/compare/v12.3.0...v12.3.1) - 2025-08-21

* [12.x] Bump Pint version by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6653
* [12.x] Making sure all related processed are closed when terminating the currently command by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6654
* [12.x] Use application name from configuration by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6655
* Bring back postAutoloadDump script by [@jasonvarga](https://github.com/jasonvarga) in https://github.com/laravel/laravel/pull/6662

## [v12.3.0](https://github.com/laravel/laravel/compare/v12.2.0...v12.3.0) - 2025-08-03

* Fix Critical Security Vulnerability in form-data Dependency by [@izzygld](https://github.com/izzygld) in https://github.com/laravel/laravel/pull/6645
* Revert "fix" by [@RobertBoes](https://github.com/RobertBoes) in https://github.com/laravel/laravel/pull/6646
* Change composer post-autoload-dump script to Artisan command by [@lmjhs](https://github.com/lmjhs) in https://github.com/laravel/laravel/pull/6647

## [v12.2.0](https://github.com/laravel/laravel/compare/v12.1.0...v12.2.0) - 2025-07-11

* Add Vite 7 support by [@timacdonald](https://github.com/timacdonald) in https://github.com/laravel/laravel/pull/6639

## [v12.1.0](https://github.com/laravel/laravel/compare/v12.0.11...v12.1.0) - 2025-07-03

* [12.x] Disable nightwatch in testing by [@laserhybiz](https://github.com/laserhybiz) in https://github.com/laravel/laravel/pull/6632
* [12.x] Reorder environment variables in phpunit.xml for logical grouping by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6634
* Change to hyphenate prefixes and cookie names by [@u01jmg3](https://github.com/u01jmg3) in https://github.com/laravel/laravel/pull/6636
* [12.x] Fix type casting for environment variables in config files by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6637

## [v12.0.11](https://github.com/laravel/laravel/compare/v12.0.10...v12.0.11) - 2025-06-10

**Full Changelog**: https://github.com/laravel/laravel/compare/v12.0.10...v12.0.11

## [v12.0.10](https://github.com/laravel/laravel/compare/v12.0.9...v12.0.10) - 2025-06-09

* fix alphabetical order by [@Khuthaily](https://github.com/Khuthaily) in https://github.com/laravel/laravel/pull/6627
* [12.x] Reduce redundancy and keeps the .gitignore file cleaner by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6629
* [12.x] Fix: Add void return type to satisfy Rector analysis by [@Aluisio-Pires](https://github.com/Aluisio-Pires) in https://github.com/laravel/laravel/pull/6628

## [v12.0.9](https://github.com/laravel/laravel/compare/v12.0.8...v12.0.9) - 2025-05-26

* [12.x] Remove apc by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6611
* [12.x] Add JSON Schema to package.json by [@martinbean](https://github.com/martinbean) in https://github.com/laravel/laravel/pull/6613
* Minor language update by [@woganmay](https://github.com/woganmay) in https://github.com/laravel/laravel/pull/6615
* Enhance .gitignore to exclude common OS and log files by [@mohammadRezaei1380](https://github.com/mohammadRezaei1380) in https://github.com/laravel/laravel/pull/6619

## [v12.0.8](https://github.com/laravel/laravel/compare/v12.0.7...v12.0.8) - 2025-05-12

* [12.x] Clean up URL formatting in README by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6601

## [v12.0.7](https://github.com/laravel/laravel/compare/v12.0.6...v12.0.7) - 2025-04-15

* Add `composer run test` command by [@crynobone](https://github.com/crynobone) in https://github.com/laravel/laravel/pull/6598
* Partner Directory Changes in ReadME by [@joshcirre](https://github.com/joshcirre) in https://github.com/laravel/laravel/pull/6599

## [v12.0.6](https://github.com/laravel/laravel/compare/v12.0.5...v12.0.6) - 2025-04-08

**Full Changelog**: https://github.com/laravel/laravel/compare/v12.0.5...v12.0.6

## [v12.0.5](https://github.com/laravel/laravel/compare/v12.0.4...v12.0.5) - 2025-04-02

* [12.x] Update `config/mail.php` to match the latest core configuration by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6594

## [v12.0.4](https://github.com/laravel/laravel/compare/v12.0.3...v12.0.4) - 2025-03-31

* Bump vite from 6.0.11 to 6.2.3 - Vulnerability patch by [@abdel-aouby](https://github.com/abdel-aouby) in https://github.com/laravel/laravel/pull/6586
* Bump vite from 6.2.3 to 6.2.4 by [@thinkverse](https://github.com/thinkverse) in https://github.com/laravel/laravel/pull/6590

## [v12.0.3](https://github.com/laravel/laravel/compare/v12.0.2...v12.0.3) - 2025-03-17

* Remove reverted change from CHANGELOG.md by [@AJenbo](https://github.com/AJenbo) in https://github.com/laravel/laravel/pull/6565
* Improves clarity in app.css file by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6569
* [12.x] Refactor: Structural improvement for clarity by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6574
* Bump axios from 1.7.9 to 1.8.2 - Vulnerability patch by [@abdel-aouby](https://github.com/abdel-aouby) in https://github.com/laravel/laravel/pull/6572
* [12.x] Remove Unnecessarily [@source](https://github.com/source) by [@AhmedAlaa4611](https://github.com/AhmedAlaa4611) in https://github.com/laravel/laravel/pull/6584

## [v12.0.2](https://github.com/laravel/laravel/compare/v12.0.1...v12.0.2) - 2025-03-04

* Make the github test action run out of the box independent of the choice of testing framework by [@ndeblauw](https://github.com/ndeblauw) in https://github.com/laravel/laravel/pull/6555

## [v12.0.1](https://github.com/laravel/laravel/compare/v12.0.0...v12.0.1) - 2025-02-24

* [12.x] prefer stable stability by [@pataar](https://github.com/pataar) in https://github.com/laravel/laravel/pull/6548

## [v12.0.0 (2025-??-??)](https://github.com/laravel/laravel/compare/v11.0.2...v12.0.0)

Laravel 12 includes a variety of changes to the application skeleton. Please consult the diff to see what's new.

## 21 Oktober 2026 — Fungsi end-to-end dan menu Pelajar
- Nota dan kurikulum sumber dipasang: 5 nota, 6 aktiviti, 36 soalan, 5 audio Bahasa Melayu.
- Betul/Salah dan 20 frasa pengelasan Zon 3 daripada PDF asal; 5 Susun Ayat dan 5 Isi Tempat Kosong Zon 4 daripada PDF asal; Imlak/refleksi Zon 5.
- Studio CRUD, rakaman/muat naik media, pautan audio/imej, pratonton interaktif dan penerbitan snapshot.
- Perjalanan, cubaan, penilaian pelayan, skor terbaik, cap, unlock, notifikasi, lencana dan sijil PDF dengan retry kegagalan storan.
- Tutorial tujuh topik automatik; Mula Pengembaraan menuju log masuk. Navbar awam Capai Pembelajaran.
- Dropdown Pelajar hanya Profil & Avatar, Bantuan, Tutorial, Log Keluar. Tetapan, Kemajuan Saya dan Sijil Digital dibuang daripada dropdown.
- Model tangan/WASM setempat dan input alternatif. Video tidak dihantar/disimpan; UAT tangan/mikrofon fizikal masih diperlukan.
- 36 ujian / 752 assertions lulus; binaan Vite dan cache Blade berjaya. Semakan pelayar menggunakan DB/storan QA berasingan.


## 21 Oktober 2026 — Dropdown akaun Admin

- Pindahkan Profil, Tetapan dan Log Keluar daripada sidebar ke dropdown profil navbar atas.
- Tambah modal pengesahan log keluar dengan Batal, Escape, fokus terkawal dan POST CSRF.
- Disemak pada desktop, tablet 768 px dan telefon 390 px; batal mengekalkan sesi, sahkan kembali ke halaman awam.
- Build Vite dan 37 ujian / 777 assertions lulus.


## 21 Oktober 2026 — Dropdown dan modal Pelajar

- Samakan dropdown Pelajar dengan Admin; hanya Profil dan Log Keluar.
- Gunakan komponen modal bersama dengan penerangan pembelajaran khusus Pelajar.
- Semak pautan Profil, Batal, Escape, pemulangan fokus, telefon 390 px dan pengesahan log keluar dalam QA.
- Build dan 37 ujian / 795 assertions lulus.


## 21 Oktober 2026 — Bahasa Melayu dan paparan mesra murid

- Seragamkan istilah paparan awam, Pelajar dan Pentadbir; lengkapkan terjemahan validasi, penomboran halaman dan Log Audit.
- Pendekkan arahan, tambah ikon/bar kemajuan/maklum balas visual, pilihan emosi bergambar dan bantuan boleh kembang.
- Kawalan audio Dengar/Jeda/Ulang serta dialog pengesahan dan mesej borang Melayu.
- Kekalkan kandungan sebenar dan pemarkahan soalan sumber.
- Build dan 40 ujian / 864 assertions lulus; semakan pelayar berasingan termasuk telefon.


## 21 Oktober 2026 — Logo rasmi dan footer awam

- Guna logo asal pengguna secara konsisten dan palet navy/emas/putih melalui komponen bersama.
- Footer tetamu sahaja, berasaskan rujukan, dengan pautan sokongan, halaman Privasi dan Syarat Penggunaan.
- Benam logo dalam sijil PDF dan tambah arahan pemulihan penjenamaan sijil lama yang mengekalkan fail asal/data anugerah.
- Semakan desktop/telefon dan PDF selesai; build serta 44 ujian / 1,050 assertions lulus.


## 21 Oktober 2026 — Lima latar zon tersendiri

- Jana lima ilustrasi landskap beridentiti berbeza dengan ruang tengah untuk kandungan.
- Gunakan pemetaan zon bersama untuk katalog, nota dan misi; kokpit Zon 3 baharu.
- Kekalkan keterbacaan, susun atur telefon, kandungan sumber dan pemarkahan.
- Simpan PNG asal, set prompt dan tangkap layar QA. Build serta 44 ujian / 1,075 assertions lulus.


## 21 Oktober 2026 — Kira detik dan masa setiap zon

- Popup 3 → 2 → 1 → Mula kini hanya sebelum Zon 2–4; Zon 1 dan 5 terus bermula.
- Masa pelayan kekal merentas sesi, berhenti apabila syarat zon lengkap dan tidak ditimpa oleh cubaan ulangan.
- Simpan masa Zon 2–4 dan jumlah ketiga-tiganya; paparkan kad Masa Pengembaraan Saya pada Pasport.
- Kekalkan rekod lama tanpa masa rekaan, tandakan masa sambungan bagi zon lama yang belum selesai.
- Migrasi setempat selesai; build, 48 ujian / 1,177 assertions serta semakan pelayar desktop/telefon lulus.


## 21 Oktober 2026 — Thumbnail dan nombor zon

- Kad Papan Pemuka dan pilihan zon Kuiz & Aktiviti menggunakan imej asal daripada config/zone-backgrounds.php, sama seperti latar sebenar Zon 1–5.
- Thumbnail menggunakan object-fit: cover untuk crop tanpa regangan; gambar hiasan lama pada kad digantikan.
- Semua nombor menggunakan kelas zone-number-badge dengan grid/place-items:center, line-height:1 dan fon angka yang sama, tanpa pelarasan individu.
- Build Vite serta semakan pelayar desktop/telefon selesai; semua lima imej dimuat, tiada limpahan halaman pada 390 px.


## 21 Oktober 2026 — Pembetulan struktur akhir zon

### Struktur akhir zon, kuiz dan masa — 21 Oktober 2026

| Zon | Kandungan | Kuiz | Kira detik | Timer |
| --- | --- | --- | --- | --- |
| 1 — Balai Pelepasan | Pengenalan, objektif, persediaan | Tidak | Tidak | Tidak |
| 2 — Taklimat Penerbangan | Nota, konsep, contoh, audio | Tidak | Ya | Ya |
| 3 — Simulasi Penerbangan | Betul / Salah, Seret dan Lepas | Ya | Ya | Ya |
| 4 — Cabaran Kecemasan | Susun Ayat, Isi Tempat Kosong | Ya | Ya | Ya |
| 5 — Balai Ketibaan & Imlak | Aktiviti Imlak dan refleksi akhir | Tidak | Tidak | Tidak |

Zon 1 dan 5 dibuka terus tanpa popup kira detik atau timer. Zon 2, 3 dan 4 menggunakan 3 → 2 → 1 → Mula sebelum mula kali pertama. Memilih tab hanya melihat katalog; kandungan sebenar zon bermasa dikunci sehingga POST mula diterima selepas tiga saat. Nota wajib melengkapkan Zon 1 dan 2; Zon 3 dan 4 memerlukan nota serta semua aktiviti wajib lulus. Zon 5 memerlukan nota, Imlak dan refleksi untuk sijil.

Timer bermula selepas kira detik dan berhenti sebaik syarat zon dipenuhi; Zon 2 berhenti selepas nota wajib terakhir. Masa diteruskan merentas halaman/sesi, tidak direset atau ditimpa oleh aktiviti ulangan. Pasport hanya memaparkan Masa Zon 2, Masa Zon 3, Masa Zon 4 dan **Jumlah Masa Keseluruhan = Zon 2 + Zon 3 + Zon 4**. Jumlah tersedia sebaik ketiga-tiga rekod lengkap, tanpa menunggu Zon 5. Tiada markah penalti berdasarkan masa. Masa lama yang tidak dapat disahkan tidak direka; masa sambungan dilabel dan dikecualikan daripada jumlah penuh.

Dasar bersama: `ZoneStructure` (timer/countdown 2–4, kuiz 3–4). Penerbitan dan CRUD Pentadbir menolak aktiviti soalan pada Zon 1/2 serta kuiz pada Zon 5. Kurikulum baharu mempunyai 6 aktiviti dan 36 soalan sumber (Zon 3: 20 Betul/Salah + satu klasifikasi; Zon 4: 5 susun ayat + 5 isi kosong; Zon 5: 5 Imlak). Syarat Pakar Kuiz ialah lulus 2 kuiz berbeza.

Migrasi `2026_10_21_000002_correct_zone_structure` mengarkib kuiz lama Zon 1/2 tanpa memadam cubaan/jawapan sejarah, mengelaskan Imlak sebagai aktiviti, mengosongkan semua masa/countdown Zon 1/5 dan mengira semula kemajuan serta jumlah Zon 2–4. Masa Zon 2 lama diselaraskan dengan waktu nota wajib terakhir jika boleh disahkan. Snapshot terbit asal dikekalkan untuk audit; pembacaan manifest menggunakan dasar struktur semasa. Jangan anggap kuiz lama dalam snapshot sebagai syarat zon aktif.

Pengesahan: 50 ujian / 1,184 assertions lulus dan build Vite berjaya. Ujian meliputi perjalanan lima zon, Zon 1/5 tanpa timer/countdown, countdown tiga saat tepat, Zon 2 tamat melalui nota, jumlah 2–4, pemilikan, rekod lama, migrasi berulang dan larangan CRUD yang tidak sah. Ini menggantikan keseluruhan struktur masa/kuiz terdahulu.


## 21 Oktober 2026 — Zon 1 sebagai persediaan identiti


### Perincian perjalanan pelajar — Balai Pelepasan hingga Imlak

**Bersedia → Belajar → Uji Kefahaman → Aplikasi → Imlak & Tamat**

Zon 1 ialah persediaan identiti: akaun aktif berdaftar (ID), maklumat diri (nama dan profil), serta avatar juruterbang aktif yang telah dipilih. Pendaftaran kekal dalam tiga langkah sebelum log masuk; Balai Pelepasan menunjukkan semakan persediaan, avatar, pautan kemas kini profil serta butang Berlepas ke Zon 2. Apabila ketiga-tiga syarat lengkap, cap Zon 1 diberi dan Zon 2 dibuka. Membaca nota sahaja tidak boleh melengkapkan Zon 1. Nota pengenalan kekal sebagai panduan pilihan; tiada kuiz, countdown atau timer. Mengubah profil/arkib avatar kemudian tidak menarik balik cap yang telah diperoleh.

Zon 2 memfokuskan konsep di- sebagai imbuhan kata kerja pasif (rapat) dan di sebagai kata sendi nama tempat (terpisah), dengan kad visual, contoh, nota dan audio. Kurikulum sumber menggunakan semula audio contoh ayat yang sudah dipautkan dalam keluaran terbit; media kekal tertakluk pada kunci zon dan permulaan timer. Countdown 3 → 2 → 1 → Mula mendahului timer. Semua nota wajib mesti selesai; tiada kuiz.

Zon 3 ialah simulasi kokpit dengan Betul/Salah serta Seret dan Lepas sahaja, berdasarkan Soalan Zon 3.pdf. Zon 4 ialah aplikasi bahasa melalui Susun Ayat serta Isi Tempat Kosong berdasarkan Soalan Zon 4.pdf; kamera/pengesanan tangan pilihan dan klik/sentuh/papan kekunci kekal tersedia. Kedua-duanya menggunakan countdown dan timer, berhenti apabila semua syarat wajib dan kelulusan dipenuhi.

Zon 5 dibuka terus tanpa countdown/timer. Pelajar mendengar audio, menulis jawapan Imlak, menerima semakan/keputusan dan melengkapkan refleksi untuk cap akhir serta sijil. Jumlah masa Pasport kekal **Zon 2 + Zon 3 + Zon 4 sahaja**.

`DepartureReadiness` ialah sumber semakan persediaan. Simpan profil mengemas kini kemajuan dalam transaksi. Migrasi 000003 menjadikan nota Zon 1 pilihan dan menyelaraskan kemajuan tanpa menarik balik cap lama. Penerbitan tidak lagi mewajibkan nota wajib pada Zon 1. Pengesahan: build berjaya; **51 ujian / 1,198 assertions lulus**, termasuk profil tanpa avatar tidak boleh membuka Zon 2 walaupun nota dibaca, simpan avatar membuka zon dan masa tidak dicipta bagi Zon 1.


### Koleksi avatar berbilang peranan — 21 Oktober 2026

Tambah 11 ilustrasi baharu (jumlah 15 bersama empat avatar lama): Juruterbang Cerdas, Kapten Suraya, Pembantu Rio, Kru Maya, Kru Danish, Jurutera Nadia, Pegawai Hakim, Angkasawan Aisyah, Saintis Arif, Navigator Luna dan Pegawai Faris. Pakaian, rambut, tona kulit, aksesori, pose dan peranan berbeza; gaya chibi navy/emas/putih konsisten. PNG asli 1024×1536 beralpha dijana menggunakan image_gen terbina dalam, disalin ke `public/images/avatars/` tanpa menimpa aset lama. Prompt lengkap disimpan dalam `docs/visuals/avatar-catalogue-prompts.md`.

Katalog `config/avatar-catalog.php`, perkhidmatan `AvatarCatalogue` dan migrasi 000004 menambah metadata `gender`/`role_label` serta rekod media/avatars secara idempoten. `php artisan dibitara:install-avatars` boleh memasang katalog semula; arahan penciptaan Pentadbir turut memasangnya bagi pemasangan baharu. Pilihan avatar Pelajar sedia ada tidak berubah.

Grid pendaftaran dan profil mempunyai penapis Lelaki/Perempuan/Semua serta Jawatan, tanda pilihan, keadaan tiada hasil dan grid responsif. Pratonton pendaftaran memaparkan imej sebenar pilihan. Komponen pilot-avatar menyokong setiap imej baharu di navigasi, pendaftaran berjaya, pasport, profil dan Balai Pelepasan sambil mengekalkan empat sprite lama. Pengesahan pelayan masih menolak avatar tidak aktif/dipadam atau ID tidak sah.

Pengesahan: 53 ujian / 1,252 assertions lulus; build Vite berjaya. Semakan QA desktop/telefon mengesahkan penapis, tiada hasil, pemilihan/simpan profil, pratonton sepadan dan tiada limpahan mendatar pada 390 px. Bukti utama: docs/screenshots/avatar-registration-preview.png dan avatar-registration-mobile.png.


## 21 Oktober 2026 — Bingkai hero Kuiz & Aktiviti

Bingkai bersama untuk hero kelima-lima zon: border putih nipis, outline biru, inner border, bucu bulat dan glow/bayang lembut. Latar zon, tajuk, penerangan dan karakter berada dalam bingkai yang sama. Spacing telefon dilaras supaya kandungan tidak menyentuh tepi. Build Vite berjaya; semakan pelayar Zon 1–5 dan telefon 390 px mengesahkan latar berbeza dan tiada limpahan mendatar. Bukti: docs/screenshots/framed-zone-hero.png dan framed-zone-hero-mobile.png.


## 22 Oktober 2026 — Background pengguna untuk Zon 2–4

Ganti pemetaan latar Zon 2, 3 dan 4 dengan salinan asal 2.jpg, 3.jpg dan 4.jpg daripada pengguna. Aset baharu: zone-2-airport.jpg, zone-3-flight-deck.jpg dan zone-4-cabin.jpg di public/images/zones/. Pemetaan bersama turut menyelaraskan hero, nota, misi, countdown dan thumbnail Papan Pemuka/pilihan zon. Fail asal disahkan melalui SHA-256; semakan pelayar ketiga-tiga zon mengesahkan gambar betul. Bingkai hero dan kaedah cover/crop dikekalkan.


### Avatar berdiri penuh — 22 Oktober 2026

Semua 15 avatar menggunakan pose berdiri penuh. Empat Kapten lama mendapat ilustrasi baharu; Angkasawan Aisyah kini berdiri dengan kedua-dua kaki berpijak. Identiti, jawatan, pakaian dan pilihan Pelajar dikekalkan. Semua imej menggunakan bingkai seragam, `object-fit: contain` dan penjajaran tengah/bawah tanpa pelarasan individu atau potongan. Komponen bersama meliputi pendaftaran, pratonton, profil, navigasi, pasport dan Balai Pelepasan. Migrasi 2026_10_22_000001 menyelaraskan media tanpa menukar ID avatar. Fail asal dikekalkan.

Aset: `public/images/avatars/*-standing.png`. Prompt: `docs/visuals/avatar-standing-prompts.md` (image_gen terbina dalam). Pengesahan: 53 ujian / 1,267 assertions lulus; build Vite berjaya. Semakan desktop dan telefon 390 px mengesahkan 15 imej dimuat, tiada pemotongan atau limpahan mendatar. Bukti: `docs/screenshots/avatar-standing-desktop.png` dan `avatar-standing-mobile.png`.


### 22 Oktober 2026 — Avatar hanya di Zon 1

Pendaftaran terus mencipta akaun tanpa avatar; langkah dan route avatar awam dibuang. Zon 1 menyediakan nama/ID, 15 avatar berdiri penuh, penapis, pratonton dan pengesahan Sedia untuk Berlepas. Pratonton tidak menyimpan pilihan. Pengesahan bertransaksi memberi satu cap dan membuka Zon 2; Profil hanya memaut ke Zon 1 untuk menukar avatar. Migrasi menambah avatar_confirmed_at, mengekalkan pilihan/cap lama. 53 ujian / 1,256 assertions lulus; build berjaya. Semakan QA desktop/390 px mengesahkan pratonton, butang pengesahan, mesej berjaya dan pembukaan Zon 2.


### 22 Oktober 2026 — Tutorial popup berpandu

Ganti skrin tutorial biasa dengan modal enam langkah di atas Papan Pemuka. Tambah spotlight sebenar, overlay gelap, ikon, dot/bilangan langkah, Sebelumnya/Seterusnya/Langkau/Selesai, sokongan Escape serta perangkap fokus native. Auto-buka hanya status pending; status disimpan merentas sesi. Tambah Lihat Tutorial Semula pada Profil dan Bantuan tanpa menambah item dropdown. Sorotan hamburger dan tajuk zon menyokong telefon. 56 ujian / 1,295 assertions lulus; build dan semakan pelayar desktop/telefon berjaya.

## Rujukan muktamad Kuiz & Aktiviti — PDF 22 Oktober 2026

Rujuk docs/references/latest-learning-flow.md serta salinan PDF asal dalam docs/references/. Bahagian ini menggantikan semua flow/soalan terdahulu yang bercanggah. Pengguna mengesahkan Zon 2 TIADA countdown atau timer. Hanya Zon 3/4/5 menggunakan countdown selepas Faham, timer dan jumlah masa 3+4+5.

Zon 1 tiada nota/kuiz: maklumat diri → avatar → Mari Meneroka → OK → cap dan buka Zon 2. Zon 2 nota visual/audio → Sudah Baca dan Teruskan → OK → cap dan buka Zon 3. Zon 3 ialah 6+6 Betul/Salah dan 10 kad Tarik & Lepas (satu soalan pengelasan). Zon 4 ialah 5 Susun Ayat serta 5 Isi Tempat Kosong dengan semakan pilihan ejaan DAN salinan ayat lengkap; maksud dua peribahasa muncul selepas betul. Zon 5 ialah popup ketibaan, tiga Imlak sebenar (di rumah; bagai dihiris sembilu; ukur baju di badan sendiri), had tiga kali audio setiap soalan, refleksi lima emoji, cap akhir dan sijil. Timer Zon 5 berhenti selepas refleksi dihantar.

Salah tidak membuka soalan seterusnya. Pelajar membetulkan soalan sama sehingga betul; sejarah setiap penghantaran disimpan, markah idempoten dan kunci jawapan tidak bocor sebelum betul. Cabaran dalam satu zon juga berurutan. Migrasi 000003 pada 22 Oktober menambah answer_submissions dan attempt_questions.audio_plays. Pemasangan kandungan menambah keluaran dan perjalanan baharu, mengarkibkan percubaan aktif versi lama sambil mengekalkan semua jawapan, masa, markah, cap, snapshot serta sijil sejarah. Pasport memaparkan sijil lama juga. Jumlah masa lama tidak dikosongkan.

Katalog baharu mengandungi 26 soalan: 12 Betul/Salah, 1 pengelasan 10 kad, 5 Susun Ayat, 5 Isi Tempat Kosong dan 3 Imlak. Semua aset audio Bahasa Melayu tersedia setempat; semua kandungan menyokong CRUD dan penerbitan Admin.

Pengesahan akhir: 60 ujian / 1,397 assertions lulus; build Vite berjaya. Semakan desktop dan telefon 390 px, semua jenis soalan, had audio, refleksi dan sijil selesai. Bukti: docs/screenshots/pdf-journey-completed.png serta pdf-zone3-retry.png, pdf-zone4-proverb.png, pdf-zone4-copy-sentence.png, pdf-zone4-mobile.png, pdf-zone5-audio-limit.png dan pdf-zone5-reflection.png. Kamera sebenar tidak diuji.


## 22 Oktober 2026 — Dokumentasi lengkap v2: akses kod dan sesi

Sumber utama baharu dibaca sepenuhnya: Dokumentasi_Lengkap_Sistem_Di-BiTaRa.pdf (8 halaman). Pelajar tidak lagi mendaftar/log masuk atau mempunyai profil/dropdown akaun. Kod sah terus mencipta learner_sessions sebenar tanpa users tersembunyi. Nama/kelas pilihan, avatar dan tutorial di Zon 1; navbar sesi dan Tamat Sesi menggantikan dropdown lama.

Tambah Kod Akses Pentadbir (cipta/nama/status/tamat tempoh/penggunaan) dan Keputusan / Sesi. Pemilikan perjalanan, jawapan, draf, notifikasi, pasport dan sijil disaring mengikut sesi. Satu kod boleh digunakan sesi berbeza tanpa akses silang. Token 256-bit dihash, session ID dijana semula, CSRF dan had cubaan kod gagal digunakan. Kod sah daripada IP sekolah sama tidak dikira sebagai kegagalan.

Satu jam bermula selepas countdown pertama Zon 3 dan berterusan hingga refleksi Zon 5. Countdown setiap cabaran Zon 3/4 sahaja, Seterusnya terus ke countdown seterusnya. Had tiga audio Imlak dipulihkan mengikut sumber baharu, tanpa extra audio/jeda versi akaun. Cap dan kapal terbang automatik; nota Zon 2 automatik. Pratonton Pentadbir merangkumi lima zon tanpa menulis rekod murid.

Migrasi 000004 dan 000005 disemak pada QA dan diterapkan setempat selepas sandaran. Hash semua lajur/rekod lama bagi users, student_profiles, student_journeys, attempts, attempt_questions, responses, passport_stamps, certificates dan learning_releases kekal sama. Tiada reset data atau sijil sejarah. Dokumen Plan/Module/Database/Prototype/README ditulis semula untuk versi kod, dan versi lama disimpan dalam docs/history. Ujian akaun yang dimansuhkan diganti ujian sesi, dengan regresi CRUD/media/sumber/branding/Bahasa Melayu dikekalkan.

## Pengesahan akhir — 22 Oktober 2026

- `php artisan test --compact`: **46 ujian lulus, 1,137 assertions**.
- `npm run build`: berjaya.
- Ujian pelayar menggunakan pangkalan data QA berasingan: kod → Zon 1/avatar → nota/audio Zon 2 → semua 26 soalan → refleksi → lima cap → sijil → Tamat Sesi → keputusan kekal dalam Pentadbir.
- Salah pada cubaan pertama direkodkan berasingan; had audio tiga kali, countdown setiap cabaran, masa tunggal hingga refleksi dan rekod sesi berlainan disahkan.
- Desktop dan telefon 390px disemak tanpa limpahan mendatar. Kamera/hand detection fizikal belum diuji dengan peranti; laluan alternatif tanpa kamera disahkan.
- Pangkalan data utama dinaik taraf selepas sandaran SQLite; semua lajur/rekod asal bagi akaun, perjalanan, jawapan, cap, sijil dan snapshot dibandingkan tanpa kehilangan data.
- Halaman utama, Masukkan Kod dan Log Masuk Pentadbir di `http://localhost:8000` memberi HTTP 200. Kod dan sesi ujian hanya dicipta dalam QA.
- Bukti UI: [Pasport lengkap](docs/screenshots/access-session-passport.png), [keputusan Pentadbir](docs/screenshots/access-session-admin-results.png), [telefon](docs/screenshots/access-session-mobile.png).

## 22 Oktober 2026 — Fokus cabaran dan akses kod

- Navbar sesi: Kuiz & Aktiviti, Pasport & Pencapaian, Profil sesi, Tamat Sesi. Papan Pemuka dan notifikasi dikeluarkan; /pelajar menghala ke aktiviti semasa.
- Halaman kod khas berpusat tanpa panel kiri/kembali. Utama tiada borang atau CTA kod dalam kandungan.
- Countdown menghantar cabaran automatik tanpa butang mula/batal. Pengenalan tiada Kembali.
- Soalan di atas, timer sisi kanan/kompak pada telefon, maklum balas merah/hijau dan cubaan semula dikekalkan.
- Profil sesi ringkas dan tutorial semula; identiti kekal sesi, bukan akaun.


Pengesahan penambahbaikan fokus cabaran: **48 ujian lulus (1,186 assertions)** dan `npm run build` berjaya. Ujian pelayar QA melalui kod sebenar → avatar Zon 1 → nota Zon 2 → countdown automatik Zon 3 → salah/merah → cuba semula/betul/hijau → Profil sesi. Timer kekal berjalan dan tidak reset. Desktop 1280px serta telefon 390px disemak; tiada limpahan mendatar. Bukti di `docs/screenshots/focused-code-desktop.png`, `focused-code-mobile.png`, `focused-challenge-retry.png` dan `focused-challenge-mobile.png`. Pelayan utama localhost:8000 memaparkan kad kod baharu. Tiada data utama atau struktur database diubah untuk penambahbaikan ini.


## Enhancement PDF dan sijil — 22 Oktober 2026

## Pemetaan pelaksanaan

1. Tajuk zon/cabaran di atas, kemajuan soalan semasa ringkas. `student/mission.blade.php`, CSS branding.
2. Panel/sejarah Jawapan Anda dikeluarkan daripada HTML semua aktiviti. Rekod asal masih disimpan untuk laporan dan pemarkahan.
3. Tarik dan Lepas menggunakan kad berwarna, Pointer Events untuk seretan mouse/touch, sasaran kategori jelas, serta pilihan kad → kategori melalui klik/papan kekunci. Tiada dropdown. Nilai hidden tetap dihantar dan digred pelayan; draf dipulihkan selepas reload. Semua kad mesti diletakkan sebelum dihantar.
4. Satu komponen jam dalam navbar sesi, format mm:ss. Tiada jam di sisi. Bermula selepas countdown pertama Zon 3, berjalan hingga refleksi Zon 5, tidak reset apabila melihat halaman/popup.
5. Canvas utama max-width 1120px, margin auto dan padding responsif. Modals berkongsi radius, bingkai dan warna yang konsisten.
6. Maksud Susun Ayat ialah langkah pelayan: jawapan betul → popup → tandakan telah membaca → Seterusnya → soalan baharu. `attempt_questions.meaning_acknowledged_at` ditambah. `pendingMeaning`, `acknowledgeMeaning` dan `syncAttempt` menahan penyelesaian cabaran terakhir sehingga maksud disahkan. Penghantaran/draf soalan seterusnya disekat. Panggilan berulang tidak menggandakan markah. Dua peribahasa menggunakan maksud sumber; tiga ayat biasa mempunyai penerangan literal ringkas melalui SentenceMeaning, termasuk bagi snapshot asal tanpa menukar jawapan. Studio meminta maksud apabila menyimpan soalan Susun Ayat baharu.
7. Refleksi berpusat, lima emosi sama saiz, emoji besar dan highlight jelas. Desktop lima lajur; telefon dua lajur dengan pilihan akhir berpusat dan sama saiz.
8. Penamat berkongsi gaya refleksi, menunjukkan markah, cap, masa beku dan mesej penghargaan. `student_journeys.completion_seen_at` memastikan popup tidak hilang akibat heartbeat/refresh; POST pemilik sesi menandakan telah dilihat. Rekod sejarah yang sudah lengkap ditanda semasa migrasi.
9. Kuiz & Aktiviti menggunakan papan perjalanan lima kad dengan thumbnail latar sebenar, warna zon, nombor berpusat, kemajuan, status dan tindakan zon semasa. Zon lepas/hadapan kekal tiada pautan untuk masuk semula/melangkau. Halaman cabaran tetap menggunakan router satu hala.
10. CertificatePdf menghasilkan A4 landscape dengan bingkai sebenar, teks tengah, nama auto-fit berdasarkan metrik fon, nombor, tarikh dan pengesahan. Preview pasport menggunakan bingkai sama. Command refresh branding menulis fail baharu `branding-v3`, mengekalkan nama/nombor/tarikh dan fail asal.

## Data dan pengesahan

Migrasi 000006 dan 000007 sahaja; 36 jadual domain kekal. Sebelum migrasi utama, sandaran SQLite dicipta dan semua lajur/rekod asal akaun, perjalanan, soalan percubaan, jawapan, cap, sijil serta keluaran dibandingkan. Dua sijil utama diselaraskan tanpa mengubah butiran anugerah. Tiada reset atau pemadaman data.

Ujian akhir: **50 ujian lulus (1,359 assertions)**, `npm run build` berjaya. Ujian termasuk flow penuh 26 soalan, gate maksud terakhir/idempotensi, penamat kekal selepas heartbeat, kod/session ownership, timer/audio, penerbitan/media/CRUD dan sijil.

QA pelayar berasingan: seretan sebenar kad, klik kategori termasuk sasaran yang telah berisi, reload/draf, penghantaran → Zon 4, Susun Ayat → maksud → pengesahan → soalan 2, refleksi dan popup penamat pada desktop/telefon. Lima emosi telefon semuanya 148.2 × 116px; tiada limpahan mendatar pada 390px. Timer membeku pada 11:03 dalam sesi QA. Prasyarat sebahagian aktiviti QA disediakan melalui engine sebenar; angka masa QA bukan penanda prestasi murid.

PDF contoh dirender dan diperiksa: `output/pdf/sijil-contoh.pdf`. Bukti UI dalam `docs/screenshots/enhancement-*`. Kamera fizikal tidak diuji dalam enhancement ini; alternatif klik/sentuhan dikekalkan.

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


### Dua latar akses berbeza

Masukkan Kod menggunakan imej lapangan terbang ceria (access-adventure-background.png); Log Masuk Pentadbir menggunakan pusat operasi (admin-control-background.png). Kedua-duanya dijana melalui built-in image_gen dan disalin ke public/images. Gaya body route khusus menggantikan perkongsian home-background terdahulu. Cover/center/no-repeat/fixed, kad legap dan preload route dikekalkan. Build berjaya; desktop dan telefon disemak. Prompt dan bukti: docs/access-background-generation.md.


### Logik jawapan dan peribahasa — 23 Oktober 2026

Jawapan betul disimpan kemudian terus ke soalan seterusnya tanpa toast/banner kejayaan. Jawapan salah kekal pada soalan sama; maklum balas seragam “Jawapan kurang tepat, sila cuba lagi.” Tiada penandaan pilihan ditolak atau pendedahan jawapan. Draf, cubaan pertama, pemarkahan, penghantaran idempoten dan timer kekal.

SentenceMeaning hanya memerlukan pengakuan bacaan bagi peribahasa. Dua peribahasa sumber lama dikenal pasti melalui ayatnya; soalan baharu menggunakan config.is_proverb yang boleh diurus melalui editor Pentadbir. Maksud diwajibkan hanya bagi peribahasa. Tajuk: Maksud Peribahasa. Ayat biasa dengan penerangan lama tidak mencetuskan popup. Sesi lama yang sudah menjawab semua soalan tetapi menunggu maksud ayat biasa diselaraskan ketika disambung.

Barcode pasport dibuang. Masa Pengembaraan Saya hanya memaparkan masa keseluruhan dan Betul Cubaan Pertama (lebar penuh). Perekodan learning_help_events dan penambahan audio_help_plays dihentikan; route pencatatan bantuan dan listener frontend dibuang. Metrik/laporan Admin tidak lagi mengira bantuan. Jadual/lajur sejarah dibiarkan tanpa pemadaman data, untuk mengekalkan rekod terdahulu. Bilangan dengar audio biasa (had tiga kali bagi flow semasa) dikekalkan.

Build berjaya. Semakan visual QA: docs/screenshots/passport-no-barcode-no-help.png.


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


## 23 Oktober 2026 — Kod mengikut tahun dan eksport sesi

Kod baharu mewajibkan Tahun 4/5/6 dan mengunci set soalan pada kod serta sesi. Set Tahun 5 dan 6 serta enam audio imlak dipasang daripada PDF pengguna; Tahun 4 dan rekod lama dikekalkan. Keputusan Sesi menyokong filter tahun serta muat turun Excel/PDF berdasarkan filter semasa. Rujuk [dokumentasi tahun dan eksport](docs/year-question-sets.md).
