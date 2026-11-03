<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class ImportTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teachers' => ['required', 'array', 'min:1'],
            'teachers.*.nip' => ['nullable', 'string', 'max:30'],
            'teachers.*.name' => ['required', 'string', 'max:100'],
            'teachers.*.gender' => ['required', 'in:L,P'],
            'teachers.*.max_jp_per_week' => ['required', 'integer', 'min:1', 'max:60'],
            'teachers.*.status' => ['nullable', 'in:active,inactive'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'teachers.required' => 'Data guru wajib diisi.',
            'teachers.array' => 'Format data guru harus berupa array.',
            'teachers.min' => 'Minimal harus ada 1 baris data guru yang diimpor.',
            'teachers.*.name.required' => 'Nama guru wajib diisi pada setiap baris data.',
            'teachers.*.gender.required' => 'Jenis kelamin wajib diisi pada setiap baris data.',
            'teachers.*.gender.in' => 'Jenis kelamin harus berupa L (Laki-Laki) atau P (Perempuan).',
            'teachers.*.max_jp_per_week.required' => 'Beban JP per minggu wajib diisi.',
            'teachers.*.max_jp_per_week.integer' => 'Beban JP per minggu harus berupa angka bulat.',
            'teachers.*.max_jp_per_week.min' => 'Beban JP per minggu minimal 1 JP.',
            'teachers.*.max_jp_per_week.max' => 'Beban JP per minggu maksimal 60 JP.',
        ];
    }
}