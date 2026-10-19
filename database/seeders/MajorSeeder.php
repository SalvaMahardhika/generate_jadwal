<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        Major::create([
            'major_name' => 'Umum',
            'status' => 'active',
        ]);
    }
}