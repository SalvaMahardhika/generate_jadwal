<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\StoreClassRequest;
use App\Http\Requests\Class\UpdateClassRequest;
use App\Models\Classes;
use App\Services\ClassService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function __construct(
        protected ClassService $classService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $classes = $this->classService->getAll(
            $request->query('search'),
            $request->query('academic_year_id'),
            $request->query('grade_id'),
            $request->query('status')
        );

        return response()->json([
            'status' => 'success',
            'data' => $classes,
        ]);
    }

    public function store(StoreClassRequest $request): JsonResponse
    {
        $class = $this->classService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil ditambahkan.',
            'data' => $class,
        ], 201);
    }

    public function show(Classes $class): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $class->load(['grade', 'major', 'academicYear', 'homeroomTeacher']),
        ]);
    }

    public function update(UpdateClassRequest $request, Classes $class): JsonResponse
    {
        $updated = $this->classService->update($class, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(Classes $class): JsonResponse
    {
        try {
            $this->classService->delete($class);

            return response()->json([
                'status' => 'success',
                'message' => 'Kelas berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}