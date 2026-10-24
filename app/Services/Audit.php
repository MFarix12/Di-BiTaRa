<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Audit
{
    public static function actionLabel(string $action): string
    {
        return ['create'=>'Tambah', 'update'=>'Kemas Kini', 'delete'=>'Arkibkan', 'publish'=>'Terbitkan', 'status'=>'Tukar Status', 'settings_update'=>'Kemas Kini Tetapan', 'profile_update'=>'Kemas Kini Profil', 'password_update'=>'Tukar Kata Laluan'][$action] ?? 'Perubahan Rekod';
    }

    public static function entityLabel(string $entity): string
    {
        return ['access_codes'=>'Kod Akses', 'learner_sessions'=>'Sesi Pelajar', 'user'=>'Pengguna', 'users'=>'Pengguna', 'learning_contents'=>'Nota', 'activities'=>'Aktiviti', 'questions'=>'Soalan', 'instructions'=>'Arahan', 'media_files'=>'Media', 'learning_releases'=>'Keluaran Pembelajaran'][$entity] ?? 'Rekod';
    }

    public static function summary(?string $json): string
    {
        $data = json_decode($json ?? '{}', true) ?: [];
        $labels = ['school_year'=>'Tahun / Darjah', 'name'=>'Nama', 'expires_at'=>'Tamat Tempoh', 'title'=>'Tajuk', 'status'=>'Status', 'zones'=>'Bilangan zon', 'revision'=>'Semakan', 'is_required'=>'Wajib', 'zone_id'=>'Zon', 'sort_order'=>'Susunan', 'display_name'=>'Nama'];
        $parts = [];
        foreach ($labels as $key=>$label) {
            if (! array_key_exists($key, $data) || ! is_scalar($data[$key])) continue;
            $value = $data[$key];
            if ($key === 'status') $value = ['active'=>'Aktif', 'inactive'=>'Tidak Aktif', 'draft'=>'Draf', 'published'=>'Diterbitkan', 'ready'=>'Sedia'][$value] ?? 'Dalam Semakan';
            if ($key === 'is_required') $value = $value ? 'Ya' : 'Tidak';
            $parts[] = $label.': '.$value;
        }
        return implode(' · ', $parts) ?: 'Perubahan disimpan';
    }

    public static function record(string $action, string $entity, int $id, array $after = []): void
    {
        DB::table('audit_logs')->insert(['admin_user_id' => auth()->id(), 'action' => $action, 'entity_type' => $entity, 'entity_id' => $id, 'after_data' => json_encode($after, JSON_UNESCAPED_UNICODE), 'request_id' => (string) Str::uuid()]);
    }
}
