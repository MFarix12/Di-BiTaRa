# Kod Akses mengikut Tahun — 23 Oktober 2026

Kontrak: **1 Kod = 1 Tahun = 1 Set Soalan**.

## Kandungan
- Tahun 4: set sedia ada dikekalkan tanpa mengubah snapshot atau rekod lama.
- Tahun 5 dan Tahun 6: kandungan tepat daripada `Di-BITaRa_Set_Soalan_Tahun_5.pdf` dan `Di-BITaRa_Set_Soalan_Tahun_6.pdf` yang diberikan pengguna.
- Setiap tahun: 12 Betul/Salah, satu Tarik dan Lepas (10 kad), lima Susun Ayat, lima Isi Tempat Kosong + salin ayat, tiga Imlak; 26 soalan, 260 markah.
- JSON sumber berada dalam `resources/curriculum/tahun-5.json` dan `tahun-6.json`. Audio MP3 sebenar berada dalam `resources/audio/tahun-5/` dan `tahun-6/`; disalin ke storan private semasa pemasangan.
- Peribahasa Susun Ayat Tahun 5: Berat sama dipikul, ringan sama dijinjing. Maksud: Susah dan senang dihadapi bersama-sama.
- Peribahasa Susun Ayat Tahun 6: Sediakan payung sebelum hujan. Maksud: Bersedia lebih awal sebelum sesuatu kesukaran berlaku.

## Kod dan sesi
Pentadbir memilih tahun wajib (4/5/6), nama sesi, status dan tamat tempoh pilihan. Format kod baharu `DBR-T{tahun}-{6 aksara rawak}`. Kod menyimpan tahun dan ID keluaran soalan pada penciptaan; kedua-duanya tidak boleh ditukar melalui kemas kini kod. Terbitkan kandungan baharu kemudian cipta kod baharu untuk menggunakannya.

Pelajar hanya memasukkan kod. Server menentukan tahun/keluaran dalam transaksi, menyimpannya pada sesi dan mencipta perjalanan berasaskan keluaran tersebut. Parameter tahun atau keluaran yang dihantar Pelajar tidak digunakan. Nama dan kelas diisi selepas kod sah; tiada pemilihan tahun. Sesi sedia ada tidak berpindah ke keluaran terbaharu. Aktiviti tahun lain ditolak oleh semakan manifest perjalanan.

Flow: Kod → Nama & Kelas → Zon 1 avatar → Zon 2 nota → Zon 3 → Zon 4 → Zon 5 → Refleksi → Sijil. Countdown dan satu timer berterusan sedia ada dikekalkan.

Migrasi `2026_10_23_000009_year_question_sets` menandakan rekod lama Tahun 4 dan mengekalkan keluaran sebenar setiap perjalanan lama. Sandaran database dibuat sebelum migrasi utama dalam `.cache/backups/`.

## Pengurusan dan pemasangan
- Aktiviti mempunyai tahun sendiri; editor soalan menyatakan tahun aktiviti.
- Penerbitan memilih tahun dan menyalin aktiviti tahun itu sahaja. Nota Zon 2 dikongsi.
- Jalankan `php artisan migrate --force`, pastikan kandungan Tahun 4 tersedia, kemudian `php artisan dibitara:install-year-sets`.
- Pemasangan berulang tidak menduplikasi set; tidak mengaktifkan semula atau menukar perjalanan lama.
- Audio boleh dibina semula menggunakan `scripts/prepare_year_audio.py` dengan suara sintetik Bahasa Melayu ms-MY-YasminNeural.

## Keputusan dan eksport
Tahun dipaparkan daripada sesi, bukan kelas yang ditaip atau kod semasa. Filter Tahun 4/5/6 dikongsi oleh jadual dan muat turun Excel/PDF. Eksport merangkumi semua baris yang sepadan merentas halaman, bukan halaman jadual sahaja.

Excel: 14 lajur, freeze A5, filter A4:N, lebar automatik, tarikh dan tempoh berformat angka; teks tidak ditafsir sebagai formula. PDF: A3 landscape, logo, tajuk Bahasa Melayu, waktu Malaysia, header jadual berulang dan nombor halaman. PDF menggunakan dompdf/dompdf; fail hanya tersedia kepada Pentadbir.

## Pengesahan
- Keseluruhan suite: 57 ujian / 1,708 assertions lulus.
- Dua ujian tambahan perjalanan penuh Tahun 5 dan Tahun 6: 104 assertions lulus hingga lima cap dan sijil, satu timer dan 260 markah setiap sesi.
- Semakan kandungan semua jawapan, ID soalan berasingan, audio sebenar, peribahasa, pemasangan idempotent, penolakan tahun tidak sah, tahun tidak boleh ditukar, parameter Pelajar diabaikan dan penolakan aktiviti tahun lain.
- Eksport diperiksa semula dengan openpyxl/pypdf: 14 lajur, tarikh/masa, filter, freeze header; PDF satu halaman dan laporan panjang enam halaman mempunyai header dan nombor halaman pada setiap halaman.
- QA pelayar: kod Tahun 5 berjaya dijana; filter Tahun 4 digunakan pada pautan Excel/PDF. Screenshot dalam `docs/screenshots/year-access-code.png` dan `session-results-export.png`.

## Pembetulan Tahap dan noktah
Tahap Pasport Digital = school_year sesi (4/5/6), bukan 1 + bilangan zon selesai. Pangkat dan cap kekal mengikut kemajuan.

SentencePunctuation membetulkan blok terakhir Susun Ayat menggunakan kunci susunan sebenar, dengan satu noktah. Pembetulan digunakan pada bank sumber, manifest yang dibaca dan paparan percubaan lama. ID blok, urutan penggredan, checksum keluaran asal dan keputusan sejarah dikekalkan. Migrasi 000010 membetulkan public_config bank sumber; JSON Tahun 5/6 turut diselaraskan. Semua 15 soalan aktif Susun Ayat disemak (lima setiap tahun).
