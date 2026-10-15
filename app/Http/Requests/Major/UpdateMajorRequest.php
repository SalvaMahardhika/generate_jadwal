<?php

namespace App\Http\Requests\Major;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $majorId = $this->route('major')?->id;

        return [
            'major_name' => ['required', 'string', 'max:255', 'unique:majors,major_name,' . $majorId],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'major_name.required' => 'Nama jurusan wajib diisi.',
            'major_name.unique' => 'Nama jurusan sudah ada.',
            'status.required' => 'Status jurusan wajib diisi.',
            'status.in' => 'Status jurusan tidak valid.',
        ];
    }
}