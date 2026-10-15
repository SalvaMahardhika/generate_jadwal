<?php

namespace App\Services;

use App\Models\Teacher;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class TeacherService
{
    public function getAll(?string $search = null, ?string $status = null): Collection
    {
        return Teacher::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('name', 'asc')
            ->get();
    }

    public function create(array $data): Teacher
    {
        return Teacher::create($data);
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $teacher->update($data);
        return $teacher->fresh();
    }

    public function delete(Teacher $teacher): bool
    {
        // Proteksi jika guru menjadi wali kelas atau di-assign mengajar mapel[cite: 4, 5]
        if ($teacher->homeroomClasses()->exists()) {
            throw new Exception('Tidak dapat menghapus guru yang sedang bertugas sebagai wali kelas.', 422);
        }

        if ($teacher->classSubjectTeachers()->exists()) {
            throw new Exception('Tidak dapat menghapus guru yang sudah di-assign ke mata pelajaran kelas.', 422);
        }

        return $teacher->delete();
    }
}