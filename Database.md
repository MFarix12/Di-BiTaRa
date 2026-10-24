# Pangkalan Data Di-BITaRa — sesi kod

Skema semasa mempunyai **36 jadual domain**. Sumber struktur: migrasi Laravel hingga 2026_10_22_000007_completion_seen. Data akaun lama dikekalkan untuk sejarah.

## Pemilikan dan peraturan

- access_codes dimiliki Pentadbir. Satu kod boleh mempunyai banyak learner_sessions.
- learner_sessions ialah sesi, bukan akaun; tiada email/kata laluan atau rekod users baharu. Token rahsia disimpan sebagai SHA-256; public_id bukan token.
- student_journeys.learner_session_id unik bagi satu perjalanan sesi, user_id NULL. user_id digunakan hanya oleh perjalanan sejarah akaun.
- student_progress, attempts, attempt_questions, responses, answer_submissions, passport_stamps, reflections dan certificates kekal dimiliki perjalanan.
- student_notifications menggunakan learner_session_id untuk sesi; user_id untuk sejarah.
- flow_version 3 ialah sesi kod: satu jam dari Mula Zon 3 hingga refleksi Zon 5; tiada masa berasingan setiap zon. Versi 1/2 ialah sejarah. Tempoh sejarah tidak dikosongkan.
- challenge_launches unik mengikut perjalanan/aktiviti; countdown sah di pelayan sebelum setiap cabaran Zon 3/4. Zon 5 tidak memerlukan countdown.
- draft_payload untuk jawapan semasa; audio_plays maksimum 3 untuk sesi kod. learning_help_events merekod bantuan arahan, tanpa membenarkan extra audio versi sesi.
- certificates unik per perjalanan. Fail dibuat oleh Pentadbir pemilik kod, penerima nama sesi. Akses fail disaring pemilik sesi dan status ready.
- Kod tamat tempoh/nyahaktif menghalang sesi baharu. Tamat Sesi menetapkan ended_at tanpa memadam kemajuan. Deadline kod dimasukkan waktu Malaysia, disimpan UTC.
- learning_releases dan grading_snapshot membekukan sumber soalan/jawapan/media. Penerbitan dan penggantian fail tidak mengubah sesi sedia ada.

## Migrasi dan keselamatan

Buat sandaran sebelum migrate. Jangan gunakan migrate:fresh terhadap data diperlukan. Migrasi 000004/000005 bersifat tambahan; tiada reset akaun, jawapan, cap atau sijil. Pemeriksaan snapshot lajur lama mengesahkan semua rekod sejarah kekal sama selepas migrasi setempat. Semua tindakan mempunyai CSRF, pemilikan dan kawalan peranan pelayan.

## Jadual dan lajur sebenar

### access_codes

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| code | varchar | Tidak | — |
| name | varchar | Tidak | — |
| status | varchar | Tidak | 'active' |
| expires_at | datetime | Ya | — |
| created_by | INTEGER | Tidak | — |
| created_at | datetime | Ya | — |
| updated_at | datetime | Ya | — |

Rujukan: created_by → users.id.

### account_recovery_tokens

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| user_id | INTEGER | Tidak | — |
| token_hash | varchar | Tidak | — |
| kind | varchar | Tidak | 'email_reset' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| expires_at | datetime | Ya | — |
| used_at | datetime | Ya | — |

Rujukan: user_id → users.id.

### activities

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| zone_id | INTEGER | Tidak | — |
| instruction_id | INTEGER | Ya | — |
| title | varchar | Tidak | — |
| kind | varchar | Tidak | 'quiz' |
| passing_percent | numeric | Tidak | '80' |
| is_required | tinyint(1) | Tidak | '1' |
| sort_order | INTEGER | Tidak | '0' |
| settings | TEXT | Tidak | — |
| status | varchar | Tidak | 'draft' |
| revision | INTEGER | Tidak | '1' |
| created_by | INTEGER | Tidak | — |
| updated_by | INTEGER | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| deleted_at | datetime | Ya | — |

Rujukan: updated_by → users.id, created_by → users.id, instruction_id → instructions.id, zone_id → zones.id.

### answer_options

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| question_id | INTEGER | Tidak | — |
| option_key | varchar | Tidak | — |
| text | TEXT | Tidak | — |
| is_correct | tinyint(1) | Tidak | '0' |
| sort_order | INTEGER | Tidak | '0' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: question_id → questions.id.

