<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['subject_code' => 'PABP', 'subject_name' => 'Pendidikan Agama & Budi Pekerti', 'color_code' => '#EF4444', 'category' => 'wajib'],
            ['subject_code' => 'PPKN', 'subject_name' => 'Pendidikan Pancasila', 'color_code' => '#F59E0B', 'category' => 'wajib'],
            ['subject_code' => 'BINDO', 'subject_name' => 'Bahasa Indonesia', 'color_code' => '#10B981', 'category' => 'wajib'],
            ['subject_code' => 'MTK', 'subject_name' => 'Matematika', 'color_code' => '#3B82F6', 'category' => 'wajib'],
            ['subject_code' => 'IPAS', 'subject_name' => 'Ilmu Pengetahuan Alam & Sosial', 'color_code' => '#EC4899', 'category' => 'wajib'],
            ['subject_code' => 'PJOK', 'subject_name' => 'PJOK', 'color_code' => '#8B5CF6', 'category' => 'wajib'],
            ['subject_code' => 'SRUP', 'subject_name' => 'Seni Rupa & Budaya', 'color_code' => '#A855F7', 'category' => 'wajib'],
            ['subject_code' => 'BING', 'subject_name' => 'Bahasa Inggris', 'color_code' => '#F97316', 'category' => 'mulok'],
            ['subject_code' => 'BJAWA', 'subject_name' => 'Bahasa Jawa', 'color_code' => '#6B7280', 'category' => 'mulok'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}