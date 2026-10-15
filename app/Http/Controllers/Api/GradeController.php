<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grade\StoreGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;
use App\Models\Grade;
use App\Services\GradeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct(
        protected GradeService $gradeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $grades = $this->gradeService->getAll($request->query('search'));

        return response()->json([
            'status' => 'success',
            'data' => $grades,
        ]);
    }

    public function store(StoreGradeRequest $request): JsonResponse
    {
        $grade = $this->gradeService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jenjang berhasil ditambahkan.',
            'data' => $grade,
        ], 201);
    }

    public function show(Grade $grade): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $grade,
        ]);
    }

    public function update(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        $updated = $this->gradeService->update($grade, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jenjang berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(Grade $grade): JsonResponse
    {
        try {
            $this->gradeService->delete($grade);

            return response()->json([
                'status' => 'success',
                'message' => 'Jenjang berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}