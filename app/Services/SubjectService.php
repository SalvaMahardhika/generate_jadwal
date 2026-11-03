<?php

namespace App\Services;

use App\Models\Subject;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SubjectService
{
    public function getAll(
        ?string $search = null,
        ?string $category = null,
        ?string $status = null,
        mixed $isJointSubject = null
    ): Collection {
        return Subject::query()
            ->when($search, function ($query, $search) {
                $query->where('subject_name', 'like', "%{$search}%")
                      ->orWhere('subject_code', 'like', "%{$search}%");
            })
            ->when($category, fn($query) => $query->where('category', $category))
            ->when($status, fn($query) => $query->where('status', $status))
            ->when(!is_null($isJointSubject) && $isJointSubject !== '', function ($query) use ($isJointSubject) {
                $query->where('is_joint_subject', filter_var($isJointSubject, FILTER_VALIDATE_BOOLEAN));
            })
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

    /**
     * Impor massal data mata pelajaran (Hanya insert mapel baru, mapel lama dilewati).
     */
    public function import(array $subjectsData): Collection
    {
        $defaultColors = [
            '#2563EB', '#D97706', '#059669', '#DC2626', '#7C3AED', 
            '#DB2777', '#0284C7', '#4D7C0F', '#475569', '#9333EA',
            '#0891B2', '#CA8A04'
        ];

        return DB::transaction(function () use ($subjectsData, $defaultColors) {
            $imported = new Collection();

            foreach ($subjectsData as $index => $data) {
                $code = strtoupper(trim($data['subject_code']));

                // Gunakan firstOrCreate agar jika mapel sudah ada, data lama TIDAK tertimpa/di-overwrite
                $subject = Subject::firstOrCreate(
                    ['subject_code' => $code],
                    [
                        'subject_name' => trim($data['subject_name']),
                        'category' => $data['category'] ?? 'wajib',
                        'passing_grade' => $data['passing_grade'] ?? 75,
                        'color_code' => $data['color_code'] ?? $defaultColors[$index % count($defaultColors)],
                        'status' => $data['status'] ?? 'active',
                        'is_joint_subject' => filter_var($data['is_joint_subject'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ]
                );

                // Hanya masukkan ke daftar koleksi respon jika data baru dibuat
                if ($subject->wasRecentlyCreated) {
                    $imported->push($subject);
                }
            }

            return $imported;
        });
    }
}