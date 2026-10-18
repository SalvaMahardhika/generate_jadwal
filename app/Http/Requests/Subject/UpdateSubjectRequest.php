<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'subject_code'  => ['required', 'string', 'max:20', 'unique:subjects,subject_code,' . $subjectId],
            'subject_name'  => ['required', 'string', 'max:255'],
            'color_code'    => ['nullable', 'string', 'max:10'],
            'category'      => ['required', 'string', 'in:wajib,peminatan,mulok,kejuruan,kokurikuler'],
            'passing_grade' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status'        => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_code.required' => 'Kode mata pelajaran wajib diisi.',
            'subject_code.unique'   => 'Kode mata pelajaran sudah terdaftar.',
            'subject_name.required' => 'Nama mata pelajaran wajib diisi.',
            'category.required'     => 'Kategori mata pelajaran wajib dipilih.',
            'category.in'           => 'Kategori mata pelajaran tidak valid.',
            'passing_grade.integer' => 'KKM harus berupa angka.',
            'status.required'       => 'Status mata pelajaran wajib diisi.',
            'status.in'             => 'Status mata pelajaran tidak valid.',
        ];
    }
}