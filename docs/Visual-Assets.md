# Aset reka bentuk berdasarkan rujukan

Tarikh: 20 Oktober 2026. Kaedah: alat ImageGen terbina dalam, menggunakan UI Prototype Student.png dan logo asal sebagai rujukan gaya/identiti. Logo asal `public/images/logo.png` dikekalkan. Aset baharu disalin ke dalam projek; aplikasi tidak bergantung pada folder imej terjana di luar projek.

## Fail akhir

Pengemaskinian gambar daripada pengguna: [home-background.png](../public/images/home-background.png) ialah gambar pertama untuk halaman utama/papan pemuka Pelajar; [zone3-background.png](../public/images/zone3-background.png) ialah gambar kedua untuk kokpit Zon 3. Kedua-duanya disalin terus daripada lampiran tanpa pengubahan atau penjanaan semula. Latar utama baharu menggantikan latar terjana pada halaman utama; latar terdahulu dikekalkan untuk skrin lain.

- [Latar angkasa dan lapangan terbang](../public/images/sky-scene.png): latar ilustrasi tanpa teks untuk bingkai aplikasi dan halaman pembelajaran.
- [Maskot juruterbang](../public/images/pilot-mascot.png): PNG telus untuk halaman utama, log masuk dan tutorial.
- [Empat potret avatar](../public/images/pilot-avatars.png): satu helaian telus; komponen `pilot-avatar.blade.php` menggunakan kedudukan CSS untuk setiap potret.
- [Semakan desktop](screenshots/home-reference-desktop.jpg) dan [semakan telefon](screenshots/home-reference-mobile.jpg).

Teks, borang, navigasi, tajuk, kad, bingkai serta butang ialah komponen HTML/Blade/CSS sebenar. Poster rujukan tidak digunakan sebagai pengganti halaman aplikasi. Awan dan pesawat tambahan ialah grafik SVG kod asli. Data skor dan kemajuan kekal daripada pelayan; angka contoh pada poster tidak disalin sebagai pencapaian murid.

## Prompt akhir yang digunakan

### Latar

> Use the attached Di-BITaRA student UI reference as visual style reference only. Create a clean wide 3:2 cartoon background illustration for the actual learning website, NO TEXT, NO UI panels, no lettering, no logo. Match closely the glossy children's educational game style: deep rich navy outer-space sky with many glowing yellow four-point stars, small white stars, layered puffy white and cyan clouds along bottom and edges, red-and-white rocket flying at upper far left, small yellow passenger airplane upper right with dotted curved flight path, distant playful airport control tower at lower right, small airport runway foreground bottom. Keep the central 65 percent largely clear navy for live website headings and panels. Rounded outlined vibrant digital illustration, bright blue cloud highlights, same visual world as reference, polished high detail. No character, no watermarks.

### Juruterbang

> Use the attached Di-BITaRA logo character as the identity reference and student prototype as style reference. Extract and redraw ONLY the same adorable young Malay cartoon pilot mascot, full body, wearing navy gold-trimmed pilot cap with wing emblem, white shirt navy tie navy jacket with gold shoulder stripes, seated/standing friendly three-quarter pose giving thumbs up with one hand and holding a small navy tablet in other hand. Big warm eyes, cheerful smile, same facial proportions and polished glossy outlined cartoon illustration as reference. Isolated mascot on genuine transparent background. No background, no clouds, no logo, no book, no letters, no words, no watermark. Leave transparent padding around whole figure. Asset for the lower left of a Di-BITaRA learning game screen.

### Avatar

> Create a single wide transparent-background sprite strip containing FOUR evenly spaced square avatar portraits side by side, four identical-size cells, no overlaps, each face fully contained in its quarter with generous padding. Match the adorable glossy outlined Malay pilot cartoon identity and colors in the supplied Di-BITaRA logo/reference. Avatar 1 young boy navy-and-gold pilot cap navy uniform. Avatar 2 young boy with glasses navy gold pilot cap navy uniform. Avatar 3 young girl pink pilot cap white and pink uniform. Avatar 4 young girl navy gold pilot cap white uniform. Friendly smiling big eyes, round soft cheeks, gold wing badges, head and shoulders only, consistent children's educational game art like the reference. Genuine transparent background, no frames, no words, no labels, no logo, no watermark. 4:1 wide strip composition, exactly four equally spaced centers at x 12.5%,37.5%,62.5%,87.5%, all same scale.

## Pengesahan dan skop

Binaan Vite berjaya dan suite sedia ada lulus (11 ujian, 86 assertions). Paparan halaman utama dan Admin diperiksa pada desktop; halaman utama juga diperiksa pada 390×844 tanpa limpahan mendatar. Varian potret avatar diperiksa daripada helaian terjana dan dirender oleh komponen yang sama pada daftar, profil dan pasport.

Ini pengemaskinian visual skrin sedia ada: halaman utama, pengesahan, tutorial, profil/avatar, papan pemuka/peta Pelajar, pasport/kemajuan serta dashboard, nota dan audit Admin. Modul permainan, audio, penerbitan serta sijil yang belum dibangunkan tidak dianggap siap kerana tema baharu telah diterapkan.

## Bukti fungsi end-to-end — 21 Oktober 2026
`docs/screenshots/e2e-zone3-cockpit.png`: jawapan Betul/Salah disimpan dan HUD meningkat.
`e2e-zone4-sentence.png` / `e2e-zone4-mobile.png`: blok Susun Ayat sumber dan susun atur telefon.
`e2e-admin-studio.png`: simpan soalan melalui borang Admin.
`e2e-student-dropdown.png`: dropdown tiga menu tambahan dibuang; dashboard 100% ialah akaun QA berasingan, bukan akaun sebenar.
`e2e-certificate.png`: render sijil PDF QA; penjanaan tidak menggunakan imej sijil statik.
Audio awal `resources/audio/imlak-1.mp3` hingga `imlak-5.mp3`: suara sintetik Bahasa Melayu ms-MY-YasminNeural; salinan private digunakan untuk main balik. Regenerasi pilihan melalui scripts/prepare_learning_audio.py; tiada TTS semasa runtime Pelajar.
Model MediaPipe dan WASM disalin daripada pakej rasmi @mediapipe/tasks-vision serta model Google Hand Landmarker float16/1. Tiada video atau landmark dimuat naik. Semakan peranti fizikal belum dilakukan.
