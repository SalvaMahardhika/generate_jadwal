<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Models\Major;
use App\Services\MajorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function __construct(
        protected MajorService $majorService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $majors = $this->majorService->getAll(
            $request->query('search'),
            $request->query('status')
        );

        return response()->json([
            'status' => 'success',
            'data' => $majors,
        ]);
    }

    public function store(StoreMajorRequest $request): JsonResponse
    {
        $major = $this->majorService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jurusan berhasil ditambahkan.',
            'data' => $major,
        ], 201);
    }

    public function show(Major $major): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $major,
        ]);
    }

    public function update(UpdateMajorRequest $request, Major $major): JsonResponse
    {
        $updated = $this->majorService->update($major, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jurusan berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(Major $major): JsonResponse
    {
        try {
            $this->majorService->delete($major);

            return response()->json([
                'status' => 'success',
                'message' => 'Jurusan berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}