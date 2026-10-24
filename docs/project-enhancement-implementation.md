# Enhancement UI dan interaksi — 22 Oktober 2026

Sumber: `docs/references/Di-BiTaRa-project-enhancement.pdf`, dibaca semua 10 halaman termasuk lima halaman gambar. SHA-256: `d346959e8bccd17b6c307a32d9f7d75dd6862945cccc7d20058830ba971e7a6f`. Bingkai sijil pengguna disalin tanpa perubahan ke `public/images/certificate-frame.png`; salinan JPEG digunakan untuk PDF A4. Arahan pengguna mengesahkan pelaksanaan semua 10 perkara.

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
