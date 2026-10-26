<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\TimeSlotProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TimeSlotProfileSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        TimeSlotProfile::truncate();
        Schema::enableForeignKeyConstraints();

        $academicYear = AcademicYear::first();
        $academicYearId = $academicYear ? $academicYear->id : 1;

        $profiles = [
            [
                'id' => 1,
                'academic_year_id' => $academicYearId,
                'name' => 'SD kelas 1-2',
                'description' => 'untuk sd kelas 1-2',
                'is_default' => 0,
            ],
            [
                'id' => 2,
                'academic_year_id' => $academicYearId,
                'name' => 'SD kelas 3-4',
                'description' => 'untuk sd kelas 3-4',
                'is_default' => 0,
            ],
            [
                'id' => 3,
                'academic_year_id' => $academicYearId,
                'name' => 'SD kelas 5-6',
                'description' => 'untuk sd kelas 5-6',
                'is_default' => 0,
            ],
        ];

        foreach ($profiles as $profile) {
            TimeSlotProfile::updateOrCreate(
                ['id' => $profile['id']],
                $profile
            );
        }
    }
}