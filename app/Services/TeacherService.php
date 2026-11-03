<?php

namespace App\Services;

use App\Models\Teacher;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
        // Proteksi jika guru menjadi wali kelas atau di-assign mengajar mapel
        if ($teacher->homeroomClasses()->exists()) {
            throw new Exception('Tidak dapat menghapus guru yang sedang bertugas sebagai wali kelas.', 422);
        }

        if ($teacher->classSubjectTeachers()->exists()) {
            throw new Exception('Tidak dapat menghapus guru yang sudah di-assign ke mata pelajaran kelas.', 422);
        }

        return $teacher->delete();
    }

    /**
     * Impor massal data guru (Hanya insert guru baru, jika NIP sudah ada maka dilewati).
     */
    public function import(array $teachersData): Collection
    {
        return DB::transaction(function () use ($teachersData) {
            $imported = new Collection();

            foreach ($teachersData as $data) {
                $nip = !empty($data['nip']) ? trim($data['nip']) : null;

                if ($nip) {
                    $teacher = Teacher::firstOrCreate(
                        ['nip' => $nip],
                        [
                            'name' => trim($data['name']),
                            'gender' => $data['gender'] ?? 'L',
                            'max_jp_per_week' => $data['max_jp_per_week'] ?? 24,
                            'status' => $data['status'] ?? 'active',
                        ]
                    );

                    if ($teacher->wasRecentlyCreated) {
                        $imported->push($teacher);
                    }
                } else {
                    $teacher = Teacher::create([
                        'nip' => null,
                        'name' => trim($data['name']),
                        'gender' => $data['gender'] ?? 'L',
                        'max_jp_per_week' => $data['max_jp_per_week'] ?? 24,
                        'status' => $data['status'] ?? 'active',
                    ]);

                    $imported->push($teacher);
                }
            }

            return $imported;
        });
    }
}