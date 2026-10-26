<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\SubjectService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function __construct(
        protected SubjectService $subjectService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $subjects = $this->subjectService->getAll(
            $request->query('search'),
            $request->query('category'),
            $request->query('status'),
            $request->query('is_joint_subject')
        );

        return response()->json([
            'status' => 'success',
            'data' => $subjects,
        ]);
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $subject = $this->subjectService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Mata pelajaran berhasil ditambahkan.',
            'data' => $subject,
        ], 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $subject,
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $updated = $this->subjectService->update($subject, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        try {
            $this->subjectService->delete($subject);

            return response()->json([
                'status' => 'success',
                'message' => 'Mata pelajaran berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}