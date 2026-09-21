<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $schoolYearId = $this->route('school_year') ? $this->route('school_year')->id : null;

        return [
            'name' => 'required|string|max:20|unique:school_years,name,' . $schoolYearId,
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del curso escolar es obligatorio.',
            'name.unique' => 'Este curso escolar ya está registrado.',
            'name.max' => 'El nombre no debe superar los 20 caracteres.',
        ];
    }
}
