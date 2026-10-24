# Sijil Pelajar dalam Keputusan Sesi

Pentadbir boleh melihat pratonton dan memuat turun PDF melalui tindakan pada setiap sesi yang layak. Fail PDF adalah fail rasmi yang sama digunakan oleh Pelajar, bukan salinan yang dijana semula ketika eksport.

Kelayakan diperiksa pada server untuk setiap permintaan: sesi dan perjalanan selesai, Zon 1–5 lengkap, lima cop, refleksi Zon 5, sijil berstatus ready dan fail PDF tersedia di storan peribadi. Sesi yang belum layak tidak mempunyai checkbox atau pautan sijil.

Pratonton ringan membaca bingkai JPEG dan baris teks terus daripada PDF rasmi format semasa. Pautan PDF asal dan muat turun turut disediakan. Format fail lama yang tidak disokong memaparkan pautan muat turun sebagai fallback.

- Muat Turun Semua Sijil Pelajar Selesai (ZIP): semua sijil tersedia yang sepadan dengan tapisan, merentas pagination.
- Muat Turun Sijil Dipilih (ZIP): checkbox pada halaman semasa. Semua ID mesti sepadan dengan tapisan dan masih layak pada masa permintaan.
- ZIP distrim satu PDF pada satu masa, nama fail menggunakan ID sijil unik. Tidak memerlukan sambungan ZipArchive.
- Penjanaan automatik selepas refleksi menggunakan aliran LearningEngine sedia ada. Tarikh dalam sijil baharu berdasarkan completed_at perjalanan, termasuk jika penjanaan dicuba semula kemudian.

Pengesahan: suite penuh 69 ujian lulus (1,960 assertions); empat ujian sijil dijalankan semula selepas penambahbaikan pratonton (42 assertions). Build berjaya. QA pelayar mengesahkan checkbox pilih semua, pautan pratonton dan muat turun ZIP dua PDF. ZIP sebenar diperiksa dengan Python zipfile/pypdf: CRC sah dan kedua-dua fail mempunyai satu halaman PDF.
