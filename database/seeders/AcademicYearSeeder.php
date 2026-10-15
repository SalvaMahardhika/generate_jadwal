<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);
    }
}