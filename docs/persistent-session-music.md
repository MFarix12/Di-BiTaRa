# Muzik sesi berterusan

Muzik bermula hanya selepas kod disahkan dan Nama serta Kelas berjaya disimpan. Middleware PersistentLearnerShell membungkus navigasi dokumen Pelajar dalam satu bekas sesi berterusan; kandungan menggunakan iframe same-origin dengan tajuk aksesibel. Semua controller, CSRF, syarat zon dan semakan identiti asal masih digunakan untuk permintaan dalaman. PDF dan audio bukan halaman dibungkus.

Pemain SessionMusic tunggal berada di bekas induk. Kandungan menghantar arahan melalui postMessage, disemak origin dan sumber frame serta public_id sesi. Halaman dalaman tidak mencipta pemain audio. URL dan tajuk browser diselaraskan apabila kandungan bertukar.

- Lagu tidak di-seek atau di-restart untuk navigasi zon, kuiz, nota, Pasport, Profil, Ulang Kaji atau popup.
- Status selesai lima zon tidak menghentikan muzik selagi sesi belum ditamatkan.
- Mute dan kedudukan lagu disimpan mengikut ID sesi dalam sessionStorage. Refresh penuh menyambung kedudukan; navigasi biasa tidak memuat semula audio.
- Audio utama dan countdown memperlahankan muzik kepada 2.5%, berbanding 22% biasa. Kawalan muzik tidak mematikan audio imlak atau countdown.
- Tab tersembunyi tidak menghentikan muzik secara sengaja; polisi pelayar/OS mungkin mengehadkan audio latar.
- Tamat Sesi yang disahkan menghentikan muzik. Kembali ke halaman awam mengeluarkan bekas sesi dan membersihkan state audio sesi.
- Pelayar boleh menyekat autoplay selepas pembukaan tab baharu atau refresh. Butang bunyi/gerak isyarat biasa mengaktifkan audio tanpa menjejaskan timer.

Pengesahan: tests/journey-audio.mjs menguji pemain tunggal, tiada reset kedudukan semasa page-ready, mute, ducking, countdown, resume dan stop. Ujian PHP PersistentSessionMusicTest menguji sebelum/selepas maklumat pelajar, shell/dalaman, status selesai dan tamat sesi. QA pelayar mengesahkan lagu bergerak ke 11.19 saat selepas navigasi, satu pemain, mute merentas halaman, bermula automatik selepas Seterusnya, serta tiada pemain pada Utama selepas Tamat Sesi. Semakan menggunakan DB QA berasingan.
