<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getAll(
            $request->query('search'),
            $request->query('status')
        );

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => $user,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->update($user, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data pengguna berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $this->userService->delete($user, $request->user()?->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengguna berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}