# Profil dan Tetapan Pentadbir

Profil `/admin/profil` menggunakan AdminProfile: avatar rasmi, nama, e-mel, ID Pentadbir, peranan dan tarikh akaun dicipta. Nama/e-mel boleh dikemas kini; peranan, ID dan avatar rasmi dikekalkan.

Tetapan `/admin/tetapan` menggunakan AdminSettings: mod cerah/gelap, teks biasa/besar, animasi hidup/mati dan borang tukar kata laluan. Pilihan disimpan dalam users.admin_preferences, berasingan mengikut akaun. Halaman dimuat semula selepas simpan supaya pilihan digunakan pada semua halaman dalaman Pentadbir. Kata laluan semasa dan pengesahan kata laluan baharu masih wajib; audit tidak menyimpan kata laluan.

Dropdown mengekalkan Profil, Tetapan dan Log Keluar dengan route berlainan. Ujian AdminSettingsTest dan NavigationAndAdminLoginTest lulus (8 ujian). Semakan QA pelayar mengesahkan halaman berasingan, simpanan mod gelap dan kontras label. Akaun QA dipulihkan kepada mod cerah selepas semakan. Migration 000011 telah dipasang selepas sandaran database.
