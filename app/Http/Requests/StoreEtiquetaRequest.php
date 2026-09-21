<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEtiquetaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('etiqueta') ? $this->route('etiqueta')->id : null;
        
        return [
            'nombre' => 'required|string|max:50|unique:etiquetas,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la etiqueta es obligatorio.',
            'nombre.unique' => 'Ya existe una etiqueta con este nombre.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ];
    }
}
