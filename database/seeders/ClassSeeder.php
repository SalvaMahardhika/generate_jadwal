<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Grade;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $grades = Grade::all();
        $teachers = Teacher::all();

        foreach ($grades as $index => $grade) {
            Classes::create([
                'class_name' => 'Kelas ' . $grade->sequence,
                'grade_id' => $grade->id,
                'major_id' => null,
                'academic_year_id' => $academicYear->id,
                'homeroom_teacher_id' => $teachers[$index % count($teachers)]->id,
                'status' => 'active',
            ]);
        }
    }
}