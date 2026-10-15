<?php

namespace App\Services;

use App\Models\Major;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class MajorService
{
    public function getAll(?string $search = null, ?string $status = null): Collection
    {
        return Major::query()
            ->when($search, fn($query) => $query->where('major_name', 'like', "%{$search}%"))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('major_name', 'asc')
            ->get();
    }

    public function create(array $data): Major
    {
        return Major::create($data);
    }

    public function update(Major $major, array $data): Major
    {
        $major->update($data);
        return $major->fresh();
    }

    public function delete(Major $major): bool
    {
        // Proteksi jika jurusan masih terhubung dengan data kelas[cite: 5]
        if ($major->classes()->exists()) {
            throw new Exception('Tidak dapat menghapus jurusan yang masih digunakan oleh kelas.', 422);
        }

        return $major->delete();
    }
}