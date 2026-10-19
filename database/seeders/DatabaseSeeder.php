<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User Default
        User::create([
            'name' => 'Admin Kurikulum',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        // Jalankan Seeder Berurutan Sesuai Dependensi Relasi DB
        $this->call([
            TimeSlotSeeder::class,
            AcademicYearSeeder::class,
            GradeSeeder::class,
            MajorSeeder::class,
            TeacherSeeder::class,
            SubjectSeeder::class,
            ClassSeeder::class,
            ClassSubjectSeeder::class,
        ]);
    }
}