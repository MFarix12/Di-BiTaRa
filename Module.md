# Modul Di-BITaRa — versi sesi kod

Rujuk [Plan.md](Plan.md) dan [flow aktif](docs/references/latest-learning-flow.md). Sumber utama ialah dokumentasi lengkap v2, bukan dokumen akaun sejarah.

## Akses dan identiti

AccessSessionController mengesahkan kod melalui POST; tiada akaun users dicipta. LearnerSession ialah identiti sesi tanpa kata laluan. LearnerIdentity memusatkan skop perjalanan, profil sesi, notifikasi dan sijil. Guard learner membaca token hash; guard web untuk Pentadbir sahaja. RequireLearnerSession melindungi semua halaman/tindakan Pelajar. Pengesahan role dan ownership tidak bergantung pada nilai borang.

Kod sah terus ke Zon 1. Nama/kelas pilihan dan avatar disimpan pada learner_sessions. Tutorial juga milik sesi. Tamat Sesi menandakan ended_at, menutup akses peranti, menyimpan semua rekod dan kembali ke Utama. Akses kod sama pada murid/peranti lain mencipta sesi baharu; tiada lookup keputusan menggunakan kod sahaja.

## Zon dan pembelajaran

AdventureController mengawal halaman semasa dan peralihan; ContinuousAdventure menentukan zon/cabaran tertunda dan countdown tiga saat pelayan. LearningEngine menyimpan manifest, percubaan, jawapan serta kemajuan dan memberi cap/sijil secara idempoten. Zon/cabaran tidak boleh dilangkau atau dimasuki semula melalui URL.

Zon 1: DeparturePreparation → pengesahan avatar → cap → transition → nota Zon 2 automatik. Zon 2: baca/audio → pengesahan → cap → Zon 3. Zon 3: dua kuiz Betul/Salah dan satu pengelasan. Zon 4: Susun Ayat serta Isi Tempat Kosong dengan salinan ayat; kamera pilihan. Zon 5: tiga Imlak dengan audio maksimum tiga kali, kemudian refleksi lima emoji dan penamat.

QuestionBank memvalidasi serta menggred ejaan, kata sendi/imbuhan, kategori, susunan tepat dan salinan ayat. Grading snapshot per percubaan tidak berubah. Salah tidak membuka soalan berikut. Setiap submission mempunyai kunci unik; markah/cap tidak berganda.

## Masa, draf dan laporan

Journey clock versi 3 menggunakan beza masa pelayan daripada Mula Cabaran 1 Zon 3 hingga refleksi Zon 5. Tidak reset pada pertukaran halaman/cabaran/zon, tidak menggunakan timer per zon. Paparan disegar tanpa bergantung pada nilai masa klien. Penamat membekukan rekod akhir.

AdventureSupportController menyimpan draf semasa sahaja dan memeriksa ownership. Draf peranti membantu semasa gangguan, kemudian diselaraskan. Reload memulihkan pilihan/ayat/susunan. LearningMetrics mengira betul dan markah cubaan pertama daripada answer_submissions; markah penguasaan daripada responses. Bantuan arahan direkod; sesi kod tidak membenarkan extra audio selepas tiga kali.

## Profil sesi, Pasport dan sijil

PassportOverview menggunakan pemilik sesi, lima zon, cap, lencana, sejarah, sijil dan masa tunggal. /pelajar menghala ke aktiviti semasa. Profil sesi ialah ringkasan nama/kelas/avatar dengan tutorial semula, tanpa akaun. NotificationSummary serta LearningController menyaring notifikasi sesi. CertificatePdf menggunakan logo rasmi; StudentCertificateController menyaring pemilik sesi, status ready, storan peribadi dan MIME PDF. Kegagalan simpan sijil tidak memadam kelayakan. Ulang Kaji hanya selepas pengembaraan tamat, tanpa menulis percubaan, cap atau masa asal.

## Pentadbir

AccessCodeController: cipta kod unik, kemas kini nama/status/tamat tempoh; penggunaan dan keputusan sesi. StudioController: CRUD nota/aktiviti/soalan/arahan/audio/media, validasi jenis soalan, pratonton dan penerbitan. Media berversi supaya fail sesi lama kekal. Audit merekod semua perubahan pengurusan termasuk kod. Pentadbir mempunyai ringkasan kod/sesi/kandungan, laporan dan pratonton lima zon. Profil/Tetapan/Log Keluar dalam dropdown toolbar.

## UI dan media

Bahasa Melayu, logo rasmi, navy/biru/emas/putih, fon besar dan arahan pendek. Latar zon turut digunakan thumbnail. Avatar berdiri penuh dengan object-fit contain. Top navbar sesi responsif, sidebar Pentadbir drawer, footer awam sahaja. Cap dan kapal terbang singkat dengan reduced motion. Audio setempat, mikrofon dan kamera meminta izin apabila digunakan; video tidak dirakam/dihantar. Input klik/sentuhan/papan kekunci sentiasa tersedia.


## Enhancement UI terkini

Timer ringkas berada dalam navbar sesi sepanjang Zon 3–5. Tajuk zon/cabaran di atas; tiada panel Jawapan Anda. Kad Tarik dan Lepas berwarna, tanpa dropdown, menyokong seretan serta pilihan kad-ke-kategori. Maksud Susun Ayat wajib disahkan sebelum soalan seterusnya. Papan perjalanan lima kad, refleksi dan penamat diseragamkan; sijil menggunakan bingkai pengguna. Lihat [pelaksanaan dan bukti](docs/project-enhancement-implementation.md). 50 ujian lulus (1,359 assertions).
