<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['nip' => '1001', 'name' => 'Ustadzah Niken', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '1002', 'name' => 'Ustadzah Melda', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '1003', 'name' => 'Ustadzah Mita', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '1004', 'name' => 'Ustadzah Ulfa', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '1005', 'name' => 'Ustadzah Isti', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '1006', 'name' => 'Ustadzah Fafa', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '1007', 'name' => 'Ustadzah Novi', 'gender' => 'P', 'max_jp_per_week' => 24],
            ['nip' => '1008', 'name' => 'Ustadzah Cindy', 'gender' => 'P', 'max_jp_per_week' => 24],
        ];

        foreach ($teachers as $teacher) {
            Teacher::create($teacher);
        }
    }
}