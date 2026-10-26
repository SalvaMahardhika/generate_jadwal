<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Kokurikuler (Joint Subject)
            [
                'subject_code' => 'KOKURIKULER',
                'subject_name' => 'Kokurikuler (P5)',
                'color_code'   => '#6366F1',
                'category'     => 'kokurikuler',
                'is_joint_subject' => true,
            ],

            // Intrakurikuler Wajib
            ['subject_code' => 'PABP',      'subject_name' => 'Pendidikan Agama & Budi Pekerti', 'color_code' => '#EF4444', 'category' => 'wajib', 'is_joint_subject' => false],
            ['subject_code' => 'PPKN',      'subject_name' => 'Pendidikan Pancasila',            'color_code' => '#F59E0B', 'category' => 'wajib', 'is_joint_subject' => false],
            ['subject_code' => 'BINDO',     'subject_name' => 'Bahasa Indonesia',                'color_code' => '#10B981', 'category' => 'wajib', 'is_joint_subject' => false],
            ['subject_code' => 'MTK',       'subject_name' => 'Matematika',                      'color_code' => '#3B82F6', 'category' => 'wajib', 'is_joint_subject' => false],
            ['subject_code' => 'IPAS',      'subject_name' => 'Ilmu Pengetahuan Alam & Sosial',  'color_code' => '#EC4899', 'category' => 'wajib', 'is_joint_subject' => false],
            ['subject_code' => 'PJOK',      'subject_name' => 'PJOK',                            'color_code' => '#8B5CF6', 'category' => 'wajib', 'is_joint_subject' => false],
            ['subject_code' => 'SENI',      'subject_name' => 'Seni Budaya',                     'color_code' => '#A855F7', 'category' => 'wajib', 'is_joint_subject' => false],

            // Muatan Lokal
            ['subject_code' => 'BJAWA',     'subject_name' => 'Bahasa Jawa',                     'color_code' => '#6B7280', 'category' => 'mulok', 'is_joint_subject' => false],

            // Pilihan / Peminatan
            ['subject_code' => 'BING',      'subject_name' => 'Bahasa Inggris',                  'color_code' => '#F97316', 'category' => 'peminatan', 'is_joint_subject' => false],
            ['subject_code' => 'AI_KODING', 'subject_name' => 'Koding & Kecerdasan Artifisial',   'color_code' => '#06B6D4', 'category' => 'peminatan', 'is_joint_subject' => false],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['subject_code' => $subject['subject_code']],
                $subject
            );
        }
    }
}