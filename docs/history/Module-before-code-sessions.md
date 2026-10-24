# Module.md - Modul dan Fungsi Di-BiTaRa

## Ketetapan aktif — akses kod dan sesi (22 Oktober 2026)

Rujukan utama ialah [Dokumentasi Lengkap Sistem v2](docs/references/Dokumentasi_Lengkap_Sistem_Di-BiTaRa.pdf), 8 halaman. [Pemetaan enhancement](docs/access-session-enhancement.md) menetapkan fail, perbezaan dan peraturan semasa. Semua bahagian akaun Pelajar serta timer per zon di bawah ialah sejarah versi terdahulu dan tidak boleh digunakan sebagai flow semasa.

Pelajar: Masukkan Kod → sesi sebenar tanpa akaun → Zon 1 (nama/kelas pilihan dan avatar) → Zon 2 (nota/audio automatik) → Zon 3 (3 cabaran) → Zon 4 (2 cabaran) → Zon 5 (3 Imlak dan refleksi) → Pasport, sijil, Tamat Sesi. Top navbar: Papan Pemuka | Kuiz & Aktiviti | Pasport & Pencapaian. Tiada profil, daftar, login, kata laluan atau dropdown akaun Pelajar. Pentadbir menggunakan log masuk biasa, mengurus Kod Akses dan Keputusan / Sesi.

Satu jam bermula selepas countdown Cabaran 1 Zon 3 dan diteruskan tanpa reset hingga refleksi Zon 5. Countdown hanya setiap cabaran Zon 3/4. Had audio Imlak tiga kali. Data dimiliki learner_sessions → access_codes; student_journeys.user_id kosong bagi sesi baharu. Akaun, perjalanan, cap, masa, snapshot dan sijil sejarah dikekalkan.


Versi 1.0 | 20 Oktober 2026 | Rujuk [Plan.md](Plan.md), [Database.md](Database.md) dan [Prototype.md](Prototype.md) sebelum pembangunan.

## 1. Kontrak bersama

Hanya dua peranan: Pelajar dan Admin. Semua paparan menggunakan Bahasa Melayu. Lima zon, nama dan urutan mengikuti Plan §5. Dasar skor, kelulusan 80% lalai, kemajuan unit wajib, cap dan sijil mengikuti Plan §8. Data terbit dibekukan melalui keluaran; perjalanan dan percubaan merujuk keluaran itu. Semua pemeriksaan akses dan pemarkahan dilakukan pelayan. Contoh dalam dokumen ialah kandungan cadangan, bukan soalan wajib daripada sumber.

Istilah teknikal jenis soalan: `objective` (objektif), `drag_drop` (seret dan lepas), `fill_blank` (isi tempat kosong), `sentence_order` (susun ayat) dan `dictation` (imlak). Kuiz ialah `activities.kind=quiz`, bukan jadual berasingan. Pelaksanaan jenis tambahan mesti menyediakan borang Admin, validasi, renderer Pelajar, skema respons, penilai pelayan dan ujian; Admin tidak boleh mencipta jenis tidak dikenali melalui teks bebas.

## A. Modul Umum

### U-01 Halaman Utama

- **Akses/tujuan:** awam; perkenalkan fokus di-/di dan tema juruterbang ruang angkasa.
- **Fungsi:** logo asal, ringkasan lima zon, manfaat, cara bermain, butang “Mulakan”, “Log Masuk” dan “Bantuan”. Kandungan bantuan boleh diurus Admin.
- **Aliran:** Mulakan → daftar/log masuk; sesi sah → papan pemuka mengikut peranan.
- **Penerimaan:** pengguna baharu memahami tujuan sebelum mendaftar; tiada skor atau nama murid lain didedahkan.

### U-02 Log Masuk, Log Keluar dan Pemulihan

Pintu masuk khusus Admin: GET/POST `/log-masuk-admin` menggunakan borang berasingan dengan label Admin. Pengesahan menambah kekangan role `admin` berdasarkan laluan pelayan, bukan nilai role daripada borang. Akaun Pelajar atau Admin tidak aktif ditolak tanpa mencipta sesi. Laluan `/log-masuk` mengekalkan aliran umum yang mengenal pasti peranan. Semua halaman log masuk menggunakan navbar global di luar bingkai kandungan.

- **Input:** kod pengguna atau emel serta kata laluan. Kata laluan ialah kaedah asas; PIN pendek tidak digunakan secara lalai walaupun disebut sebagai pilihan dalam sumber.
- **Proses:** sahkan akaun aktif, hadkan percubaan, cipta sesi dan tentukan role daripada pangkalan data. Mesej kelayakan salah tidak mendedahkan kewujudan akaun.
- **Output:** Pelajar kali pertama → tutorial/profil; Pelajar kembali → papan pemuka; Admin → papan pemuka Admin.
- **Pemulihan:** emel jika tersedia; Pelajar tanpa emel menggunakan kod pemulihan sekali guna yang diberi selepas daftar dan disimpan sebagai hash. Token/kod tamat 30 minit selepas permintaan pemulihan bagi aliran emel; kod sandaran kekal sehingga digunakan/diganti. Penetapan semula membatalkan token lama. Kehilangan kedua-duanya memerlukan bantuan pentadbir melalui proses luar aplikasi yang mengesahkan identiti.
- **Log keluar:** tamat sesi, jeda audio dan hentikan semua trek kamera/mikrofon. Permintaan URL terlarang menghasilkan halaman akses ditolak dalam Bahasa Melayu.
- **Penerimaan:** Pelajar tidak boleh membuka `/admin` atau menggunakan tindakan CRUD secara terus; akaun tidak aktif tidak boleh mencipta sesi.

### U-03 Tutorial Kali Pertama

