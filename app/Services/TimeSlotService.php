<?php

namespace App\Services;

use App\Models\TimeSlot;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TimeSlotService
{
    public function getAll(?int $profileId = null, ?string $day = null, ?bool $isBreak = null): Collection
    {
        return TimeSlot::query()
            ->when($profileId, fn($query) => $query->where('time_slot_profile_id', $profileId))
            ->when($day, fn($query) => $query->where('day', strtolower($day)))
            ->when(!is_null($isBreak), fn($query) => $query->where('is_break', $isBreak))
            ->orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('period_number', 'asc')
            ->get();
    }

    public function create(array $data): TimeSlot
    {
        return TimeSlot::create($data);
    }

    public function update(TimeSlot $timeSlot, array $data): TimeSlot
    {
        $timeSlot->update($data);
        return $timeSlot->fresh();
    }

    public function delete(TimeSlot $timeSlot): bool
    {
        if ($timeSlot->schedules()->exists()) {
            throw new Exception('Tidak dapat menghapus slot waktu yang sudah terikat dengan data jadwal.', 422);
        }

        return $timeSlot->delete();
    }

    /**
     * Menghapus seluruh slot waktu pada hari tertentu untuk profil spesifik dalam 1 query transaksi.
     */
    public function deleteByDay(string $day, ?int $profileId = null): int
    {
        $dayLower = strtolower($day);

        // Periksa apakah ada salah satu slot pada hari tersebut yang sudah terpakai di jadwal
        $hasSchedules = TimeSlot::where('day', $dayLower)
            ->when($profileId, fn($q) => $q->where('time_slot_profile_id', $profileId))
            ->whereHas('schedules')
            ->exists();

        if ($hasSchedules) {
            throw new Exception("Tidak dapat menghapus slot waktu pada hari {$day} karena ada slot yang sudah terikat dengan data jadwal.", 422);
        }

        return TimeSlot::where('day', $dayLower)
            ->when($profileId, fn($q) => $q->where('time_slot_profile_id', $profileId))
            ->delete();
    }

    /**
     * Mengkalkulasi dan membuat susunan JP & istirahat secara otomatis berdasarkan profil slot waktu.
     */
    public function generate(array $data): Collection
    {
        return DB::transaction(function () use ($data) {
            $profileId     = (int) $data['time_slot_profile_id'];
            $startTime     = $data['start_time'];
            $duration      = (int) $data['period_duration'];
            $selectedDays  = $data['days'];
            $periodsPerDay = $data['periods_per_day'];
            $breaks        = $data['breaks'] ?? [];
            $overwrite     = (bool) ($data['overwrite'] ?? true);

            // Urutkan konfigurasi istirahat berdasarkan JP pembatasnya (after_period) secara ascending
            usort($breaks, fn($a, $b) => ($a['after_period'] ?? 0) <=> ($b['after_period'] ?? 0));

            // Validasi keterikatan data jadwal pada hari-hari yang dipilih
            foreach ($selectedDays as $day) {
                $dayLower = strtolower($day);
                $hasSchedules = TimeSlot::where('time_slot_profile_id', $profileId)
                    ->where('day', $dayLower)
                    ->whereHas('schedules')
                    ->exists();

                if ($hasSchedules) {
                    throw new Exception("Tidak dapat meregenerasi slot waktu pada hari " . ucfirst($day) . " karena ada slot yang sudah terikat dengan data jadwal.", 422);
                }
            }

            // Jika overwrite aktif, hapus data slot yang ada pada profil dan hari-hari yang dipilih
            if ($overwrite) {
                foreach ($selectedDays as $day) {
                    TimeSlot::where('time_slot_profile_id', $profileId)
                        ->where('day', strtolower($day))
                        ->delete();
                }
            }

            $createdSlots = [];

            foreach ($selectedDays as $day) {
                $dayLower = strtolower($day);
                $totalJp = (int) ($periodsPerDay[$dayLower] ?? $periodsPerDay[$day] ?? 0);

                if ($totalJp <= 0) {
                    continue;
                }

                // Hitung menit awal dari jam mulai (misal 07:00 => 420 menit)
                $timeParts = explode(':', $startTime);
                $startHour = (int) ($timeParts[0] ?? 7);
                $startMin  = (int) ($timeParts[1] ?? 0);
                $currentMinutes = ($startHour * 60) + $startMin;

                $periodSequence = 1;

                for ($jpNumber = 1; $jpNumber <= $totalJp; $jpNumber++) {
                    // 1. Buat Slot Jam Pelajaran (JP)
                    $sTime = sprintf('%02d:%02d:00', floor($currentMinutes / 60) % 24, $currentMinutes % 60);
                    $currentMinutes += $duration;
                    $eTime = sprintf('%02d:%02d:00', floor($currentMinutes / 60) % 24, $currentMinutes % 60);

                    $createdSlots[] = TimeSlot::create([
                        'time_slot_profile_id' => $profileId,
                        'day'                  => $dayLower,
                        'period_number'        => $periodSequence++,
                        'label'                => "JP {$jpNumber}",
                        'start_time'           => $sTime,
                        'end_time'             => $eTime,
                        'is_break'             => false,
                    ]);

                    // 2. Cek apakah ada jam istirahat yang disisipkan setelah JP ini
                    foreach ($breaks as $b) {
                        if ((int) $b['after_period'] === $jpNumber) {
                            $breakDuration = (int) $b['duration'];
                            $breakLabel    = !empty($b['label']) ? $b['label'] : 'Istirahat';

                            $bsTime = sprintf('%02d:%02d:00', floor($currentMinutes / 60) % 24, $currentMinutes % 60);
                            $currentMinutes += $breakDuration;
                            $beTime = sprintf('%02d:%02d:00', floor($currentMinutes / 60) % 24, $currentMinutes % 60);

                            $createdSlots[] = TimeSlot::create([
                                'time_slot_profile_id' => $profileId,
                                'day'                  => $dayLower,
                                'period_number'        => $periodSequence++,
                                'label'                => $breakLabel,
                                'start_time'           => $bsTime,
                                'end_time'             => $beTime,
                                'is_break'             => true,
                            ]);
                        }
                    }
                }
            }

            return new Collection($createdSlots);
        });
    }
}