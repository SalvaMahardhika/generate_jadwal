<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeSlot\GenerateTimeSlotRequest;
use App\Http\Requests\TimeSlot\StoreTimeSlotRequest;
use App\Http\Requests\TimeSlot\UpdateTimeSlotRequest;
use App\Models\TimeSlot;
use App\Services\TimeSlotService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function __construct(
        protected TimeSlotService $timeSlotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $profileId = $request->query('time_slot_profile_id');
        $isBreak   = $request->filled('is_break') ? $request->boolean('is_break') : null;

        $timeSlots = $this->timeSlotService->getAll(
            $profileId ? (int) $profileId : null,
            $request->query('day'),
            $isBreak
        );

        return response()->json([
            'status' => 'success',
            'data'   => $timeSlots,
        ]);
    }

    public function store(StoreTimeSlotRequest $request): JsonResponse
    {
        $timeSlot = $this->timeSlotService->create($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Slot waktu berhasil ditambahkan.',
            'data'    => $timeSlot,
        ], 201);
    }

    public function show(TimeSlot $timeSlot): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $timeSlot->load('timeSlotProfile'),
        ]);
    }

    public function update(UpdateTimeSlotRequest $request, TimeSlot $timeSlot): JsonResponse
    {
        $updated = $this->timeSlotService->update($timeSlot, $request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Slot waktu berhasil diperbarui.',
            'data'    => $updated,
        ]);
    }

    public function destroy(TimeSlot $timeSlot): JsonResponse
    {
        try {
            $this->timeSlotService->delete($timeSlot);

            return response()->json([
                'status'  => 'success',
                'message' => 'Slot waktu berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            $code = (int) $e->getCode();
            $statusCode = ($code >= 400 && $code <= 599) ? $code : 500;

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Menghapus seluruh slot waktu pada hari tertentu milik profil tertentu (Bulk Delete).
     */
    public function clearByDay(Request $request, string $day): JsonResponse
    {
        try {
            $profileId = $request->query('time_slot_profile_id');
            $affected  = $this->timeSlotService->deleteByDay($day, $profileId ? (int) $profileId : null);

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil menghapus {$affected} slot waktu pada hari " . ucfirst($day) . ".",
            ]);
        } catch (Exception $e) {
            $code = (int) $e->getCode();
            $statusCode = ($code >= 400 && $code <= 599) ? $code : 500;

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Otomatis meng-generate seluruh jam pelajaran & istirahat berdasarkan parameter profil.
     */
    public function generate(GenerateTimeSlotRequest $request): JsonResponse
    {
        try {
            $slots = $this->timeSlotService->generate($request->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'Slot waktu berhasil di-generate secara otomatis.',
                'data'    => $slots,
            ], 201);
        } catch (Exception $e) {
            $code = (int) $e->getCode();
            $statusCode = ($code >= 400 && $code <= 599) ? $code : 500;

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}