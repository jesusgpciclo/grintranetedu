<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('categoria') ? $this->route('categoria')->id : null;

        return [
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $id,
            'parent_id' => 'nullable|exists:categorias,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
            'tipo_recurso_id.required' => 'Debes seleccionar un tipo de recurso.',
            'tipo_recurso_id.exists' => 'el tipo de recurso seleccionado no es válido.',
        ];
    }
}
