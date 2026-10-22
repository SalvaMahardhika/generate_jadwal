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
     * Memeriksa apakah guru (atau salah satu guru dalam team teaching) mengalami bentrok jadwal
     * di kelas lain pada rentang jam yang beririsan di hari yang sama (Mendukung Multi-Profil Slot).
     *
     * @param int|array $teacherIds Single ID atau Array ID guru
     */
    public function checkTeacherConflict(
        int|array $teacherIds,
        int $timeSlotId,
        int $academicYearId,
        ?int $excludeScheduleId = null
    ): bool {
        $ids = is_array($teacherIds) ? $teacherIds : [$teacherIds];

        $targetSlot = TimeSlot::find($timeSlotId);
        if (!$targetSlot) {
            return false;
        }

        // Pengecekan beririsan rentang waktu jam KBM pada hari yang sama (start_time < target_end && end_time > target_start)
        return Schedule::query()
            ->where('academic_year_id', $academicYearId)
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->whereHas('classSubjectTeacher', function ($q) use ($ids) {
                $q->whereIn('teacher_id', $ids);
            })
            ->whereHas('timeSlot', function ($q) use ($targetSlot) {
                $q->where('day', $targetSlot->day)
                  ->where('start_time', '<', $targetSlot->end_time)
                  ->where('end_time', '>', $targetSlot->start_time);
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
            $status   = 'balanced';

            if ($assignedJp < $targetJp) {
                $status = 'under';
            } elseif ($assignedJp > $targetJp) {
                $status = 'over';
            }

            $balanceReport[] = [
                'class_subject_id' => $cs->id,
                'subject_code'     => $cs->subject?->subject_code ?? '-',
                'subject_name'     => $cs->subject?->subject_name ?? '-',
                'color_code'       => $cs->subject?->color_code ?? '#cbd5e1',
                'teachers'         => $cs->classSubjectTeachers->map(fn($cst) => $cst->teacher?->name)->filter()->join(', '),
                'target_jp'        => $targetJp,
                'assigned_jp'      => $assignedJp,
                'remaining_jp'     => max(0, $targetJp - $assignedJp),
                'status'           => $status,
            ];
        }

        return $balanceReport;
    }

    /**
     * Data Matriks Jadwal per Guru (Teacher View)
     */
    public function getTeacherMatrixData(int $teacherId, int $academicYearId, ?int $profileId = null): array
    {
        $teacher = Teacher::findOrFail($teacherId);

        $schedules = Schedule::with([
            'class.grade',
            'class.major',
            'class.timeSlotProfile',
            'classSubjectTeacher.classSubject.subject',
            'classSubjectTeacher.teacher',
            'timeSlot',
        ])
        ->where('academic_year_id', $academicYearId)
        ->when($profileId, function ($q) use ($profileId) {
            $q->whereHas('timeSlot', fn($ts) => $ts->where('time_slot_profile_id', $profileId));
        })
        ->whereHas('classSubjectTeacher', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->get()
        ->keyBy('time_slot_id');

        return [
            'teacher'           => $teacher,
            'schedules'         => $schedules,
            'total_assigned_jp' => $schedules->count(),
        ];
    }

    /**
     * Data Matriks Master Seluruh Kelas (Master View)
     */
    public function getMasterMatrixData(int $academicYearId, ?int $profileId = null): array
    {
        $classes = Classes::with(['grade', 'major', 'timeSlotProfile'])
            ->where('academic_year_id', $academicYearId)
            ->when($profileId, fn($q) => $q->where('time_slot_profile_id', $profileId))
            ->where('status', 'active')
            ->get();

        $schedules = Schedule::with([
            'classSubjectTeacher.classSubject.subject',
            'classSubjectTeacher.teacher',
            'timeSlot',
        ])
        ->where('academic_year_id', $academicYearId)
        ->whereIn('class_id', $classes->pluck('id'))
        ->get()
        ->groupBy('class_id');

        $matrix = [];
        foreach ($classes as $cls) {
            $matrix[$cls->id] = $schedules->get($cls->id, collect())->keyBy('time_slot_id');
        }

        return [
            'classes' => $classes,
            'matrix'  => $matrix,
        ];
    }

    /**
     * Eksekusi Algoritma Auto-Generate Jadwal Berbasis Profil Slot Waktu Masing-Masing Kelas
     */
    public function generate(int $academicYearId, ?int $classId = null, int $userId = 1, ?int $maxPeriodNumber = null): array
    {
        return DB::transaction(function () use ($academicYearId, $classId, $userId, $maxPeriodNumber) {
            $classesQuery = Classes::with(['grade', 'timeSlotProfile'])
                ->where('academic_year_id', $academicYearId)
                ->where('status', 'active');

            if ($classId) {
                $classesQuery->where('id', $classId);
            }
            $classes = $classesQuery->get();

            if ($classes->isEmpty()) {
                throw new Exception('Tidak ada kelas aktif yang ditemukan untuk di-generate.', 404);
            }

            // Hitung beban statistik mengajar guru di lintas kelas
            $teacherClassCounts = DB::table('class_subject_teachers')
                ->join('class_subjects', 'class_subjects.id', '=', 'class_subject_teachers.class_subject_id')
                ->select('class_subject_teachers.teacher_id', DB::raw('COUNT(DISTINCT class_subjects.class_id) as class_count'))
                ->groupBy('class_subject_teachers.teacher_id')
                ->pluck('class_count', 'teacher_id');

            // Hapus draft jadwal lama yang TIDAK dikunci
            Schedule::where('academic_year_id', $academicYearId)
                ->when($classId, fn($q) => $q->where('class_id', $classId))
                ->where('is_locked', false)
                ->delete();

            $unplacedPool = [];

            foreach ($classes as $class) {
                $sequence = $class->grade?->sequence ?? 1;

                $limitPeriod = $maxPeriodNumber ?: match ($sequence) {
                    1, 2    => 6,
                    3, 4    => 8,
                    default => 11,
                };

                // Filter slot waktu yang spesifik milik profil kelas ini
                $profileId = $class->time_slot_profile_id;

                $classTimeSlots = TimeSlot::where('is_break', false)
                    ->when($profileId, fn($q) => $q->where('time_slot_profile_id', $profileId))
                    ->orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                    ->orderBy('period_number', 'asc')
                    ->get()
                    ->filter(fn($slot) => $slot->period_number <= $limitPeriod);

                $slotsByDay = $classTimeSlots->groupBy('day');

                $lockedSchedules = Schedule::where('academic_year_id', $academicYearId)
                    ->where('class_id', $class->id)
                    ->get()
                    ->keyBy('time_slot_id');

                $classSubjects = ClassSubject::with('classSubjectTeachers')
                    ->where('class_id', $class->id)
                    ->where('status', 'active')
                    ->get();

                // Catat alokasi harian per mapel/guru
                $dailySubjectUsage = [];
                foreach ($lockedSchedules as $slotId => $sched) {
                    if ($sched->classSubjectTeacher) {
                        $slot = $classTimeSlots->firstWhere('id', $slotId);
                        if ($slot) {
                            $csId = $sched->classSubjectTeacher->class_subject_id;
                            $dailySubjectUsage[$slot->day][$csId] = ($dailySubjectUsage[$slot->day][$csId] ?? 0) + 1;
                        }
                    }
                }

                // Bentuk Paket Blok JP (Block Pool)
                $blockPool = [];

                foreach ($classSubjects as $cs) {
                    if ($cs->classSubjectTeachers->isEmpty()) {
                        continue;
                    }

                    $maxJpPerDay = max(1, $cs->max_jp_per_day);

                    // Proses setiap guru pengampu berdasarkan alokasi JP individunya
                    foreach ($cs->classSubjectTeachers as $cst) {
                        $targetJpForThisTeacher = $cst->jp > 0 ? $cst->jp : $cs->total_jp;

                        $alreadyPlacedCount = $lockedSchedules->filter(function ($sched) use ($cst) {
                            return $sched->class_subject_teacher_id == $cst->id;
                        })->count();

                        $remainingToPlace = $targetJpForThisTeacher - $alreadyPlacedCount;
                        if ($remainingToPlace <= 0) {
                            continue;
                        }

                        $teacherId    = $cst->teacher_id;
                        $isSpecialist = ($teacherClassCounts->get($teacherId, 1) > 1);

                        // Pecah sisa JP guru menjadi blok berurutan
                        while ($remainingToPlace > 0) {
                            $blockSize = min($remainingToPlace, $maxJpPerDay);
                            $blockPool[] = [
                                'class_subject_id'         => $cs->id,
                                'class_subject_teacher_id' => $cst->id,
                                'teacher_id'               => $teacherId,
                                'max_jp_per_day'           => $maxJpPerDay,
                                'size'                     => $blockSize,
                                'is_specialist'            => $isSpecialist,
                            ];
                            $remainingToPlace -= $blockSize;
                        }
                    }
                }

                // Pengurutan Prioritas Penempatan Blok
                usort($blockPool, function ($a, $b) {
                    if ($a['is_specialist'] !== $b['is_specialist']) {
                        return $b['is_specialist'] <=> $a['is_specialist'];
                    }
                    if ($a['size'] !== $b['size']) {
                        return $b['size'] <=> $a['size'];
                    }
                    return 0;
                });

                // Tempatkan Masing-Masing Blok ke Slot Berurutan
                foreach ($blockPool as $block) {
                    $placed = $this->tryPlaceBlock(
                        $block,
                        $slotsByDay,
                        $lockedSchedules,
                        $dailySubjectUsage,
                        $academicYearId,
                        $class->id,
                        $userId
                    );

                    if (!$placed && $block['size'] > 1) {
                        for ($s = 0; $s < $block['size']; $s++) {
                            $singleBlock = $block;
                            $singleBlock['size'] = 1;
                            $singlePlaced = $this->tryPlaceBlock(
                                $singleBlock,
                                $slotsByDay,
                                $lockedSchedules,
                                $dailySubjectUsage,
                                $academicYearId,
                                $class->id,
                                $userId
                            );

                            if (!$singlePlaced) {
                                $unplacedPool[] = [
                                    'class_id'         => $class->id,
                                    'class_name'       => $class->class_name,
                                    'class_subject_id' => $block['class_subject_id'],
                                ];
                            }
                        }
                    } elseif (!$placed) {
                        $unplacedPool[] = [
                            'class_id'         => $class->id,
                            'class_name'       => $class->class_name,
                            'class_subject_id' => $block['class_subject_id'],
                        ];
                    }
                }
            }

            return [
                'success'          => true,
                'unplaced_count'   => count($unplacedPool),
                'unplaced_details' => $unplacedPool,
            ];
        });
    }

    /**
     * Mencoba menempatkan paket blok K jam pelajaran pada slot berurutan di hari yang sama.
     */
    private function tryPlaceBlock(
        array $block,
        Collection $slotsByDay,
        Collection &$lockedSchedules,
        array &$dailySubjectUsage,
        int $academicYearId,
        int $classId,
        int $userId
    ): bool {
        $blockSize   = $block['size'];
        $csId        = $block['class_subject_id'];
        $maxJpPerDay = $block['max_jp_per_day'];
        $teacherId   = $block['teacher_id'];

        $days = $slotsByDay->keys()->toArray();

        usort($days, function ($dayA, $dayB) use ($dailySubjectUsage, $csId) {
            $usageA = $dailySubjectUsage[$dayA][$csId] ?? 0;
            $usageB = $dailySubjectUsage[$dayB][$csId] ?? 0;
            return $usageA <=> $usageB;
        });

        foreach ($days as $day) {
            $daySlots  = $slotsByDay->get($day, collect())->values();
            $slotCount = $daySlots->count();

            if ($slotCount < $blockSize) {
                continue;
            }

            $currentDailyUsage = $dailySubjectUsage[$day][$csId] ?? 0;
            if ($currentDailyUsage + $blockSize > $maxJpPerDay) {
                continue;
            }

            for ($i = 0; $i <= $slotCount - $blockSize; $i++) {
                $candidateSlots = [];
                $canPlace       = true;

                for ($k = 0; $k < $blockSize; $k++) {
                    $slot = $daySlots->get($i + $k);

                    if ($lockedSchedules->has($slot->id)) {
                        $canPlace = false;
                        break;
                    }

                    if ($this->checkTeacherConflict($teacherId, $slot->id, $academicYearId)) {
                        $canPlace = false;
                        break;
                    }

                    $candidateSlots[] = $slot;
                }

                if ($canPlace) {
                    foreach ($candidateSlots as $slot) {
                        Schedule::create([
                            'academic_year_id'         => $academicYearId,
                            'class_id'                 => $classId,
                            'time_slot_id'             => $slot->id,
                            'class_subject_teacher_id' => $block['class_subject_teacher_id'],
                            'is_locked'                => false,
                            'status'                   => 'draft',
                            'created_by'               => $userId,
                        ]);

                        $lockedSchedules->put($slot->id, true);
                    }

                    $dailySubjectUsage[$day][$csId] = $currentDailyUsage + $blockSize;
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Mengunci atau membuka kuncian slot jadwal tertentu.
     */
    public function lockSlot(array $data, int $userId): Schedule
    {
        return DB::transaction(function () use ($data, $userId) {
            $schedule = Schedule::firstOrNew([
                'academic_year_id' => $data['academic_year_id'],
                'class_id'         => $data['class_id'],
                'time_slot_id'     => $data['time_slot_id'],
            ]);

            $schedule->is_locked                = $data['is_locked'];
            $schedule->custom_activity          = $data['custom_activity'] ?? null;
            $schedule->class_subject_teacher_id = $data['class_subject_teacher_id'] ?? null;
            $schedule->created_by               = $userId;
            $schedule->save();

            return $schedule->load(['timeSlot', 'classSubjectTeacher.classSubject.subject', 'classSubjectTeacher.teacher']);
        });
    }

    /**
     * Penempatan / penggeseran jadwal secara manual dengan pengetatan validasi bentrok guru
     * DAN validasi batas kuota JP individu guru.
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
            $cst = ClassSubjectTeacher::with(['teacher', 'classSubject.subject'])->findOrFail($data['class_subject_teacher_id']);

            foreach ($targetClassIds as $targetClassId) {
                // 1. Validasi Bentrok Jam Mengajar Guru di Kelas Lain
                $existingSchedule = Schedule::where('academic_year_id', $data['academic_year_id'])
                    ->where('class_id', $targetClassId)
                    ->where('time_slot_id', $data['time_slot_id'])
                    ->first();

                $excludeId = $existingSchedule?->id;

                $hasConflict = $this->checkTeacherConflict(
                    $cst->teacher_id,
                    $data['time_slot_id'],
                    $data['academic_year_id'],
                    $excludeId
                );

                if ($hasConflict) {
                    $teacherName = $cst->teacher?->name ?? 'Guru';
                    throw new Exception("Gagal assign manual: {$teacherName} sedang mengajar di kelas lain pada slot/rentang waktu ini.", 422);
                }

                // 2. Validasi Batas Kuota JP Guru Individu
                $maxTeacherJp = $cst->jp > 0 ? $cst->jp : ($cst->classSubject?->total_jp ?? 0);

                $currentAssignedCount = Schedule::where('academic_year_id', $data['academic_year_id'])
                    ->where('class_id', $targetClassId)
                    ->where('class_subject_teacher_id', $cst->id)
                    ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                    ->count();

                if ($currentAssignedCount >= $maxTeacherJp) {
                    $teacherName = $cst->teacher?->name ?? 'Guru';
                    $subjectName = $cst->classSubject?->subject?->subject_name ?? 'Mata Pelajaran';
                    throw new Exception("Gagal assign manual: {$teacherName} sudah mencapai batas maksimal alokasi mengajar untuk {$subjectName} ({$maxTeacherJp} JP).", 422);
                }
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
                        'class_id'         => $classId,
                        'time_slot_id'     => $data['time_slot_id'],
                    ],
                    [
                        'class_subject_teacher_id' => $data['class_subject_teacher_id'] ?? null,
                        'custom_activity'          => $data['custom_activity'] ?? null,
                        'is_locked'                => $isLocked,
                        'created_by'               => $userId,
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
     * Mengosongkan sel/slot spesifik.
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