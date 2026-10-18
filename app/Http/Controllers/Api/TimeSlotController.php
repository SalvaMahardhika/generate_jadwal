<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        // Menggunakan filled() agar query parameter yang bernilai empty string ("") diabaikan/bernilai null
        $isBreak = $request->filled('is_break') ? $request->boolean('is_break') : null;

        $timeSlots = $this->timeSlotService->getAll(
            $request->query('day'),
            $isBreak
        );

        return response()->json([
            'status' => 'success',
            'data' => $timeSlots,
        ]);
    }

    public function store(StoreTimeSlotRequest $request): JsonResponse
    {
        $timeSlot = $this->timeSlotService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Slot waktu berhasil ditambahkan.',
            'data' => $timeSlot,
        ], 201);
    }

    public function show(TimeSlot $timeSlot): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $timeSlot,
        ]);
    }

    public function update(UpdateTimeSlotRequest $request, TimeSlot $timeSlot): JsonResponse
    {
        $updated = $this->timeSlotService->update($timeSlot, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Slot waktu berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(TimeSlot $timeSlot): JsonResponse
    {
        try {
            $this->timeSlotService->delete($timeSlot);

            return response()->json([
                'status' => 'success',
                'message' => 'Slot waktu berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}