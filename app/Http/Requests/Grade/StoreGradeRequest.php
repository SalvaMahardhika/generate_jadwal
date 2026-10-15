<?php

namespace App\Http\Requests\Grade;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_name' => ['required', 'string', 'max:255', 'unique:grades,grade_name'],
            'sequence' => ['required', 'integer', 'min:1', 'unique:grades,sequence'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_name.required' => 'Nama jenjang/tingkat wajib diisi.',
            'grade_name.unique' => 'Nama jenjang/tingkat sudah ada.',
            'sequence.required' => 'Urutan tingkat wajib diisi.',
            'sequence.integer' => 'Urutan tingkat harus berupa angka.',
            'sequence.min' => 'Urutan tingkat minimal 1.',
            'sequence.unique' => 'Urutan tingkat ini sudah digunakan.',
        ];
    }
}