<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class GenerateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id'  => ['required', 'exists:academic_years,id'],
            'class_id'          => ['nullable', 'exists:classes,id'],
            'max_period_number' => ['nullable', 'integer', 'min:1', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists'   => 'Tahun ajaran tidak terdaftar.',
            'class_id.exists'          => 'Kelas tidak terdaftar.',
            'max_period_number.integer' => 'Maksimal JP harus berupa angka.',
            'max_period_number.min'     => 'Maksimal JP minimal 1.',
            'max_period_number.max'     => 'Maksimal JP tidak boleh melebihi 15.',
        ];
    }
}