- Empat langkah: pengenalan misi; navigasi lima zon; pasport/ganjaran; audio dan input permainan.
- Butang Sebelumnya, Seterusnya, Langkau dan Selesai; simpan langkah terakhir serta status `pending`, `skipped` atau `completed` pada profil.
- Selepas langkau, murid boleh memulakan misi selepas profil minimum; boleh ulang daripada Bantuan tanpa menetapkan semula kemajuan.
- Sorotan komponen sebenar, teks ringkas, boleh digunakan dengan papan kekunci. Jangan meminta izin kamera/mikrofon pada tutorial.

### U-04 Bantuan

- Panduan simbol, cara bermain, audio, rapat/jarak, pemulihan sambungan serta kamera/mikrofon.
- Buka tutorial semula; panduan izin pelayar dan mod alternatif; contoh bahasa disemak sebelum terbit.
- Pengguna boleh kembali ke halaman asal; input jawapan draf tidak hilang apabila bantuan ditutup.

## B. Modul Pelajar

### P-01 Pendaftaran dan Profil

Nama paparan, kod pengguna unik, kata laluan/pengesahan, avatar dan warna pesawat; emel pilihan. Jangan kumpul nombor pengenalan atau foto wajah. Semak kod unik dan format pada pelayan, simpan kata laluan hash. Tunjukkan kod pemulihan sekali sahaja dan minta murid menyimpannya dengan penjaga. Akaun baharu sentiasa role Pelajar. Nama/avatar boleh dikemas kini; perubahan nama selepas sijil tidak menukar salinan nama pada sijil terdahulu. Output: `users`, `student_profiles` dan perjalanan bagi keluaran terbit yang tersedia. Jika tiada keluaran, profil tetap boleh disimpan tetapi misi memaparkan “Misi sedang disediakan”.

### P-02 Pemilihan Avatar

Pilih avatar aktif daripada katalog juruterbang dan warna pesawat yang dibenarkan. Pratonton serta “Simpan Avatar”; tiada muat naik foto murid. Avatar tidak aktif yang telah dipilih boleh dipaparkan sebagai sejarah dengan avatar lalai jika fail tiada. Avatar bersifat kosmetik, tidak mempengaruhi jawapan atau markah.

### P-03 Papan Pemuka

Paparkan nama/avatar, ringkasan mata terbaik, kemajuan keseluruhan, cap, sambung unit terakhir dan akses pasport/sijil. Kiraan datang daripada rekod pelayan dalam keluaran murid. Permulaan baharu menunjukkan 0% dan Zon 1 tersedia; keputusan disimpan dipaparkan selepas muat semula. Tiada kedudukan awam. Keadaan sambungan gagal menawarkan cuba semula tanpa menunjukkan skor rekaan.

### P-04 Peta Perjalanan

Lima nod bersambung dengan pesawat. Setiap nod mempunyai nombor, nama, status dan sebab terkunci. Klik zon tersedia membuka taklimat zon; zon selesai boleh diulang; nod terkunci menerangkan prasyarat dan membawa ke zon yang perlu diselesaikan. Kebenaran diperiksa semula pada pelayan, bukan bergantung visual kunci. Peta vertikal pada telefon dan mendatar pada skrin luas.

### P-05 Nota Pembelajaran

Nota boleh mengandungi teks kaya tersanitasi, imej dengan teks alternatif, perbandingan dua pola, contoh ayat, audio suara atau lagu pembelajaran jika disediakan dan berlesen. Kawalan Sebelumnya, Seterusnya dan Sudah Baca. Butang Sudah Baca merekod `content_completions`; menatal atau menunggu animasi bukan bukti selesai. Pembacaan semula tidak menggandakan kemajuan. Penerangan konsep: ditulis/dibaca berimbuhan pasif; di sekolah/di rumah menunjukkan tempat. Semakan waktu menggunakan “pada” dan tidak mengulang kesilapan formula sumber.

### P-06 Kuiz dan Percubaan

1. Papar tajuk, arahan, bilangan soalan, markah maksimum dan syarat lulus.
2. “Mula Aktiviti” mencipta satu percubaan dengan versi/manifest, urutan soalan dan kunci penilaian tersimpan di pelayan.
3. Murid menjawab, boleh menyunting draf, kemudian “Hantar Jawapan”. Pelayan menolak soalan luar percubaan dan jawapan pendua.
4. Papar maklum balas: betul/salah, ejaan tepat dan sebab ringkas. Tiada bunyi atau animasi menakutkan.
5. “Seterusnya” selepas respons diterima. “Tamat Aktiviti” hanya apabila semua soalan wajib dihantar.
6. Keputusan akhir menunjukkan skor/peratus, status, unit yang perlu dibaiki dan Ulang Aktiviti/Kembali ke Peta.

| Jenis | Input Pelajar | Konfigurasi dan penilaian |
|---|---|---|
| Objektif | Satu pilihan daripada sekurang-kurangnya dua | Tepat satu pilihan betul; nilai ID pilihan dalam salinan percubaan |
| Seret dan lepas | Seret token ke sasaran atau pilih token kemudian sasaran | Token unik ber-ID, slot ber-ID, senarai token diterima setiap slot, bilangan guna token; semua slot betul untuk markah penuh |
| Isi tempat kosong | Isi satu/lebih ruang | Kunci pelayan per slot dan variasi diterima; normalisasi tidak menghapus beza di-/di |
| Susun ayat | Atur token melalui tangan, sentuh atau kekunci | Urutan ID token dinilai terhadap urutan diterima; token sama teks tetap ber-ID berbeza |
| Imlak | Teks hasil pendengaran | Audio wajib; variasi ejaan diterima disimpan pelayan; jarak di-/di mesti dinilai |

Normalisasi teks: Unicode NFC, trim hujung, lipat huruf besar/kecil, rapatkan ruang berlebihan; tanda baca hanya boleh diabaikan jika tetapan menyatakan demikian. **Jangan buang semua ruang atau membetulkan ejaan automatik** sehingga “disekolah” menjadi sama dengan “di sekolah”. Semua kunci dan variasi jawapan berada pada bahagian pelayan.