### answer_submissions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| attempt_question_id | INTEGER | Tidak | — |
| submission_key | varchar | Tidak | — |
| payload | TEXT | Tidak | — |
| is_correct | tinyint(1) | Tidak | — |
| input_mode | varchar | Tidak | 'pointer' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: attempt_question_id → attempt_questions.id.

### attempt_questions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| attempt_id | INTEGER | Tidak | — |
| question_id | INTEGER | Tidak | — |
| position | INTEGER | Tidak | — |
| question_snapshot | TEXT | Tidak | — |
| grading_snapshot | TEXT | Tidak | — |
| max_marks | INTEGER | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| audio_plays | INTEGER | Tidak | '0' |
| audio_help_plays | INTEGER | Tidak | '0' |
| draft_payload | TEXT | Ya | — |
| draft_saved_at | datetime | Ya | — |

Rujukan: question_id → questions.id, attempt_id → attempts.id.

### attempts

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| activity_id | INTEGER | Tidak | — |
| attempt_no | INTEGER | Tidak | — |
| status | varchar | Tidak | 'in_progress' |
| input_mode | varchar | Tidak | 'pointer' |
| activity_snapshot | TEXT | Tidak | — |
| score | INTEGER | Tidak | '0' |
| max_score | INTEGER | Tidak | — |
| percentage | numeric | Ya | — |
| passed | tinyint(1) | Tidak | '0' |
| started_at | datetime | Tidak | CURRENT_TIMESTAMP |
| completed_at | datetime | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: activity_id → activities.id, student_journey_id → student_journeys.id.

### audit_logs

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| admin_user_id | INTEGER | Ya | — |
| action | varchar | Tidak | — |
| entity_type | varchar | Tidak | — |
| entity_id | INTEGER | Ya | — |
| before_data | TEXT | Ya | — |
| after_data | TEXT | Ya | — |
| request_id | varchar | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: admin_user_id → users.id.

### avatars

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| code | varchar | Tidak | — |
| name | varchar | Tidak | — |
| media_file_id | INTEGER | Tidak | — |
| sort_order | INTEGER | Tidak | '0' |
| is_active | tinyint(1) | Tidak | '1' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| deleted_at | datetime | Ya | — |
| gender | varchar | Ya | — |
| role_label | varchar | Ya | — |

Rujukan: media_file_id → media_files.id.

### certificates

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| certificate_number | varchar | Tidak | — |
| recipient_name | varchar | Tidak | — |
| media_file_id | INTEGER | Ya | — |
| status | varchar | Tidak | 'pending' |
| qualified_at | datetime | Tidak | CURRENT_TIMESTAMP |
| issued_at | datetime | Ya | — |
| failure_reason | varchar | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: media_file_id → media_files.id, student_journey_id → student_journeys.id.

### challenge_launches

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| activity_id | INTEGER | Tidak | — |
| countdown_started_at | datetime | Ya | — |
| started_at | datetime | Ya | — |

Rujukan: activity_id → activities.id, student_journey_id → student_journeys.id.

### content_completions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| learning_content_id | INTEGER | Tidak | — |
| completed_at | datetime | Tidak | CURRENT_TIMESTAMP |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: learning_content_id → learning_contents.id, student_journey_id → student_journeys.id.

### content_media

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| learning_content_id | INTEGER | Tidak | — |
| media_file_id | INTEGER | Tidak | — |
| purpose | varchar | Tidak | 'illustration' |
| sort_order | INTEGER | Tidak | '0' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: media_file_id → media_files.id, learning_content_id → learning_contents.id.

### game_sessions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| attempt_id | INTEGER | Tidak | — |
| mode | varchar | Tidak | 'cockpit' |
| phase | varchar | Tidak | 'briefing' |
| checkpoint | TEXT | Tidak | — |
| input_mode | varchar | Tidak | 'pointer' |
| calibration_settings | TEXT | Ya | — |
| version | INTEGER | Tidak | '1' |
| last_saved_at | datetime | Tidak | CURRENT_TIMESTAMP |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: attempt_id → attempts.id.

### instruction_audio

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| instruction_id | INTEGER | Tidak | — |
| media_file_id | INTEGER | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: media_file_id → media_files.id, instruction_id → instructions.id.

### instructions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| title | varchar | Tidak | — |
| body | TEXT | Tidak | — |
| screen_key | varchar | Ya | — |
| status | varchar | Tidak | 'draft' |
| revision | INTEGER | Tidak | '1' |
| created_by | INTEGER | Tidak | — |
| updated_by | INTEGER | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| deleted_at | datetime | Ya | — |

