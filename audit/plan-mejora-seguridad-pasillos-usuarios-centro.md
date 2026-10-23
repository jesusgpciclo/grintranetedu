# 🛡️ Plan Integral de Mejora de Seguridad: Módulo de Pasillos, Usuarios y Centro

> **Documento:** Plan de Remediación y Pasos de Implementación de Seguridad  
> **Referencia:** Auditoría de Seguridad [`audit/security-audit-report.md`](file:///c:/xampp/htdocs/grintranetedu/audit/security-audit-report.md)  
> **Proyecto:** Intranet Educativa (`grintranetedu`)  
> **Áreas de Enfoque:** 
> 1. **Módulo de Pasillos (Control de Salidas, Historial y Monitor)**
> 2. **Gestión de Usuarios (Docentes, Alumnos, Perfiles, Autenticación y Credenciales)**
> 3. **Gestión del Centro (Cursos Escolares, Grupos, Aulas, Dependencias y Configuración)**
>
> **Normativa aplicable:** RGPD / LOPDGDD (Tratamiento de datos personales de menores de edad).

---

## 1. Resumen Ejecutivo y Diagnóstico

El informe de auditoría identificó vulnerabilidades estructurales en la aplicación. Este documento traduce los hallazgos en una **hoja de ruta secuencial y aplicable paso a paso**, enfocándose prioritariamente en salvaguardar la identidad de los usuarios, la integridad de los datos académicos y disciplinarios del centro, y el funcionamiento seguro del módulo de pasillos.

### 1.1 Principios Rectores del Plan
1. **Autorización *Fail-Closed* por Defecto:** Ninguna acción de escritura, edición o borrado debe ejecutarse a menos que el usuario acredite explícitamente el permiso o rol requerido. Se elimina el antipatrón `if ($user->hasRole('profesor')) { ... }` sin cláusula de denegación (`abort(403)`) para el resto de roles.
2. **Defensa en Profundidad:** La seguridad no recae únicamente en la interfaz visual o en el middleware de ruta, sino que se valida en el middleware, en el controlador/Policy y en la consulta a la base de datos.
3. **Mínimo Privilegio:** Un profesor solo debe gestionar sus propias salidas o los alumnos de su tutoría; un alumno no debe tener visibilidad ni capacidad de alteración sobre ningún dato ajeno; la administración del centro se reserva a los roles `admin` y `directiva`.
4. **Protección de Datos Personales (RGPD):** Especial atención a los datos de menores en el monitor público de pasillos y justificantes.

---

## 2. Diagnóstico Específico por Componente

### 2.1 Módulo de Pasillos (`HallPassController`, Incompatibilidades y Monitor)

| Vector de Riesgo | Ubicación | Severidad | Descripción del Problema |
|---|---|:---:|---|
| **SQL Injection en Historial** | [`HallPassController.php:645-647`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L645-L647) | 🟠 Alta | El parámetro `$direction` se concatena directamente en sentencias `orderByRaw()`, abriendo vectores de inyección SQL a ciegas. |
| **Borrado no Autorizado de Salidas (IDOR)** | [`HallPassController.php:487-490`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L487-L490) | 🟠 Alta | La condición `$canDelete` permite que **cualquier profesor borre cualquier pase** (incluso los de otros docentes), permitiendo eliminar evidencias de absentismo o faltas. |
| **Rutas Sobre-Privilegiadas y Métodos Inexistentes** | [`routes/web.php:148-166`](file:///c:/xampp/htdocs/grintranetedu/routes/web.php#L148-L166) | 🟠 Alta | Rutas críticas como `clearHistory`, `bulkDelete` y `settings.update` están dentro del middleware común (`conserje`, `controlador-pasillo`, `profesor`). Además, `clearHistory` no existe en el controlador. |
| **Bypass de Límite de Alumnos en Servicio** | [`HallPassController.php:306, 327`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L306) | 🟡 Media | Los parámetros booleanos `force` y `force_service` son aceptados directamente del cliente sin verificar si el usuario tiene privilegios para saltarse los límites establecidos. |
| **Creación de Pases a Usuarios no Alumnos** | [`HallPassController.php:291`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L291) | 🟡 Media | La validación `'student_id' => 'required|exists:users,id'` no verifica que el usuario tenga el rol `alumno`, permitiendo generar pases espurios a profesores o personal de conserjería. |
| **Retorno Masivo sin Control de Grupo** | [`HallPassController.php:560`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L560) | 🟡 Media | Cualquier profesor puede finalizar en masa (`returnAll`) los pases de alumnos de **cualquier grupo**, interfiriendo en clases de otros compañeros. |
| **Exposición de Datos Sensibles en Monitor** | [`salidas/monitor.blade.php`](file:///c:/xampp/htdocs/grintranetedu/resources/views/salidas/monitor.blade.php) | 🟡 Media | En pantallas públicas de conserjería o pasillo se muestran motivos literales que pueden contener datos de salud o personales de menores. |

---

### 2.2 Gestión de Usuarios y Autenticación

| Vector de Riesgo | Ubicación | Severidad | Descripción del Problema |
|---|---|:---:|---|
| **Takeover de Cuentas de Profesores (C-03)** | [`TeacherController.php:99-125`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/TeacherController.php#L99-L125) | 🔴 Crítica | Rutas protegidas únicamente por `auth`. Cualquier usuario autenticado (incluidos alumnos) puede cambiar el correo y la contraseña de cualquier profesor o crear/eliminar docentes. |
| **Manipulación y Borrado de Alumnos (A-02)** | [`StudentController.php:92, 119, 140`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/StudentController.php#L92) | 🟠 Alta | El bloque `if ($user->hasRole('profesor') && !$user->hasRole('admin'))` es *fail-open*: si un `alumno` envía peticiones `PUT` o `DELETE`, la condición evalúa a `false` y el controlador ejecuta la modificación/borrado. |
| **Pre-Hijacking y Auto-Registro Google (C-05)** | [`SocialAuthController.php:24-38`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/SocialAuthController.php#L24-L38) | 🔴 Crítica | Inicio de sesión por coincidencia de email sin comprobar contraseña previa ni verificar que el dominio pertenezca al centro (`@centro.edu.es`). Cualquier cuenta externa de Gmail obtiene cuenta con rol `alumno`. |
| **Contraseñas por Defecto Predecibles (C-06)** | `TeacherController.php:84`, `StudentController.php:104` | 🔴 Crítica | Si no se indica contraseña, el sistema asigna el propio email como contraseña en texto plano cifrado. Cuentas creadas masivamente comparten credenciales conocidas. |
| **Tokens de Reset sin Expiración y en Logs (C-04)** | [`PasswordResetController.php:48-76`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/PasswordResetController.php#L48-L76) | 🔴 Crítica | Enlaces de recuperación con tokens válidos indefinidamente, registrados en `storage/logs/laravel.log`, sin rate limit en el endpoint `POST` y con mensajes que permiten enumerar usuarios. |
| **Cambio de Contraseña sin Verificar la Actual (M-05)** | [`ProfileController.php:108-118`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/ProfileController.php#L108-L118) | 🟡 Media | Si una sesión queda abierta o es secuestrada, el atacante cambia la contraseña sin conocer la previa. |
| **Campos Sensibles en `$fillable` de `User` (A-03)** | [`User.php:30-46`](file:///c:/xampp/htdocs/grintranetedu/app/Models/User.php#L30-L46) | 🟠 Alta | `password`, `google_id`, `must_change_password` y `active_role` están en `$fillable`, expuestos a sobreescritura accidental o por asignación masiva. |

---

### 2.3 Gestión del Centro y Configuración

| Vector de Riesgo | Ubicación | Severidad | Descripción del Problema |
|---|---|:---:|---|
| **Cambio de Curso Escolar por GET sin CSRF (A-07)** | [`ActiveSchoolYear.php:20-31`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Middleware/ActiveSchoolYear.php#L20-L31) | 🟠 Alta | El parámetro `?set_school_year_id=` cambia el año escolar de la sesión mediante una simple petición `GET` vulnerable a CSRF. |
| **Importación de Aulas sin Autorización (A-02, A-04)** | [`AulaController.php:177`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/AulaController.php#L177) | 🟠 Alta | Importación masiva de aulas sin comprobación de rol directivo, permitiendo inyectar nombres que luego se renderizan en layouts transversales. |
| **Exposición de `.env` y Backup con Passwords (A-06, A-09)** | [`BackupController.php:101-117`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/BackupController.php#L101-L117) | 🟠 Alta | Pasaje de contraseña de BD en argumento CLI `--password=` visible en listas de procesos del sistema y en mensajes flash de error. Riesgo si el VirtualHost no apunta a `/public`. |

---

## 3. Plan de Acción Paso a Paso

---

### FASE 1: Hotfixes Inmediatos de Seguridad (Prioridad P0 - Críticos)

#### Paso 1.1: Cerrar el Takeover de Cuentas en [`TeacherController`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/TeacherController.php) y [`StudentController`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/StudentController.php)
- **Objetivo:** Impedir que usuarios no autorizados o alumnos puedan alterar profesores o alumnos.
- **Acciones concretas:**
  1. En [`routes/web.php`](file:///c:/xampp/htdocs/grintranetedu/routes/web.php): Aislar los recursos `teachers` y `students` tras middleware de permisos estrictos:
     ```php
     Route::middleware(['role:admin'])->group(function () {
         Route::resource('teachers', TeacherController::class);
         Route::post('teachers/bulk-delete', [TeacherController::class, 'bulkDelete'])->name('teachers.bulk-delete');
         Route::post('teachers/import-mass', [TeacherController::class, 'import'])->name('teachers.mass-import');
     });

     Route::middleware(['role:admin|directiva|profesor'])->group(function () {
         Route::resource('students', StudentController::class);
         Route::post('students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
         Route::post('students/bulk-change-group', [StudentController::class, 'bulkChangeGroup'])->name('students.bulk-change-group');
     });
     ```
  2. En [`TeacherController.php`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/TeacherController.php): Implementar una barrera *fail-closed* al inicio de cada método sensible:
     ```php
     abort_unless(auth()->user()->hasRole('admin'), 403, 'Acción restringida a administradores.');
     ```
  3. En [`StudentController.php`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/StudentController.php): Reemplazar la condición *fail-open* por una función de autorización estricta:
     ```php
     private function authorizeStudentAction(?User $student = null): void
     {
         $user = auth()->user();
         if ($user->hasAnyRole(['admin', 'directiva'])) {
             return;
         }
         if ($user->hasRole('profesor') && $student) {
             // Solo el tutor del grupo asignado al alumno puede gestionarlo
             $isTutor = Group::where('tutor_id', $user->id)
                 ->where('id', $student->group_id)
                 ->exists();
             if ($isTutor) return;
         }
         abort(403, 'No tienes permisos para gestionar este alumno.');
     }
     ```

#### Paso 1.2: Blindaje de Google OAuth en [`SocialAuthController`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/SocialAuthController.php)
- **Objetivo:** Evitar pre-hijacking de cuentas y auto-registro masivo de usuarios externos.
- **Acciones concretas:**
  1. Definir en `.env` y `config/services.php` el dominio educativo autorizado:
     ```dotenv
     GOOGLE_ALLOWED_DOMAIN=iescentro.es
     ```
  2. En [`SocialAuthController.php`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/SocialAuthController.php):
     - Validar que `$googleUser->email` pertenezca al dominio configurado.
     - **Eliminar el login por `orWhere('email', $googleUser->email)`**: Solo vincular si coincide `google_id`.
     - Si el correo existe pero no tiene `google_id`, requerir inicio de sesión con contraseña antes de enlazar la cuenta.
     - **Desactivar el auto-registro abierto:** Si el usuario no existe en la base de datos, rechazar la autenticación e indicar que debe ser dado de alta por Secretaría.

#### Paso 1.3: Erradicación del Esquema de Contraseñas por Defecto
- **Objetivo:** Eliminar contraseñas predecibles (como el propio email o `alumno1234`).
- **Acciones concretas:**
  1. En `TeacherController`, `StudentController` y `StudentImportController`: Generar contraseñas criptográficas seguras y aleatorias por usuario:
     ```php
     $initialPassword = \Illuminate\Support\Str::password(16, symbols: false);
     $user->password = Hash::make($initialPassword);
     $user->must_change_password = true;
     ```
  2. Crear un comando artisan para identificar y marcar forzosamente para cambio de contraseña a todos los usuarios cuyas contraseñas coincidan con patrones antiguos:
     `php artisan intranet:audit-default-passwords`

#### Paso 1.4: Blindaje de Recuperación de Contraseñas en [`PasswordResetController`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/PasswordResetController.php)
- **Objetivo:** Garantizar caducidad estricta de tokens, evitar filtraciones y bloquear enumeración.
- **Acciones concretas:**
  1. **Respuesta genérica:** Devolver siempre el mensaje: *"Si el correo electrónico existe en nuestra base de datos, se ha enviado un enlace de recuperación."*
  2. **Eliminar registros de tokens en logs:** Suprimir cualquier llamada `Log::info("Password Reset Link...")`.
  3. **Comprobar caducidad (TTL 60 min):** En `reset()`, verificar que `created_at` del token no sea superior a 60 minutos.
  4. **Rate Limiting:** Aplicar `throttle:5,1` a la ruta `password.update` en `routes/web.php`.

#### Paso 1.5: Configuración Segura de Infraestructura y Despliegue
- **Objetivo:** Proteger variables de entorno y sesiones.
- **Acciones concretas:**
  1. Verificar que el DocumentRoot de Apache apunte a `C:/xampp/htdocs/grintranetedu/public` y **nunca** a la raíz del proyecto.
  2. Configurar en `.env`:
     ```dotenv
     APP_DEBUG=false
     SESSION_SECURE_COOKIE=true
     SESSION_ENCRYPT=true
     ```

---

### FASE 2: Fortalecimiento del Módulo de Pasillos (Prioridad P1)

#### Paso 2.1: Eliminación del Vector SQL Injection en Ordenación
- **Objetivo:** Prevenir inyección SQL en consultas de historial.
- **Acciones concretas:**
  - En [`HallPassController.php`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php) método `buildHistoryQuery()`:
    1. Forzar una lista blanca estricta tanto para `$sort` como para `$direction`:
       ```php
       $allowedSorts = ['fecha', 'alumno', 'clase', 'motivo', 'duracion', 'profesor'];
       $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'fecha';
       $direction = strtolower($request->input('direction')) === 'asc' ? 'ASC' : 'DESC';
       ```
    2. En el caso `'duracion'`, sustituir la concatenación insegura por una sentencia con dirección tipificada en mayúsculas:
       ```php
       case 'duracion':
           if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
               $query->orderByRaw("(julianday(end_time) - julianday(start_time)) {$direction}");
           } else {
               $query->orderByRaw("TIMESTAMPDIFF(MINUTE, start_time, end_time) {$direction}");
           }
           break;
       ```

#### Paso 2.2: Corrección de Autorización e IDOR en Eliminación de Salidas
- **Objetivo:** Impedir que profesores eliminen salidas autorizadas por otros compañeros.
- **Acciones concretas:**
  - En [`HallPassController::destroy()`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L480):
    ```php
    public function destroy(HallPass $hallPass)
    {
        $user = auth()->user();
        abort_unless($user, 401);

        // Solo puede eliminar el profesor que emitió la salida O roles directivos
        $isOwnerTeacher = ($hallPass->teacher_id === $user->id);
        $isExecutive = $user->hasAnyRole(['admin', 'jefatura', 'directiva', 'director']);

        if (!$isOwnerTeacher && !$isExecutive) {
            abort(403, 'No tienes permiso para eliminar una salida autorizada por otro docente.');
        }

        $hallPass->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Salida eliminada correctamente.']);
        }
        return back()->with('success', 'Salida eliminada correctamente.');
    }
    ```

#### Paso 2.3: Segregación de Rutas y Permisos en [`routes/web.php`](file:///c:/xampp/htdocs/grintranetedu/routes/web.php)
- **Objetivo:** Restringir acciones sensibles de pasillos únicamente a roles autorizados.
- **Acciones concretas:**
  - Modificar el grupo de rutas de `salidas`:
    ```php
    // Acceso general para emisión y consulta operativa de salidas
    Route::middleware(['role:admin|profesor|conserje|directiva|director|controlador-pasillo|jefatura|jefe-de-departamento'])->group(function () {
        Route::get('/salidas', [HallPassController::class, 'index'])->name('salidas.index');
        Route::get('/salidas/cards', [HallPassController::class, 'getCards'])->name('salidas.cards');
        Route::post('/salidas/pass', [HallPassController::class, 'store'])->name('salidas.store');
        Route::patch('/salidas/pass/{hallPass}', [HallPassController::class, 'update'])->name('salidas.update');
        Route::get('/salidas/monitor', [HallPassController::class, 'monitor'])->name('salidas.monitor');
        Route::post('/salidas/favorites', [HallPassController::class, 'toggleFavoriteGroup'])->name('salidas.toggle-favorite');
        Route::patch('/salidas/pass/{hallPass}/time', [HallPassController::class, 'updatePassTime'])->name('salidas.pass.update-time');
        Route::delete('/salidas/pass/{hallPass}', [HallPassController::class, 'destroy'])->name('salidas.destroy');
    });

    // Operaciones administrativas y de configuración (solo directiva y admin)
    Route::middleware(['role:admin|jefatura|directiva|director'])->group(function () {
        Route::get('/salidas/history', [HallPassController::class, 'history'])->name('salidas.history');
        Route::get('/salidas/history/export-csv', [HallPassController::class, 'exportCsv'])->name('salidas.history.export-csv');
        Route::get('/salidas/history/print', [HallPassController::class, 'printHistory'])->name('salidas.history.print');
        Route::post('/salidas/history/bulk-delete', [HallPassController::class, 'bulkDelete'])->name('salidas.history.bulk-delete');
        Route::patch('/salidas/settings', [HallPassController::class, 'updateSettings'])->name('salidas.settings.update');
        
        // Incompatibilidades y Avisos restringidos a dirección/orientación
        Route::resource('salidas/incompatibilities', HallPassIncompatibilityController::class, ['as' => 'salidas']);
        Route::resource('salidas/notices', HallPassNoticeController::class, ['as' => 'salidas']);
        Route::patch('salidas/notices/{notice}/toggle', [HallPassNoticeController::class, 'toggle'])->name('salidas.notices.toggle');
    });
    ```
  - **Eliminar** la ruta muerta `Route::delete('/salidas/history/clear')`.

#### Paso 2.4: Validación Estricta en Creación de Pases y Parámetros Forzados
- **Objetivo:** Evitar pases a no-alumnos y uso abusivo de banderas `force`.
- **Acciones concretas:**
  - En [`HallPassController::store()`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L290):
    ```php
    $request->validate([
        'student_id' => [
            'required',
            \Illuminate\Validation\Rule::exists('users', 'id')->where(function ($query) {
                // Asegurar que el usuario asignado tenga rol alumno
                $query->whereHas('roles', fn ($q) => $q->where('name', 'alumno'));
            }),
        ],
        'reason' => 'required|string|max:255',
    ]);
    ```
  - Para los flags `force` y `force_service`: Permitirlos solo si el usuario autenticado tiene rol `profesor` o directivo, y registrar en bitácora (`Log::info`) cuando un docente fuerza una salida por encima del cupo de servicio o de aula.

#### Paso 2.5: Retorno Masivo Acotado al Contexto Docente
- **Objetivo:** Evitar que un profesor interrumpa pases de grupos ajenos.
- **Acciones concretas:**
  - En [`HallPassController::returnAll()`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/HallPassController.php#L547):
    - Si el usuario es `profesor` (y no directivo/admin), validar que el profesor imparta clase a ese grupo en ese tramo horario o sea tutor del mismo.

#### Paso 2.6: Modo Privacidad en Monitor de Pasillos (RGPD)
- **Objetivo:** Cumplir con la protección de datos personales de menores en pantallas visibles a terceros.
- **Acciones concretas:**
  - En [`salidas/monitor.blade.php`](file:///c:/xampp/htdocs/grintranetedu/resources/views/salidas/monitor.blade.php):
    - Añadir un conmutador de **"Modo Pantalla Pública"**: Oculta detalles íntimos de los motivos ("Enfermería", "Motivo reservado" en lugar de descripciones médicas explícitas) y muestra únicamente: *Nombre de pila + Inicial de apellido*, *Curso/Grupo*, *Tiempo fuera* y *Alerta de exceso de tiempo*.

---

### FASE 3: Seguridad del Centro y Gobernanza de Datos (Prioridad P2)

#### Paso 3.1: Securización del Cambio de Curso Escolar Activo
- **Objetivo:** Eliminar vector CSRF en el cambio de año escolar.
- **Acciones concretas:**
  1. En `routes/web.php`: Crear ruta `POST`:
     ```php
     Route::post('/school-year/set-active', [SchoolYearController::class, 'setActiveYear'])
         ->name('school-year.set-active');
     ```
  2. En el middleware [`ActiveSchoolYear.php`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Middleware/ActiveSchoolYear.php): Eliminar la lectura del parámetro GET `?set_school_year_id=`.
  3. Actualizar el selector en la interfaz superior/barra lateral para enviar un formulario `POST` con directiva `@csrf`.

#### Paso 3.2: Limpieza del Modelo [`User.php`](file:///c:/xampp/htdocs/grintranetedu/app/Models/User.php) contra Asignación Masiva
- **Objetivo:** Proteger campos de control de acceso en la base de datos.
- **Acciones concretas:**
  - En [`app/Models/User.php`](file:///c:/xampp/htdocs/grintranetedu/app/Models/User.php):
    - Retirar de `$fillable`: `'password'`, `'google_id'`, `'must_change_password'`, `'active_role'`.
    - Mantener únicamente datos de perfil no críticos (`name`, `last_name`, `email`, `avatar`, `group_id`, `department`, `phone`).
    - Para actualizar los campos sensibles, utilizar llamadas explícitas a `$user->forceFill([...])->save()` desde los controladores autorizados (`AuthController`, `PasswordResetController`, `ProfileController`).

#### Paso 3.3: Verificación de Contraseña Actual en Perfil
- **Objetivo:** Evitar persistencia de sesión ilegítima tras secuestro.
- **Acciones concretas:**
  - En [`ProfileController::update()`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/ProfileController.php):
    - Si el request incluye un nuevo valor para `password`, exigir la regla `'current_password' => 'required|current_password'`.

#### Paso 3.4: Blindaje de [`BackupController`](file:///c:/xampp/htdocs/grintranetedu/app/Http/Controllers/BackupController.php)
- **Objetivo:** Evitar filtración de contraseñas de base de datos en procesos del sistema y flashes de error.
- **Acciones concretas:**
  1. Sustituir el paso del argumento `--password=` por un archivo de opciones temporal (`--defaults-extra-file`) con permisos `0600` que se elimine inmediatamente tras la ejecución.
  2. En caso de error, devolver un mensaje genérico *"Error al generar copia de seguridad. Consulte el log del servidor"* sin volcar la variable `$command` o `$fullDebug` en la sesión flash.

#### Paso 3.5: Rate Limiting y Protección contra Ataques DoS
- **Objetivo:** Prevenir denegación de servicio en listados masivos.
- **Acciones concretas:**
  - Acotar el parámetro `per_page` en todos los controladores que acepten paginación dinámica:
    ```php
    $perPage = min(100, max(10, (int) $request->input('per_page', 25)));
    ```

---

## 4. Batería de Pruebas de Seguridad y Regresión

Para garantizar que todas las medidas funcionen y no se produzcan regresiones, se implementará una suite de pruebas automatizadas en `tests/Feature/SecurityHardeningPhaseTest.php`.

### Casos de Prueba Automatizados Obligatorios

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityHardeningPhaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_alumno_no_puede_modificar_a_un_profesor()
    {
        $alumno = User::factory()->create();
        $alumno->assignRole('alumno');
        $profesor = User::factory()->create();
        $profesor->assignRole('profesor');

        $this->actingAs($alumno)
            ->patch(route('teachers.update', $profesor), [
                'name' => 'Docente Modificado',
                'email' => 'docente.hack@centro.es',
                'password' => 'NuevaPassword#123',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function un_alumno_no_puede_borrar_ni_modificar_a_otro_alumno()
    {
        $alumnoA = User::factory()->create();
        $alumnoA->assignRole('alumno');
        $alumnoB = User::factory()->create();
        $alumnoB->assignRole('alumno');

        $this->actingAs($alumnoA)
            ->delete(route('students.destroy', $alumnoB))
            ->assertForbidden();
    }

    /** @test */
    public function un_profesor_no_puede_borrar_el_pase_autorizado_por_otro_profesor()
    {
        $profesor1 = User::factory()->create();
        $profesor1->assignRole('profesor');
        $profesor2 = User::factory()->create();
        $profesor2->assignRole('profesor');
        $alumno = User::factory()->create();
        $alumno->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $alumno->id,
            'teacher_id' => $profesor1->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        // Profesor 2 intenta borrar el pase del Profesor 1
        $this->actingAs($profesor2)
            ->delete(route('salidas.destroy', $pass))
            ->assertForbidden();

        $this->assertDatabaseHas('hall_passes', ['id' => $pass->id]);
    }

    /** @test */
    public function el_profesor_autorizador_si_puede_cancelar_su_propio_pase()
    {
        $profesor = User::factory()->create();
        $profesor->assignRole('profesor');
        $alumno = User::factory()->create();
        $alumno->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $alumno->id,
            'teacher_id' => $profesor->id,
            'reason' => 'Biblioteca',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        $this->actingAs($profesor)
            ->delete(route('salidas.destroy', $pass))
            ->assertRedirect();

        $this->assertDatabaseMissing('hall_passes', ['id' => $pass->id]);
    }

    /** @test */
    public function la_ordenacion_del_historial_de_salidas_rechaza_inyecciones_sql()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = 'asc,(SELECT 1 FROM (SELECT SLEEP(0))a)';

        $this->actingAs($admin)
            ->get(route('salidas.history', ['sort' => 'duracion', 'direction' => $payload]))
            ->assertOk(); // El controlador debe sanitizar a 'DESC' o 'ASC' sin error SQL
    }

    /** @test */
    public function google_oauth_rechaza_dominios_no_autorizados()
    {
        // Simular callback con cuenta externa @gmail.com
        config(['services.google.allowed_domain' => 'iescentro.es']);

        // Debe redirigir con error al login sin crear usuario
        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'));
    }
}
```

---

## 5. Matriz de Responsabilidades y Checklist de Verificación

| ID | Tarea de Seguridad | Módulo Afectado | Prioridad | Estado |
|:---:|---|---|:---:|:---:|
| **SEC-01** | Proteger `TeacherController` con rol `admin` y fail-closed | Usuarios (Docentes) | **P0** | ⏳ Pendiente |
| **SEC-02** | Proteger `StudentController` con permisos de tutor o directiva | Usuarios (Alumnos) | **P0** | ⏳ Pendiente |
| **SEC-03** | Restringir Google OAuth a `@dominio-centro` y sin auto-registro abierto | Autenticación | **P0** | ⏳ Pendiente |
| **SEC-04** | Erradicar esquema de contraseñas por defecto (`email` como password) | Autenticación | **P0** | ⏳ Pendiente |
| **SEC-05** | Caducidad (TTL 60m), sin logs y anti-enumeración en `PasswordReset` | Autenticación | **P0** | ⏳ Pendiente |
| **SEC-06** | Sanitizar `orderByRaw` en `HallPassController::history` (anti-SQLi) | Pasillos (Salidas) | **P1** | ⏳ Pendiente |
| **SEC-07** | Restringir `HallPassController::destroy` al creador o directiva | Pasillos (Salidas) | **P1** | ⏳ Pendiente |
| **SEC-08** | Limpieza de rutas de salidas (`clearHistory`, `settings`, `history`) | Pasillos (Rutas) | **P1** | ⏳ Pendiente |
| **SEC-09** | Validar rol `alumno` en creación de pases y auditar `force` | Pasillos (Salidas) | **P1** | ⏳ Pendiente |
| **SEC-10** | Migrar cambio de curso escolar (`ActiveSchoolYear`) de GET a POST | Centro (Cursos) | **P2** | ⏳ Pendiente |
| **SEC-11** | Retirar campos sensibles de `$fillable` en `User.php` | Usuarios (Modelo) | **P2** | ⏳ Pendiente |
| **SEC-12** | Requerir `current_password` en cambio de contraseña de perfil | Usuarios (Perfil) | **P2** | ⏳ Pendiente |
| **SEC-13** | Hardening de `BackupController` (credenciales en defaults-file) | Centro (Backups) | **P2** | ⏳ Pendiente |
| **SEC-14** | Ejecución de suite de tests de regresión de seguridad (`SecurityHardeningPhaseTest`) | Transversal | **P0/P1** | ⏳ Pendiente |

---

## 6. Recomendaciones Operativas para Producción

1. **Revisión del VirtualHost de Apache en XAMPP/VPS:**
   Garantizar que el servidor web nunca sirva la raíz del proyecto. El archivo de configuración debe declarar:
   ```apache
   DocumentRoot "C:/xampp/htdocs/grintranetedu/public"
   <Directory "C:/xampp/htdocs/grintranetedu/public">
       AllowOverride All
       Require all granted
   </Directory>
   ```
2. **Monitoreo de Intentos de Acceso:**
   Activar alertas automáticas en `storage/logs/laravel.log` ante bloqueos recurrentes por `403 Forbidden` en endpoints de profesorado o restablecimiento de contraseñas.
3. **Copia de Seguridad Diaria Cifrada:**
   Programar la ejecución de backups de la base de datos fuera del horario lectivo y alojarlos en un almacenamiento aislado o secundario.