Percubaan `in_progress` boleh disambung pada soalan belum dihantar. Penghantaran menggunakan kunci idempoten dan transaksi. Draf dalam memori tidak dianggap disimpan sehingga pengesahan pelayan; mesej gangguan sambungan menerangkan hal ini. Cubaan tamat tidak boleh diedit; ulangan mencipta rekod baharu.

### P-07 Audio Soalan dan Arahan

Ikon Dengar Soalan/Dengar Arahan hanya jika audio terbit dan sedia. Ada main, jeda, ulang, kelantangan dan durasi; satu suara aktif pada satu masa. Jawapan tidak ditetapkan semula sewaktu audio atau Livewire dikemas kini. Teks arahan sentiasa ada. Bagi imlak, teks setara yang membocorkan jawapan hanya dibuka selepas penghantaran dalam mod biasa. Mod bantuan teks boleh dipilih sebelum percubaan untuk aksesibiliti, menggunakan rubrik sama dan merekod `assisted_text`; rekod membezakan mod bantuan bagi tafsiran hasil imlak. Audio rosak menawarkan ulang muat atau bantuan teks, bukan jawapan kosong yang dihukum.

### P-08 Skor dan Kemajuan

Paparkan percubaan terakhir, terbaik, maksimum dan peratus dengan label berbeza. Nota, aktiviti dan refleksi wajib merupakan unit kemajuan seperti Plan §8. Pelayan mengemas kini `student_progress` sebagai ringkasan boleh dibina semula daripada bukti selesai, bukan sumber bukti tunggal. Mata tidak bertambah dengan refresh atau ulang jawapan sama. Jika sesi terputus selepas simpan, cubaan semula memulangkan keputusan asal.

### P-09 Pasport dan Cap

Pasport ialah paparan daripada profil, perjalanan dan `passport_stamps`; tidak memerlukan jadual pasport berasingan. Papar nama, avatar, tarikh mula dan lima ruang cap. Cap kosong menunjukkan syarat belum selesai; cap diperoleh mempunyai nama zon dan tarikh. Transaksi pemberian cap memerlukan semua unit wajib selesai dan kunci unik perjalanan-zon. Animasi cap boleh dilangkau dan tidak menentukan status simpan. Zon berikut dibuka berdasarkan bukti cap sebelumnya.

### P-10 Refleksi

Zon 5 memerlukan satu pilihan emosi (sangat seronok, seronok, biasa sahaja, mencabar, perlukan bantuan), catatan pilihan maksimum 500 aksara dan Hantar Refleksi. Emosi tidak memberi/menolak markah. Satu rekod per perjalanan-zon; boleh kemas kini sebelum penamatan, perubahan selepas tamat disimpan sebagai kemas kini refleksi tanpa mengubah sijil. Gunakan teks bersama emoji, bukan warna sahaja.

### P-11 Sijil Digital

Pelayan semak lima cap, aktiviti wajib lulus dan refleksi Zon 5 dalam keluaran sama. Cipta nombor sijil unik, salinan nama penerima dan tarikh; jana fail PDF secara latar jika perlu. Status `pending`, `ready` atau `failed`; ulang penjanaan pada rekod sama apabila gagal. Muat turun hanya milik sendiri. Tiada pautan awam yang boleh mendedahkan nama kanak-kanak; butang kongsi dalam ilustrasi ditangguhkan sehingga keperluan privasi ditetapkan. Nama dan rupa logo kekal mengikut sumber.

## C. Lima Zon Pembelajaran

### Z-01 Balai Pelepasan

**Tujuan:** pengenalan, identiti juruterbang dan kesiapsiagaan. **Pra-syarat:** log masuk dan profil minimum. Papar balai lapangan terbang, karakter juruterbang, objektif dan avatar. Murid membaca nota pengenalan, mengenali perbezaan umum di-/di, menguji butang audio dan menekan Sudah Baca. Senarai semak profil + semua nota wajib menghasilkan cap Peneroka Nota; tiada kuiz wajib jika tidak ditetapkan dalam keluaran. Butang “Teruskan ke Taklimat” membuka Zon 2 selepas syarat dipenuhi. Jika kandungan belum terbit, kembali ke peta dan jangan mengeluarkan cap.

### Z-02 Taklimat Penerbangan

**Tujuan:** menguasai dua pola ejaan. **Pra-syarat:** cap Zon 1. Dua kad perbandingan, contoh, formula Di-2P, audio dan semakan kefahaman. Contoh: “Surat itu ditulis oleh Ali.” berbanding “Ali berada di sekolah.” Formula masa dalam sumber dibetulkan menurut Plan §1.1. Murid menanda nota wajib selesai dan meluluskan aktiviti wajib; mendapat cap Pakar Tatabahasa dan Zon 3 dibuka. Jika belum lulus, pautan kembali ke bahagian nota berkaitan.

### Z-03 Simulasi Penerbangan

**Tujuan:** aplikasi konsep melalui penerbangan maya. **Pra-syarat:** cap Zon 2. Pengalaman utama ialah kokpit, bukannya senarai borang kuiz di latar pesawat.

| Fasa | Visual/interaksi | Logik |
|---|---|---|
| Persediaan | Panel juruterbang, peta laluan, semakan audio dan taklimat | Muat manifest, sahkan aktiviti/soalan dan cipta/sambung percubaan |
| Berlepas | Landasan melalui tingkap, pesawat bergerak, panel “Mula Misi” | Animasi singkat boleh dilangkau, belum menambah markah |
| Navigasi | Kokpit kekal; HUD menunjukkan misi, markah, laluan dan status penerbangan | Satu soalan aktif pada panel kawalan; objektif, seret dan lepas atau isi tempat kosong |
| Maklum balas | Laluan bercahaya bagi betul; petunjuk pembetulan bagi salah | Hasil disahkan pelayan; checkpoint hanya selepas respons diterima |
| Pendaratan | Landasan ketibaan, ringkasan misi dan cap jika layak | Tamat percubaan, kira terbaik, semak syarat zon dan buka Zon 4 |