Rujukan: updated_by → users.id, created_by → users.id.

### journey_clocks

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| elapsed_ms | INTEGER | Tidak | '0' |
| started_at | datetime | Tidak | — |
| last_seen_at | datetime | Ya | — |
| finished_at | datetime | Ya | — |
| is_partial | tinyint(1) | Tidak | '0' |

Rujukan: student_journey_id → student_journeys.id.

### learner_sessions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| public_id | varchar | Tidak | — |
| token_hash | varchar | Tidak | — |
| access_code_id | INTEGER | Tidak | — |
| display_name | varchar | Tidak | 'Juruterbang' |
| class_name | varchar | Ya | — |
| avatar_id | INTEGER | Ya | — |
| avatar_confirmed_at | datetime | Ya | — |
| onboarding_status | varchar | Tidak | 'pending' |
| onboarding_step | INTEGER | Tidak | '1' |
| onboarding_completed_at | datetime | Ya | — |
| completed_at | datetime | Ya | — |
| ended_at | datetime | Ya | — |
| created_at | datetime | Ya | — |
| updated_at | datetime | Ya | — |

Rujukan: avatar_id → avatars.id, access_code_id → access_codes.id.

### learning_contents

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| zone_id | INTEGER | Ya | — |
| instruction_id | INTEGER | Ya | — |
| title | varchar | Tidak | — |
| kind | varchar | Tidak | 'note' |
| body | TEXT | Tidak | — |
| sort_order | INTEGER | Tidak | '0' |
| is_required | tinyint(1) | Tidak | '1' |
| status | varchar | Tidak | 'draft' |
| revision | INTEGER | Tidak | '1' |
| created_by | INTEGER | Tidak | — |
| updated_by | INTEGER | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| deleted_at | datetime | Ya | — |

Rujukan: updated_by → users.id, created_by → users.id, instruction_id → instructions.id, zone_id → zones.id.

### learning_help_events

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| attempt_question_id | INTEGER | Tidak | — |
| request_key | varchar | Tidak | — |
| kind | varchar | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: attempt_question_id → attempt_questions.id.

### learning_releases

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| version | varchar | Tidak | — |
| title | varchar | Tidak | — |
| status | varchar | Tidak | 'draft' |
| manifest | TEXT | Tidak | — |
| scoring_policy | TEXT | Tidak | — |
| checksum | varchar | Tidak | — |
| published_at | datetime | Ya | — |
| published_by | INTEGER | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: published_by → users.id.

### media_files

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| kind | varchar | Tidak | — |
| title | varchar | Tidak | — |
| disk | varchar | Tidak | 'private' |
| path | varchar | Tidak | — |
| original_name | varchar | Tidak | — |
| mime_type | varchar | Tidak | — |
| size_bytes | INTEGER | Tidak | — |
| duration_ms | INTEGER | Ya | — |
| width | INTEGER | Ya | — |
| height | INTEGER | Ya | — |
| alt_text | varchar | Ya | — |
| source | varchar | Tidak | 'upload' |
| license_note | TEXT | Ya | — |
| checksum | varchar | Tidak | — |
| status | varchar | Tidak | 'processing' |
| failure_reason | varchar | Ya | — |
| parent_media_id | INTEGER | Ya | — |
| created_by | INTEGER | Tidak | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| deleted_at | datetime | Ya | — |

Rujukan: created_by → users.id, parent_media_id → media_files.id.

### passport_stamps

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| zone_id | INTEGER | Tidak | — |
| title_snapshot | varchar | Tidak | — |
| awarded_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: zone_id → zones.id, student_journey_id → student_journeys.id.

### question_audio

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| question_id | INTEGER | Tidak | — |
| media_file_id | INTEGER | Tidak | — |
| purpose | varchar | Tidak | 'prompt' |
| transcript | TEXT | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: media_file_id → media_files.id, question_id → questions.id.

### question_items

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| question_id | INTEGER | Tidak | — |
| item_key | varchar | Tidak | — |
| text | varchar | Tidak | — |
| media_file_id | INTEGER | Ya | — |
| max_uses | INTEGER | Tidak | '1' |
| sort_order | INTEGER | Tidak | '0' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: media_file_id → media_files.id, question_id → questions.id.

