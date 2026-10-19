<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\GenerateScheduleRequest;
use App\Http\Requests\Schedule\LockSlotRequest;
use App\Http\Requests\Schedule\ManualAssignScheduleRequest;
use App\Models\Classes;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Services\ScheduleGeneratorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleWizardController extends Controller
{
    public function __construct(
        protected ScheduleGeneratorService $generatorService
    ) {}

    /**
     * 1. Class View: Matriks jadwal per kelas.
     */
    public function getMatrix(Request $request, Classes $class): JsonResponse
    {
        $academicYearId = $request->query('academic_year_id', $class->academic_year_id);

        $timeSlots = TimeSlot::orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('period_number', 'asc')
            ->get();

        $schedules = Schedule::with([
            'classSubjectTeacher.classSubject.subject',
            'classSubjectTeacher.teacher',
        ])
        ->where('academic_year_id', $academicYearId)
        ->where('class_id', $class->id)
        ->get()
        ->keyBy('time_slot_id');

        $balance = $this->generatorService->getClassBalance($class->id, $academicYearId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'class' => $class->load(['grade', 'major', 'academicYear']),
                'time_slots' => $timeSlots,
                'schedules' => $schedules,
                'balance' => $balance,
            ],
        ]);
    }

    /**
     * 2. Teacher View: Matriks jadwal per guru.
     */
    public function getTeacherMatrix(Request $request, Teacher $teacher): JsonResponse
    {
        $academicYearId = $request->query('academic_year_id');
        if (!$academicYearId) {
            return response()->json(['status' => 'error', 'message' => 'Academic year ID wajib diisi.'], 422);
        }

        $timeSlots = TimeSlot::orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('period_number', 'asc')
            ->get();

        $data = $this->generatorService->getTeacherMatrixData($teacher->id, (int)$academicYearId);

        return response()->json([
            'status' => 'success',
            'data' => array_merge($data, ['time_slots' => $timeSlots]),
        ]);
    }

    /**
     * 3. Master View: Matriks jadwal makro seluruh kelas.
     */
    public function getMasterMatrix(Request $request): JsonResponse
    {
        $academicYearId = $request->query('academic_year_id');
        if (!$academicYearId) {
            return response()->json(['status' => 'error', 'message' => 'Academic year ID wajib diisi.'], 422);
        }

        $timeSlots = TimeSlot::orderByRaw("FIELD(day, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('period_number', 'asc')
            ->get();

        $data = $this->generatorService->getMasterMatrixData((int)$academicYearId);

        return response()->json([
            'status' => 'success',
            'data' => array_merge($data, ['time_slots' => $timeSlots]),
        ]);
    }

    /**
     * Mengambil status keseimbangan JP (balance) saja.
     */
    public function getBalance(Request $request, Classes $class): JsonResponse
    {
        $academicYearId = $request->query('academic_year_id', $class->academic_year_id);
        $balance = $this->generatorService->getClassBalance($class->id, $academicYearId);

        return response()->json([
            'status' => 'success',
            'data' => $balance,
        ]);
    }

    /**
     * Auto-generate jadwal otomatis.
     */
    public function generate(GenerateScheduleRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()?->id ?? 1;
            $result = $this->generatorService->generate(
                $request->input('academic_year_id'),
                $request->input('class_id'),
                $userId
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Proses auto-generate jadwal berhasil diselesaikan.',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Mengunci atau membuka kuncian slot jadwal harian.
     */
    public function lockSlot(LockSlotRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()?->id ?? 1;
            $schedule = $this->generatorService->lockSlot($request->validated(), $userId);

            return response()->json([
                'status' => 'success',
                'message' => 'Status kuncian slot berhasil diperbarui.',
                'data' => $schedule,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Menempatkan/mengubah mapel secara manual.
     */
    public function manualAssign(ManualAssignScheduleRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()?->id ?? 1;
            $schedule = $this->generatorService->manualAssign($request->validated(), $userId);

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal berhasil diperbarui secara manual.',
                'data' => $schedule,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Mengosongkan 1 slot sel secara manual.
     */
    public function clearSlot(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'clear_all_classes' => ['nullable', 'boolean'],
        ]);

        $cleared = $this->generatorService->clearSlot(
            $request->input('academic_year_id'),
            $request->input('class_id'),
            $request->input('time_slot_id'),
            (bool) $request->input('clear_all_classes', false)
        );

        return response()->json([
            'status' => 'success',
            'message' => $cleared ? 'Slot berhasil dikosongkan.' : 'Slot sudah kosong.',
        ]);
    }

    /**
     * Reset / Hapus seluruh slot yang tidak terkunci.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $affected = $this->generatorService->resetSchedule(
            $request->input('academic_year_id'),
            $request->input('class_id')
        );

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menghapus {$affected} entri jadwal yang tidak dikunci.",
        ]);
    }

    /**
     * Mengunci dan mempublikasikan jadwal secara resmi.
     */
    public function publish(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $affected = $this->generatorService->publish(
            $request->input('academic_year_id'),
            $request->input('class_id')
        );

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil mempublikasikan {$affected} entri jadwal.",
        ]);
    }

    /**
     * Mengembalikan status jadwal PUBLISHED menjadi DRAFT.
     */
    public function unpublish(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $affected = $this->generatorService->unpublish(
            $request->input('academic_year_id'),
            $request->input('class_id')
        );

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil mengembalikan {$affected} entri jadwal menjadi draft.",
        ]);
    }
}