Contoh integrasi: pilih “ditulis” untuk melengkapkan mesej menara kawalan; seret “di” ke slot “Buku itu ___ atas meja”; isi “dibaca” dalam log penerbangan. Kelajuan dan altitud hiasan tidak menentukan kelulusan. “Jeda Misi” menghentikan animasi/audio/timer; “Keluar Misi” mengesahkan keluar dan menyimpan checkpoint yang sah. Memuat semula menyambung soalan belum dijawab tanpa mengulang markah. Telefon mengekalkan bingkai kokpit ringkas dengan soalan besar; mod kurang gerakan mengekalkan cerita dan status tanpa kamera bergerak. Selesai semua aktiviti wajib memberikan cap Juruterbang Bahasa.

### Z-04 Cabaran Kecemasan

**Tujuan:** membina dan menyusun ayat menggunakan di-/di dengan gerakan tangan. **Pra-syarat:** cap Zon 3. Kamera murid **mesti kelihatan secara langsung semasa mod kamera**; bukan hanya ikon status.

1. Taklimat kecemasan mesra murid: pulihkan komunikasi penerbangan dengan menyusun ayat tepat. Papar arahan teks/audio dan penjelasan privasi.
2. Tekan “Aktifkan Kamera”. Meminta video sahaja, bukan mikrofon. Izinkan, pilih kamera jika lebih daripada satu, kemudian tunggu model dan aliran video sedia.
3. Kalibrasi: letakkan satu tangan dalam bingkai, uji penunjuk dan genggaman/cubitan. Video dicerminkan dan koordinat overlay dipadankan. Sensitiviti lalai calon: tahan 600 ms sebelum pemilihan; perlu ujian murid sebelum dimuktamadkan.
4. Permainan: video langsung di panel utama, titik/garisan tangan, token perkataan, slot ayat, arahan, soalan, markah dan kemajuan. Penunjuk pada token + cubitan memilih; gerak ke slot + lepaskan meletakkan. Mod tahan penunjuk boleh dipilih jika cubitan sukar. Hanya satu token aktif.
5. Maklum balas gerakan (“Tangan dikesan”, “Dekatkan tangan”, “Perkataan dipilih”) berasingan daripada maklum balas jawapan (“Ayat tepat”, “Semak jarak di”). Pemilihan token tidak terus menghantar; murid tekan Hantar Jawapan.
6. Tangan hilang seketika → batal token terapung/jeda, kekalkan slot disahkan, papar panduan. Cahaya rendah/dua tangan/peranti lambat → minta satu tangan, tawarkan kalibrasi atau mod sentuh. Tiada hukuman markah.
7. Kamera ditolak, tiada, sedang digunakan, izin ditarik, model gagal atau konteks tidak selamat → mesej khusus, Cuba Semula, panduan izin dan “Gunakan Sentuhan/Papan Kekunci”. Jangan berulang kali meminta izin secara automatik.
8. Keluar/selesai/tab tidak aktif → jeda; keluar halaman/log keluar → hentikan trek video dan lepaskan model. Tiada rakaman atau muat naik video.

Contoh ayat: “Adik sedang membaca buku di rumah.” Token mengandungi pilihan gangguan; kunci urutan di pelayan. Mod alternatif menggunakan token dan slot sama dengan markah/cap sama. Simpan hanya `input_mode`, checkpoint permainan minimum dan keputusan. Semua aktiviti wajib lulus menghasilkan cap Kapten Terlatih. Tiada kebergantungan peranti kamera yang menghalang murid menamatkan perjalanan.

### Z-05 Balai Ketibaan & Imlak

**Tujuan:** pengukuhan ejaan melalui pendengaran dan refleksi. **Pra-syarat:** cap Zon 4. Dengar arahan, main/jeda/ulang audio ayat, taip jawapan dan hantar. Auto-betulkan ejaan dimatikan bagi medan imlak setakat dibenarkan pelayar. Penilaian mengekalkan beza rapat/jarak; maklum balas memaparkan ayat sasaran selepas penghantaran. Mod bantuan teks ditanda seperti P-07. Nota umum boleh dibaca semula, tetapi kunci imlak tidak didedahkan sebelum jawapan dihantar dalam mod biasa.

Lulus semua aktiviti imlak wajib → isi refleksi → tamatkan misi → cap Misi Lengkap → semakan lima cap → penjanaan sijil. Audio tidak boleh dimainkan: cuba muat semula, pilih bantuan teks atau kembali tanpa kehilangan jawapan diterima. Refleksi gagal disimpan: misi belum lengkap, boleh hantar semula tanpa mengulang imlak.

## D. Modul Admin

### A-01 Papan Pemuka Admin

Kad bilangan nota, kuiz, aktiviti, soalan, audio sedia/dalam proses dan lima zon; senarai perubahan terkini; pintasan Tambah Kandungan. Bilangan dikira daripada rekod sebenar mengikut status, bukan nombor dalam imej. Sidebar: Papan Pemuka, Kandungan, Kuiz & Aktiviti, Soalan & Jawapan, Arahan, Audio, Media, Pratonton dan Log Audit. Tiada laporan pencapaian murid individu dalam skop asas.

### A-02 Pengurusan Nota

