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
     * Mencari slot waktu ekuivalen pada profil kelas sasaran dengan jaminan
     * tidak menimpa slot istirahat (is_break = false) untuk jam pelajaran KBM.
     */
    public function getEquivalentTimeSlot(TimeSlot $sourceSlot, ?int $targetProfileId): ?TimeSlot
    {
        if ($targetProfileId === null || $sourceSlot->time_slot_profile_id == $targetProfileId) {
            return TimeSlot::query()
                ->when($targetProfileId, fn($q) => $q->where('time_slot_profile_id', $targetProfileId))
                ->where('day', $sourceSlot->day)
                ->where('period_number', $sourceSlot->period_number)
                ->first();
        }

        if ($sourceSlot->is_break) {
            $sourceBreakIndex = TimeSlot::where('time_slot_profile_id', $sourceSlot->time_slot_profile_id)
                ->where('day', $sourceSlot->day)
                ->where('is_break', true)
                ->where('period_number', '<=', $sourceSlot->period_number)
                ->count() - 1;

            return TimeSlot::where('time_slot_profile_id', $targetProfileId)
                ->where('day', $sourceSlot->day)
                ->where('is_break', true)
                ->orderBy('period_number', 'asc')
                ->skip(max(0, $sourceBreakIndex))
                ->first();
        }

        $sourceNonBreakIndex = TimeSlot::where('time_slot_profile_id', $sourceSlot->time_slot_profile_id)
            ->where('day', $sourceSlot->day)
            ->where('is_break', false)
            ->where('period_number', '<=', $sourceSlot->period_number)
            ->count() - 1;

        return TimeSlot::where('time_slot_profile_id', $targetProfileId)
            ->where('day', $sourceSlot->day)
            ->where('is_break', false)
            ->orderBy('period_number', 'asc')
            ->skip(max(0, $sourceNonBreakIndex))
            ->first();
    }

    /**
     * Memeriksa bentrok jadwal guru di kelas lain pada rentang waktu beririsan.
     */
    public function checkTeacherConflict(
        int|array $teacherIds,
        int $timeSlotId,
        int $academicYearId,
        ?int $excludeScheduleId = null,
        bool $isJointSubject = false
    ): bool {
        $ids = is_array($teacherIds) ? $teacherIds : [$teacherIds];

        $targetSlot = TimeSlot::find($timeSlotId);
        if (!$targetSlot) {
            return false;
        }

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
            ->when($isJointSubject, function ($q) {
                $q->whereHas('classSubjectTeacher.classSubject.subject', function ($sq) {
                    $sq->where('is_joint_subject', false);
                });
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
                'subject_code'      => $cs->subject?->subject_code ?? '-',
                'subject_name'      => $cs->subject?->subject_name ?? '-',
                'color_code'        => $cs->subject?->color_code ?? '#cbd5e1',
                'teachers'          => $cs->classSubjectTeachers->map(fn($cst) => $cst->teacher?->name)->filter()->join(', '),
                'target_jp'         => $targetJp,
                'assigned_jp'       => $assignedJp,
                'remaining_jp'      => max(0, $targetJp - $assignedJp),
                'status'            => $status,
            ];
        }

        return $balanceReport;
    }

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
        ->keyBy('id'); // Diubah dari keyBy('time_slot_id') ke keyBy('id') agar tidak saling menimpa jika mengajar lintas kelas

        return [
            'teacher'           => $teacher,
            'schedules'         => $schedules,
            'total_assigned_jp' => $schedules->count(),
        ];
    }

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
     * Memecah total JP sesuai standar distribusi kurikulum sekolah.
     */
    private function calculateOptimalBlockSizes(int $totalJp, int $maxJpPerDay): array
    {
        if ($totalJp <= 0) return [];

        switch ($totalJp) {
            case 7:
                return [4, 3];
            case 6:
                return [3, 3];
            case 5:
                return [3, 2];
            case 4:
                return [2, 2];
            case 3:
                return [3];
            case 2:
                return [2];
            case 1:
                return [1];
            default:
                $blocks = [];
                $remaining = $totalJp;
                while ($remaining > 0) {
                    $chunk = min($remaining, $maxJpPerDay > 0 ? $maxJpPerDay : 3);
                    if ($chunk == 1 && count($blocks) > 0) {
                        $blocks[count($blocks) - 1] += 1;
                        $remaining = 0;
                    } else {
                        $blocks[] = $chunk;
                        $remaining -= $chunk;
                    }
                }
                return $blocks;
        }
    }

    /**
     * Eksekusi Algoritma Auto-Generate Jadwal Berstandar Sekolah.
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
                $profileId = $class->time_slot_profile_id;

                $classTimeSlots = TimeSlot::where('is_break', false)
                    ->when($profileId, fn($q) => $q->where('time_slot_profile_id', $profileId))
                    ->when($maxPeriodNumber, fn($q) => $q->where('period_number', '<=', $maxPeriodNumber))
                    ->orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                    ->orderBy('period_number', 'asc')
                    ->get();

                $slotsByDay = $classTimeSlots->groupBy('day');

                $lockedSchedules = Schedule::where('academic_year_id', $academicYearId)
                    ->where('class_id', $class->id)
                    ->get()
                    ->keyBy('time_slot_id');

                $classSubjects = ClassSubject::with(['classSubjectTeachers', 'subject'])
                    ->where('class_id', $class->id)
                    ->where('status', 'active')
                    ->get();

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

                $blockPool = [];

                foreach ($classSubjects as $cs) {
                    if ($cs->classSubjectTeachers->isEmpty()) {
                        continue;
                    }

                    $subjectName    = strtolower($cs->subject?->subject_name ?? '');
                    $isJointSubject = (bool) ($cs->subject?->is_joint_subject ?? false);
                    $isPjok         = str_contains($subjectName, 'pjok') || str_contains($subjectName, 'olahraga');

                    foreach ($cs->classSubjectTeachers as $cst) {
                        $targetJpForThisTeacher = $cst->jp > 0 ? $cst->jp : $cs->total_jp;

                        $alreadyPlacedCount = $lockedSchedules->filter(function ($sched) use ($cst) {
                            return is_object($sched) && $sched->class_subject_teacher_id == $cst->id;
                        })->count();

                        $remainingToPlace = $targetJpForThisTeacher - $alreadyPlacedCount;
                        if ($remainingToPlace <= 0) {
                            continue;
                        }

                        $maxJpPerDay  = ($cs->max_jp_per_day > 0) ? $cs->max_jp_per_day : $targetJpForThisTeacher;
                        $teacherId    = $cst->teacher_id;
                        $isSpecialist = ($teacherClassCounts->get($teacherId, 1) > 1);

                        // Gunakan alokasi blok standar kurikulum
                        $optimalBlocks = $this->calculateOptimalBlockSizes($remainingToPlace, $maxJpPerDay);

                        foreach ($optimalBlocks as $blockSize) {
                            $blockPool[] = [
                                'class_subject_id'         => $cs->id,
                                'class_subject_teacher_id' => $cst->id,
                                'teacher_id'               => $teacherId,
                                'max_jp_per_day'           => $maxJpPerDay,
                                'size'                      => $blockSize,
                                'is_specialist'             => $isSpecialist,
                                'is_joint_subject'         => $isJointSubject,
                                'is_pjok'                  => $isPjok,
                                'random_weight'            => rand(1, 1000),
                            ];
                        }
                    }
                }

                // Prioritas Antrean: PJOK -> Guru Spesialis -> Ukuran Blok Terbesar -> Variasi Acak
                usort($blockPool, function ($a, $b) {
                    if ($a['is_pjok'] !== $b['is_pjok']) {
                        return $b['is_pjok'] <=> $a['is_pjok'];
                    }
                    if ($a['is_specialist'] !== $b['is_specialist']) {
                        return $b['is_specialist'] <=> $a['is_specialist'];
                    }
                    if ($a['size'] !== $b['size']) {
                        return $b['size'] <=> $a['size'];
                    }
                    return $a['random_weight'] <=> $b['random_weight'];
                });

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

                    if (!$placed) {
                        $unplacedPool[] = [
                            'class_id'         => $class->id,
                            'class_name'       => $class->class_name,
                            'class_subject_id' => $block['class_subject_id'],
                            'size'             => $block['size'],
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
     * Menempatkan 1 blok jam pelajaran secara rapat urut dari jam terawal (mengisi slot kosong sebelum istirahat).
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
        $blockSize      = $block['size'];
        $csId           = $block['class_subject_id'];
        $teacherId      = $block['teacher_id'];
        $isJointSubject = (bool) ($block['is_joint_subject'] ?? false);
        $isPjok         = (bool) ($block['is_pjok'] ?? false);

        $days = $slotsByDay->keys()->toArray();
        shuffle($days);

        foreach ($days as $day) {
            // ATURAN KETAT: 1 Mapel hanya boleh diajarkan 1 kali per hari (maksimal 1 sesi)
            if (isset($dailySubjectUsage[$day][$csId]) && $dailySubjectUsage[$day][$csId] > 0) {
                continue;
            }

            $daySlots  = $slotsByDay->get($day, collect())->values();
            $slotCount = $daySlots->count();

            if ($slotCount < $blockSize) {
                continue;
            }

            // PENCARIAN SLOT RAPAT DARI JAM TERAWAL (JAM PERTAMA KBM KE BAWAH)
            for ($i = 0; $i <= $slotCount - $blockSize; $i++) {
                $candidateSlots = [];
                $canPlace       = true;

                // Khusus Olahraga (PJOK): Harus berada di jam pagi (period_number <= 4)
                if ($isPjok) {
                    $firstSlot = $daySlots->get($i);
                    if ($firstSlot->period_number > 4) {
                        $canPlace = false;
                        break;
                    }
                }

                for ($k = 0; $k < $blockSize; $k++) {
                    $slot = $daySlots->get($i + $k);

                    // Cek kuncian slot
                    if ($lockedSchedules->has($slot->id)) {
                        $canPlace = false;
                        break;
                    }

                    // Cek bentrok jadwal guru di kelas lain
                    if ($this->checkTeacherConflict($teacherId, $slot->id, $academicYearId, null, $isJointSubject)) {
                        $canPlace = false;
                        break;
                    }

                    $candidateSlots[] = $slot;
                }

                if ($canPlace) {
                    foreach ($candidateSlots as $slot) {
                        $createdSchedule = Schedule::create([
                            'academic_year_id'         => $academicYearId,
                            'class_id'                 => $classId,
                            'time_slot_id'             => $slot->id,
                            'class_subject_teacher_id' => $block['class_subject_teacher_id'],
                            'is_locked'                => false,
                            'status'                   => 'draft',
                            'created_by'               => $userId,
                        ]);

                        $lockedSchedules->put($slot->id, $createdSchedule);
                    }

                    $dailySubjectUsage[$day][$csId] = ($dailySubjectUsage[$day][$csId] ?? 0) + $blockSize;
                    return true;
                }
            }
        }

        return false;
    }

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
            $schedule->created_by                = $userId;
            $schedule->save();

            return $schedule->load(['timeSlot', 'classSubjectTeacher.classSubject.subject', 'classSubjectTeacher.teacher']);
        });
    }

    public function manualAssign(array $data, int $userId): ?Schedule
    {
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
        $sourceSlot = TimeSlot::findOrFail($data['time_slot_id']);

        if ($applyToAll) {
            $targetClasses = Classes::where('academic_year_id', $data['academic_year_id'])
                ->where('status', 'active')
                ->get();
        } else {
            $targetClasses = Classes::where('id', $data['class_id'])->get();
        }

        $targetCstMap = [];
        if (!empty($data['class_subject_teacher_id'])) {
            $sourceCst = ClassSubjectTeacher::with(['teacher', 'classSubject.subject'])->findOrFail($data['class_subject_teacher_id']);

            foreach ($targetClasses as $targetClass) {
                if ($targetClass->id == $data['class_id']) {
                    $targetCstMap[$targetClass->id] = $sourceCst;
                } else {
                    $foundCst = ClassSubjectTeacher::with(['teacher', 'classSubject.subject'])
                        ->whereHas('classSubject', function ($q) use ($targetClass, $sourceCst) {
                            $q->where('class_id', $targetClass->id)
                              ->where('subject_id', $sourceCst->classSubject->subject_id);
                        })
                        ->where('teacher_id', $sourceCst->teacher_id)
                        ->first();

                    if (!$foundCst) {
                        $foundCst = ClassSubjectTeacher::with(['teacher', 'classSubject.subject'])
                            ->whereHas('classSubject', function ($q) use ($targetClass, $sourceCst) {
                                $q->where('class_id', $targetClass->id)
                                  ->where('subject_id', $sourceCst->classSubject->subject_id);
                            })->first();
                    }

                    $targetCstMap[$targetClass->id] = $foundCst;
                }
            }
        }

        foreach ($targetClasses as $targetClass) {
            $targetSlot = $this->getEquivalentTimeSlot($sourceSlot, $targetClass->time_slot_profile_id);

            if (!$targetSlot) {
                continue;
            }

            $targetCst = $targetCstMap[$targetClass->id] ?? null;

            if ($targetCst) {
                $existingSchedule = Schedule::where('academic_year_id', $data['academic_year_id'])
                    ->where('class_id', $targetClass->id)
                    ->where('time_slot_id', $targetSlot->id)
                    ->first();

                $excludeId = $existingSchedule?->id;

                if ($targetCst->teacher_id) {
                    $isJointSubject = (bool) ($targetCst->classSubject?->subject?->is_joint_subject ?? false);

                    $hasConflict = $this->checkTeacherConflict(
                        $targetCst->teacher_id,
                        $targetSlot->id,
                        $data['academic_year_id'],
                        $excludeId,
                        $isJointSubject
                    );

                    if ($hasConflict) {
                        $teacherName = $targetCst->teacher?->name ?? 'Guru';
                        throw new Exception("Gagal assign manual: {$teacherName} sedang mengajar di kelas lain pada slot/rentang waktu ini.", 422);
                    }
                }

                $maxTeacherJp = $targetCst->jp > 0 ? $targetCst->jp : ($targetCst->classSubject?->total_jp ?? 0);

                $currentAssignedCount = Schedule::where('academic_year_id', $data['academic_year_id'])
                    ->where('class_id', $targetClass->id)
                    ->where('class_subject_teacher_id', $targetCst->id)
                    ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                    ->count();

                if ($currentAssignedCount >= $maxTeacherJp) {
                    $teacherName = $targetCst->teacher?->name ?? 'Guru';
                    $subjectName = $targetCst->classSubject?->subject?->subject_name ?? 'Mata Pelajaran';
                    throw new Exception("Gagal assign manual: {$teacherName} sudah mencapai batas maksimal alokasi mengajar untuk {$subjectName} ({$maxTeacherJp} JP).", 422);
                }
            }
        }

        return DB::transaction(function () use ($data, $targetClasses, $sourceSlot, $targetCstMap, $userId) {
            $isLocked = array_key_exists('is_locked', $data)
                ? (bool) $data['is_locked']
                : true;

            $currentSchedule = null;

            foreach ($targetClasses as $targetClass) {
                $targetSlot = $this->getEquivalentTimeSlot($sourceSlot, $targetClass->time_slot_profile_id);

                if (!$targetSlot) {
                    continue;
                }

                $targetCst = $targetCstMap[$targetClass->id] ?? null;

                $schedule = Schedule::updateOrCreate(
                    [
                        'academic_year_id' => $data['academic_year_id'],
                        'class_id'         => $targetClass->id,
                        'time_slot_id'     => $targetSlot->id,
                    ],
                    [
                        'class_subject_teacher_id' => $targetCst?->id,
                        'custom_activity'          => $data['custom_activity'] ?? null,
                        'is_locked'                => $isLocked,
                        'created_by'               => $userId,
                    ]
                );

                if ($targetClass->id == $data['class_id']) {
                    $currentSchedule = $schedule;
                }
            }

            return $currentSchedule ? $currentSchedule->load(['timeSlot', 'classSubjectTeacher.classSubject.subject', 'classSubjectTeacher.teacher']) : null;
        });
    }

    public function clearSlot(int $academicYearId, int $classId, int $timeSlotId, bool $clearAllClasses = false): bool
    {
        if (!$clearAllClasses) {
            return Schedule::where('academic_year_id', $academicYearId)
                ->where('class_id', $classId)
                ->where('time_slot_id', $timeSlotId)
                ->delete() > 0;
        }

        $sourceSlot = TimeSlot::find($timeSlotId);
        if (!$sourceSlot) {
            return false;
        }

        $classes = Classes::where('academic_year_id', $academicYearId)->where('status', 'active')->get();
        $equivalentSlotIds = [];

        foreach ($classes as $cls) {
            $eqSlot = $this->getEquivalentTimeSlot($sourceSlot, $cls->time_slot_profile_id);
            if ($eqSlot) {
                $equivalentSlotIds[] = $eqSlot->id;
            }
        }

        if (empty($equivalentSlotIds)) {
            return false;
        }

        return Schedule::where('academic_year_id', $academicYearId)
            ->whereIn('time_slot_id', array_unique($equivalentSlotIds))
            ->delete() > 0;
    }

    public function resetSchedule(int $academicYearId, ?int $classId = null): int
    {
        return Schedule::where('academic_year_id', $academicYearId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->where('is_locked', false)
            ->delete();
    }

    public function publish(int $academicYearId, ?int $classId = null): int
    {
        return Schedule::where('academic_year_id', $academicYearId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->update(['status' => 'published']);
    }

    public function unpublish(int $academicYearId, ?int $classId = null): int
    {
        return Schedule::where('academic_year_id', $academicYearId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->update(['status' => 'draft']);
    }
}