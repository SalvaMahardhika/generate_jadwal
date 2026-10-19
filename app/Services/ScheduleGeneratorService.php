<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\Teacher;
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
                'subject_code' => $cs->subject?->subject_code ?? '-',
                'subject_name' => $cs->subject?->subject_name ?? '-',
                'color_code' => $cs->subject?->color_code ?? '#cbd5e1',
                'teachers' => $cs->classSubjectTeachers->map(fn($cst) => $cst->teacher?->name)->filter()->join(', '),
                'target_jp' => $targetJp,
                'assigned_jp' => $assignedJp,
                'remaining_jp' => max(0, $targetJp - $assignedJp),
                'status' => $status,
            ];
        }

        return $balanceReport;
    }

    /**
     * Data Matriks Jadwal per Guru (Teacher View)
     */
    public function getTeacherMatrixData(int $teacherId, int $academicYearId): array
    {
        $teacher = Teacher::findOrFail($teacherId);

        $schedules = Schedule::with([
            'class.grade',
            'class.major',
            'classSubjectTeacher.classSubject.subject',
            'timeSlot',
        ])
        ->where('academic_year_id', $academicYearId)
        ->whereHas('classSubjectTeacher', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->get()
        ->keyBy('time_slot_id');

        return [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'total_assigned_jp' => $schedules->count(),
        ];
    }

    /**
     * Data Matriks Master Seluruh Kelas (Master View)
     */
    public function getMasterMatrixData(int $academicYearId): array
    {
        $classes = Classes::with(['grade', 'major'])
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->get();

        $schedules = Schedule::with([
            'classSubjectTeacher.classSubject.subject',
            'classSubjectTeacher.teacher',
        ])
        ->where('academic_year_id', $academicYearId)
        ->get()
        ->groupBy('class_id');

        $matrix = [];
        foreach ($classes as $cls) {
            $matrix[$cls->id] = $schedules->get($cls->id, collect())->keyBy('time_slot_id');
        }

        return [
            'classes' => $classes,
            'matrix' => $matrix,
        ];
    }

    /**
     * Eksekusi Algoritma Auto-Generate Jadwal.
     */
    public function generate(int $academicYearId, ?int $classId = null, int $userId = 1, ?int $maxPeriodNumber = null): array
    {
        return DB::transaction(function () use ($academicYearId, $classId, $userId, $maxPeriodNumber) {
            $classesQuery = Classes::with('grade')
                ->where('academic_year_id', $academicYearId)
                ->where('status', 'active');

            if ($classId) {
                $classesQuery->where('id', $classId);
            }
            $classes = $classesQuery->get();

            if ($classes->isEmpty()) {
                throw new Exception('Tidak ada kelas aktif yang ditemukan untuk di-generate.', 404);
            }

            // Urutkan period_number ASC terlebih dahulu agar JP terbagi merata dari Senin sampai Jumat
            $allTimeSlots = TimeSlot::where('is_break', false)
                ->orderBy('period_number', 'asc')
                ->orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                ->get();

            // Hapus draft jadwal lama yang TIDAK dikunci
            Schedule::where('academic_year_id', $academicYearId)
                ->when($classId, fn($q) => $q->where('class_id', $classId))
                ->where('is_locked', false)
                ->delete();

            $unplacedPool = [];

            foreach ($classes as $class) {
                $sequence = $class->grade?->sequence ?? 1;

                // Tentukan batas jam: gunakan input kustom dari Wizard jika dikirim, atau fallback ke default jenjang
                $limitPeriod = $maxPeriodNumber ?: match ($sequence) {
                    1, 2    => 6,
                    3, 4    => 8,
                    default => 11,
                };

                // Filter slot waktu sesuai batas jam pelajaran per hari
                $timeSlots = $allTimeSlots->filter(fn($slot) => $slot->period_number <= $limitPeriod);

                $lockedSchedules = Schedule::where('academic_year_id', $academicYearId)
                    ->where('class_id', $class->id)
                    ->get()
                    ->keyBy('time_slot_id');

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

                shuffle($jpPool);

                $dailySubjectUsage = [];
                foreach ($lockedSchedules as $slotId => $sched) {
                    if ($sched->classSubjectTeacher) {
                        $slot = $allTimeSlots->firstWhere('id', $slotId);
                        if ($slot) {
                            $csId = $sched->classSubjectTeacher->class_subject_id;
                            $dailySubjectUsage[$slot->day][$csId] = ($dailySubjectUsage[$slot->day][$csId] ?? 0) + 1;
                        }
                    }
                }

                foreach ($jpPool as $item) {
                    $placed = false;

                    foreach ($timeSlots as $slot) {
                        if ($lockedSchedules->has($slot->id)) {
                            continue;
                        }

                        $currentDailyUsage = $dailySubjectUsage[$slot->day][$item['class_subject_id']] ?? 0;
                        if ($currentDailyUsage >= $item['max_jp_per_day']) {
                            continue;
                        }

                        $hasConflict = $this->checkTeacherConflict(
                            $item['teacher_id'],
                            $slot->id,
                            $academicYearId
                        );

                        if (!$hasConflict) {
                            Schedule::create([
                                'academic_year_id' => $academicYearId,
                                'class_id' => $class->id,
                                'time_slot_id' => $slot->id,
                                'class_subject_teacher_id' => $item['class_subject_teacher_id'],
                                'is_locked' => false,
                                'status' => 'draft',
                                'created_by' => $userId,
                            ]);

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
     * Penempatan / penggeseran jadwal secara manual.
     */
    public function manualAssign(array $data, int $userId): ?Schedule
    {
        // Jika dikirim kosong, maka kosongkan slot
        if (empty($data['class_subject_teacher_id']) && empty($data['custom_activity'])) {
            $this->clearSlot(
                $data['academic_year_id'],
                $data['class_id'],
                $data['time_slot_id'],
                !empty($data['apply_to_all_classes'])
            );
            return null;
        }

        $applyToAll = !empty($data['apply_to_all_classes']);

        $targetClassIds = [];
        if ($applyToAll) {
            $targetClassIds = Classes::where('academic_year_id', $data['academic_year_id'])
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
        } else {
            $targetClassIds = [$data['class_id']];
        }

        if (!empty($data['class_subject_teacher_id'])) {
            $cst = ClassSubjectTeacher::findOrFail($data['class_subject_teacher_id']);

            $hasConflict = $this->checkTeacherConflict(
                $cst->teacher_id,
                $data['time_slot_id'],
                $data['academic_year_id']
            );

            if ($hasConflict) {
                throw new Exception('Gagal assign manual: Guru pengampu sedang mengajar di kelas lain pada slot waktu ini.', 422);
            }
        }

        return DB::transaction(function () use ($data, $targetClassIds, $userId) {
            $isLocked = array_key_exists('is_locked', $data)
                ? (bool) $data['is_locked']
                : !empty($data['custom_activity']);

            $currentSchedule = null;

            foreach ($targetClassIds as $classId) {
                $schedule = Schedule::updateOrCreate(
                    [
                        'academic_year_id' => $data['academic_year_id'],
                        'class_id' => $classId,
                        'time_slot_id' => $data['time_slot_id'],
                    ],
                    [
                        'class_subject_teacher_id' => $data['class_subject_teacher_id'] ?? null,
                        'custom_activity' => $data['custom_activity'] ?? null,
                        'is_locked' => $isLocked,
                        'created_by' => $userId,
                    ]
                );

                if ($classId == $data['class_id']) {
                    $currentSchedule = $schedule;
                }
            }

            return $currentSchedule ? $currentSchedule->load(['timeSlot', 'classSubjectTeacher.classSubject.subject', 'classSubjectTeacher.teacher']) : null;
        });
    }

    /**
     * Mengosongkan sel/slot spesifik (mendukung opsi hapus di semua kelas dan mengabaikan kuncian saat dihapus manual oleh admin).
     */
    public function clearSlot(int $academicYearId, int $classId, int $timeSlotId, bool $clearAllClasses = false): bool
    {
        return Schedule::where('academic_year_id', $academicYearId)
            ->where('time_slot_id', $timeSlotId)
            ->when(!$clearAllClasses, fn($q) => $q->where('class_id', $classId))
            ->delete() > 0;
    }

    /**
     * Reset / Hapus seluruh jadwal yang tidak dikunci.
     */
    public function resetSchedule(int $academicYearId, ?int $classId = null): int
    {
        return Schedule::where('academic_year_id', $academicYearId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->where('is_locked', false)
            ->delete();
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

    /**
     * Mengembalikan status jadwal dari PUBLISHED kembali menjadi DRAFT.
     */
    public function unpublish(int $academicYearId, ?int $classId = null): int
    {
        return Schedule::where('academic_year_id', $academicYearId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->update(['status' => 'draft']);
    }
}