Senarai carian/penapis zon/status, paginasi, lihat/tambah/kemas kini/padam. Borang: tajuk, zon, susunan, wajib/pilihan, teks kaya, ilustrasi, teks alternatif, arahan dan audio penerangan. Simpan sebagai draf, pratonton dan terbit melalui keluaran. Sanitasi HTML. Padam lembut jika digunakan; sebelum padam, papar pautan kebergantungan serta pengesahan. Nota tidak mempunyai markah lulus; markah lulus pada aktiviti, walaupun gambar borang menggabungkan kedua-duanya.

### A-03 Pengurusan Kuiz dan Aktiviti

Tab Kuiz/Aktiviti Interaktif; tetapkan zon, nama, jenis (`quiz`, `simulation`, `hand_mission`, `dictation`), arahan, susunan, wajib, peratus lulus dan status. Tetapan permainan: tema kokpit/laluan checkpoint, mod kamera/alternatif, sensitiviti, soalan dan maklum balas. Bilangan soalan serta markah maksimum dikira, bukan ditaip tanpa validasi. Aktiviti kosong/tiada soalan tidak boleh terbit. Zon 3 mesti renderer simulasi, Zon 4 mesti renderer kamera dengan alternatif; tidak boleh diganti borang kuiz umum melalui pilihan Admin.

### A-04 Pengurusan Soalan dan Pilihan Jawapan

Pembina soalan menyediakan jenis, teks, markah, susunan, audio, penerangan jawapan dan konfigurasi khusus. Objektif memerlukan minimum dua pilihan, tepat satu betul. CRUD dan susun pilihan menggunakan kekunci naik/turun selain seret. Seret dan lepas mempunyai kanvas token/slot dan pemetaan betul di panel Admin; token/slot mesti wujud dan dapat diselesaikan. Isi tempat kosong mempunyai kunci per slot; susun ayat mempunyai token serta urutan; imlak memerlukan audio sedia, kunci ejaan dan variasi sah. Paparan pratonton boleh diselesaikan sebelum terbit. Jenis baharu yang belum dilaksanakan dilabel “Belum tersedia” dan tidak boleh terbit.

### A-05 Pengurusan Arahan

CRUD tajuk dan teks Bahasa Melayu untuk nota, aktiviti atau skrin sistem melalui `screen_key` yang dibenarkan. Pautkan audio arahan, susun dan tentukan status. Arahan berkongsi boleh digunakan banyak nota/aktiviti; perubahan draf tidak menimpa arahan yang sudah dibekukan dalam keluaran. Teks arahan kekal walaupun audio ada. Padam ditolak jika berkebergantungan aktif tanpa nyahpaut/penggantian jelas.

### A-06 Pengurusan Audio dan Rakaman Mikrofon

Aliran lengkap: pilih sasaran soalan/arahan/nota → Rakam Audio atau Muat Naik Audio → izin → mula → meter/durasi/Sedang Merakam → Henti → pratonton → Simpan Rakaman → proses dan sahkan → pautkan sasaran → pratonton Pelajar → terbit.

- Rakam Semula membuang draf rakaman dengan pengesahan jika belum disimpan; jangan menimpa fail terbit secara senyap.
- Muat naik menyemak saiz, MIME dan durasi di pelayan. Cadangan awal MP3, WAV, Ogg atau WebM yang benar-benar boleh dinyahkod; sokongan rakaman mengikut keupayaan pelayar, bukan satu format wajib.
- Pemprosesan audio menghasilkan status `processing`, `ready` atau `failed`; pautan terbit hanya ke fail sedia. Simpan sumber asal dan derivatif jika penukaran format diperlukan.
- Main/jeda/ulang, durasi, masa semasa dan kawalan kelantangan; gelombang audio pilihan visual, bukan bukti rakaman disimpan.
- Ganti mencipta fail baharu dan mengganti pautan draf. Versi lama kekal bagi cubaan/keluaran lama. Padam tidak memutuskan media sejarah; sekat jika masih dirujuk.
- Izin ditolak/mikrofon tiada/peranti sibuk/rakaman gagal menunjukkan sebab dan pilihan muat naik. Hentikan trek selepas henti/keluar. Jangan meminta mikrofon Pelajar untuk imlak kerana jawapan ditaip.

### A-07 Pengurusan Media

Pustaka imej, audio, avatar dan ilustrasi; carian jenis/status, tajuk, teks alternatif, sumber/lesen dan senarai penggunaan. Pratonton imej/audio, muat naik, ganti dan nyahaktif. Storan fail persendirian; logo asal dipelihara tanpa pengubahan nisbah. Tolak fail boleh laksana dan MIME palsu. Fail tidak dirujuk boleh dibersihkan melalui tugas berjadual selepas tempoh pemulihan yang ditetapkan; audit setiap pemadaman sebenar.

### A-08 Penerbitan dan Nyahaktif

Validasi rujukan, bahasa, jawapan, susunan, syarat zon dan media; papar senarai ralat yang boleh diklik. Pratonton sebelum tindakan Terbit. Keluaran baharu mempunyai manifest lengkap, versi dan dasar skor; selepas terbit tidak boleh disunting. Murid baharu menggunakan keluaran semasa; perjalanan lama kekal pada keluaran sendiri. Nyahaktif kandungan sumber memerlukan keluaran baharu bagi murid baharu; tarik keluaran lama hanya untuk isu kritikal dengan kesan dan audit jelas. Jangan membatalkan cap atau sijil sah secara automatik.

### A-09 Pratonton Pelajar

Pratonton visual latar Zon 3 tersedia kepada Admin di `/admin/pratonton/zon-3`, menggunakan komponen kokpit boleh guna semula dan gambar kedua yang dibekalkan pengguna. Ia memaparkan latar serta taklimat visual sahaja sementara enjin permainan belum dibangunkan; tidak mencipta cubaan, skor atau cap.

