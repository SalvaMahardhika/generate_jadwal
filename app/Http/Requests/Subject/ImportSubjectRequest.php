<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class ImportSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*.subject_code' => ['required', 'string', 'max:20'],
            'subjects.*.subject_name' => ['required', 'string', 'max:255'],
            'subjects.*.category' => ['nullable', 'string', 'in:wajib,peminatan,mulok,kejuruan,kokurikuler'],
            'subjects.*.passing_grade' => ['nullable', 'integer', 'min:0', 'max:100'],
            'subjects.*.color_code' => ['nullable', 'string', 'max:10'],
            'subjects.*.status' => ['required', 'in:active,inactive'],
            'subjects.*.is_joint_subject' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjects.required' => 'Data impor mata pelajaran tidak boleh kosong.',
            'subjects.array' => 'Format payload data impor harus berupa array.',
            'subjects.*.subject_code.required' => 'Kode mapel wajib diisi.',
            'subjects.*.subject_name.required' => 'Nama mata pelajaran wajib diisi.',
            'subjects.*.status.required' => 'Status mapel wajib diisi.',
            'subjects.*.passing_grade.integer' => 'KKM harus berupa angka bulat.',
        ];
    }
}