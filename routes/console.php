<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('intranet:sync-permissions', function () {
    $this->info('Sincronizando permisos y roles para el despliegue del centro...');
    \App\Services\PermissionManagerService::applyDefaultAssignments();
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $this->info('✓ Permisos sincronizados y caché restablecida correctamente.');
})->purpose('Sincroniza y restablece los permisos y roles según la configuración del centro.');