Gunakan renderer Pelajar yang sama dengan jalur “Mod Pratonton Admin”. Pilihan PC/tablet/telefon, zon, jenis soalan dan status media. Boleh mencuba audio, kokpit dan kamera selepas izin sebenar. Data percubaan pratonton berasingan dalam memori/sesi; tiada `attempts`, cap, refleksi atau sijil sebenar dicipta. Draf hanya dihantar kepada Admin sah. Keluar kembali ke editor; perubahan belum disimpan ditandakan.

### A-10 Log Audit

Rekod tambah, kemas kini, padam lembut, tukar susunan, media, terbit, nyahaktif dan kegagalan tindakan kritikal yang berkaitan. Papar masa, Admin, tindakan, jenis entiti, ID dan ringkasan perubahan sebelum/selepas yang ditapis. Carian/penapis tarikh, pelaku, entiti dan tindakan; paparan baca sahaja. Jangan simpan kata laluan, token, bingkai video, kunci jawapan lengkap atau kandungan sensitif dalam log. Audit penulisan bersama transaksi perubahan penting; kegagalan audit menggagalkan perubahan itu.

## 2. Matriks pengujian penerimaan

| Kes | Hasil wajib |
|---|---|
| Pelajar menghantar ID pengguna lain atau membuka URL Admin | Ditolak pelayan; tiada rekod berubah |
| Ulang tutorial, audio atau refresh | Jawapan dan mata disimpan tidak digandakan/reset |
| Cuba zon terkunci secara URL | Ditolak dengan sebab dan pautan zon prasyarat |
| “disekolah” vs “di sekolah” dalam imlak | Dinilai berlainan; huruf besar/tanda baca mengikut dasar sahaja |
| Seret guna sentuh, tetikus dan kekunci | Pemetaan jawapan serta markah setara |
| Soalan Zon 3 dijawab betul/salah | Status kokpit berubah mengikut keputusan pelayan dan cerita misi kekal |
| Kamera ditolak atau tangan hilang | Tiada kehilangan jawapan; alternatif atau jeda tersedia |
| Keluar Zon 4 | Kamera berhenti; tiada bingkai dihantar dalam trafik rangkaian |
| Terbit dengan objektif dua jawapan betul/audio imlak rosak | Terbit dihalang, ralat khusus ditunjukkan |
| Admin mengubah soalan selepas murid memulakan cubaan | Cubaan lama dinilai terhadap salinan lamanya |
| Dua permintaan cap/sijil serentak | Satu cap/sijil melalui kunci unik dan transaksi |
| Pratonton Admin diselesaikan | Tiada rekod pembelajaran sebenar tercipta |
| Putus rangkaian selepas hantar kemudian cuba semula | Respons asal dipulangkan; tiada markah berganda |

## 3. Kebolehkesanan

U-01..04 → FR-01/02/14; P-01..04 → FR-02/03; P-05..08 → FR-04/05/08/09; P-09..11 → FR-10; Z-03 → FR-06; Z-04 → FR-07; A-01..05 → FR-11; A-06..07 → FR-12; A-08..09 → FR-13; A-10 → FR-14. Reka bentuk skrin menggunakan ID S01..S16 dan A01..A12 dalam Prototype.md; nama jadual dan kontrak respons dirujuk dalam Database.md.

## Penyelarasan navigasi UI — 20 Oktober 2026

Awam menggunakan navbar global di atas semua kandungan. Pelajar menggunakan sidebar: Papan Pemuka, Peta Pengembaraan, Zon Pembelajaran, Pasport Digital, Kemajuan, Ganjaran, Sijil, Bantuan, Tetapan, Profil, Log Keluar. Admin menggunakan sidebar: Dashboard, Kandungan, Nota, Kuiz & Aktiviti, Soalan & Jawapan, Arahan, Audio, Media, Pratonton Pelajar, Pengguna, Laporan, Audit Log, Tetapan, Profil, Log Keluar. Menu aktif menggunakan kuning serta `aria-current=page`. Semua laluan baharu dilindungi middleware peranan yang sama.

Pelaksanaan semasa: halaman modul belum siap menggunakan keadaan kosong berlabel. Tambah Kuiz ialah pratonton borang empat langkah tanpa penyimpanan. Nota draf sedia ada kekal berfungsi. Profil Admin menyokong nama/emel dan penukaran kata laluan selepas pengesahan kata laluan semasa; rekod audit tidak mengandungi kata laluan. Tetapan akaun menggunakan borang profil sedia ada, tanpa toggle pemberitahuan rekaan.

### U-01a Halaman awam khusus
Tentang (`/tentang`) menerangkan latar, tujuan, masalah pembelajaran dan konsep gamifikasi. Pengembaraan (`/pengembaraan`) menerangkan kelima-lima zon mengikut urutan. Cara Bermain (`/cara-bermain`) menerangkan pendaftaran, aktiviti, audio, markah, pasport, cap dan syarat membuka zon. Navbar mempunyai enam destinasi unik tanpa anchor halaman; `/bantuan` terus menyokong menu Bantuan sesi sah.

### Navigasi Pelajar semasa
Sidebar Pelajar diganti dengan top navbar untuk semua skrin sesi Pelajar. `/kuiz-aktiviti` menyediakan keadaan persediaan modul sehingga permainan/penerbitan tersedia. Pencapaian menuju Ganjaran; Kemajuan dan Sijil tersedia melalui dropdown akaun. Profil/navbar menggunakan avatar sebenar dan username sebagai ID pelajar sedia ada. Ikon notifikasi tidak mendakwa menyokong rekod notifikasi peribadi atau bilangan belum dibaca pada fasa ini.

### Paparan ringkasan Pelajar
Jumlah sijil diperoleh dikira daripada `certificates.status=ready` bagi perjalanan semasa. Ringkasan zon/cap daripada cap perjalanan (0–5), markah daripada kemajuan terbaik dan sijil (0–1). Notifikasi Terkini mempunyai laluan `/notifikasi` dengan keadaan kosong sehingga modul pemberitahuan dilaksanakan. Menu navbar Pelajar semasa ialah Utama, Papan Pemuka, Kuiz & Aktiviti, Pencapaian, Pasport Digital.

