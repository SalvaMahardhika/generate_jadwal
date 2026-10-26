<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        TimeSlot::truncate();
        Schema::enableForeignKeyConstraints();

        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        /**
         * Profile 1: SD Kelas 1-2 (8 Slot Penuh per Hari, Pulang Maksimal 11:20)
         */
        $p1Slots = [
            ['period_number' => 1, 'label' => 'Jam 1 (Upacara/Litnum)', 'start_time' => '07:00:00', 'end_time' => '07:35:00', 'is_break' => 0],
            ['period_number' => 2, 'label' => 'JP 1',                   'start_time' => '07:35:00', 'end_time' => '08:10:00', 'is_break' => 0],
            ['period_number' => 3, 'label' => 'JP 2',                   'start_time' => '08:10:00', 'end_time' => '08:45:00', 'is_break' => 0],
            ['period_number' => 4, 'label' => 'JP 3',                   'start_time' => '08:45:00', 'end_time' => '09:20:00', 'is_break' => 0],
            ['period_number' => 5, 'label' => 'Istirahat',              'start_time' => '09:20:00', 'end_time' => '09:35:00', 'is_break' => 1],
            ['period_number' => 6, 'label' => 'JP 4',                   'start_time' => '09:35:00', 'end_time' => '10:10:00', 'is_break' => 0],
            ['period_number' => 7, 'label' => 'JP 5',                   'start_time' => '10:10:00', 'end_time' => '10:45:00', 'is_break' => 0],
            ['period_number' => 8, 'label' => 'JP 6',                   'start_time' => '10:45:00', 'end_time' => '11:20:00', 'is_break' => 0],
        ];

        /**
         * Profile 2: SD Kelas 3-4 (10 Slot Penuh per Hari, Pulang Maksimal 12:10)
         */
        $p2Slots = [
            ['period_number' => 1,  'label' => 'Jam 1 (Upacara/Litnum)', 'start_time' => '07:00:00', 'end_time' => '07:35:00', 'is_break' => 0],
            ['period_number' => 2,  'label' => 'JP 1',                   'start_time' => '07:35:00', 'end_time' => '08:10:00', 'is_break' => 0],
            ['period_number' => 3,  'label' => 'JP 2',                   'start_time' => '08:10:00', 'end_time' => '08:45:00', 'is_break' => 0],
            ['period_number' => 4,  'label' => 'JP 3',                   'start_time' => '08:45:00', 'end_time' => '09:20:00', 'is_break' => 0],
            ['period_number' => 5,  'label' => 'Istirahat 1',            'start_time' => '09:20:00', 'end_time' => '09:35:00', 'is_break' => 1],
            ['period_number' => 6,  'label' => 'JP 4',                   'start_time' => '09:35:00', 'end_time' => '10:10:00', 'is_break' => 0],
            ['period_number' => 7,  'label' => 'JP 5',                   'start_time' => '10:10:00', 'end_time' => '10:45:00', 'is_break' => 0],
            ['period_number' => 8,  'label' => 'Istirahat 2',            'start_time' => '10:45:00', 'end_time' => '11:00:00', 'is_break' => 1],
            ['period_number' => 9,  'label' => 'JP 6',                   'start_time' => '11:00:00', 'end_time' => '11:35:00', 'is_break' => 0],
            ['period_number' => 10, 'label' => 'JP 7',                   'start_time' => '11:35:00', 'end_time' => '12:10:00', 'is_break' => 0],
        ];

        /**
         * Profile 3: SD Kelas 5-6 (11 Slot Penuh per Hari, Pulang Maksimal 12:45)
         */
        $p3Slots = [
            ['period_number' => 1,  'label' => 'Jam 1 (Upacara/Litnum)', 'start_time' => '07:00:00', 'end_time' => '07:35:00', 'is_break' => 0],
            ['period_number' => 2,  'label' => 'JP 1',                   'start_time' => '07:35:00', 'end_time' => '08:10:00', 'is_break' => 0],
            ['period_number' => 3,  'label' => 'JP 2',                   'start_time' => '08:10:00', 'end_time' => '08:45:00', 'is_break' => 0],
            ['period_number' => 4,  'label' => 'JP 3',                   'start_time' => '08:45:00', 'end_time' => '09:20:00', 'is_break' => 0],
            ['period_number' => 5,  'label' => 'Istirahat 1',            'start_time' => '09:20:00', 'end_time' => '09:35:00', 'is_break' => 1],
            ['period_number' => 6,  'label' => 'JP 4',                   'start_time' => '09:35:00', 'end_time' => '10:10:00', 'is_break' => 0],
            ['period_number' => 7,  'label' => 'JP 5',                   'start_time' => '10:10:00', 'end_time' => '10:45:00', 'is_break' => 0],
            ['period_number' => 8,  'label' => 'Istirahat 2',            'start_time' => '10:45:00', 'end_time' => '11:00:00', 'is_break' => 1],
            ['period_number' => 9,  'label' => 'JP 6',                   'start_time' => '11:00:00', 'end_time' => '11:35:00', 'is_break' => 0],
            ['period_number' => 10, 'label' => 'JP 7',                   'start_time' => '11:35:00', 'end_time' => '12:10:00', 'is_break' => 0],
            ['period_number' => 11, 'label' => 'JP 8',                   'start_time' => '12:10:00', 'end_time' => '12:45:00', 'is_break' => 0],
        ];

        // 1. Seed Profile 1 (SD Kelas 1-2)
        foreach ($days as $day) {
            foreach ($p1Slots as $slot) {
                TimeSlot::create(array_merge($slot, [
                    'time_slot_profile_id' => 1,
                    'day' => $day,
                ]));
            }
        }

        // 2. Seed Profile 2 (SD Kelas 3-4)
        foreach ($days as $day) {
            foreach ($p2Slots as $slot) {
                TimeSlot::create(array_merge($slot, [
                    'time_slot_profile_id' => 2,
                    'day' => $day,
                ]));
            }
        }

        // 3. Seed Profile 3 (SD Kelas 5-6)
        foreach ($days as $day) {
            foreach ($p3Slots as $slot) {
                TimeSlot::create(array_merge($slot, [
                    'time_slot_profile_id' => 3,
                    'day' => $day,
                ]));
            }
        }
    }
}