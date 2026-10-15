<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYear\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function __construct(
        protected AcademicYearService $academicYearService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $academicYears = $this->academicYearService->getAll($request->query('search'));

        return response()->json([
            'status' => 'success',
            'data' => $academicYears,
        ]);
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $academicYear = $this->academicYearService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Tahun ajaran berhasil ditambahkan.',
            'data' => $academicYear,
        ], 201);
    }

    public function show(AcademicYear $academicYear): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $academicYear,
        ]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $updated = $this->academicYearService->update($academicYear, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Tahun ajaran berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        try {
            $this->academicYearService->delete($academicYear);

            return response()->json([
                'status' => 'success',
                'message' => 'Tahun ajaran berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    public function setActive(AcademicYear $academicYear): JsonResponse
    {
        $updated = $this->academicYearService->setActive($academicYear);

        return response()->json([
            'status' => 'success',
            'message' => "Tahun ajaran {$updated->name} berhasil diaktifkan.",
            'data' => $updated,
        ]);
    }
}