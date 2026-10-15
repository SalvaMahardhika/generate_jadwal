<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $classes = Classes::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        foreach ($classes as $class) {
            foreach ($subjects as $sIndex => $subject) {
                // Tentukan beban JP berdasarkan jenjang kelas
                $totalJp = ($class->grade_id <= 2) ? 4 : 5;
                if (in_array($subject->subject_code, ['PABP', 'PJOK', 'BJAWA'])) {
                    $totalJp = 2;
                }

                // 1. Tambah ke class_subjects
                $classSubject = ClassSubject::create([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'total_jp' => $totalJp,
                    'max_jp_per_day' => 2,
                    'status' => 'active',
                ]);

                // 2. Hubungkan pengampu di class_subject_teachers
                ClassSubjectTeacher::create([
                    'class_subject_id' => $classSubject->id,
                    'teacher_id' => $teachers[$sIndex % count($teachers)]->id,
                ]);
            }
        }
    }
}