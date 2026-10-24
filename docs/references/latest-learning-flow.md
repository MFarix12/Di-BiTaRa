# Flow aktif Di-BiTaRa — akses kod dan sesi

Dokumen utama: [Dokumentasi Lengkap Sistem v2](Dokumentasi_Lengkap_Sistem_Di-BiTaRa.pdf), 8 halaman. Dokumen ini menggantikan Flow Di-BiTaRa terdahulu. Kandungan soalan sebenar kekal daripada [Soalan Kuiz](Soalan%20Kuiz.pdf), 6 halaman.

## Perjalanan semasa

Masukkan Kod → Kod Disahkan → sesi baharu → Zon 1 → Zon 2 → Zon 3 → Zon 4 → Zon 5 → Tamat.

- Tiada akaun, daftar, login, kata laluan atau dropdown akaun Pelajar. Profil sesi ringkas dibenarkan oleh arahan UI terbaru; ia bukan akaun. Nama dan kelas pilihan di Zon 1; avatar disahkan di Zon 1 sahaja.
- Zon 1: persediaan → pengesahan → cap → kapal terbang → nota Zon 2 secara automatik.
- Zon 2: nota/audio → Sudah Baca dan Teruskan → cap → kapal terbang → pengenalan Zon 3.
- Zon 3: countdown Cabaran 1 → mula jam keseluruhan → 6 Betul/Salah imbuhan → markah → countdown → 6 Betul/Salah kata sendi → markah → countdown → 10 kad Tarik & Lepas → cap → Zon 4.
- Zon 4: mod kamera atau alternatif → countdown → 5 Susun Ayat → markah → countdown → 5 Isi Tempat Kosong serta salin ayat → cap → Zon 5.
- Zon 5: popup ketibaan, tiada countdown baharu → 3 Imlak → refleksi lima emoji → jam berhenti → cap akhir, pencapaian dan sijil.
- Satu jam sahaja, dari countdown pertama Zon 3 selesai hingga refleksi Zon 5. Termasuk transition dan pertukaran cabaran; tiada reset atau jam berasingan bagi setiap zon. Sesi kod mengikut dokumen penuh; pengecualian jeda/extra audio versi akaun tidak digunakan.
- Audio Imlak maksimum tiga kali. Salah kekal pada soalan sama. Betul membuka langkah seterusnya. Kunci jawapan tidak didedahkan sebelum betul.
- Zon masa hadapan dan zon selesai tidak boleh dimasuki semula dalam perjalanan utama. Mod Ulang Kaji berasingan selepas tamat tidak mengubah rekod asal.
- Semua rekod dimiliki sesi serta kod. Kod bersama boleh mewujudkan sesi berasingan bagi setiap peranti/murid; kod sahaja tidak boleh membaca keputusan sesi orang lain. Tamat Sesi membatalkan akses sesi peranti sambil mengekalkan keputusan untuk guru.

## Kandungan tepat

Zon 3 (halaman 1–2): ditulis, di baca, dimasak, di buang, dihantar, di sapu; di sekolah, dirumah, di padang, dipejabat, di taman, dikelas. Kad kategori: dijawab, di dapur, dimasak, di padang, dicuci, di kantin, ditutup, di kedai, dipukul, di bilik.

Zon 4 Susun Ayat (halaman 3–4):
1. Ukur baju di badan sendiri. (urutan 3,4,1,2,5)
2. Bagai dihiris sembilu. (urutan 2,3,4,1; blok di dan hiris dikekalkan seperti sumber, ayat lengkap selepas betul menggunakan dihiris)
3. Saya makan roti di kantin. (2,1,3)
4. Murid-murid belajar di sekolah. (1,3,2)
5. Karangan ditulis oleh Kapten Adam. (2,4,1,3)

Dua peribahasa memaparkan maksud selepas betul sahaja: berbelanja mengikut kemampuan sendiri; hati sangat sedih atau pedih. Maksud ini ialah penerangan tambahan yang ringkas; bukan soalan pengganti.

Zon 4 Isi Tempat Kosong (halaman 5): Buku itu ditulis oleh saya.; Buah epal itu dimakan oleh saya.; Kami berkumpul di dewan sekolah.; Amir makan nasi di kantin.; Kami bermain bola di padang. Pelayan memerlukan kedua-dua ejaan pilihan dan salinan ayat lengkap betul.

Zon 5 (halaman 6): **di rumah**, **bagai dihiris sembilu**, **ukur baju di badan sendiri**. Audio Bahasa Melayu dibundel dalam resources/audio/latest-20261022; runtime tidak memerlukan perkhidmatan TTS. Had tiga permintaan audio bagi setiap soalan dikuatkuasakan pelayan dan kekal selepas muat semula. Pautan media biasa tidak boleh memintas had ini.


## Data dan pengesahan

Migration 000005 menambah access_codes, learner_sessions serta pemilikan sesi nullable pada perjalanan/notifikasi. Tiada akaun tersembunyi dicipta. Pemilikan draf, jawapan, audio, nota dan sijil diperiksa pelayan. Hash token sesi tidak dipaparkan atau disimpan sebagai kata laluan; kod dimasukkan melalui POST dengan CSRF dan had cubaan. Admin sahaja boleh mencipta/menyunting kod, menetapkan tamat tempoh dan melihat sesi/keputusan.

Kandungan semasa: 26 soalan (12 Betul/Salah, 1 klasifikasi 10 kad, 5 Susun Ayat, 5 Isi Tempat Kosong dan 3 Imlak), maksimum 260 markah penguasaan. Laporan turut membezakan cubaan pertama daripada pembetulan. Sijil dijana sekali bagi perjalanan lengkap. Rekod sejarah akaun kekal tersedia dalam data/laporan Pentadbir.


## Penyelarasan UI terkini

Timer dipaparkan dalam navbar; tajuk zon/cabaran di atas, tiada senarai Jawapan Anda. Selepas Susun Ayat betul, pelayan memerlukan pengesahan membaca maksud sebelum soalan atau cabaran seterusnya. Countdown, had audio, pemarkahan, cap dan penamat refleksi kekal. Popup tamat kekal hingga Lihat Pasport ditekan.
