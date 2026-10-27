# Tahun 4 sahaja dan countdown Imlak

Dilaksanakan 27 Oktober 2026.

- Zon 5 menggunakan aliran arahan → countdown 3, 2, 1, Mula → soalan pertama. Kesan bunyi dan animasi dikongsi dengan countdown zon lain. Server menolak permulaan sebelum tiga saat dan sesi yang sudah bermula tidak mengulang countdown.
- Borang cipta kod, aktiviti, penerbitan dan filter sesi tidak lagi mempunyai pemilihan tahun. Kod baharu sentiasa DBR-T4-XXXXXX.
- Set sumber, pengimport dan audio Tahun 5/6 telah dibuang daripada aplikasi aktif. Migrasi membersihkan kod, sesi, perjalanan, soalan, jawapan, keluaran dan media berkaitan mengikut foreign key.
- Metadata school_year kekal untuk rekod, laporan dan sijil; database SQLite menyekat INSERT/UPDATE kepada nilai 4. learning_release_id dikekalkan untuk sejarah versi soalan Tahun 4, bukan pemilihan tahun.
- Tahap pasport, profil, sijil, export dan paparan sesi menggunakan Tahun 4.
- Sandaran sebelum pembersihan: .cache/year-four-backup-20261027. Fail ini tidak digunakan oleh aplikasi aktif. Jangan pulihkan hanya satu jadual; pulihkan snapshot penuh jika pemulihan diperlukan.

Pengesahan: 69 ujian, 1706 assertions lulus; ujian JavaScript audio lulus; Vite build berjaya. Database utama hanya mengandungi nilai tahun 4 dalam access_codes, learner_sessions, activities dan learning_releases, tiada foreign key rosak dan tiada media tahun-5/tahun-6. Browser QA mengesahkan countdown membuka Imlak Soalan 1/3 secara automatik.