<?php

namespace App\Services;

use App\Models\Subject;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class SubjectService
{
    public function getAll(?string $search = null, ?string $category = null, ?string $status = null): Collection
    {
        return Subject::query()
            ->when($search, function ($query, $search) {
                $query->where('subject_name', 'like', "%{$search}%")
                      ->orWhere('subject_code', 'like', "%{$search}%");
            })
            ->when($category, fn($query) => $query->where('category', $category))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('subject_name', 'asc')
            ->get();
    }

    public function create(array $data): Subject
    {
        return Subject::create($data);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        return $subject->fresh();
    }

    public function delete(Subject $subject): bool
    {
        if ($subject->classSubjects()->exists()) {
            throw new Exception('Tidak dapat menghapus mata pelajaran yang sudah terhubung dengan kelas.', 422);
        }

        return $subject->delete();
    }
}