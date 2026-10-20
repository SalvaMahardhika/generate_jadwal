<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        // Jam belajar Senin - Kamis (Sesuai Permendikdasmen No 13 Tahun 2025)
        $weekdaySlots = [
            ['period' => 1, 'start' => '07:00', 'end' => '07:35', 'is_break' => false, 'label' => 'JP 1 (Upacara / Litnum)'],
            ['period' => 2, 'start' => '07:35', 'end' => '08:10', 'is_break' => false, 'label' => 'JP 2'],
            ['period' => 3, 'start' => '08:10', 'end' => '08:45', 'is_break' => false, 'label' => 'JP 3'],
            ['period' => 4, 'start' => '08:45', 'end' => '09:20', 'is_break' => false, 'label' => 'JP 4'],
            ['period' => 5, 'start' => '09:20', 'end' => '09:35', 'is_break' => true,  'label' => 'Istirahat I'],
            ['period' => 6, 'start' => '09:35', 'end' => '10:10', 'is_break' => false, 'label' => 'JP 5'],
            ['period' => 7, 'start' => '10:10', 'end' => '10:45', 'is_break' => false, 'label' => 'JP 6'],
            ['period' => 8, 'start' => '10:45', 'end' => '11:00', 'is_break' => true,  'label' => 'Istirahat II'],
            ['period' => 9, 'start' => '11:00', 'end' => '11:35', 'is_break' => false, 'label' => 'JP 7'],
            ['period' => 10,'start' => '11:35', 'end' => '12:10', 'is_break' => false, 'label' => 'JP 8'],
        ];

        // Jam belajar Jumat (Pendek)
        $fridaySlots = [
            ['period' => 1, 'start' => '07:00', 'end' => '07:35', 'is_break' => false, 'label' => 'JP 1 (Litnum)'],
            ['period' => 2, 'start' => '07:35', 'end' => '08:10', 'is_break' => false, 'label' => 'JP 2'],
            ['period' => 3, 'start' => '08:10', 'end' => '08:45', 'is_break' => false, 'label' => 'JP 3'],
            ['period' => 4, 'start' => '08:45', 'end' => '09:20', 'is_break' => false, 'label' => 'JP 4'],
            ['period' => 5, 'start' => '09:20', 'end' => '09:35', 'is_break' => true,  'label' => 'Istirahat'],
            ['period' => 6, 'start' => '09:35', 'end' => '10:10', 'is_break' => false, 'label' => 'JP 5'],
            ['period' => 7, 'start' => '10:10', 'end' => '10:45', 'is_break' => false, 'label' => 'JP 6'],
        ];

        // Jam belajar Sabtu (Khusus Litnum + Kokurikuler / P5)
        $saturdaySlots = [
            ['period' => 1, 'start' => '07:00', 'end' => '07:35', 'is_break' => false, 'label' => 'JP 1 (Litnum)'],
            ['period' => 2, 'start' => '07:35', 'end' => '08:10', 'is_break' => false, 'label' => 'Kokurikuler 1'],
            ['period' => 3, 'start' => '08:10', 'end' => '08:45', 'is_break' => false, 'label' => 'Kokurikuler 2'],
            ['period' => 4, 'start' => '08:45', 'end' => '09:20', 'is_break' => false, 'label' => 'Kokurikuler 3'],
            ['period' => 5, 'start' => '09:20', 'end' => '09:35', 'is_break' => true,  'label' => 'Istirahat'],
            ['period' => 6, 'start' => '09:35', 'end' => '10:10', 'is_break' => false, 'label' => 'Kokurikuler 4'],
            ['period' => 7, 'start' => '10:10', 'end' => '10:45', 'is_break' => false, 'label' => 'Kokurikuler 5'],
        ];

        Schema::disableForeignKeyConstraints();
        TimeSlot::truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($days as $day) {
            $slots = match ($day) {
                'jumat' => $fridaySlots,
                'sabtu' => $saturdaySlots,
                default => $weekdaySlots,
            };

            foreach ($slots as $slot) {
                TimeSlot::create([
                    'day' => $day,
                    'period_number' => $slot['period'],
                    'label' => $slot['label'] . ' (' . $slot['start'] . ' - ' . $slot['end'] . ')',
                    'start_time' => $slot['start'] . ':00',
                    'end_time' => $slot['end'] . ':00',
                    'is_break' => $slot['is_break'],
                ]);
            }
        }
    }
}