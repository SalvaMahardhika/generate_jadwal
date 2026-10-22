<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeSlotProfile\AssignProfileToClassesRequest;
use App\Http\Requests\TimeSlotProfile\StoreTimeSlotProfileRequest;
use App\Http\Requests\TimeSlotProfile\UpdateTimeSlotProfileRequest;
use App\Models\TimeSlotProfile;
use App\Services\TimeSlotProfileService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeSlotProfileController extends Controller
{
    public function __construct(
        protected TimeSlotProfileService $profileService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $academicYearId = $request->query('academic_year_id');
        $profiles = $this->profileService->getAll($academicYearId ? (int) $academicYearId : null);

        return response()->json([
            'status' => 'success',
            'data'   => $profiles,
        ]);
    }

    public function store(StoreTimeSlotProfileRequest $request): JsonResponse
    {
        $profile = $this->profileService->create($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil slot waktu berhasil ditambahkan.',
            'data'    => $profile,
        ], 201);
    }

    public function show(TimeSlotProfile $timeSlotProfile): JsonResponse
    {
        $data = $this->profileService->getById($timeSlotProfile->id);

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    public function update(UpdateTimeSlotProfileRequest $request, TimeSlotProfile $timeSlotProfile): JsonResponse
    {
        $updated = $this->profileService->update($timeSlotProfile, $request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil slot waktu berhasil diperbarui.',
            'data'    => $updated,
        ]);
    }

    public function destroy(TimeSlotProfile $timeSlotProfile): JsonResponse
    {
        try {
            $this->profileService->delete($timeSlotProfile);

            return response()->json([
                'status'  => 'success',
                'message' => 'Profil slot waktu berhasil dihapus.',
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
     * Plotting / Menghubungkan profil ke banyak kelas sekaligus.
     */
    public function assignToClasses(AssignProfileToClassesRequest $request, TimeSlotProfile $timeSlotProfile): JsonResponse
    {
        try {
            $result = $this->profileService->assignToClasses(
                $timeSlotProfile,
                $request->validated()['class_ids']
            );

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil menghubungkan profil slot waktu ke {$result['assigned_count']} kelas.",
                'data'    => $result['profile'],
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
     * Duplikasi profil beserta seluruh slot waktunya.
     */
    public function duplicate(Request $request, TimeSlotProfile $timeSlotProfile): JsonResponse
    {
        $request->validate([
            'target_academic_year_id' => ['required', 'exists:academic_years,id'],
            'new_name'                => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $newProfile = $this->profileService->duplicate(
                $timeSlotProfile,
                (int) $request->input('target_academic_year_id'),
                $request->input('new_name')
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Profil slot waktu berhasil diduplikasi.',
                'data'    => $newProfile,
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