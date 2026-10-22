<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\TimeSlotProfile;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TimeSlotProfileService
{
    /**
     * Mengambil seluruh profil slot waktu, opsional difilter per tahun ajaran.
     */
    public function getAll(?int $academicYearId = null): Collection
    {
        return TimeSlotProfile::with(['academicYear'])
            ->withCount(['timeSlots', 'classes'])
            ->when($academicYearId, fn($query) => $query->where('academic_year_id', $academicYearId))
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Detail 1 profil beserta daftar slot waktu dan kelas yang terikat.
     */
    public function getById(int $id): TimeSlotProfile
    {
        return TimeSlotProfile::with([
            'academicYear',
            'timeSlots' => function ($q) {
                $q->orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                  ->orderBy('period_number', 'asc');
            },
            'classes.grade',
            'classes.major',
        ])
        ->withCount(['timeSlots', 'classes'])
        ->findOrFail($id);
    }

    /**
     * Membuat profil baru.
     */
    public function create(array $data): TimeSlotProfile
    {
        return DB::transaction(function () use ($data) {
            $isDefault = (bool) ($data['is_default'] ?? false);

            // Jika ditandai default, reset status default profil lain di tahun ajaran yang sama
            if ($isDefault) {
                TimeSlotProfile::where('academic_year_id', $data['academic_year_id'])
                    ->update(['is_default' => false]);
            }

            return TimeSlotProfile::create([
                'academic_year_id' => $data['academic_year_id'],
                'name'             => $data['name'],
                'description'      => $data['description'] ?? null,
                'is_default'       => $isDefault,
            ]);
        });
    }

    /**
     * Mengubah data profil.
     */
    public function update(TimeSlotProfile $profile, array $data): TimeSlotProfile
    {
        return DB::transaction(function () use ($profile, $data) {
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                TimeSlotProfile::where('academic_year_id', $data['academic_year_id'])
                    ->where('id', '!=', $profile->id)
                    ->update(['is_default' => false]);
            }

            $profile->update([
                'academic_year_id' => $data['academic_year_id'],
                'name'             => $data['name'],
                'description'      => $data['description'] ?? null,
                'is_default'       => $isDefault,
            ]);

            return $profile->fresh(['academicYear', 'timeSlots', 'classes']);
        });
    }

    /**
     * Menghapus profil jika belum digunakan di jadwal KBM.
     */
    public function delete(TimeSlotProfile $profile): bool
    {
        // Cek apakah ada slot waktu di profil ini yang sudah digunakan dalam tabel schedules
        $hasSchedules = $profile->timeSlots()->whereHas('schedules')->exists();

        if ($hasSchedules) {
            throw new Exception('Gagal menghapus: Profil slot waktu ini sudah digunakan oleh data jadwal.', 422);
        }

        return DB::transaction(function () use ($profile) {
            // Lepaskan keterikatan kelas dari profil ini
            Classes::where('time_slot_profile_id', $profile->id)
                ->update(['time_slot_profile_id' => null]);

            // Hapus profil (time_slots akan otomatis terhapus via cascade constraint)
            return $profile->delete();
        });
    }

    /**
     * Menghubungkan (plotting) daftar kelas ke profil slot waktu ini.
     */
    public function assignToClasses(TimeSlotProfile $profile, array $classIds): array
    {
        return DB::transaction(function () use ($profile, $classIds) {
            // Update kelas yang dipilih agar menggunakan profil ini
            $affected = Classes::whereIn('id', $classIds)
                ->update(['time_slot_profile_id' => $profile->id]);

            return [
                'assigned_count' => $affected,
                'profile'        => $profile->load(['classes.grade', 'classes.major']),
            ];
        });
    }

    /**
     * Duplikasi/Salin Profil beserta seluruh susunan Slot Waktunya ke Tahun Ajaran Baru atau Sama.
     */
    public function duplicate(TimeSlotProfile $sourceProfile, int $targetAcademicYearId, ?string $newName = null): TimeSlotProfile
    {
        return DB::transaction(function () use ($sourceProfile, $targetAcademicYearId, $newName) {
            $sourceProfile->load('timeSlots');

            $newProfile = TimeSlotProfile::create([
                'academic_year_id' => $targetAcademicYearId,
                'name'             => $newName ?: ($sourceProfile->name . ' (Salinan)'),
                'description'      => $sourceProfile->description,
                'is_default'       => false,
            ]);

            foreach ($sourceProfile->timeSlots as $slot) {
                $newProfile->timeSlots()->create([
                    'day'           => $slot->day,
                    'period_number' => $slot->period_number,
                    'label'         => $slot->label,
                    'start_time'    => $slot->start_time,
                    'end_time'      => $slot->end_time,
                    'is_break'      => $slot->is_break,
                ]);
            }

            return $newProfile->load(['academicYear', 'timeSlots']);
        });
    }
}