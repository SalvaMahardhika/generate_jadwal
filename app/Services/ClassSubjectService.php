<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ClassSubjectService
{
    public function getByClass(int $classId): Collection
    {
        return ClassSubject::with(['subject', 'classSubjectTeachers.teacher'])
            ->where('class_id', $classId)
            ->get();
    }

    public function assignSubject(int $classId, array $data): ClassSubject
    {
        $class = Classes::findOrFail($classId);

        // Cek apakah mata pelajaran sudah ditambahkan di kelas ini
        $exists = ClassSubject::where('class_id', $classId)
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if ($exists) {
            throw new Exception('Mata pelajaran ini sudah ditambahkan ke dalam kelas.', 422);
        }

        return DB::transaction(function () use ($classId, $data) {
            $classSubject = ClassSubject::create([
                'class_id' => $classId,
                'subject_id' => $data['subject_id'],
                'total_jp' => $data['total_jp'],
                'max_jp_per_day' => $data['max_jp_per_day'],
                'status' => 'active',
            ]);

            foreach ($data['teachers'] as $teacherData) {
                ClassSubjectTeacher::create([
                    'class_subject_id' => $classSubject->id,
                    'teacher_id' => $teacherData['teacher_id'],
                    'jp' => $teacherData['jp'],
                ]);
            }

            return $classSubject->load(['subject', 'classSubjectTeachers.teacher']);
        });
    }

    public function updateAssignment(ClassSubject $classSubject, array $data): ClassSubject
    {
        return DB::transaction(function () use ($classSubject, $data) {
            $classSubject->update([
                'total_jp' => $data['total_jp'],
                'max_jp_per_day' => $data['max_jp_per_day'],
            ]);

            // Sync guru pengampu beserta alokasi JP
            $classSubject->classSubjectTeachers()->delete();

            foreach ($data['teachers'] as $teacherData) {
                ClassSubjectTeacher::create([
                    'class_subject_id' => $classSubject->id,
                    'teacher_id' => $teacherData['teacher_id'],
                    'jp' => $teacherData['jp'],
                ]);
            }

            return $classSubject->fresh(['subject', 'classSubjectTeachers.teacher']);
        });
    }

    public function deleteAssignment(ClassSubject $classSubject): bool
    {
        // Proteksi jika pemetaan mapel ini sudah digunakan di tabel schedules
        $hasSchedules = DB::table('schedules')
            ->whereIn('class_subject_teacher_id', $classSubject->classSubjectTeachers->pluck('id'))
            ->exists();

        if ($hasSchedules) {
            throw new Exception('Tidak dapat menghapus pemetaan mapel yang sudah terdaftar dalam jadwal.', 422);
        }

        return DB::transaction(function () use ($classSubject) {
            $classSubject->classSubjectTeachers()->delete();
            return $classSubject->delete();
        });
    }
}