### question_solutions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| question_id | INTEGER | Tidak | — |
| answer_key | TEXT | Tidak | — |
| normalization | TEXT | Tidak | — |
| scoring_mode | varchar | Tidak | 'all_or_nothing' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: question_id → questions.id.

### question_targets

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| question_id | INTEGER | Tidak | — |
| target_key | varchar | Tidak | — |
| label | varchar | Tidak | — |
| capacity | INTEGER | Tidak | '1' |
| sort_order | INTEGER | Tidak | '0' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: question_id → questions.id.

### questions

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| activity_id | INTEGER | Tidak | — |
| prompt | TEXT | Tidak | — |
| type | varchar | Tidak | 'objective' |
| max_marks | INTEGER | Tidak | '10' |
| sort_order | INTEGER | Tidak | '0' |
| public_config | TEXT | Tidak | — |
| feedback_correct | TEXT | Ya | — |
| feedback_incorrect | TEXT | Ya | — |
| status | varchar | Tidak | 'draft' |
| revision | INTEGER | Tidak | '1' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| deleted_at | datetime | Ya | — |

Rujukan: activity_id → activities.id.

### reflections

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| zone_id | INTEGER | Tidak | — |
| emotion | varchar | Tidak | — |
| note | varchar | Ya | — |
| submitted_at | datetime | Tidak | CURRENT_TIMESTAMP |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: zone_id → zones.id, student_journey_id → student_journeys.id.

### responses

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| attempt_question_id | INTEGER | Tidak | — |
| idempotency_key | varchar | Tidak | — |
| payload | TEXT | Tidak | — |
| is_correct | tinyint(1) | Ya | — |
| awarded_marks | INTEGER | Tidak | '0' |
| input_mode | varchar | Tidak | 'pointer' |
| submitted_at | datetime | Ya | — |
| feedback | TEXT | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: attempt_question_id → attempt_questions.id.

### student_journeys

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| user_id | INTEGER | Ya | — |
| learning_release_id | INTEGER | Tidak | — |
| status | varchar | Tidak | 'active' |
| started_at | datetime | Tidak | CURRENT_TIMESTAMP |
| completed_at | datetime | Ya | — |
| last_zone_id | INTEGER | Ya | — |
| last_content_id | INTEGER | Ya | — |
| last_activity_id | INTEGER | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| total_duration_seconds | INTEGER | Ya | — |
| flow_version | INTEGER | Tidak | '1' |
| learner_session_id | INTEGER | Ya | — |

Rujukan: learner_session_id → learner_sessions.id, user_id → users.id, learning_release_id → learning_releases.id, last_zone_id → zones.id, last_content_id → learning_contents.id, last_activity_id → activities.id.

### student_notifications

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| user_id | INTEGER | Ya | — |
| dedupe_key | varchar | Tidak | — |
| title | varchar | Tidak | — |
| body | TEXT | Tidak | — |
| read_at | datetime | Ya | — |
| created_at | datetime | Ya | — |
| updated_at | datetime | Ya | — |
| learner_session_id | INTEGER | Ya | — |

Rujukan: learner_session_id → learner_sessions.id, user_id → users.id.

### student_profiles

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| user_id | INTEGER | Tidak | — |
| avatar_id | INTEGER | Ya | — |
| aircraft_color | varchar | Tidak | 'yellow' |
| onboarding_status | varchar | Tidak | 'pending' |
| onboarding_step | INTEGER | Tidak | '1' |
| onboarding_completed_at | datetime | Ya | — |
| reduced_motion | tinyint(1) | Tidak | '0' |
| audio_enabled | tinyint(1) | Tidak | '1' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| avatar_confirmed_at | datetime | Ya | — |

Rujukan: avatar_id → avatars.id, user_id → users.id.

### student_progress

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| student_journey_id | INTEGER | Tidak | — |
| zone_id | INTEGER | Tidak | — |
| status | varchar | Tidak | 'locked' |
| required_units | INTEGER | Tidak | — |
| completed_units | INTEGER | Tidak | '0' |
| progress_percent | numeric | Tidak | '0' |
| best_score | INTEGER | Tidak | '0' |
| completed_at | datetime | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| countdown_started_at | datetime | Ya | — |
| timing_started_at | datetime | Ya | — |
| timing_finished_at | datetime | Ya | — |
| duration_seconds | INTEGER | Ya | — |
| timing_is_partial | tinyint(1) | Tidak | '0' |

Rujukan: zone_id → zones.id, student_journey_id → student_journeys.id.

