<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

        // Jam belajar SD Senin - Kamis (07.30 - 15.00)
        $mondayToThursdaySlots = [
            ['period' => 1, 'start' => '07.30', 'end' => '08.30', 'is_break' => false, 'label' => 'JP 1'],
            ['period' => 2, 'start' => '08.30', 'end' => '09.05', 'is_break' => false, 'label' => 'JP 2'],
            ['period' => 3, 'start' => '09.05', 'end' => '09.40', 'is_break' => false, 'label' => 'JP 3'],
            ['period' => 4, 'start' => '09.40', 'end' => '10.15', 'is_break' => true,  'label' => 'Istirahat / Dhuha'],
            ['period' => 5, 'start' => '10.15', 'end' => '10.50', 'is_break' => false, 'label' => 'JP 4'],
            ['period' => 6, 'start' => '10.50', 'end' => '11.25', 'is_break' => false, 'label' => 'JP 5'],
            ['period' => 7, 'start' => '11.25', 'end' => '12.00', 'is_break' => false, 'label' => 'JP 6'],
            ['period' => 8, 'start' => '12.00', 'end' => '13.15', 'is_break' => true,  'label' => 'ISHOMA'],
            ['period' => 9, 'start' => '13.15', 'end' => '13.50', 'is_break' => false, 'label' => 'JP 7'],
            ['period' => 10, 'start' => '13.50', 'end' => '14.25', 'is_break' => false, 'label' => 'JP 8'],
            ['period' => 11, 'start' => '14.25', 'end' => '15.00', 'is_break' => false, 'label' => 'JP 9'],
        ];

        // Jam belajar SD Jumat (07.30 - 13.50)
        $fridaySlots = [
            ['period' => 1, 'start' => '07.30', 'end' => '08.30', 'is_break' => false, 'label' => 'JP 1'],
            ['period' => 2, 'start' => '08.30', 'end' => '09.05', 'is_break' => false, 'label' => 'JP 2'],
            ['period' => 3, 'start' => '09.05', 'end' => '09.40', 'is_break' => false, 'label' => 'JP 3'],
            ['period' => 4, 'start' => '09.40', 'end' => '10.15', 'is_break' => true,  'label' => 'Istirahat / Dhuha'],
            ['period' => 5, 'start' => '10.15', 'end' => '10.50', 'is_break' => false, 'label' => 'JP 4'],
            ['period' => 6, 'start' => '10.50', 'end' => '11.25', 'is_break' => false, 'label' => 'JP 5'],
            ['period' => 7, 'start' => '11.25', 'end' => '12.00', 'is_break' => false, 'label' => 'JP 6'],
            ['period' => 8, 'start' => '12.00', 'end' => '13.15', 'is_break' => true,  'label' => 'ISHOMA / Sholat Jumat'],
            ['period' => 9, 'start' => '13.15', 'end' => '13.50', 'is_break' => false, 'label' => 'JP 7'],
        ];

        foreach ($days as $day) {
            $slots = ($day === 'jumat') ? $fridaySlots : $mondayToThursdaySlots;
            foreach ($slots as $slot) {
                TimeSlot::create([
                    'day' => $day,
                    'period_number' => $slot['period'],
                    'label' => $slot['label'] . ' (' . $slot['start'] . ' - ' . $slot['end'] . ')',
                    'start_time' => str_replace('.', ':', $slot['start']) . ':00',
                    'end_time' => str_replace('.', ':', $slot['end']) . ':00',
                    'is_break' => $slot['is_break'],
                ]);
            }
        }
    }
}