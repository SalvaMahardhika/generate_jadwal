<?php

namespace App\Services;

use App\Models\Grade;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class GradeService
{
    public function getAll(?string $search = null): Collection
    {
        return Grade::query()
            ->when($search, fn($query) => $query->where('grade_name', 'like', "%{$search}%"))
            ->orderBy('sequence', 'asc')
            ->get();
    }

    public function create(array $data): Grade
    {
        return Grade::create($data);
    }

    public function update(Grade $grade, array $data): Grade
    {
        $grade->update($data);
        return $grade->fresh();
    }

    public function delete(Grade $grade): bool
    {
        // Proteksi jika jenjang masih memiliki relasi ke tabel classes[cite: 5]
        if ($grade->classes()->exists()) {
            throw new Exception('Tidak dapat menghapus jenjang yang masih digunakan oleh kelas.', 422);
        }

        return $grade->delete();
    }
}