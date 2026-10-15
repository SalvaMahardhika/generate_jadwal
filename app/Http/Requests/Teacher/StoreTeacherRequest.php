<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nip' => ['nullable', 'string', 'max:50', 'unique:teachers,nip'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'max_jp_per_week' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.unique' => 'NIP sudah terdaftar.',
            'name.required' => 'Nama guru wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid (pilih L atau P).',
            'max_jp_per_week.required' => 'Batas JP per minggu wajib diisi.',
            'max_jp_per_week.integer' => 'Batas JP per minggu harus berupa angka.',
            'max_jp_per_week.min' => 'Batas JP per minggu minimal 1.',
            'status.in' => 'Status guru tidak valid.',
        ];
    }
}