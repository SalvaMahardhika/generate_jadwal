<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassSubject\AssignClassSubjectRequest;
use App\Http\Requests\ClassSubject\UpdateClassSubjectRequest;
use App\Models\Classes;
use App\Models\ClassSubject;
use App\Services\ClassSubjectService;
use Exception;
use Illuminate\Http\JsonResponse;

class ClassSubjectController extends Controller
{
    public function __construct(
        protected ClassSubjectService $classSubjectService
    ) {}

    public function getByClass(Classes $class): JsonResponse
    {
        $assignments = $this->classSubjectService->getByClass($class->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'class' => $class->load(['grade', 'major', 'academicYear', 'homeroomTeacher']),
                'subjects' => $assignments,
            ],
        ]);
    }

    public function store(AssignClassSubjectRequest $request, Classes $class): JsonResponse
    {
        try {
            $assignment = $this->classSubjectService->assignSubject($class->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Mata pelajaran dan guru pengampu berhasil di-assign ke kelas.',
                'data' => $assignment,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    public function update(UpdateClassSubjectRequest $request, ClassSubject $classSubject): JsonResponse
    {
        $updated = $this->classSubjectService->updateAssignment($classSubject, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Pemetaan mapel dan guru berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(ClassSubject $classSubject): JsonResponse
    {
        try {
            $this->classSubjectService->deleteAssignment($classSubject);

            return response()->json([
                'status' => 'success',
                'message' => 'Pemetaan mapel berhasil dihapus dari kelas.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}