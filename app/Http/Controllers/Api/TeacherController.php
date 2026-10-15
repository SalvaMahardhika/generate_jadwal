<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Services\TeacherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $teachers = $this->teacherService->getAll(
            $request->query('search'),
            $request->query('status')
        );

        return response()->json([
            'status' => 'success',
            'data' => $teachers,
        ]);
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = $this->teacherService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data guru berhasil ditambahkan.',
            'data' => $teacher,
        ], 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $teacher,
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $updated = $this->teacherService->update($teacher, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data guru berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        try {
            $this->teacherService->delete($teacher);

            return response()->json([
                'status' => 'success',
                'message' => 'Data guru berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}