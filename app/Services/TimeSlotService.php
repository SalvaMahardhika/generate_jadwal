<?php

namespace App\Services;

use App\Models\TimeSlot;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class TimeSlotService
{
    public function getAll(?string $day = null, ?bool $isBreak = null): Collection
    {
        return TimeSlot::query()
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
     * Menghapus seluruh slot waktu pada hari tertentu dalam 1 query transaksi.
     */
    public function deleteByDay(string $day): int
    {
        $dayLower = strtolower($day);

        // Periksa apakah ada salah satu slot pada hari tersebut yang sudah terpakai di jadwal
        $hasSchedules = TimeSlot::where('day', $dayLower)
            ->whereHas('schedules')
            ->exists();

        if ($hasSchedules) {
            throw new Exception("Tidak dapat menghapus slot waktu pada hari {$day} karena ada slot yang sudah terikat dengan data jadwal.", 422);
        }

        return TimeSlot::where('day', $dayLower)->delete();
    }
}