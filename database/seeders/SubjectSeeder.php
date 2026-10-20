<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Mata Pelajaran Wajib Intrakurikuler
            ['subject_code' => 'PABP',        'subject_name' => 'Pendidikan Agama & Budi Pekerti', 'color_code' => '#EF4444', 'category' => 'wajib'],
            ['subject_code' => 'PPKN',        'subject_name' => 'Pendidikan Pancasila',            'color_code' => '#F59E0B', 'category' => 'wajib'],
            ['subject_code' => 'BINDO',       'subject_name' => 'Bahasa Indonesia',                'color_code' => '#10B981', 'category' => 'wajib'],
            ['subject_code' => 'MTK',         'subject_name' => 'Matematika',                      'color_code' => '#3B82F6', 'category' => 'wajib'],
            ['subject_code' => 'IPAS',        'subject_name' => 'Ilmu Pengetahuan Alam & Sosial',  'color_code' => '#EC4899', 'category' => 'wajib'],
            ['subject_code' => 'PJOK',        'subject_name' => 'PJOK',                            'color_code' => '#8B5CF6', 'category' => 'wajib'],
            ['subject_code' => 'SRUP',        'subject_name' => 'Seni Rupa & Budaya',              'color_code' => '#A855F7', 'category' => 'wajib'],

            // Muatan Lokal (BTQ dan Bahasa Jawa)
            ['subject_code' => 'BJAWA',       'subject_name' => 'Bahasa Jawa',                     'color_code' => '#6B7280', 'category' => 'mulok'],
            ['subject_code' => 'BTQ',         'subject_name' => 'Baca Tulis Al-Qur\'an (BTQ)',     'color_code' => '#14B8A6', 'category' => 'mulok'],

            // Mapel Pilihan / Peminatan
            ['subject_code' => 'BING',        'subject_name' => 'Bahasa Inggris',                  'color_code' => '#F97316', 'category' => 'peminatan'],
            ['subject_code' => 'AI_KODING',   'subject_name' => 'Koding & AI',                     'color_code' => '#06B6D4', 'category' => 'peminatan'],

            // Kokurikuler / P5
            ['subject_code' => 'KOKURIKULER', 'subject_name' => 'Kokurikuler (P5)',                'color_code' => '#6366F1', 'category' => 'kokurikuler'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['subject_code' => $subject['subject_code']],
                $subject
            );
        }
    }
}