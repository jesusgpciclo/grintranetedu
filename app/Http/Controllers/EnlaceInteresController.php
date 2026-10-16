<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnlaceInteresController extends Controller
{
    /**
     * Muestra la tabla de enlaces de interés para el profesorado.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user || (!$user->can('enlaces.view') && !$user->hasRole('admin'))) {
            abort(403, 'No tienes permiso para acceder a los enlaces de interés.');
        }

        $links = [
            [
                'id' => 1,
                'title' => 'GUARDIAS',
                'url' => 'https://myblog-4eixzn7jtc.live-website.com/#/parte',
                'description' => 'Acceso al cuadrante y parte de guardias del centro educativo.',
                'badge' => 'Guardias',
                'badge_color' => 'amber',
                'icon' => '🛡️',
                'domain' => 'myblog-4eixzn7jtc.live-website.com',
            ],
            [
                'id' => 2,
                'title' => 'PARTES DE INCIDENCIA',
                'url' => 'https://forms.gle/dgcJD6BQU3uJsH2M6',
                'description' => 'Formulario para la comunicación y registro de partes de incidencia disciplinaria.',
                'badge' => 'Convivencia',
                'badge_color' => 'rose',
                'icon' => '📋',
                'domain' => 'forms.gle (Google Forms)',
            ],
            [
                'id' => 3,
                'title' => 'RESERVA DE AULAS',
                'url' => 'https://calendar.google.com/calendar/u/0/appointments/AcZssZ3OvQc_Rb3aJA1l6AYW57zaYTZxKFesrvsyJr4=',
                'description' => 'Sistema de citas y calendario para la reserva de aulas y espacios polivalentes.',
                'badge' => 'Espacios',
                'badge_color' => 'blue',
                'icon' => '🏫',
                'domain' => 'calendar.google.com',
            ],
            [
                'id' => 4,
                'title' => 'RESERVA DE PORTÁTILES',
                'url' => 'https://calendar.google.com/calendar/u/0/appointments/AcZssZ0nXoUXH1H_BWSP6p-SRKyAidTDb1yd6sKeCzg=',
                'description' => 'Citas y calendario para la reserva y préstamo de carritos de ordenadores portátiles.',
                'badge' => 'Recursos TIC',
                'badge_color' => 'emerald',
                'icon' => '💻',
                'domain' => 'calendar.google.com',
            ],
            [
                'id' => 5,
                'title' => 'INCIDENCIAS TDE',
                'url' => 'https://myblog-4eixzn7jtc.live-website.com/#/incidencias',
                'description' => 'Comunicación y soporte de incidencias de Transformación Digital Educativa.',
                'badge' => 'Soporte TDE',
                'badge_color' => 'purple',
                'icon' => '🔧',
                'domain' => 'myblog-4eixzn7jtc.live-website.com',
            ],
            [
                'id' => 6,
                'title' => 'PROTOCOLO RECREOS INCLEMENCIAS METEOROLÓGICAS O SIMILAR',
                'url' => 'https://drive.google.com/file/d/18-4nsLMdkAmjnscowqsWhSakqIpleWbm/view?usp=sharing',
                'description' => 'Documento y directrices de actuación del profesorado y vigilancia en días de lluvia o mal tiempo.',
                'badge' => 'Protocolos',
                'badge_color' => 'sky',
                'icon' => '🌧️',
                'domain' => 'drive.google.com',
            ],
        ];

        return view('enlaces.index', compact('links'));
    }
}
