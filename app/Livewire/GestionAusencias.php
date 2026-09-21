<?php

namespace App\Livewire;

use App\Models\Aula;
use App\Models\Ausencia;
use App\Models\Group;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\Guardias\AusenciaService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

// Soporte retrocompatible y fallback si Livewire no está registrado en el autoloader de Composer
if (!class_exists('Livewire\Component')) {
    abstract class BaseLivewireComponent {
        public function resetValidation($fields = null): void {}

        public function reset($properties = []): void
        {
            if (is_array($properties)) {
                foreach ($properties as $prop) {
                    if (property_exists($this, $prop)) {
                        $rp = new \ReflectionProperty($this, $prop);
                        $type = $rp->getType();
                        if ($type && !$type->allowsNull() && $type->getName() === 'string') {
                            $this->{$prop} = '';
                        } elseif ($type && !$type->allowsNull() && $type->getName() === 'bool') {
                            $this->{$prop} = false;
                        } else {
                            $this->{$prop} = null;
                        }
                    }
                }
            } elseif (is_string($properties) && property_exists($this, $properties)) {
                $this->reset([$properties]);
            }
        }

        public function validate($rules = null, $messages = [], $attributes = []): array
        {
            $rulesToValidate = $rules ?? (method_exists($this, 'rules') ? $this->rules() : []);
            $data = [];
            foreach (array_keys($rulesToValidate) as $key) {
                if (property_exists($this, $key)) {
                    $data[$key] = $this->{$key};
                }
            }
            $msgs = $messages ?: ($this->messages ?? []);
            return \Illuminate\Support\Facades\Validator::make($data, $rulesToValidate, $msgs)->validate();
        }

        public function dispatch($event, ...$params): void {}
    }
    class_alias(BaseLivewireComponent::class, 'Livewire\Component');
}
if (!trait_exists('Livewire\WithFileUploads')) {
    trait BaseWithFileUploadsTrait {}
    class_alias(BaseWithFileUploadsTrait::class, 'Livewire\WithFileUploads');
}

/**
 * Componente Livewire 3 Senior para la Gestión de Ausencias del Módulo de Guardias.
 *
 * Funcionalidades clave:
 * 1. Creación rápida por tramo con modal interactivo y precarga de hora/fecha.
 * 2. Validación de reglas de negocio y políticas de autorización:
 *    - Los docentes solo pueden modificar o borrar ausencias si el tramo lectivo no ha comenzado.
 *    - Si la guardia ya está cubierta o firmada, solo 'admin' o 'directivo' pueden modificarla o borrarla.
 *    - Confirmaciones fluidas con SweetAlert2 / Alpine.js antes del borrado.
 * 3. Filtros reactivos con navegación día a día y filtrado por estado de cobertura y justificación.
 */
