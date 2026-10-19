<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            // Guru Kelas SD (Kelas 1 - 6)
            ['nip' => '198501122010011001', 'name' => 'Budi Santoso, S.Pd.', 'gender' => 'L', 'max_jp_per_week' => 24],
            ['nip' => '198803242014022002', 'name' => 'Siti Rahmawati, S.Pd.', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '199005152018011003', 'name' => 'Ahmad Dahlan, S.Pd.', 'gender' => 'L', 'max_jp_per_week' => 24],
            ['nip' => '199208102019032004', 'name' => 'Dewi Lestari, S.Pd.', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '198711052011011005', 'name' => 'Eko Prasetyo, S.Pd.', 'gender' => 'L', 'max_jp_per_week' => 24],
            ['nip' => '198902182015022006', 'name' => 'Fitriani, S.Pd.', 'gender' => 'P', 'max_jp_per_week' => 24],
            
            // Guru Mata Pelajaran Khusus SD
            ['nip' => '199104122016011007', 'name' => 'Bambang Wijaya, S.Pd.Jas', 'gender' => 'L', 'max_jp_per_week' => 24], // PJOK
            ['nip' => '198609202012011008', 'name' => 'Ustadz Nur Hidayat, S.Pd.I', 'gender' => 'L', 'max_jp_per_week' => 24], // PABP
            ['nip' => '199406142020022009', 'name' => 'Rina Utami, S.Pd.', 'gender' => 'P', 'max_jp_per_week' => 24], // B. Inggris & Seni
            ['nip' => '199307222019022010', 'name' => 'Sri Wahyuni, S.Pd.', 'gender' => 'P', 'max_jp_per_week' => 24], // B. Jawa
        ];

        foreach ($teachers as $teacher) {
            Teacher::create($teacher);
        }
    }
}