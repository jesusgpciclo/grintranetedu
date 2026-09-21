<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SocialAuthController;

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ScheduleTemplateController;
use App\Http\Controllers\ZonaController;
use App\Http\Controllers\AusenciaController;
use App\Http\Controllers\GuardiaController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->middleware('throttle:3,1')->name('password.email');
Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

// Google Auth Routes
Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Group routes
    Route::get('groups/export/{format}', [GroupController::class, 'export'])->name('groups.export');
    Route::get('groups/template/{format}', [GroupController::class, 'template'])->name('groups.template');
    Route::post('groups/import', [GroupController::class, 'import'])->name('groups.import');
    Route::resource('groups', GroupController::class);

    // Student Manager routes
    Route::post('students/bulk-delete', [\App\Http\Controllers\StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    Route::post('students/bulk-change-group', [\App\Http\Controllers\StudentController::class, 'bulkChangeGroup'])->name('students.bulk-change-group');
    Route::get('students/export/{format}', [\App\Http\Controllers\StudentController::class, 'export'])->name('students.export');
    Route::get('students/template/{format}', [\App\Http\Controllers\StudentController::class, 'template'])->name('students.template');
    Route::post('students/import-mass', [\App\Http\Controllers\StudentController::class, 'import'])->name('students.mass-import');
    Route::resource('students', \App\Http\Controllers\StudentController::class);

    // Teacher Manager routes
    Route::post('teachers/bulk-delete', [\App\Http\Controllers\TeacherController::class, 'bulkDelete'])->name('teachers.bulk-delete');
    Route::get('teachers/export/{format}', [\App\Http\Controllers\TeacherController::class, 'export'])->name('teachers.export');
    Route::get('teachers/template/{format}', [\App\Http\Controllers\TeacherController::class, 'template'])->name('teachers.template');
    Route::post('teachers/import-mass', [\App\Http\Controllers\TeacherController::class, 'import'])->name('teachers.mass-import');
    Route::resource('teachers', \App\Http\Controllers\TeacherController::class);

    // Only admins can manage users, roles, and backups
    Route::middleware(['role:admin'])->group(function () {
        Route::get('users/import', [\App\Http\Controllers\ImportController::class, 'index'])->name('users.import');
        Route::post('users/import', [\App\Http\Controllers\ImportController::class, 'import'])->name('users.import.process');
        
        Route::resource('users', UserController::class);
        
        // Permission Matrix and Role Management
        Route::get('roles/permissions-matrix', [RoleController::class, 'matrix'])->name('roles.matrix');
        Route::post('roles/permissions-matrix', [RoleController::class, 'updateMatrix'])->name('roles.matrix.update');
        Route::post('roles/permissions-matrix/toggle', [RoleController::class, 'togglePermission'])->name('roles.matrix.toggle');
        Route::resource('roles', RoleController::class);
        Route::resource('zonas', ZonaController::class);
        Route::post('school-years/{school_year}/activate', [\App\Http\Controllers\SchoolYearController::class, 'activate'])->name('school-years.activate');
        Route::resource('school-years', \App\Http\Controllers\SchoolYearController::class);

        // Backups (Admin Only)
        Route::get('/backups', [\App\Http\Controllers\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [\App\Http\Controllers\BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/download/{filename}', [\App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backups.destroy');
    });

    // Calendar Routes (Modified to point to custom Calendar module)
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    
    // Management routes for directiva/admin
    Route::middleware(['role:admin|directiva'])->group(function () {
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    });

    // Schedule Templates
    Route::resource('schedule-templates', ScheduleTemplateController::class);
    Route::get('schedule-templates/{id}/preview', [ScheduleTemplateController::class, 'preview'])->name('schedule-templates.preview');
    Route::post('schedule-templates/{id}/copy', [ScheduleTemplateController::class, 'copy'])->name('schedule-templates.copy');

    // Personal Schedules
    Route::get('personal-schedules/{personal_schedule}/print', [\App\Http\Controllers\PersonalScheduleController::class, 'print'])->name('personal-schedules.print');
    Route::get('personal-schedules/{personal_schedule}/export/{format?}', [\App\Http\Controllers\PersonalScheduleController::class, 'export'])->name('personal-schedules.export');
    Route::post('personal-schedules/import', [\App\Http\Controllers\PersonalScheduleController::class, 'import'])->name('personal-schedules.import');
    Route::get('personal-schedules/template-download/{format}', [\App\Http\Controllers\PersonalScheduleController::class, 'downloadImportTemplate'])->name('personal-schedules.template');
    Route::resource('personal-schedules', \App\Http\Controllers\PersonalScheduleController::class);

    // Teacher Schedules (Gestión de Horarios del Profesorado)
    Route::get('teacher-schedules', [\App\Http\Controllers\TeacherScheduleController::class, 'index'])->name('teacher-schedules.index');
    Route::get('teacher-schedules/export/{format?}', [\App\Http\Controllers\TeacherScheduleController::class, 'exportAll'])->name('teacher-schedules.export-all');
    Route::post('teacher-schedules/import-bulk', [\App\Http\Controllers\TeacherScheduleController::class, 'importBulk'])->name('teacher-schedules.import-bulk');
    Route::get('teacher-schedules/template/{format}', [\App\Http\Controllers\TeacherScheduleController::class, 'downloadBulkTemplate'])->name('teacher-schedules.template-bulk');

    // --- MÓDULO: GUARDIAS Y AUSENCIAS ---
    Route::resource('ausencias', AusenciaController::class);
    
    Route::prefix('guardias')->name('guardias.')->group(function () {
        Route::get('/parte', [GuardiaController::class, 'parte'])->name('parte');
        Route::post('/confirmar/{ausencia}', [GuardiaController::class, 'confirmar'])->name('confirmar');
        Route::post('/desconfirmar/{ausencia}', [GuardiaController::class, 'desconfirmar'])->name('desconfirmar');
        Route::get('/mis-horas', [GuardiaController::class, 'misHoras'])->name('mis-horas');
        Route::get('/mis-asignadas', [GuardiaController::class, 'misAsignadas'])->name('asignadas');
        Route::post('/toggle-hora', [GuardiaController::class, 'toggleHora'])->name('toggle-hora');
        Route::get('/cuadrante', [GuardiaController::class, 'cuadrante'])->name('cuadrante');
        
        // Directiva / Admin routes
        Route::middleware(['role:admin|directiva'])->group(function () {
            Route::get('/justificaciones', [GuardiaController::class, 'justificaciones'])->name('justificaciones');
            Route::post('/justificar-dia', [GuardiaController::class, 'toggleJustificarDia'])->name('justificar-dia');
            Route::get('/estadisticas', [GuardiaController::class, 'estadisticas'])->name('estadisticas');
            Route::get('/configuracion', [GuardiaController::class, 'configuracion'])->name('configuracion');
            Route::post('/configuracion', [GuardiaController::class, 'updateConfiguracion'])->name('configuracion.update');
            Route::post('/configuracion/zona', [GuardiaController::class, 'storeZona'])->name('configuracion.zona.store');
            Route::delete('/configuracion/zona/{zona}', [GuardiaController::class, 'destroyZona'])->name('configuracion.zona.destroy');
            Route::post('/justificar/{ausencia}', [GuardiaController::class, 'toggleJustificada'])->name('justificar');
        });
    });

    // Salidas Integration
    Route::middleware(['role:admin|profesor|conserje|directiva|director|controlador-pasillo'])->group(function () {
        Route::get('/salidas', [\App\Http\Controllers\HallPassController::class, 'index'])->name('salidas.index');
        Route::post('/salidas/pass', [\App\Http\Controllers\HallPassController::class, 'store'])->name('salidas.store');
        Route::post('/salidas/return-all', [\App\Http\Controllers\HallPassController::class, 'returnAll'])->name('salidas.return-all');
        Route::patch('/salidas/pass/{hallPass}', [\App\Http\Controllers\HallPassController::class, 'update'])->name('salidas.update');
        Route::get('/salidas/monitor', [\App\Http\Controllers\HallPassController::class, 'monitor'])->name('salidas.monitor');
        Route::get('/salidas/history', [\App\Http\Controllers\HallPassController::class, 'history'])->name('salidas.history');
        
        // Salidas history extensions
        Route::get('/salidas/history/export-csv', [\App\Http\Controllers\HallPassController::class, 'exportCsv'])->name('salidas.history.export-csv');
        Route::get('/salidas/history/print', [\App\Http\Controllers\HallPassController::class, 'printHistory'])->name('salidas.history.print');
        Route::delete('/salidas/history/clear', [\App\Http\Controllers\HallPassController::class, 'clearHistory'])->name('salidas.history.clear');
    });

    // --- MÓDULO: Mensajería Interna (Fernández, Raquel) ---
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/sent', [\App\Http\Controllers\MessageController::class, 'sent'])->name('messages.sent');
    Route::get('/messages/create', [\App\Http\Controllers\MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/bulk-action', [\App\Http\Controllers\MessageController::class, 'bulkAction'])->name('messages.bulk-action');
    Route::get('/messages/{message}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{message}', [\App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');
    Route::patch('/messages/{message}/toggle-unread', [\App\Http\Controllers\MessageController::class, 'toggleUnread'])->name('messages.toggle-unread');

    // --- MÓDULO: Reserva de Recursos TIC (De la Orden, Emilio) ---
    Route::get('/tic-bookings', [\App\Http\Controllers\TicBookingController::class, 'index'])->name('tic-bookings.index');
    Route::post('/tic-bookings', [\App\Http\Controllers\TicBookingController::class, 'store'])->name('tic-bookings.store');
    Route::put('/tic-bookings/{ticBooking}', [\App\Http\Controllers\TicBookingController::class, 'update'])->name('tic-bookings.update');
    Route::delete('/tic-bookings/{ticBooking}', [\App\Http\Controllers\TicBookingController::class, 'destroy'])->name('tic-bookings.destroy');

    // --- MÓDULO: Cuaderno del Profesor, Aula y Académico (Jiménez, Juan Antonio / Lugo, Javier) ---
    Route::get('groups/{group}/import', [\App\Http\Controllers\StudentImportController::class, 'create'])->name('students.import');
    Route::post('groups/{group}/import', [\App\Http\Controllers\StudentImportController::class, 'store'])->name('students.import.process');
    Route::get('students/csv-template', [\App\Http\Controllers\StudentImportController::class, 'downloadTemplate'])->name('students.csv-template');
    

    Route::get('aulas/export/{format}', [\App\Http\Controllers\AulaController::class, 'export'])->name('aulas.export');
    Route::get('aulas/template/{format}', [\App\Http\Controllers\AulaController::class, 'template'])->name('aulas.template');
    Route::post('aulas/import', [\App\Http\Controllers\AulaController::class, 'import'])->name('aulas.import');
    Route::resource('aulas', \App\Http\Controllers\AulaController::class);
    
    // RA y CE (gestión dentro de módulos)
    Route::post('/modulos/{modulo}/ra', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'store'])->name('ra.store');
    Route::put('/ra/{ra}', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'update'])->name('ra.update');
    Route::delete('/ra/{ra}', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'destroy'])->name('ra.destroy');
    Route::post('/ra/reorder', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'reorder'])->name('ra.reorder');
    Route::post('/ra/{ra}/ce', [\App\Http\Controllers\CriterioEvaluacionController::class, 'store'])->name('ce.store');
    Route::put('/ce/{ce}', [\App\Http\Controllers\CriterioEvaluacionController::class, 'update'])->name('ce.update');
    Route::delete('/ce/{ce}', [\App\Http\Controllers\CriterioEvaluacionController::class, 'destroy'])->name('ce.destroy');
    Route::post('/ce/reorder', [\App\Http\Controllers\CriterioEvaluacionController::class, 'reorder'])->name('ce.reorder');
    
    // Actualizaciones (crear/eliminar)
    Route::get('/actualizaciones/create', [\App\Http\Controllers\ActualizacionController::class, 'create'])->name('actualizaciones.create');
    Route::post('/actualizaciones', [\App\Http\Controllers\ActualizacionController::class, 'store'])->name('actualizaciones.store');
    Route::delete('/actualizaciones/{actualizacion}', [\App\Http\Controllers\ActualizacionController::class, 'destroy'])->name('actualizaciones.destroy');
    Route::get('/actualizaciones', [\App\Http\Controllers\ActualizacionController::class, 'index'])->name('actualizaciones.index');
    
    Route::resource('modulos', \App\Http\Controllers\ModuloController::class);
    
    // CUADERNO DE CLASE (Sesiones + Asistencia)
    Route::resource('sesiones', \App\Http\Controllers\SesionController::class)->parameters(['sesiones' => 'sesion']);
    Route::post('/sesiones/{sesion}/asistencia', [\App\Http\Controllers\SesionController::class, 'guardarAsistencia'])->name('sesiones.asistencia');
    
    // OBSERVACIONES DE ALUMNOS
    Route::get('/observaciones', [\App\Http\Controllers\ObservacionAlumnoController::class, 'index'])->name('observaciones.index');
    Route::get('/observaciones/create', [\App\Http\Controllers\ObservacionAlumnoController::class, 'create'])->name('observaciones.create');
    Route::post('/observaciones', [\App\Http\Controllers\ObservacionAlumnoController::class, 'store'])->name('observaciones.store');
    Route::delete('/observaciones/{observacion}', [\App\Http\Controllers\ObservacionAlumnoController::class, 'destroy'])->name('observaciones.destroy');
    
    // ACTIVIDADES
    Route::resource('actividades', \App\Http\Controllers\ActividadController::class)->parameters(['actividades' => 'actividad']);
    
    // RÚBRICAS
    Route::get('/actividades/{actividad}/rubrica/create', [\App\Http\Controllers\RubricaController::class, 'create'])->name('rubricas.create');
    Route::post('/actividades/{actividad}/rubrica', [\App\Http\Controllers\RubricaController::class, 'store'])->name('rubricas.store');
    Route::get('/rubricas/{rubrica}', [\App\Http\Controllers\RubricaController::class, 'show'])->name('rubricas.show');
    Route::delete('/rubricas/{rubrica}', [\App\Http\Controllers\RubricaController::class, 'destroy'])->name('rubricas.destroy');
    
    // NOTAS Y CALIFICACIONES
    Route::get('/notas', [\App\Http\Controllers\NotaController::class, 'index'])->name('notas.index');
    Route::post('/notas', [\App\Http\Controllers\NotaController::class, 'guardarNotas'])->name('notas.guardar');
    Route::get('/boletin', [\App\Http\Controllers\NotaController::class, 'boletin'])->name('notas.boletin');
    
    // CUADERNO DE CLASE (Vista unificada)
    Route::get('/cuaderno', [\App\Http\Controllers\CuadernoController::class, 'index'])->name('cuaderno.index');
    Route::get('/cuaderno/alumno/{alumno}', [\App\Http\Controllers\CuadernoController::class, 'alumno'])->name('cuaderno.alumno');
    
    // GESTOR DOCUMENTAL (Jiménez's classroom/grading documents)
    Route::get('api/documentos-list', [\App\Http\Controllers\DocumentoController::class, 'apiList'])->name('documentos.apiList');
    Route::resource('documentos', \App\Http\Controllers\DocumentoController::class);

    // --- MÓDULO: Inventario (López, Francisco Javier) ---
    Route::resource('inventory', \App\Http\Controllers\InventoryController::class);

    // --- MÓDULO: Recursos e Incidencias (Madroñal, Jesús & Peinado, Salvador) ---
    Route::resource('recursos', \App\Http\Controllers\RecursoController::class);
    Route::resource('tipo-recursos', \App\Http\Controllers\TipoRecursoController::class);
    Route::resource('incidencias', \App\Http\Controllers\IncidenciaController::class);

    // --- MÓDULO: Documentos Institucionales (Peinado, Salvador) ---
    Route::get('documentos-institucionales', [\App\Http\Controllers\DocumentoInstitucionalController::class, 'index'])->name('documentos-institucionales.index');
    Route::get('categorias', [\App\Http\Controllers\CategoriaController::class, 'index'])->name('categorias.index');
    Route::get('categorias/{categoria}', [\App\Http\Controllers\CategoriaController::class, 'show'])->name('categorias.show');
    Route::resource('documentos-institucionales', \App\Http\Controllers\DocumentoInstitucionalController::class)->except(['index', 'show']);
    Route::resource('etiquetas', \App\Http\Controllers\EtiquetaController::class);
    Route::resource('categorias', \App\Http\Controllers\CategoriaController::class)->except(['index', 'show']);
    Route::get('documentos-institucionales/{documento}', [\App\Http\Controllers\DocumentoInstitucionalController::class, 'show'])->name('documentos-institucionales.show');

    // --- MÓDULO: Calendario y Eventos (Sánchez, Álvaro) ---
    Route::post('/calendar/events', [\App\Http\Controllers\CalendarController::class, 'store'])->name('calendar.events.store');
    Route::put('/calendar/events/{event}', [\App\Http\Controllers\CalendarController::class, 'update'])->name('calendar.events.update');
    Route::delete('/calendar/events/{event}', [\App\Http\Controllers\CalendarController::class, 'destroy'])->name('calendar.events.destroy');
    Route::post('/calendar/events/{event}/move', [\App\Http\Controllers\CalendarController::class, 'move'])->name('calendar.events.move');
    Route::get('/calendar/events/{event}/attachment', [\App\Http\Controllers\CalendarController::class, 'downloadAttachment'])->name('calendar.events.attachment');
    Route::post('/calendar/import-ics', [\App\Http\Controllers\CalendarController::class, 'importIcs'])->name('calendar.import-ics');
    Route::get('/calendar/export-ics', [\App\Http\Controllers\CalendarController::class, 'exportIcs'])->name('calendar.export-ics');
    Route::get('/calendar/export-csv', [\App\Http\Controllers\CalendarController::class, 'exportCsv'])->name('calendar.export-csv');
    Route::get('/calendar/print', [\App\Http\Controllers\CalendarController::class, 'printView'])->name('calendar.print');
    Route::get('/calendars', [\App\Http\Controllers\CalendarManagerController::class, 'index'])->name('calendars.index');
    Route::post('/calendars', [\App\Http\Controllers\CalendarManagerController::class, 'store'])->name('calendars.store');
    Route::put('/calendars/{calendar}', [\App\Http\Controllers\CalendarManagerController::class, 'update'])->name('calendars.update');
    Route::delete('/calendars/{calendar}', [\App\Http\Controllers\CalendarManagerController::class, 'destroy'])->name('calendars.destroy');
});

// Public iCal Feed Subscription Route (no auth required)
Route::get('/calendar/feed/{token}.ics', [\App\Http\Controllers\CalendarController::class, 'feed'])->name('calendar.feed');

// Política de Privacidad RGPD/LOPDGDD
Route::view('/privacidad', 'legal.privacidad')->name('privacidad');

// PWA: Manifest y Service Worker
Route::get('/manifest.json', function () {
    return response(file_get_contents(public_path('manifest.json')), 200, [
        'Content-Type' => 'application/manifest+json; charset=utf-8'
    ]);
});

Route::get('/sw.js', function () {
    return response(file_get_contents(public_path('sw.js')), 200, [
        'Content-Type' => 'application/javascript; charset=utf-8'
    ]);
});