### users

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| display_name | varchar | Tidak | — |
| username | varchar | Tidak | — |
| email | varchar | Ya | — |
| password | varchar | Tidak | — |
| role | varchar | Tidak | 'pelajar' |
| status | varchar | Tidak | 'active' |
| remember_token | varchar | Ya | — |
| last_login_at | datetime | Ya | — |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |
| deleted_at | datetime | Ya | — |

### zones

| Lajur | Jenis | Boleh kosong | Nilai asal |
|---|---|---|---|
| id (PK) | INTEGER | Tidak | — |
| code | varchar | Tidak | — |
| name | varchar | Tidak | — |
| sequence | INTEGER | Tidak | — |
| description | TEXT | Ya | — |
| stamp_title | varchar | Tidak | — |
| stamp_media_id | INTEGER | Ya | — |
| is_active | tinyint(1) | Tidak | '1' |
| created_at | datetime | Tidak | CURRENT_TIMESTAMP |
| updated_at | datetime | Tidak | CURRENT_TIMESTAMP |

Rujukan: stamp_media_id → media_files.id.


## Penambahan enhancement UI

- `attempt_questions.meaning_acknowledged_at`: timestamp nullable. Jawapan Susun Ayat betul yang mempunyai maksud menahan soalan/cabaran seterusnya sehingga POST pengesahan pemilik sesi. Migrasi mengisi nilai bagi jawapan betul sejarah.
- `student_journeys.completion_seen_at`: timestamp nullable. Popup penamat muncul bagi perjalanan lengkap yang belum dilihat, kekal walaupun heartbeat atau reload. POST Lihat Pasport menandakan timestamp. Perjalanan sejarah lengkap ditanda semasa migrasi.
- Tiada jadual atau lajur lama dibuang; jumlah jadual domain kekal 36.


### Flow awal Pelajar terkini — Nama dan Kelas sebelum Zon 1

Kod → Nama Pelajar dan Kelas (wajib) → Seterusnya → Zon 1 pilih/pratonton/sahkan avatar → Mari Meneroka → cap Zon 1 → transition kapal terbang → Zon 2 hingga Zon 5. Tiada pendaftaran atau log masuk Pelajar.

GET/POST /maklumat-pelajar mengisi medan display_name dan class_name pada learner_sessions yang sudah dipautkan kepada access_code_id. Middleware menghalang akses halaman dan tindakan zon selagi maklumat belum lengkap; sesi boleh ditamatkan. Maklumat yang sudah disimpan tidak boleh ditulis semula melalui borang ini atau komponen avatar. Sesi lama yang tiada nama/kelas diminta melengkapkan maklumat tanpa memadam kemajuan. Avatar, perjalanan, cap, markah, jam dan status kekal menggunakan rekod sesi sedia ada; tiada migrasi atau reset data diperlukan.


### Logik jawapan dan peribahasa — 23 Oktober 2026

Jawapan betul disimpan kemudian terus ke soalan seterusnya tanpa toast/banner kejayaan. Jawapan salah kekal pada soalan sama; maklum balas seragam “Jawapan kurang tepat, sila cuba lagi.” Tiada penandaan pilihan ditolak atau pendedahan jawapan. Draf, cubaan pertama, pemarkahan, penghantaran idempoten dan timer kekal.

SentenceMeaning hanya memerlukan pengakuan bacaan bagi peribahasa. Dua peribahasa sumber lama dikenal pasti melalui ayatnya; soalan baharu menggunakan config.is_proverb yang boleh diurus melalui editor Pentadbir. Maksud diwajibkan hanya bagi peribahasa. Tajuk: Maksud Peribahasa. Ayat biasa dengan penerangan lama tidak mencetuskan popup. Sesi lama yang sudah menjawab semua soalan tetapi menunggu maksud ayat biasa diselaraskan ketika disambung.

Barcode pasport dibuang. Masa Pengembaraan Saya hanya memaparkan masa keseluruhan dan Betul Cubaan Pertama (lebar penuh). Perekodan learning_help_events dan penambahan audio_help_plays dihentikan; route pencatatan bantuan dan listener frontend dibuang. Metrik/laporan Admin tidak lagi mengira bantuan. Jadual/lajur sejarah dibiarkan tanpa pemadaman data, untuk mengekalkan rekod terdahulu. Bilangan dengar audio biasa (had tiga kali bagi flow semasa) dikekalkan.

Build berjaya. Semakan visual QA: docs/screenshots/passport-no-barcode-no-help.png.
