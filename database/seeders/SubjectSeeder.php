<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Rutin Pagi & Kokurikuler
            ['subject_code' => 'UPACARA_LITNUM', 'subject_name' => 'Upacara & Literasi Numerasi', 'color_code' => '#64748B', 'category' => 'wajib'],
            ['subject_code' => 'KOKURIKULER',    'subject_name' => 'Kokurikuler (P5)',             'color_code' => '#6366F1', 'category' => 'kokurikuler'],

            // Intrakurikuler Wajib
            ['subject_code' => 'PABP',          'subject_name' => 'Pendidikan Agama & Budi Pekerti', 'color_code' => '#EF4444', 'category' => 'wajib'],
            ['subject_code' => 'PPKN',          'subject_name' => 'Pendidikan Pancasila',            'color_code' => '#F59E0B', 'category' => 'wajib'],
            ['subject_code' => 'BINDO',         'subject_name' => 'Bahasa Indonesia',                'color_code' => '#10B981', 'category' => 'wajib'],
            ['subject_code' => 'MTK',           'subject_name' => 'Matematika',                      'color_code' => '#3B82F6', 'category' => 'wajib'],
            ['subject_code' => 'IPAS',          'subject_name' => 'Ilmu Pengetahuan Alam & Sosial',  'color_code' => '#EC4899', 'category' => 'wajib'],
            ['subject_code' => 'PJOK',          'subject_name' => 'PJOK',                            'color_code' => '#8B5CF6', 'category' => 'wajib'],
            ['subject_code' => 'SENI',          'subject_name' => 'Seni Budaya',                     'color_code' => '#A855F7', 'category' => 'wajib'],

            // Muatan Lokal Khusus Bahasa Jawa
            ['subject_code' => 'BJAWA',         'subject_name' => 'Bahasa Jawa',                     'color_code' => '#6B7280', 'category' => 'mulok'],

            // Pilihan / Peminatan
            ['subject_code' => 'BING',          'subject_name' => 'Bahasa Inggris',                  'color_code' => '#F97316', 'category' => 'peminatan'],
            ['subject_code' => 'AI_KODING',     'subject_name' => 'Koding & Kecerdasan Artifisial',   'color_code' => '#06B6D4', 'category' => 'peminatan'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['subject_code' => $subject['subject_code']],
                $subject
            );
        }
    }
}