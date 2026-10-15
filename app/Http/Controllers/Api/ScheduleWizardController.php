<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\GenerateScheduleRequest;
use App\Http\Requests\Schedule\LockSlotRequest;
use App\Http\Requests\Schedule\ManualAssignScheduleRequest;
use App\Models\Classes;
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
     * Mengambil matriks jadwal utuh per kelas untuk kanvas Wizard.
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
     * Mengambil status keseimbangan JP (balance) untuk sidebar checklist UI.
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
     * Menjalankan proses auto-generate jadwal otomatis.
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
     * Menempatkan mapel/kegiatan secara manual oleh admin.
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
}