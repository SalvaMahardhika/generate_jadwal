<?php

namespace App\Services;

use App\Models\Classes;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ClassService
{
    public function getAll(
        ?string $search = null,
        ?int $academicYearId = null,
        ?int $gradeId = null,
        ?string $status = null
    ): Collection {
        return Classes::with(['grade', 'major', 'academicYear', 'homeroomTeacher'])
            ->when($search, fn($query) => $query->where('class_name', 'like', "%{$search}%"))
            ->when($academicYearId, fn($query) => $query->where('academic_year_id', $academicYearId))
            ->when($gradeId, fn($query) => $query->where('grade_id', $gradeId))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('grade_id', 'asc')
            ->orderBy('class_name', 'asc')
            ->get();
    }

    public function create(array $data): Classes
    {
        $class = Classes::create($data);
        return $class->load(['grade', 'major', 'academicYear', 'homeroomTeacher']);
    }

    public function update(Classes $class, array $data): Classes
    {
        $class->update($data);
        return $class->fresh(['grade', 'major', 'academicYear', 'homeroomTeacher']);
    }

    public function delete(Classes $class): bool
    {
        if ($class->classSubjects()->exists()) {
            throw new Exception('Tidak dapat menghapus kelas yang sudah memiliki pemetaan mata pelajaran.', 422);
        }

        if ($class->schedules()->exists()) {
            throw new Exception('Tidak dapat menghapus kelas yang sudah terdaftar dalam jadwal.', 422);
        }

        return $class->delete();
    }
}