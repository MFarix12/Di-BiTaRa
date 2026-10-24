# Antaramuka Di-BITaRa — sesi kod

Nama rasmi: **Di-BITaRa** / **Bijak Imbuhan & Tatabahasa Ruang Angkasa**. Logo `public/images/official-logo.png`; navy/biru, emas/kuning dan putih, awan/bintang/kapal terbang, avatar berdiri penuh. Bahasa Melayu dan satu arahan pendek pada satu masa.

## Layout awam

Navbar putih: Utama, Tentang, Cara Bermain, Capai Pembelajaran, Masukkan Kod, Log Masuk Pentadbir. Logo kiri, menu kanan, active state navy/kuning dan hamburger telefon. Footer awam sahaja. Utama menyediakan gambaran menyeluruh sahaja; halaman Masukkan Kod Pengembaraan mempunyai kad berpusat, ikon tiket, satu medan besar serta Mula Pengembaraan. Tiada panel kiri atau butang kembali. Tiada pautan pendaftaran/login Pelajar.

## Layout sesi Pelajar

Top navbar navy: Kuiz & Aktiviti | Pasport & Pencapaian | Profil | Tamat Sesi. Profil memaparkan avatar/nama/sesi. Tiada sidebar atau dropdown akaun. Modal Tamat Sesi mempunyai Batal/Tamat Sesi dan penjelasan keputusan disimpan. Tiada footer.

Laluan Papan Pemuka lama diarahkan ke aktiviti semasa. Pasport mengandungi ringkasan, kemajuan, cap dan sijil. Kad zon menggunakan background sebenar. Kad selesai bertanda selesai, kad masa hadapan terkunci tanpa pautan; hanya zon semasa boleh dimasuki.

## Zon dan interaksi

Hero Kuiz & Aktiviti berbingkai rounded dengan ilustrasi juruterbang dan bubble. Penunjuk zon menunjukkan urutan/status. Zon 1 menyediakan nama/kelas pilihan, grid 15 avatar, penapis, pratonton serta pengesahan. Tutorial popup/spotlight di atas halaman ini bagi sesi baharu.

Zon 2 terus membuka nota visual/audio. Zon 3/4 mempunyai kad pengenalan, Faham dan countdown besar sebelum setiap cabaran. Satu jam pengembaraan dipaparkan selepas bermula di Zon 3. Jenis soalan menyediakan pilihan besar, kad klasifikasi, token susunan dan pilihan ejaan + ruang salinan. Jawapan salah: cuba lagi pada soalan sama. Betul: visual positif dan markah; maksud peribahasa hanya selepas betul.

Selesai zon: animasi cap, markah zon jika ada, kapal terbang besar, navigasi automatik. Reduced motion menggunakan butang Teruskan. Zon 5: modal ketibaan, tiga Imlak dengan Dengar/Henti/kiraan 0–3, refleksi lima emoji, penamat dan pasport. Tiada countdown Zon 5.

Pasport menunjukkan identiti sesi/avatar, cap lima zon, lencana, pencapaian terkini, masa keseluruhan dan sijil boleh dipratonton/dimuat turun. Ulang Kaji berasingan selepas tamat tanpa perubahan keputusan asal.

## Layout Pentadbir dan responsif

Sidebar pengurusan, toolbar profile dropdown (Profil/Tetapan/Log Keluar), modal log keluar. Kod Akses menyediakan cipta/status/deadline/penggunaan, Keputusan / Sesi dan Laporan menunjukkan markah, cubaan pertama, masa dan status tamat. Pratonton lima zon tanpa kesan pada data murid. Borang studio CRUD dan audio kekal.

Laptop/desktop menggunakan ruang berkolum; tablet/telefon menggunakan hamburger/drawer dan grid bertindan. Butang sekurang-kurangnya ruang sentuh yang selesa, fon jelas, tiada limpahan mendatar. Latar cover/crop; avatar contain tanpa pemotongan. Kamera sebenar memerlukan UAT peranti selain ujian alternatif klik/sentuh.


## Enhancement UI terkini

Timer ringkas berada dalam navbar sesi sepanjang Zon 3–5. Tajuk zon/cabaran di atas; tiada panel Jawapan Anda. Kad Tarik dan Lepas berwarna, tanpa dropdown, menyokong seretan serta pilihan kad-ke-kategori. Maksud Susun Ayat wajib disahkan sebelum soalan seterusnya. Papan perjalanan lima kad, refleksi dan penamat diseragamkan; sijil menggunakan bingkai pengguna. Lihat [pelaksanaan dan bukti](docs/project-enhancement-implementation.md). 50 ujian lulus (1,359 assertions).
