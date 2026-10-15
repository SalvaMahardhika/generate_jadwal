<?php

namespace App\Http\Requests\TimeSlot;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'period_number' => ['required', 'integer', 'min:1'],
            'label' => ['nullable', 'string', 'max:50'],
            'start_time' => ['required', 'date_format:H:i:s,H:i'],
            'end_time' => ['required', 'date_format:H:i:s,H:i', 'after:start_time'],
            'is_break' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'day.required' => 'Hari wajib dipilih.',
            'day.in' => 'Pilihan hari tidak valid.',
            'period_number.required' => 'Urutan jam pelajaran (JP) wajib diisi.',
            'period_number.integer' => 'Urutan jam pelajaran harus berupa angka.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'is_break.required' => 'Status jam istirahat wajib diisi.',
        ];
    }
}