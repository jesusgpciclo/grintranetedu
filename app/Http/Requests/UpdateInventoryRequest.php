<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\InventorySubtype;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_type_id' => 'required|exists:inventory_types,id',
            'inventory_subtype_id' => [
                'required',
                'exists:inventory_subtypes,id',
                function ($attribute, $value, $fail) {
                    $subtype = InventorySubtype::find($value);
                    if ($subtype && $subtype->inventory_type_id != $this->inventory_type_id) {
                        $fail('El subtipo seleccionado no pertenece al tipo de material elegido.');
                    }
                },
            ],
            'zona_id' => 'required|exists:zonas,id',
            'procedencia' => 'nullable|string|max:255',
            'estado' => 'required|in:En uso,No disponible,Pendiente de retirar por APAE,Cualquiera',
            'descripcion' => 'nullable|string',
            'fecha_alta' => 'nullable|date',
            'precio' => 'nullable|numeric',
            'num_registro_general' => 'nullable|string|max:255',
            'num_serie' => 'nullable|string|max:255',
            'dependencia_adscripcion' => 'nullable|string|max:255',
            'solicitud_retirada' => 'boolean',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ];
    }
}
