<?php

namespace App\Http\Requests\TimeSlotProfile;

use Illuminate\Foundation\Http\FormRequest;

class AssignProfileToClassesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_ids'   => ['required', 'array'],
            'class_ids.*' => ['required', 'exists:classes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_ids.required' => 'Daftar kelas wajib dipilih.',
            'class_ids.array'    => 'Format data kelas tidak valid.',
            'class_ids.*.exists' => 'Salah satu kelas yang dipilih tidak ditemukan.',
        ];
    }
}