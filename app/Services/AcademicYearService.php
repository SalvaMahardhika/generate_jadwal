<?php

namespace App\Services;

use App\Models\AcademicYear;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function getAll(?string $search = null): Collection
    {
        return AcademicYear::query()
            ->when($search, fn($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['is_active']) && $data['is_active']) {
                AcademicYear::query()->update(['is_active' => false]);
            }

            return AcademicYear::create($data);
        });
    }

    public function update(AcademicYear $academicYear, array $data): AcademicYear
    {
        return DB::transaction(function () use ($academicYear, $data) {
            if (!empty($data['is_active']) && $data['is_active']) {
                AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
            }

            $academicYear->update($data);
            return $academicYear->fresh();
        });
    }

    public function delete(AcademicYear $academicYear): bool
    {
        if ($academicYear->is_active) {
            throw new Exception('Tidak dapat menghapus tahun ajaran yang sedang aktif.', 422);
        }

        return $academicYear->delete();
    }

    public function setActive(AcademicYear $academicYear): AcademicYear
    {
        return DB::transaction(function () use ($academicYear) {
            AcademicYear::query()->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
            
            return $academicYear->fresh();
        });
    }
}