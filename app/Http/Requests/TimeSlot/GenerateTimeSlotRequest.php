<?php

namespace App\Http\Requests\TimeSlot;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'time_slot_profile_id' => ['required', 'exists:time_slot_profiles,id'],
            'start_time'           => ['required', 'string', 'date_format:H:i'],
            'period_duration'      => ['required', 'integer', 'min:15', 'max:120'],
            'days'                 => ['required', 'array', 'min:1'],
            'days.*'               => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'periods_per_day'      => ['required', 'array'],
            'periods_per_day.*'    => ['required', 'integer', 'min:1', 'max:15'],
            'breaks'               => ['nullable', 'array'],
            'breaks.*.after_period'=> ['required', 'integer', 'min:1'],
            'breaks.*.duration'    => ['required', 'integer', 'min:5', 'max:120'],
            'breaks.*.label'       => ['nullable', 'string', 'max:100'],
            'overwrite'            => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'time_slot_profile_id.required' => 'Profil slot waktu wajib dipilih.',
            'time_slot_profile_id.exists'   => 'Profil slot waktu tidak valid.',
            'start_time.required'           => 'Jam mulai sekolah wajib diisi.',
            'start_time.date_format'        => 'Format jam mulai harus HH:MM (contoh: 07:00).',
            'period_duration.required'      => 'Durasi 1 JP wajib diisi.',
            'period_duration.min'           => 'Durasi minimum 1 JP adalah 15 menit.',
            'days.required'                 => 'Pilih minimal satu hari untuk di-generate.',
            'periods_per_day.required'      => 'Jumlah JP per hari wajib diatur.',
            'breaks.*.after_period.required'=> 'Posisi JP untuk istirahat wajib ditentukan.',
            'breaks.*.duration.required'    => 'Durasi istirahat wajib diisi.',
        ];
    }
}