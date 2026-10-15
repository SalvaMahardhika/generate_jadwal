<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['grade_name' => 'Kelas 1', 'sequence' => 1],
            ['grade_name' => 'Kelas 2', 'sequence' => 2],
            ['grade_name' => 'Kelas 3', 'sequence' => 3],
            ['grade_name' => 'Kelas 4', 'sequence' => 4],
            ['grade_name' => 'Kelas 5', 'sequence' => 5],
            ['grade_name' => 'Kelas 6', 'sequence' => 6],
        ];

        foreach ($grades as $grade) {
            Grade::create($grade);
        }
    }
}