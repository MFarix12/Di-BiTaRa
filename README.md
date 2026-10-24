# Di-BITaRa

**Bijak Imbuhan & Tatabahasa Ruang Angkasa** — pembelajaran Bahasa Melayu untuk murid sekolah rendah. Pelajar menggunakan kod akses dan sesi tanpa akaun. Pentadbir sahaja menggunakan log masuk.

## Jalankan sistem

```powershell
cd C:\laragon\www\Di-BiTaRa
.\scripts\run.ps1 serve
```

Buka [Di-BITaRa](http://localhost:8000). Untuk pemasangan baharu: Composer/npm dan `.env`, kemudian `migrate`, `admin`, `content`, `build`, `serve` melalui skrip sama. Jangan gunakan `migrate:fresh` pada data diperlukan. SQLite setempat berada dalam `database/database.sqlite`; sandaran migrasi dalam `.cache/backups`.

## Mula menggunakan kod

1. Pentadbir log masuk melalui `/log-masuk-admin`.
2. Buka **Kod Akses**, cipta kod, tetapkan nama aktiviti/status/tamat tempoh jika diperlukan.
3. Berikan kod kepada murid. Murid menggunakan **Masukkan Kod** pada halaman utama atau `/masukkan-kod`.
4. Kod sah mencipta sesi tersendiri dan membuka halaman Nama Pelajar dan Kelas (kedua-duanya wajib). Selepas Seterusnya, Zon 1 hanya untuk memilih, melihat pratonton dan mengesahkan avatar. Kod sama boleh digunakan oleh beberapa murid; setiap sesi dan keputusan kekal berasingan.

Tiada pendaftaran, ID log masuk, kata laluan, profil akaun atau Log Keluar Pelajar. Nama dan kelas pilihan di Zon 1. Navbar sesi: **Kuiz & Aktiviti | Pasport & Pencapaian | Profil | Tamat Sesi**. Profil memaparkan identiti sesi, bukan akaun. Tiada Papan Pemuka atau Notifikasi. Footer hanya halaman umum.

## Perjalanan pembelajaran

| Zon | Kandungan | Kira detik |
|---|---|---|
| 1 | Nama/kelas pilihan, avatar berdiri penuh, pengesahan, cap | Tiada |
| 2 | Nota visual dan audio dibuka automatik | Tiada |
| 3 | 6 Betul/Salah imbuhan, 6 Betul/Salah kata sendi, 10 kad klasifikasi | Setiap cabaran |
| 4 | 5 Susun Ayat, 5 Isi Tempat Kosong dan salin ayat | Setiap cabaran |
| 5 | 3 Imlak dan refleksi lima emoji | Tiada |

Satu jam bermula selepas countdown pertama Zon 3, terus berjalan sepanjang cabaran/transition hingga refleksi Zon 5. Tiada timer per zon. Audio Imlak maksimum tiga kali. Salah kekal pada soalan semasa sehingga betul. Jumlah 26 soalan dan 260 markah penguasaan; laporan turut merekod cubaan pertama.

Selesai zon → cap → animasi kapal terbang → zon seterusnya secara automatik. Zon selesai dan terkunci tidak boleh dimasuki semula dalam perjalanan utama. Ulang Kaji selepas tamat tidak mengubah rekod asal. Lima cap dan refleksi menghasilkan sijil PDF. Tamat Sesi menutup akses peranti tetapi mengekalkan keputusan untuk guru.

Sesi menggunakan cookie Laravel dan token rahsia yang dihash; tempoh cookie mengikut konfigurasi `SESSION_LIFETIME`. Kod sahaja tidak membolehkan murid membaca keputusan sesi lain. Kod baharu yang dinyahaktifkan/tamat tempoh ditolak pada kemasukan; sesi yang telah bermula mengekalkan versi kandungannya. Tiada akaun Pelajar tersembunyi dicipta.

## Pengurusan Pentadbir

Kod Akses; Keputusan / Sesi; CRUD Kandungan/Nota, Kuiz & Aktiviti, Soalan & Jawapan, Arahan, Audio dan Media; pratonton lima zon; penerbitan; laporan; Audit Log. Profil, Tetapan dan Log Keluar dalam dropdown atas.

Audio boleh dirakam atau dimuat naik, dipratonton, diganti dan dipaut kepada nota/soalan/arahan. Fail divalidasi pelayan. Penggantian media dan penerbitan menyimpan versi, jadi sesi lama mengekalkan snapshot. Markah, cap dan sijil disahkan pelayan dan idempoten.

Kawalan tangan Zon 4 ialah pilihan. Klik, sentuhan dan papan kekunci sentiasa tersedia. MediaPipe/model disimpan setempat; video diproses pada peranti tanpa rakaman/muat naik. Kamera/mikrofon sebenar memerlukan izin dan UAT peranti; jangan anggap ujian automatik mengesahkan perkakasan.

## Rujukan dan semakan

Sumber utama: [Dokumentasi Lengkap Sistem v2](docs/references/Dokumentasi_Lengkap_Sistem_Di-BiTaRa.pdf). [Flow aktif dan kandungan tepat](docs/references/latest-learning-flow.md). [Pemetaan enhancement](docs/access-session-enhancement.md). Plan.md, Module.md, Database.md dan Prototype.md diselaraskan. Versi akaun lama berada dalam docs/history untuk rujukan sejarah sahaja.

```powershell
.\scripts\run.ps1 test
.\scripts\run.ps1 build
```

Migrasi tambahan mengekalkan semua akaun, jawapan, markah, masa, cap, sijil dan snapshot lama. Tiada reset pangkalan data. Ujian akaun yang telah dimansuhkan diganti ujian sesi kod; semakan CRUD, media, sumber soalan, keselamatan, branding dan Bahasa Melayu dikekalkan.

## Pengesahan akhir — 22 Oktober 2026

- `php artisan test --compact`: **46 ujian lulus, 1,137 assertions**.
- `npm run build`: berjaya.
- Ujian pelayar menggunakan pangkalan data QA berasingan: kod → Zon 1/avatar → nota/audio Zon 2 → semua 26 soalan → refleksi → lima cap → sijil → Tamat Sesi → keputusan kekal dalam Pentadbir.
- Salah pada cubaan pertama direkodkan berasingan; had audio tiga kali, countdown setiap cabaran, masa tunggal hingga refleksi dan rekod sesi berlainan disahkan.
- Desktop dan telefon 390px disemak tanpa limpahan mendatar. Kamera/hand detection fizikal belum diuji dengan peranti; laluan alternatif tanpa kamera disahkan.
- Pangkalan data utama dinaik taraf selepas sandaran SQLite; semua lajur/rekod asal bagi akaun, perjalanan, jawapan, cap, sijil dan snapshot dibandingkan tanpa kehilangan data.
- Halaman utama, Masukkan Kod dan Log Masuk Pentadbir di `http://localhost:8000` memberi HTTP 200. Kod dan sesi ujian hanya dicipta dalam QA.
- Bukti UI: [Pasport lengkap](docs/screenshots/access-session-passport.png), [keputusan Pentadbir](docs/screenshots/access-session-admin-results.png), [telefon](docs/screenshots/access-session-mobile.png).



## Enhancement UI terkini

Timer ringkas berada dalam navbar sesi sepanjang Zon 3–5. Tajuk zon/cabaran di atas; tiada panel Jawapan Anda. Kad Tarik dan Lepas berwarna, tanpa dropdown, menyokong seretan serta pilihan kad-ke-kategori. Maksud Susun Ayat wajib disahkan sebelum soalan seterusnya. Papan perjalanan lima kad, refleksi dan penamat diseragamkan; sijil menggunakan bingkai pengguna. Lihat [pelaksanaan dan bukti](docs/project-enhancement-implementation.md). 50 ujian lulus (1,359 assertions).


## 23 Oktober 2026 — Kod mengikut tahun dan eksport sesi

Kod baharu mewajibkan Tahun 4/5/6 dan mengunci set soalan pada kod serta sesi. Set Tahun 5 dan 6 serta enam audio imlak dipasang daripada PDF pengguna; Tahun 4 dan rekod lama dikekalkan. Keputusan Sesi menyokong filter tahun serta muat turun Excel/PDF berdasarkan filter semasa. Rujuk [dokumentasi tahun dan eksport](docs/year-question-sets.md).
