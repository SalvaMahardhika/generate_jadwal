<?php

namespace App\Http\Requests\Major;

use Illuminate\Foundation\Http\FormRequest;

class StoreMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'major_name' => ['required', 'string', 'max:255', 'unique:majors,major_name'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'major_name.required' => 'Nama jurusan wajib diisi.',
            'major_name.unique' => 'Nama jurusan sudah ada.',
            'status.in' => 'Status jurusan tidak valid.',
        ];
    }
}