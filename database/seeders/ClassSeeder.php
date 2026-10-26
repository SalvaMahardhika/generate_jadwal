<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Grade;
use App\Models\Major;
use App\Models\Teacher;
use App\Models\TimeSlotProfile;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $major = Major::first();
        $grades = Grade::orderBy('sequence')->get();
        $teachers = Teacher::all();

        // Ambil ID Profil Waktu
        $profiles = TimeSlotProfile::all()->keyBy('id');

        foreach ($grades as $index => $grade) {
            $sequence = $grade->sequence;

            // Tentukan profil slot waktu berdasarkan jenjang kelas
            $profileId = match (true) {
                in_array($sequence, [1, 2]) => 1,
                in_array($sequence, [3, 4]) => 2,
                in_array($sequence, [5, 6]) => 3,
                default => $profiles->first()?->id
            };

            Classes::create([
                'class_name' => 'Kelas ' . $sequence,
                'grade_id' => $grade->id,
                'major_id' => $major?->id,
                'academic_year_id' => $academicYear->id,
                'time_slot_profile_id' => $profileId,
                'homeroom_teacher_id' => $teachers[$index % count($teachers)]->id,
                'status' => 'active',
            ]);
        }
    }
}