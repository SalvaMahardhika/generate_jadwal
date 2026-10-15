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
            ['subject_code' => 'PJOK', 'subject_name' => 'PJOK', 'color_code' => '#8B5CF6', 'category' => 'wajib'],
            ['subject_code' => 'IPAS', 'subject_name' => 'IPAS', 'color_code' => '#EC4899', 'category' => 'wajib'],
            ['subject_code' => 'BJAWA', 'subject_name' => 'Bahasa Jawa', 'color_code' => '#6B7280', 'category' => 'mulok'],
            ['subject_code' => 'BARAB', 'subject_name' => 'Bahasa Arab', 'color_code' => '#14B8A6', 'category' => 'mulok'],
            ['subject_code' => 'BING', 'subject_name' => 'Bahasa Inggris', 'color_code' => '#F97316', 'category' => 'mulok'],
            ['subject_code' => 'AQD', 'subject_name' => 'Aqidah Akhlak', 'color_code' => '#84CC16', 'category' => 'wajib'],
            ['subject_code' => 'SHR', 'subject_name' => 'Shiroh', 'color_code' => '#06B6D4', 'category' => 'wajib'],
            ['subject_code' => 'SRUP', 'subject_name' => 'Seni Rupa', 'color_code' => '#A855F7', 'category' => 'wajib'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}