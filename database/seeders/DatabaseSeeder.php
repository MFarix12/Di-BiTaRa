<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $names = ['Balai Pelepasan', 'Taklimat Penerbangan', 'Simulasi Penerbangan', 'Cabaran Kecemasan', 'Balai Ketibaan & Imlak'];
        $stamps = ['Peneroka Nota','Pakar Tatabahasa','Juruterbang Bahasa','Kapten Terlatih','Misi Lengkap'];
        $descriptions = ['Kenali misi anda dan bersedia untuk pengembaraan.', 'Fahami dua pola ejaan: di- rapat dan di jarak.', 'Kendalikan kokpit dan selesaikan misi bahasa.', 'Pulihkan komunikasi dengan membina ayat yang tepat.', 'Dengar, tulis dan lengkapkan perjalanan anda.'];
        foreach ($names as $i => $name) {
            DB::table('zones')->updateOrInsert(['code' => 'Z'.($i+1)], ['name' => $name, 'sequence' => $i+1, 'stamp_title' => $stamps[$i], 'description' => $descriptions[$i]]);
        }
    }
}
