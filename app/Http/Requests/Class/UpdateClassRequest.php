<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_name' => ['required', 'string', 'max:255'],
            'grade_id' => ['required', 'exists:grades,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_name.required' => 'Nama kelas wajib diisi.',
            'grade_id.required' => 'Tingkat/jenjang kelas wajib dipilih.',
            'grade_id.exists' => 'Tingkat/jenjang kelas tidak terdaftar.',
            'major_id.exists' => 'Jurusan tidak terdaftar.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak terdaftar.',
            'homeroom_teacher_id.exists' => 'Wali kelas tidak terdaftar.',
            'status.required' => 'Status kelas wajib diisi.',
            'status.in' => 'Status kelas tidak valid.',
        ];
    }
}
