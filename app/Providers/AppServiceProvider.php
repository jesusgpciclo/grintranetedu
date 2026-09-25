<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Ausencia;
use App\Models\HallPass;
use App\Models\Incidencia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useTailwind();

        // El rol 'admin' tiene automáticamente todos los permisos del sistema
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        View::composer(['layouts.app', 'components.sidebar', 'layouts.sidebar'], function ($view) {
            if (Auth::check()) {
                $todayStr = now()->format('Y-m-d');
                $userId = Auth::id();
                $view->with([
                    'sidebarBadgeGuardias' => Ausencia::whereDate('fecha', $todayStr)
                        ->where('es_guardia', false)
                        ->whereNull('guardia_confirmed_at')
                        ->count(),
                    'misGuardiasHoyCount' => Ausencia::whereDate('fecha', $todayStr)
                        ->where('guardia_user_id', $userId)
                        ->count(),
                    'sidebarBadgeSalidas' => HallPass::whereNull('end_time')->count(),
                    'sidebarBadgeIncidencias' => Incidencia::where('estado', 'abierta')->count(),
                ]);
            }
        });
    }
}