Pindaan navigasi terkini: navbar Pelajar mempunyai empat menu sahaja — Papan Pemuka, Kuiz & Aktiviti, Pencapaian, Pasport Digital. Menu Utama dikeluarkan. Dashboard ialah destinasi selepas log masuk Pelajar; tutorial kekal tersedia melalui dropdown dan aliran pendaftaran.

### Katalog Kuiz & Aktiviti mengikut zon
Lima pilihan zon pada `/kuiz-aktiviti?zon=N` menukar konteks zon dan kad cadangan. Kemajuan dibaca daripada perjalanan Pelajar sendiri. Empat kad setiap zon kini ialah pratonton UI yang dilabel jelas dengan butang mula belum aktif; bilangan soalan dan masa hanya cadangan. Tiada rekod draf Admin atau kunci jawapan didedahkan. Navbar Pelajar mempunyai lima menu: Papan Pemuka, Peta Zon, Kuiz & Aktiviti, Pencapaian, Pasport Digital.

### Modul Pasport & Pencapaian gabungan
Gabungkan Pasport, Kemajuan, Ganjaran dan Sijil pada `/pasport-pencapaian`. Laluan lama mengalih ke anchor berkenaan. Navbar: Papan Pemuka, Peta Zon, Kuiz & Aktiviti, Pasport & Pencapaian. Ringkasan, pasport, cap, lencana, sejarah dan sijil menggunakan data perjalanan aktif/terkini milik Pelajar.
Dasar paparan v1: tahap = 1 + zon selesai; pangkat mengikut ambang 0/1/3/5 zon (Juruterbang Baharu/Pelatih Bahasa/Penjelajah Bahasa/Kapten Bahasa). Enam lencana bacaan sahaja: 1 nota; 5 kuiz berbeza lulus; 5 nota; 3 cap; 5 cap; sijil ready. Tiada mata atau rekod ganjaran ditambah ketika membuka halaman. Sejarah merekod markah percubaan tanpa mendakwa ia pertambahan XP. PDF sijil hanya tersedia untuk pemilik apabila sijil dan media berstatus ready, MIME PDF dan disk private/local; tiada URL fail mentah dipaparkan.

### Aliran zon semasa
Halaman dan route Peta Zon telah dibuang, bukan dialih ke peta gantian. Kad Zon N pada dashboard membuka Kuiz & Aktiviti dengan query zon=N. Status terkunci/dalam persediaan tidak menghalang membaca pengenalan zon, tetapi misi yang belum tersedia kekal tidak boleh dimulakan. Cap dan penyelesaian dipaparkan dalam Pasport & Pencapaian. Navbar tiga menu sahaja; kawalan notifikasi dan akaun tidak berubah.

### Kontrak pendaftaran semasa
GET/POST `/daftar` membaca/menyimpan draf Maklumat Akaun selepas validasi ID/nama/password-confirmation. Draf sesi mengandungi hash password serta masa tamat 20 minit, bukan password/confirmation mentah. GET `/daftar/avatar` memerlukan draf sah. POST `/daftar/avatar` menyemak semula ID dan avatar aktif, mencipta user role pelajar, profil warna pesawat lalai kuning dan hash kod pemulihan secara transaksi. Penghantaran berulang dengan receipt sama menuju skrin berjaya tanpa akaun tambahan.
GET `/daftar/berjaya` memerlukan receipt akaun ciptaan sesi itu dan memaparkan navbar awam, avatar, nama, ID dan tahap permulaan. Kod pemulihan diflash untuk satu paparan. POST `/daftar/mula` memerlukan receipt aktif 20 minit, mengesahkan akaun, membersihkan draf/receipt dan menukar sesi sebelum membuka dashboard. GET ke skrin akhir tanpa receipt tidak dianggap pendaftaran berjaya. POST dilindungi CSRF, throttle dan session lock. ID yang diambil sesi lain antara langkah mengembalikan borang Akaun dengan ralat; tiada akaun separa dicipta. Emel dan warna pesawat tidak diperlukan dalam wizard ini; warna boleh disunting pada profil.

## Pelaksanaan end-to-end — 21 Oktober 2026
Pelan terkini menjadi rujukan: navbar Pelajar tiga menu, tiada Peta Zon; pendaftaran akaun tanpa avatar diikuti log masuk/tutorial popup enam langkah. Zon 3 hanya true_false dan drag_drop daripada Soalan Kuiz.pdf (12 ejaan dan 10 kad). Zon 4 sentence_order dan fill_blank daripada Soalan Kuiz.pdf (5 Susun Ayat + 5 Isi Tempat Kosong dengan salinan ayat); Zon 5 dictation serta refleksi. Semua jenis mempunyai editor Admin, validasi, renderer dan penilai pelayan. Admin CRUD nota/aktiviti/soalan/arahan/media, rakam/muat naik audio dan terbit keluaran snapshot immutable. Perjalanan menggunakan manifest keluaran sendiri; jawapan betul tidak dimasukkan dalam payload HTML Pelajar. Penghantaran jawapan idempoten; skor terbaik, kemajuan, cap dan sijil dikira pelayan. Pratonton Admin menggunakan renderer sama tanpa rekod Pelajar. Notifikasi milik penerima dan status baca disimpan; media private melalui route yang mengesahkan peranan/pemilikan serta kaitan manifest. Sijil PDF dijana selepas lima cap dan refleksi. Webcam pilihan memproses landmark setempat; input klik/sentuh/papan kekunci sentiasa tersedia.
Tambahan data: student_notifications (user_id, dedupe_key unik, title, body, read_at, timestamps). Manifest menyimpan salinan nota, aktiviti, soalan, kunci, media dan arahan, hanya dibaca pelayan. Tiada perubahan destructive pada rekod sedia ada.

