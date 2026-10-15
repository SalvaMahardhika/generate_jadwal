<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new Exception('Email atau kata sandi salah.', 401);
        }

        if ($user->status !== 'active') {
            throw new Exception('Akun Anda tidak aktif. Silakan hubungi administrator.', 403);
        }

        // Buat token akses baru via Sanctum
        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): bool
    {
        // Hapus token yang sedang digunakan saat ini
        return $user->currentAccessToken()->delete();
    }
}