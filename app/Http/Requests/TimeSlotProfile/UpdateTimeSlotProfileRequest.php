<?php

namespace App\Http\Requests\TimeSlotProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeSlotProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string'],
            'is_default'       => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib diisi.',
            'academic_year_id.exists'   => 'Tahun ajaran yang dipilih tidak valid.',
            'name.required'             => 'Nama profil slot waktu wajib diisi.',
            'name.max'                  => 'Nama profil maksimal 100 karakter.',
        ];
    }
}