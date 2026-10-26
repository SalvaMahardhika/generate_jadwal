<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class ManualAssignScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id'         => ['required', 'exists:academic_years,id'],
            'class_id'                 => ['required', 'exists:classes,id'],
            'time_slot_id'             => ['required', 'exists:time_slots,id'],
            'class_subject_teacher_id' => ['nullable', 'exists:class_subject_teachers,id'],
            'custom_activity'          => ['nullable', 'string', 'max:255'],
            'is_locked'                => ['nullable', 'boolean'],
            'apply_to_all_classes'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required'       => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists'         => 'Tahun ajaran tidak terdaftar.',
            'class_id.required'               => 'Kelas wajib dipilih.',
            'class_id.exists'                 => 'Kelas tidak terdaftar.',
            'time_slot_id.required'           => 'Slot waktu wajib dipilih.',
            'time_slot_id.exists'             => 'Slot waktu tidak terdaftar.',
            'class_subject_teacher_id.exists' => 'Pengampu mapel tidak terdaftar.',
        ];
    }
}