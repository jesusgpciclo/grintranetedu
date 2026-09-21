<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionManagerService
{
    /**
     * Retorna el catálogo estructurado de módulos con sus permisos, iconos y descripciones.
     *
     * @return array<string, array{title: string, icon: string, description: string, permissions: array<string, array{label: string, description: string}>}>
     */
    public static function getCatalog(): array
    {
        return [
            'guardias' => [
                'title' => 'Guardias del Centro',
                'icon' => '🛡️',
                'description' => 'Supervisión, partes diarios, firmas y configuración de guardias escolares.',
                'permissions' => [
                    'guardias.view' => [
                        'label' => 'Ver Cuadrantes y Partes',
                        'description' => 'Permite consultar el parte diario en vivo y el cuadrante semanal de guardias.'
                    ],
                    'guardias.assign' => [
                        'label' => 'Asignar Profesores a Guardias',
                        'description' => 'Permite seleccionar y asignar profesores sustitutos a faltas del claustro.'
                    ],
                    'guardias.sign' => [
                        'label' => 'Firmar Guardias Realizadas',
                        'description' => 'Permite a los docentes firmar la realización de su guardia asignada.'
                    ],
                    'guardias.manage' => [
                        'label' => 'Configurar Servicio de Guardias',
                        'description' => 'Configuración de parámetros, tramos de guardia, zonas y equidad.'
                    ],
                ],
            ],
            'ausencias' => [
                'title' => 'Gestión de Ausencias',
                'icon' => '📅',
                'description' => 'Comunicación de faltas, tareas para el aula y justificaciones.',
                'permissions' => [
                    'ausencias.view' => [
                        'label' => 'Ver Ausencias del Claustro',
                        'description' => 'Consultar las ausencias registradas de los compañeros del claustro.'
                    ],
                    'ausencias.create' => [
                        'label' => 'Comunicar Ausencias',
                        'description' => 'Permite dar de alta una falta propia o de otro compañero y dejar tareas.'
                    ],
                    'ausencias.edit' => [
                        'label' => 'Modificar Ausencias',
                        'description' => 'Editar datos, aulas, tareas o adjuntos de ausencias registradas.'
                    ],
                    'ausencias.delete' => [
                        'label' => 'Eliminar Ausencias',
                        'description' => 'Eliminar faltas antes del inicio del tramo o cuando no estén cubiertas.'
                    ],
                    'ausencias.justify' => [
                        'label' => 'Controlar Justificaciones',
                        'description' => 'Validar o rechazar justificantes médicos y motivos oficiales de ausencia.'
                    ],
                ],
            ],
            'usuarios_roles' => [
                'title' => 'Usuarios y Roles',
                'icon' => '👥',
                'description' => 'Administración de cuentas de acceso, roles y permisos de la intranet.',
                'permissions' => [
                    'users.view' => [
                        'label' => 'Ver Usuarios',
                        'description' => 'Listar usuarios registrados en la plataforma.'
                    ],
                    'users.create' => [
                        'label' => 'Crear Usuarios',
                        'description' => 'Dar de alta nuevas cuentas de usuario de forma individual.'
                    ],
                    'users.edit' => [
                        'label' => 'Editar Usuarios',
                        'description' => 'Modificar perfiles, contraseñas y datos de usuarios.'
                    ],
                    'users.delete' => [
                        'label' => 'Eliminar Usuarios',
                        'description' => 'Borrar cuentas de usuario del sistema.'
                    ],
                    'users.import' => [
                        'label' => 'Importar Usuarios',
                        'description' => 'Carga masiva de usuarios mediante archivos CSV/JSON.'
                    ],
                    'roles.view' => [
                        'label' => 'Ver Roles',
                        'description' => 'Consultar los roles definidos en la intranet.'
                    ],
                    'roles.create' => [
                        'label' => 'Crear Roles',
                        'description' => 'Definir nuevos roles de acceso.'
                    ],
                    'roles.edit' => [
                        'label' => 'Modificar Roles',
                        'description' => 'Editar nombres y configuración de roles existentes.'
                    ],
                    'roles.delete' => [
                        'label' => 'Eliminar Roles',
                        'description' => 'Borrar roles no reservados del sistema.'
                    ],
                    'permissions.manage' => [
                        'label' => 'Gestionar Permisos y Matriz',
                        'description' => 'Asignar, revocar y configurar la matriz de permisos para los roles.'
                    ],
                ],
            ],
            'alumnos_profesores' => [
                'title' => 'Alumnos, Profesores y Grupos',
                'icon' => '🎓',
                'description' => 'Gestión de expedientes de alumnado, claustro docente y grupos lectivos.',
                'permissions' => [
                    'students.view' => [
                        'label' => 'Ver Alumnos',
                        'description' => 'Consultar el directorio y fichas de los alumnos matriculados.'
                    ],
                    'students.create' => [
                        'label' => 'Crear Alumnos',
                        'description' => 'Dar de alta nuevos alumnos individualmente.'
                    ],
                    'students.edit' => [
                        'label' => 'Editar Alumnos',
                        'description' => 'Actualizar datos personales y asignaciones de grupo de alumnos.'
                    ],
                    'students.delete' => [
                        'label' => 'Eliminar Alumnos',
                        'description' => 'Eliminar fichas de alumnos.'
                    ],
                    'students.import' => [
                        'label' => 'Importación Masiva de Alumnos',
                        'description' => 'Subida de alumnado mediante plantillas CSV o JSON.'
                    ],
                    'teachers.view' => [
                        'label' => 'Ver Profesores',
                        'description' => 'Consultar el directorio del claustro docente.'
                    ],
                    'teachers.create' => [
                        'label' => 'Crear Profesores',
                        'description' => 'Dar de alta profesores con su departamento y datos base.'
                    ],
                    'teachers.edit' => [
                        'label' => 'Editar Profesores',
                        'description' => 'Modificar perfiles, departamentos y vinculaciones de profesores.'
                    ],
                    'teachers.delete' => [
                        'label' => 'Eliminar Profesores',
                        'description' => 'Borrar profesores del directorio escolar.'
                    ],
                    'groups.view' => [
                        'label' => 'Ver Grupos',
                        'description' => 'Consultar grupos formativos, cursos y tutores.'
                    ],
                    'groups.create' => [
                        'label' => 'Crear Grupos',
                        'description' => 'Dar de alta nuevos grupos escolares.'
                    ],
                    'groups.edit' => [
                        'label' => 'Editar Grupos',
                        'description' => 'Modificar niveles, tutores y aulas de grupos.'
                    ],
                    'groups.delete' => [
                        'label' => 'Eliminar Grupos',
                        'description' => 'Eliminar grupos de alumnos.'
                    ],
                ],
            ],
            'centro' => [
                'title' => 'Centro, Aulas y Calendario',
                'icon' => '🏫',
                'description' => 'Infraestructura del centro educativo, cursos escolares y calendarios.',
                'permissions' => [
                    'school_years.manage' => [
                        'label' => 'Cursos Escolares',
                        'description' => 'Crear, editar y activar el curso escolar vigente.'
                    ],
                    'aulas.view' => [
                        'label' => 'Ver Aulas y Espacios',
                        'description' => 'Consultar aulas del instituto, capacidades y tipos.'
                    ],
                    'aulas.manage' => [
                        'label' => 'Administrar Aulas',
                        'description' => 'Crear, editar y eliminar aulas o espacios docentes.'
                    ],
                    'zonas.view' => [
                        'label' => 'Ver Zonas de Guardia',
                        'description' => 'Consultar zonas de patio, pasillos y guardias.'
                    ],
                    'zonas.manage' => [
                        'label' => 'Administrar Zonas',
                        'description' => 'Crear, editar y eliminar zonas de vigilancia.'
                    ],
                    'calendars.view' => [
                        'label' => 'Ver Calendario Escolar',
                        'description' => 'Consultar eventos, días lectivos y festivos del centro.'
                    ],
                    'calendars.manage' => [
                        'label' => 'Administrar Calendario y Festivos',
                        'description' => 'Añadir, modificar y borrar festivos y eventos del calendario.'
                    ],
                ],
            ],
            'horarios' => [
                'title' => 'Horarios del Profesorado',
                'icon' => '⏰',
                'description' => 'Plantillas horarias y horarios individuales de profesores.',
                'permissions' => [
                    'schedules.view' => [
                        'label' => 'Ver Horarios del Centro',
                        'description' => 'Consultar horarios personales y cuadrantes del profesorado.'
                    ],
                    'schedules.manage' => [
                        'label' => 'Gestionar Plantillas y Horarios',
                        'description' => 'Crear plantillas de tramos y configurar horarios de profesores.'
                    ],
                ],
            ],
            'salidas' => [
                'title' => 'Gestor de Salidas (Pases de Aula)',
                'icon' => '🚪',
                'description' => 'Pases digitales de salida del aula (baño, enfermería, jefatura).',
                'permissions' => [
                    'salidas.view' => [
                        'label' => 'Monitor de Salidas en Vivo',
                        'description' => 'Visualizar los alumnos que están fuera del aula en tiempo real.'
                    ],
                    'salidas.create' => [
                        'label' => 'Emitir Pases de Salida',
                        'description' => 'Conceder un pase de salida a un alumno desde el aula.'
                    ],
                    'salidas.return_monitor' => [
                        'label' => 'Regresar Alumnos en Monitor',
                        'description' => 'Permite registrar el regreso de alumnos y finalizar pases desde el monitor de pasillos.'
                    ],
                    'salidas.manage' => [
                        'label' => 'Administrar Pases e Historial',
                        'description' => 'Finalizar pases de cualquier aula, consultar historial y exportar CSV.'
                    ],
                ],
            ],
            'cuaderno_evaluacion' => [
                'title' => 'Cuaderno del Profesor y Evaluación',
                'icon' => '📓',
                'description' => 'Módulos, asistencias de clase, actividades y calificaciones.',
                'permissions' => [
                    'modulos.view' => [
                        'label' => 'Ver Módulos / Asignaturas',
                        'description' => 'Consultar los módulos y materias asignadas.'
                    ],
                    'modulos.manage' => [
                        'label' => 'Administrar Módulos',
                        'description' => 'Crear, asignar profesores y estructurar módulos lectivos.'
                    ],
                    'cuaderno.view' => [
                        'label' => 'Ver Cuaderno de Notas',
                        'description' => 'Consultar sesiones de clase y registros de asistencia.'
                    ],
                    'cuaderno.manage' => [
                        'label' => 'Gestionar Cuaderno y Asistencias',
                        'description' => 'Pasar lista, registrar observaciones, criterios y rúbricas.'
                    ],
                    'notas.manage' => [
                        'label' => 'Poner Calificaciones',
                        'description' => 'Introducir y editar notas de exámenes y actividades.'
                    ],
                ],
            ],
            'comunicacion_documentos' => [
                'title' => 'Comunicación y Documentos',
                'icon' => '✉️',
                'description' => 'Mensajería interna, repositorio documental e incidencias.',
                'permissions' => [
                    'messages.view' => [
                        'label' => 'Bandeja de Mensajería',
                        'description' => 'Acceder y leer la mensajería interna del centro.'
                    ],
                    'messages.send' => [
                        'label' => 'Enviar Mensajes',
                        'description' => 'Redactar y remitir mensajes a otros usuarios.'
                    ],
                    'documentos.view' => [
                        'label' => 'Consultar Documentos',
                        'description' => 'Ver y descargar documentos oficiales del instituto.'
                    ],
                    'documentos.manage' => [
                        'label' => 'Administrar Documentos',
                        'description' => 'Subir, clasificar y borrar documentos institucionales.'
                    ],
                    'incidencias.view' => [
                        'label' => 'Ver Incidencias',
                        'description' => 'Consultar el registro de incidencias disciplinarias.'
                    ],
                    'incidencias.manage' => [
                        'label' => 'Tramitar Incidencias',
                        'description' => 'Crear, modificar y resolver partes de incidencia.'
                    ],
                ],
            ],
            'tic_mantenimiento' => [
                'title' => 'Recursos TIC y Mantenimiento',
                'icon' => '💻',
                'description' => 'Inventario TIC, reservas, copias de seguridad y sistema.',
                'permissions' => [
                    'tic.view' => [
                        'label' => 'Ver Recursos y Reservas TIC',
                        'description' => 'Consultar disponibilidad de carritos, proyectores y aulas TIC.'
                    ],
                    'tic.manage' => [
                        'label' => 'Administrar Recursos TIC',
                        'description' => 'Gestionar inventario, crear categorías y autorizar reservas.'
                    ],
                    'backups.manage' => [
                        'label' => 'Copias de Seguridad',
                        'description' => 'Generar y descargar copias de respaldo de la base de datos.'
                    ],
                    'actualizaciones.manage' => [
                        'label' => 'Actualizaciones del Sistema',
                        'description' => 'Consultar estado y aplicar actualizaciones de la plataforma.'
                    ],
                ],
            ],
        ];
    }

    /**
     * Retorna la lista plana de todos los nombres técnicos de permisos definidos.
     *
     * @return array<string>
     */
    public static function getAllPermissionKeys(): array
    {
        $keys = [];
        foreach (self::getCatalog() as $module) {
            foreach ($module['permissions'] as $permKey => $permData) {
                $keys[] = $permKey;
            }
        }
        return $keys;
    }

    /**
     * Retorna los permisos que deben asignarse por defecto a cada rol base del centro.
     *
     * @return array<string, array<string>>
     */
    public static function getDefaultRoleAssignments(): array
    {
        $all = self::getAllPermissionKeys();

        return [
            'admin' => $all, // Admin tiene todos los permisos

            'directiva' => [
                // Guardias y Ausencias completas
                'guardias.view', 'guardias.assign', 'guardias.sign', 'guardias.manage',
                'ausencias.view', 'ausencias.create', 'ausencias.edit', 'ausencias.delete', 'ausencias.justify',
                // Usuarios y Roles de consulta
                'users.view', 'users.create', 'users.edit', 'roles.view',
                // Alumnos, Profesores y Grupos
                'students.view', 'students.create', 'students.edit', 'students.import',
                'teachers.view', 'teachers.create', 'teachers.edit',
                'groups.view', 'groups.create', 'groups.edit',
                // Centro y Horarios
                'school_years.manage', 'aulas.view', 'aulas.manage', 'zonas.view', 'zonas.manage',
                'calendars.view', 'calendars.manage',
                'schedules.view', 'schedules.manage',
                // Salidas
                'salidas.view', 'salidas.create', 'salidas.return_monitor', 'salidas.manage',
                // Evaluación y Módulos
                'modulos.view', 'cuaderno.view',
                // Comunicación y Documentos
                'messages.view', 'messages.send',
                'documentos.view', 'documentos.manage',
                'incidencias.view', 'incidencias.manage',
                'tic.view',
            ],

            'director' => [
                // Equivalente a directiva con permisos ampliados
                'guardias.view', 'guardias.assign', 'guardias.sign', 'guardias.manage',
                'ausencias.view', 'ausencias.create', 'ausencias.edit', 'ausencias.delete', 'ausencias.justify',
                'users.view', 'roles.view',
                'students.view', 'teachers.view', 'groups.view',
                'school_years.manage', 'aulas.view', 'zonas.view', 'calendars.view', 'calendars.manage',
                'schedules.view', 'schedules.manage',
                'salidas.view', 'salidas.return_monitor', 'salidas.manage',
                'modulos.view', 'cuaderno.view',
                'messages.view', 'messages.send',
                'documentos.view', 'documentos.manage',
                'incidencias.view', 'incidencias.manage',
            ],

            'controlador-pasillo' => [
                'salidas.view', 'salidas.return_monitor',
                'messages.view', 'messages.send',
            ],

            'conserje' => [
                'salidas.view',
                'messages.view', 'messages.send',
                'calendars.view',
            ],

            'profesor' => [
                // Guardias y Ausencias operativas del docente
                'guardias.view', 'guardias.sign',
                'ausencias.view', 'ausencias.create',
                // Consulta de alumnos y profesores
                'students.view', 'teachers.view', 'groups.view',
                'aulas.view', 'zonas.view', 'calendars.view',
                'schedules.view',
                // Gestor de salidas
                'salidas.view', 'salidas.create',
                // Cuaderno y notas
                'modulos.view', 'cuaderno.view', 'cuaderno.manage', 'notas.manage',
                // Comunicación
                'messages.view', 'messages.send',
                'documentos.view',
                'incidencias.view', 'incidencias.manage',
                'tic.view',
            ],

            'jefe-de-departamento' => [
                'guardias.view', 'guardias.sign',
                'ausencias.view', 'ausencias.create',
                'students.view', 'teachers.view', 'groups.view',
                'aulas.view', 'zonas.view', 'calendars.view',
                'schedules.view',
                'salidas.view', 'salidas.create',
                'modulos.view', 'modulos.manage', 'cuaderno.view', 'cuaderno.manage', 'notas.manage',
                'messages.view', 'messages.send',
                'documentos.view', 'documentos.manage',
                'incidencias.view', 'incidencias.manage',
                'tic.view',
            ],

            'administrativo' => [
                'users.view', 'users.create', 'users.edit',
                'students.view', 'students.create', 'students.edit', 'students.import',
                'teachers.view', 'groups.view',
                'school_years.manage', 'aulas.view', 'calendars.view', 'calendars.manage',
                'documentos.view', 'documentos.manage',
                'messages.view', 'messages.send',
            ],

            'tecnico-tic' => [
                'users.view',
                'aulas.view',
                'tic.view', 'tic.manage',
                'messages.view', 'messages.send',
                'documentos.view',
            ],

            'alumno' => [
                'modulos.view',
                'salidas.view',
                'messages.view', 'messages.send',
                'calendars.view',
                'documentos.view',
            ],
        ];
    }

    /**
     * Sincroniza la creación de todos los permisos en la base de datos
     * y asegura que los permisos legacy sean mapeados o conservados.
     */
    public static function syncDatabasePermissions(): int
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos legacy que conservamos por compatibilidad
        $legacy = ['manage roles', 'manage teacher schedules', 'manage users'];
        foreach ($legacy as $legKey) {
            Permission::firstOrCreate(['name' => $legKey, 'guard_name' => 'web']);
        }

        $count = 0;
        foreach (self::getAllPermissionKeys() as $key) {
            Permission::firstOrCreate(['name' => $key, 'guard_name' => 'web']);
            $count++;
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        return $count;
    }

    /**
     * Aplica la asignación de permisos predeterminada a los roles existentes.
     */
    public static function applyDefaultAssignments(): void
    {
        self::syncDatabasePermissions();

        $assignments = self::getDefaultRoleAssignments();
        foreach ($assignments as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                // Obtenemos los permisos válidos
                $validPerms = Permission::whereIn('name', $perms)->get();
                $role->syncPermissions($validPerms);
            }
        }

        // El rol admin debe tener absolutamente todos los permisos
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->syncPermissions(Permission::all());
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