class GestionAusencias extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    // --- Filtros Reactivos ---
    public string $date;
    public string $filterStatus = 'all'; // 'all', 'pendientes', 'justificadas', 'sin_justificar'
    public string $viewScope = 'all';    // 'all' o 'mine'

    // --- Control de Modales y Búsqueda ---
    public bool $showCreateModal = false;
    public bool $showDeleteModal = false;
    public ?int $ausenciaIdToDelete = null;
    public ?string $searchTeacher = '';

    // --- Campos del Formulario de Creación ---
    public ?int $user_id = null;
    public ?int $time_slot_id = null;
    public ?int $group_id = null;
    public ?int $zona_id = null;
    public string $motivo = 'Enfermedad común';
    public ?string $tarea = '';
    public ?string $enlace_tarea = null;
    public $archivo = null;
    public bool $es_guardia = false;

    // --- Opciones de Motivo Frecuentes ---
    public array $motivosComunes = [
        'Enfermedad común / IT',
        'Cita o consulta médica',
        'Deber inexcusable / Trámite',
        'Actividad extraescolar / Salida',
        'Formación / Reunión externa',
        'Asuntos propios',
        'Otro motivo',
    ];

    /**
     * Reglas de validación para la creación de ausencias.
     */
    protected function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'group_id' => 'nullable|exists:groups,id',
            'zona_id' => 'nullable|exists:aulas,id',
            'motivo' => 'nullable|string|max:255',
            'tarea' => 'nullable|string|max:2000',
            'enlace_tarea' => 'nullable|url|max:255',
            'archivo' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'es_guardia' => 'boolean',
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     */
    protected $messages = [
        'user_id.required' => 'Debes seleccionar el docente ausente.',
        'user_id.exists' => 'El docente seleccionado no es válido.',
        'time_slot_id.required' => 'El tramo horario es obligatorio.',
        'enlace_tarea.url' => 'El enlace proporcionado debe ser una URL válida (ej: https://...).',
        'archivo.max' => 'El archivo adjunto no puede exceder 10MB.',
        'archivo.mimes' => 'Solo se admiten documentos PDF, Word o imágenes (jpg, png).',
    ];

    public function mount(?string $date = null): void
    {
        $this->date = $date ?? date('Y-m-d');
        $this->user_id = Auth::id();
    }

    // ─────────────────────────────────────────────────────────────
    // NAVEGACIÓN Y FILTROS REACTIVOS
    // ─────────────────────────────────────────────────────────────

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->format('Y-m-d');
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->format('Y-m-d');
    }

    public function today(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedDate(): void
    {
        // Reactividad automática al cambiar la fecha por el datepicker
    }

    // ─────────────────────────────────────────────────────────────
    // CREACIÓN RÁPIDA POR TRAMO
    // ─────────────────────────────────────────────────────────────

    /**
     * Abre el modal precargando automáticamente el tramo horario y fecha actual.
     */
    public function openCreateModal(int $slotId): void
    {
        $this->resetValidation();
        $this->reset(['group_id', 'zona_id', 'tarea', 'enlace_tarea', 'archivo', 'es_guardia', 'searchTeacher']);
        
        $this->time_slot_id = $slotId;
        $this->motivo = 'Enfermedad común / IT';

        // Si es profesor regular, fija su propio ID; si es directivo o admin, permite elegir
        $user = Auth::user();
        $isDirectiva = $user?->hasAnyRole(['admin', 'directiva', 'directivo']);
        $this->user_id = $user?->id;

        $this->showCreateModal = true;
    }

    /**
     * Cierra el modal de creación.
     */
    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    /**
     * Procesa y almacena la nueva ausencia aplicando las reglas de negocio mediante AusenciaService.
     */
    public function saveAusencia(?AusenciaService $ausenciaService = null): void
    {
        $this->validate();

        $user = Auth::user();
        $isDirectiva = $user?->hasAnyRole(['admin', 'directiva', 'directivo']);

        // Seguridad: un docente sin privilegios no puede registrar ausencias para otro docente
        if (!$isDirectiva && $this->user_id !== $user?->id) {
            $this->user_id = $user->id;
        }

        $enlaceFinal = $this->enlace_tarea;

        // Gestión de subida de archivo opcional
        if ($this->archivo && method_exists($this->archivo, 'store')) {
            $path = $this->archivo->store('ausencias_adjuntos', 'public');
            if (empty($enlaceFinal)) {
                $enlaceFinal = Storage::url($path);
            }
        }

        $ausenciaService = $ausenciaService ?? app(AusenciaService::class);

        $ausenciaService->registrarAusencia([
            'user_id' => $this->user_id,
            'fecha' => $this->date,
            'time_slot_id' => $this->time_slot_id,
            'group_id' => $this->group_id,
            'zona_id' => $this->zona_id,
            'tarea' => $this->tarea,
            'enlace_tarea' => $enlaceFinal,
            'es_guardia' => $this->es_guardia,
            'justificada' => false,
            'justificacion_nota' => $this->motivo,
        ]);

        $this->showCreateModal = false;
        $this->reset(['tarea', 'enlace_tarea', 'archivo', 'group_id', 'zona_id']);

        session()->flash('success', '✓ Ausencia comunicada y registrada en el parte correctamente.');
        
        // Notificación al navegador mediante evento compatible con SweetAlert2 / Toast
        $this->dispatch('swal:alert', [
            'type' => 'success',
            'title' => '¡Ausencia Registrada!',
            'text' => 'La falta se ha incorporado al cuadrante de guardias.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // POLÍTICAS DE BORRADO Y CONFIRMACIÓN
    // ─────────────────────────────────────────────────────────────

    /**
     * Solicita confirmación antes de eliminar una ausencia.
     */
    public function confirmDelete(int $ausenciaId): void
    {
        $ausencia = Ausencia::findOrFail($ausenciaId);
        $user = Auth::user();

        if (!$ausencia->canBeDeletedBy($user)) {
            $this->dispatch('swal:alert', [
                'type' => 'error',
                'title' => 'Acción no permitida',
                'text' => $ausencia->isCubierta()
                    ? 'Esta ausencia ya tiene una guardia cubierta/firmada. Solo la Dirección puede cancelarla.'
                    : 'No es posible cancelar esta ausencia porque el tramo lectivo ya ha comenzado.',
            ]);
            return;
        }

        $this->ausenciaIdToDelete = $ausenciaId;
        $this->showDeleteModal = true;
    }

    /**
     * Ejecuta el borrado de la ausencia una vez confirmado mediante AusenciaService.
     */
    public function deleteAusencia(?int $ausenciaId = null, ?AusenciaService $ausenciaService = null): void
    {
        $id = $ausenciaId ?? $this->ausenciaIdToDelete;
        if (!$id) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            return;
        }

        $ausenciaService = $ausenciaService ?? app(AusenciaService::class);

        try {
            $ausenciaService->eliminarAusencia($id, $user);

            $this->showDeleteModal = false;
            $this->ausenciaIdToDelete = null;

            session()->flash('success', '✓ Ausencia eliminada del parte de guardia.');

            $this->dispatch('swal:alert', [
                'type' => 'success',
                'title' => 'Ausencia eliminada',
                'text' => 'El registro ha sido retirado satisfactoriamente.',
            ]);
        } catch (\Throwable $e) {
            $this->showDeleteModal = false;
            session()->flash('error', $e->getMessage());

            $this->dispatch('swal:alert', [
                'type' => 'error',
                'title' => 'Error al eliminar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // RENDERIZADO REACTIVO DEL COMPONENTE
    // ─────────────────────────────────────────────────────────────

    public function render()
    {
        $user = Auth::user();
        $isDirectiva = $user?->hasAnyRole(['admin', 'directiva', 'directivo']);

        $timeSlots = ScheduleTemplate::getActiveTimeSlots();

        // Consulta de ausencias para la fecha actual con relaciones
        $query = Ausencia::whereDate('fecha', $this->date)
            ->with(['user', 'guardiaUser', 'timeSlot', 'group', 'zona']);

        // Filtro de ámbito: mis ausencias vs todas
        if (!$isDirectiva || $this->viewScope === 'mine') {
            $query->where('user_id', $user?->id);
        }

        // Filtro reactivo por estado de guardia y justificación
        match ($this->filterStatus) {
            'pendientes' => $query->whereNull('guardia_confirmed_at'),
            'justificadas' => $query->where('justificada', true),
            'sin_justificar' => $query->where('justificada', false),
            default => null,
        };

        $ausencias = $query->get()->groupBy('time_slot_id');

        // Colecciones para el formulario modal
        $groups = Group::orderBy('course')->orderBy('name')->get();
        $aulas = Aula::orderBy('nombre')->get();
        
        $teachersQuery = User::orderBy('name');
        if (!empty($this->searchTeacher)) {
            $teachersQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTeacher . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchTeacher . '%');
            });
        }
        $teachers = $teachersQuery->get();

        $selectedSlot = $this->time_slot_id ? TimeSlot::find($this->time_slot_id) : null;

        return view('livewire.gestion-ausencias', [
            'timeSlots' => $timeSlots,
            'ausencias' => $ausencias,
            'groups' => $groups,
            'aulas' => $aulas,
            'teachers' => $teachers,
            'selectedSlot' => $selectedSlot,
            'isDirectiva' => $isDirectiva,
            'carbonDate' => Carbon::parse($this->date),
        ]);
    }
}
