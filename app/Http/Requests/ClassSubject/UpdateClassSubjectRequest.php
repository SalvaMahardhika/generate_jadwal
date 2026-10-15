<?php

namespace App\Http\Requests\ClassSubject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'total_jp' => ['required', 'integer', 'min:1'],
            'max_jp_per_day' => ['required', 'integer', 'min:1'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['exists:teachers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'total_jp.required' => 'Total JP per minggu wajib diisi.',
            'total_jp.integer' => 'Total JP harus berupa angka.',
            'total_jp.min' => 'Total JP minimal 1.',
            'max_jp_per_day.required' => 'Maksimal JP per hari wajib diisi.',
            'max_jp_per_day.integer' => 'Maksimal JP per hari harus berupa angka.',
            'max_jp_per_day.min' => 'Maksimal JP per hari minimal 1.',
            'teacher_ids.required' => 'Minimal pilih satu guru pengampu.',
            'teacher_ids.array' => 'Format data guru pengampu tidak valid.',
            'teacher_ids.min' => 'Minimal pilih satu guru pengampu.',
            'teacher_ids.*.exists' => 'Guru pengampu tidak terdaftar.',
        ];
    }
}