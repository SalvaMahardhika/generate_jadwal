<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil masuk.',
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token'],
                    'token_type' => 'Bearer',
                ],
            ], 200);
        } catch (Exception $e) {
            $rawCode = is_numeric($e->getCode()) ? (int) $e->getCode() : 500;
            $statusCode = ($rawCode >= 100 && $rawCode <= 599) ? $rawCode : 401;

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil keluar.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal melakukan logout.',
            ], 500);
        }
    }
}