<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentoInstitucionalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'url' => 'required|url|max:255',
            'tipo_archivo' => 'required|string|in:Video,Documento,Presentación,Imagen,Otros',
            'etiquetas' => 'required|array|min:1',
            'etiquetas.*' => 'exists:etiquetas,id',
            'categoria_id' => 'nullable|exists:categorias,id',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'url.required' => 'La URL es obligatoria.',
            'url.url' => 'Debes introducir una URL válida.',
            'tipo_archivo.required' => 'Debes seleccionar un tipo de archivo.',
            'tipo_archivo.in' => 'El tipo de archivo seleccionado no es válido.',
            'etiquetas.required' => 'El documento debe tener al menos una etiqueta.',
            'etiquetas.min' => 'El documento debe tener al menos una etiqueta.',
            'categorias.required' => 'El documento debe tener al menos una categoría.',
            'categorias.min' => 'El documento debe tener al menos una categoría.',
        ];
    }
}
