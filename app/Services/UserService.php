<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll(?string $search = null, ?string $status = null): Collection
    {
        return User::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user, ?int $currentUserId = null): bool
    {
        if ($currentUserId && $user->id === $currentUserId) {
            throw new Exception('Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan.', 422);
        }

        if ($user->schedules()->exists()) {
            throw new Exception('Tidak dapat menghapus pengguna yang sudah membuat data riwayat jadwal.', 422);
        }

        return $user->delete();
    }
}