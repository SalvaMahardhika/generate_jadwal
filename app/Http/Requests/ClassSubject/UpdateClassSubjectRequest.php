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
            'teachers' => ['required', 'array', 'min:1'],
            'teachers.*.teacher_id' => ['required', 'exists:teachers,id'],
            'teachers.*.jp' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $totalJp = (int) $this->input('total_jp', 0);
            $teachers = $this->input('teachers', []);

            if (is_array($teachers) && count($teachers) > 0) {
                $sumTeacherJp = array_reduce($teachers, function ($carry, $item) {
                    return $carry + (int) ($item['jp'] ?? 0);
                }, 0);

                if ($totalJp > 0 && $sumTeacherJp !== $totalJp) {
                    $validator->errors()->add('teachers', "Total alokasi JP guru ({$sumTeacherJp} JP) harus sama dengan Total JP Mata Pelajaran ({$totalJp} JP).");
                }
            }
        });
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
            'teachers.required' => 'Minimal pilih satu guru pengampu.',
            'teachers.array' => 'Format data guru pengampu tidak valid.',
            'teachers.min' => 'Minimal pilih satu guru pengampu.',
            'teachers.*.teacher_id.required' => 'Guru pengampu wajib dipilih.',
            'teachers.*.teacher_id.exists' => 'Guru pengampu tidak terdaftar.',
            'teachers.*.jp.required' => 'JP untuk tiap guru wajib diisi.',
            'teachers.*.jp.integer' => 'JP guru harus berupa angka.',
            'teachers.*.jp.min' => 'JP tiap guru minimal 1.',
        ];
    }
}