### Menu Pelajar dipermudah — keputusan terkini 21 Oktober 2026
Top navbar kekal Papan Pemuka | Kuiz & Aktiviti | Pasport & Pencapaian. Dropdown hanya Profil dan Log Keluar. Tetapan, Kemajuan Saya dan Sijil Digital dibuang daripada menu/dropdown Pelajar. Kemajuan kekal pada dashboard; sijil kekal dalam Pasport & Pencapaian. Menu Admin tidak berubah. Keputusan ini mengatasi senarai dropdown terdahulu.


### Navigasi akaun Admin — 21 Oktober 2026

Sidebar Admin hanya mengandungi Dashboard, Kandungan, Nota, Kuiz & Aktiviti, Soalan & Jawapan, Arahan, Audio, Media, Pratonton Pelajar, Pengguna, Laporan dan Audit Log. Profil, Tetapan dan Log Keluar berada dalam dropdown profil di kanan atas, dibuka melalui nama, avatar atau ikon dropdown. Log Keluar membuka modal “Adakah anda pasti mahu log keluar?” dengan butang Batal dan Log Keluar. Batal/Escape mengekalkan sesi dan mengembalikan fokus ke pencetus; pengesahan menghantar POST dengan CSRF dan menamatkan sesi. Dropdown dan modal menyokong desktop, tablet dan telefon. Ketetapan ini mengatasi senarai navigasi Admin terdahulu.


### Penyelarasan dropdown Pelajar — 21 Oktober 2026

Dropdown Pelajar hanya memaparkan Profil dan Log Keluar. Gaya kad, ikon, jarak, hover navy/kuning dan sudut bulat disamakan dengan Admin. Kedua-dua peranan berkongsi komponen modal pengesahan; Pelajar menggunakan mesej “Adakah anda pasti mahu log keluar?” dan “Anda boleh log masuk semula untuk meneruskan pembelajaran Di-BiTaRa.” Butang Batal atau Escape mengekalkan sesi dan memulangkan fokus; Log Keluar menamatkan sesi melalui POST CSRF. Tiada Tetapan, Kemajuan Saya, Sijil Digital, Bantuan atau Tutorial dalam dropdown Pelajar. Ini mengatasi senarai menu akaun Pelajar terdahulu; laluan bantuan/tutorial kekal tersedia.


### Bahasa Melayu dan paparan mesra murid — 21 Oktober 2026

Semua label sistem menggunakan Bahasa Melayu: Papan Pemuka, Pentadbir, Tetapan, Profil, Log Keluar, Hantar, Seterusnya, Sebelumnya, Markah, Kemajuan, Pencapaian, Kuiz dan Log Audit. Laluan, nama kelas serta nilai dalaman pangkalan data tidak perlu diterjemah; nilai tersebut dipadankan kepada label Melayu sebelum dipaparkan. Nama pengguna dan kandungan yang ditaip pengguna dikekalkan.

Arahan murid dipendekkan dengan ikon mengikut jenis soalan, bar kemajuan, butang sekurang-kurangnya 48 px, pilihan perasaan bergambar dan maklum balas warna/simbol. Panduan tambahan serta kawalan tangan pilihan boleh dibuka apabila diperlukan. Kawalan audio menggunakan Dengar, Jeda dan Ulang; mesej medan wajib dan dialog pengesahan turut menggunakan Bahasa Melayu. Soalan, ejaan dan kunci jawapan sumber Zon 3/4 serta audio Imlak dikekalkan. Pentadbir hendaklah menulis kandungan baharu dalam Bahasa Melayu dan menggunakan arahan pendek. Ketetapan ini mengatasi istilah paparan terdahulu.

Pengesahan: 40 ujian / 864 assertions; semakan pelayar untuk susun ayat, maklum balas markah, audio Imlak, medan wajib, dialog pengesahan, Log Audit dan telefon 390 px. Semakan menggunakan data QA berasingan.


### Logo rasmi dan footer awam — 21 Oktober 2026

Logo rasmi ialah salinan asal lampiran pengguna di `public/images/official-logo.png`; bentuk, tulisan dalam karya dan ketelusan asal dikekalkan. Nama teks UI kekal Di-BiTaRa dan Bijak Imbuhan & Tatabahasa Ruang Angkasa. Komponen `brand-logo` digunakan pada navigasi awam/Pelajar, menu sisi Pentadbir, halaman utama, log masuk, pendaftaran, dropdown, pasport dan pratonton sijil. Avatar peribadi kekal berasingan daripada logo rasmi. Palet serta peraturan responsif dikumpulkan dalam `resources/css/branding.css`.

Footer hanya dirender untuk tetamu melalui `@guest` pada layout bersama. Tiada footer pada mana-mana halaman selepas log masuk, termasuk jika pengguna membuka halaman umum ketika masih bersesi. Footer mengandungi logo besar, pautan pantas, sokongan, awan, bintang, kapal terbang dan hak cipta 2026. Pautan Privasi dan Syarat Penggunaan menuju halaman sebenar; label Log Masuk Pentadbir mengekalkan Bahasa Melayu.

Sijil PDF membenamkan logo, menggunakan salinan JPEG khusus PDF di `resources/branding/certificate-logo.jpg` tanpa memerlukan GD semasa berjalan. Arahan `php artisan dibitara:refresh-certificate-branding` boleh dijalankan semula dengan selamat: hanya sijil lama yang belum mempunyai logo dijana ke fail baharu; fail asal, nama, nombor dan tarikh anugerah dikekalkan.

Pengesahan: 44 ujian / 1,050 assertions, build Vite, semakan pelayar desktop/telefon serta render sijil PDF. Ketetapan ini mengatasi footer atau penjenamaan terdahulu.


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
