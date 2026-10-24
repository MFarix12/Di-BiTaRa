# Reka bentuk halaman umum — 23 Oktober 2026

Utama, Tentang, Cara Bermain dan Capai Pembelajaran menggunakan satu sistem visual dalam resources/css/public-theme.css, dimuatkan selepas gaya lama dan diskop kepada public-redesign. Halaman akses kod, log masuk Pentadbir serta halaman sesi tidak menerima gaya baharu ini.

Latar off-white, kad putih, teks navy, accent kuning, butang utama/sekunder, hero ilustrasi sedia ada dan spacing konsisten. Komponen public-hero, public-zones dan public-cta dikongsi. Imej home-background-hd.png hanya dalam panel ilustrasi hero; tiada salinan latar di belakang kandungan.

Utama: pengenalan, lima zon, contoh di-/di, kelebihan dan tindakan ke halaman kod.
Tentang: pengenalan, murid Tahun 4/5/6, objektif dan gamifikasi.
Cara Bermain: kod → nama/kelas → avatar → nota → misi → refleksi, FAQ interaktif dan tiada kamera.
Capai Pembelajaran: kemahiran, lima zon, cap, lencana, sijil dan Tahap mengikut tahun.

Navbar mengekalkan empat menu dan dua tindakan, active state serta hamburger sedia ada. Footer mengekalkan logo, tagline, enam pautan pantas, tiga pautan sokongan dan hak cipta dalam susunan padat.

Pengesahan: build Vite lulus; 25 ujian / 668 assertions lulus. Semakan pelayar keempat-empat halaman desktop dan telefon 390px tiada overflow mendatar; tablet 1024px menggunakan hamburger dan tiada overflow. Hamburger dibuka/ditutup dengan berjaya. Pasangan warna teks utama, isi, label, butang dan footer diperiksa sekurang-kurangnya 4.5:1. Screenshot dalam docs/screenshots/public-redesign-*.png.

## Edisi mesra kanak-kanak
Keempat-empat halaman kini menggunakan teks lebih pendek, tipografi bulat, butang permainan dengan pressed state, kad berwarna terkawal, gambar sebenar lima zon, nombor misi dan badge. Tentang mempunyai bingkai buku pengembaraan; Cara Bermain menggunakan enam langkah sebenar (termasuk nama/kelas); Capai Pembelajaran menggunakan gaya rak trofi.

Interaksi sebenar: tekan karakter untuk sapaan semangat (teks sahaja), tekan di-/di untuk menukar contoh ejaan, buka Teroka Misi untuk penerangan zon. Semua tindakan ini boleh diakses dengan papan kekunci dan sentuhan, tidak menyimpan markah atau kemajuan palsu. Hover terangkat, reaksi karakter, bintang berkelip perlahan, awan dan kapal terbang bergerak secara lembut. Gerakan awan/pesawat dihentikan pada telefon; prefers-reduced-motion menghentikan animasi dan transition.

QA: build lulus; ujian berkaitan akses sesi, Bahasa Melayu, branding dan navigasi lulus (22 ujian; navigasi diuji semula selepas pelarasan teks). Interaksi sapaan, pilihan konsep dan details zon disahkan melalui pelayar. Empat halaman pada 390px tiada limpahan mendatar; CTA 56px; animasi pesawat none. Warna label zon dikira sekurang-kurangnya 4.5:1 dengan latar putih. Screenshot public-playful-*.png.

## Latar bertema bagi setiap halaman
Latar vektor tersuai dalam public/images/public-backgrounds/: home (lapangan terbang, roket), about (buku, planet), play (jejak penerokaan, permainan), outcomes (medal, trofi). Setiap latar mempunyai versi desktop dan telefon; jumlah lapan SVG sekitar 21KB. Tiada permintaan rangkaian luar, skrip tambahan atau pengulangan imej. Atribut data-public-scene menentukan satu latar body; footer asal kekal.

Permukaan hero dan tajuk menggunakan putih cerah untuk keterbacaan. Hiasan disusun di tepi dan ruang antara kandungan. Build lulus, 10 ujian branding/navigasi lulus; empat halaman desktop dan telefon disemak tanpa limpahan mendatar. Bukti dalam docs/screenshots/public-background-*.png.
