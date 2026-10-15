<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\Schedule;
use App\Models\TimeSlot;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleGeneratorService
{
    /**
     * Memeriksa apakah guru mengalami bentrok jadwal di kelas lain pada slot waktu yang sama.
     */
    public function checkTeacherConflict(
        int $teacherId,
        int $timeSlotId,
        int $academicYearId,
        ?int $excludeScheduleId = null
    ): bool {
        return Schedule::query()
            ->where('academic_year_id', $academicYearId)
            ->where('time_slot_id', $timeSlotId)
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->whereHas('classSubjectTeacher', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->exists();
    }

    /**
     * Menghitung keseimbangan (balance) beban JP mapel per kelas.
     */
    public function getClassBalance(int $classId, int $academicYearId): array
    {
        $classSubjects = ClassSubject::with(['subject', 'classSubjectTeachers.teacher'])
            ->where('class_id', $classId)
            ->get();

        $balanceReport = [];

        foreach ($classSubjects as $cs) {
            $cstIds = $cs->classSubjectTeachers->pluck('id');

            $assignedJp = Schedule::where('academic_year_id', $academicYearId)
                ->where('class_id', $classId)
                ->whereIn('class_subject_teacher_id', $cstIds)
                ->count();

            $targetJp = $cs->total_jp;
            $status = 'balanced';

            if ($assignedJp < $targetJp) {
                $status = 'under';
            } elseif ($assignedJp > $targetJp) {
                $status = 'over';
            }

            $balanceReport[] = [
                'class_subject_id' => $cs->id,
                'subject_code' => $cs->subject->subject_code,
                'subject_name' => $cs->subject->subject_name,
                'color_code' => $cs->subject->color_code,
                'teachers' => $cs->classSubjectTeachers->map(fn($cst) => $cst->teacher->name)->join(', '),
                'target_jp' => $targetJp,
                'assigned_jp' => $assignedJp,
                'remaining_jp' => max(0, $targetJp - $assignedJp),
                'status' => $status,
            ];
        }

        return $balanceReport;
    }

    /**
     * Eksekusi Algoritma Auto-Generate Jadwal.
     */
    public function generate(int $academicYearId, ?int $classId = null, int $userId = 1): array
    {
        return DB::transaction(function () use ($academicYearId, $classId, $userId) {
            $classesQuery = Classes::where('academic_year_id', $academicYearId)->where('status', 'active');
            if ($classId) {
                $classesQuery->where('id', $classId);
            }
            $classes = $classesQuery->get();

            if ($classes->isEmpty()) {
                throw new Exception('Tidak ada kelas aktif yang ditemukan untuk di-generate.', 404);
            }

            // 1. Ambil slot waktu non-istirahat
            $timeSlots = TimeSlot::where('is_break', false)
                ->orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                ->orderBy('period_number', 'asc')
                ->get();

            // 2. Hapus draft jadwal lama yang TIDAK dikunci (is_locked = false)
            Schedule::where('academic_year_id', $academicYearId)
                ->when($classId, fn($q) => $q->where('class_id', $classId))
                ->where('is_locked', false)
                ->delete();

            $unplacedPool = [];

            foreach ($classes as $class) {
                // Ambil slot jam yang sudah dikunci untuk kelas ini
                $lockedSchedules = Schedule::where('academic_year_id', $academicYearId)
                    ->where('class_id', $class->id)
                    ->get()
                    ->keyBy('time_slot_id');

                // Siapkan kumpulan (pool) JP yang harus ditempatkan
                $jpPool = [];
                $classSubjects = ClassSubject::with('classSubjectTeachers')
                    ->where('class_id', $class->id)
                    ->where('status', 'active')
                    ->get();

                foreach ($classSubjects as $cs) {
                    $cst = $cs->classSubjectTeachers->first();
                    if (!$cst) {
                        continue;
                    }

                    // Hitung berapa JP yang sudah terpasang lewat slot dikunci
                    $alreadyPlacedCount = $lockedSchedules->filter(function ($sched) use ($cs) {
                        return $sched->classSubjectTeacher && $sched->classSubjectTeacher->class_subject_id == $cs->id;
                    })->count();

                    $remainingToPlace = $cs->total_jp - $alreadyPlacedCount;

                    for ($i = 0; $i < $remainingToPlace; $i++) {
                        $jpPool[] = [
                            'class_subject_id' => $cs->id,
                            'class_subject_teacher_id' => $cst->id,
                            'teacher_id' => $cst->teacher_id,
                            'max_jp_per_day' => $cs->max_jp_per_day,
                        ];
                    }
                }

                // Acak urutan pool JP untuk pola penataan yang bervariasi
                shuffle($jpPool);

                // Tracking penggunaan JP per mapel per hari untuk mematuhi max_jp_per_day
                $dailySubjectUsage = [];

                // Inisialisasi penghitungan awal dari slot yang sudah dikunci
                foreach ($lockedSchedules as $slotId => $sched) {
                    if ($sched->classSubjectTeacher) {
                        $slot = $timeSlots->firstWhere('id', $slotId);
                        if ($slot) {
                            $csId = $sched->classSubjectTeacher->class_subject_id;
                            $dailySubjectUsage[$slot->day][$csId] = ($dailySubjectUsage[$slot->day][$csId] ?? 0) + 1;
                        }
                    }
                }

                // 3. Proses Penempatan JP ke Slot Kosong
                foreach ($jpPool as $item) {
                    $placed = false;

                    foreach ($timeSlots as $slot) {
                        // Jika slot ini sudah terisi jadwal terkunci, lewati
                        if ($lockedSchedules->has($slot->id)) {
                            continue;
                        }

                        // Cek batas harian mapel
                        $currentDailyUsage = $dailySubjectUsage[$slot->day][$item['class_subject_id']] ?? 0;
                        if ($currentDailyUsage >= $item['max_jp_per_day']) {
                            continue;
                        }

                        // Cek konflik guru di kelas lain pada slot waktu yang sama
                        $hasConflict = $this->checkTeacherConflict(
                            $item['teacher_id'],
                            $slot->id,
                            $academicYearId
                        );

                        if (!$hasConflict) {
                            // Simpan jadwal baru
                            Schedule::create([
                                'academic_year_id' => $academicYearId,
                                'class_id' => $class->id,
                                'time_slot_id' => $slot->id,
                                'class_subject_teacher_id' => $item['class_subject_teacher_id'],
                                'is_locked' => false,
                                'status' => 'draft',
                                'created_by' => $userId,
                            ]);

                            // Catat slot sebagai terisi
                            $lockedSchedules->put($slot->id, true);
                            $dailySubjectUsage[$slot->day][$item['class_subject_id']] = $currentDailyUsage + 1;
                            $placed = true;
                            break;
                        }
                    }

                    if (!$placed) {
                        $unplacedPool[] = [
                            'class_id' => $class->id,
                            'class_name' => $class->class_name,
                            'class_subject_id' => $item['class_subject_id'],
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'unplaced_count' => count($unplacedPool),
                'unplaced_details' => $unplacedPool,
            ];
        });
    }

    /**
     * Mengunci atau membuka kuncian slot jadwal tertentu.
     */
    public function lockSlot(array $data, int $userId): Schedule
    {
        return DB::transaction(function () use ($data, $userId) {
            $schedule = Schedule::firstOrNew([
                'academic_year_id' => $data['academic_year_id'],
                'class_id' => $data['class_id'],
                'time_slot_id' => $data['time_slot_id'],
            ]);

            $schedule->is_locked = $data['is_locked'];
            $schedule->custom_activity = $data['custom_activity'] ?? null;
            $schedule->class_subject_teacher_id = $data['class_subject_teacher_id'] ?? null;
            $schedule->created_by = $userId;
            $schedule->save();

            return $schedule->load(['timeSlot', 'classSubjectTeacher.classSubject.subject', 'classSubjectTeacher.teacher']);
        });
    }

    /**
     * Melakukan penempatan / penggeseran jadwal secara manual oleh admin.
     */
    public function manualAssign(array $data, int $userId): Schedule
    {
        if (!empty($data['class_subject_teacher_id'])) {
            $cst = ClassSubjectTeacher::findOrFail($data['class_subject_teacher_id']);

            // Cek bentrok guru di kelas lain
            $hasConflict = $this->checkTeacherConflict(
                $cst->teacher_id,
                $data['time_slot_id'],
                $data['academic_year_id']
            );

            if ($hasConflict) {
                throw new Exception('Gagal assign manual: Guru pengampu sedang mengajar di kelas lain pada slot waktu ini.', 422);
            }
        }

        return DB::transaction(function () use ($data, $userId) {
            $schedule = Schedule::updateOrCreate(
                [
                    'academic_year_id' => $data['academic_year_id'],
                    'class_id' => $data['class_id'],
                    'time_slot_id' => $data['time_slot_id'],
                ],
                [
                    'class_subject_teacher_id' => $data['class_subject_teacher_id'] ?? null,
                    'custom_activity' => $data['custom_activity'] ?? null,
                    'created_by' => $userId,
                ]
            );

            return $schedule->load(['timeSlot', 'classSubjectTeacher.classSubject.subject', 'classSubjectTeacher.teacher']);
        });
    }

    /**
     * Mempublikasikan jadwal dari DRAFT menjadi PUBLISHED.
     */
    public function publish(int $academicYearId, ?int $classId = null): int
    {
        return Schedule::where('academic_year_id', $academicYearId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->update(['status' => 'published']);
    }
}