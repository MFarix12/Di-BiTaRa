# Audio dan perayaan perjalanan

- Muzik latar asli 32 saat (scripts/prepare_journey_music.py) menggunakan loop ringan pada Zon 1–5. Tiada rakaman pihak ketiga.
- Butang bunyi bersebelahan jam; sebelum jam bermula pada Zon 1/2, butang tetap tersedia. Kawalan hanya mengubah muzik latar.
- Pilihan mute dan kedudukan lagu disimpan di sessionStorage mengikut public_id sesi, bertahan merentas halaman dan refresh dalam tab yang sama.
- Muzik 22% kelantangan biasa, 2.5% ketika audio utama dimainkan. Arahan, audio nota dan imlak DOM mencetuskan ducking automatik. Countdown 28% kelantangan sumber dan tidak disenyapkan oleh butang muzik.
- Pelayar mungkin memerlukan interaksi pengguna sebelum audio dibenarkan. Klik/tap/papan kekunci biasa atau butang bunyi mengaktifkan muzik. Audio tidak menghalang timer atau navigation apabila autoplay ditolak. Halaman baharu menyambung kedudukan lagu; pertukaran halaman penuh boleh menghasilkan jeda ringkas.
- Tab tersembunyi menjeda muzik. Tiada muzik pada halaman admin, awam atau Ulang Kaji.
- Piala menggunakan bounce/shine/sparkle sekali sahaja, dengan prefers-reduced-motion. Butang akhir diberi margin atas 24px.
- Overlay kapal terbang kecil diagonal serta pencegahan klik pautan lama dibuang; transition cop dan kapal terbang utama antara zon dikekalkan.

Pengesahan: node tests/journey-audio.mjs lulus; 14 ujian PHP berkaitan lulus (578 assertions); build Vite berjaya. QA pelayar mengesahkan toggle dan mute selepas refresh. Data utama tidak diubah.
