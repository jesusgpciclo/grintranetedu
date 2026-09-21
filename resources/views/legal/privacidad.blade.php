@extends('layouts.app')

@section('title', 'Política de Privacidad - GR Intranet EDU')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    
    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl transition-colors">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 shrink-0 text-2xl font-black">
                ⚖️
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Política de Privacidad</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Información sobre el tratamiento de datos personales en el centro educativo conforme al RGPD y la LOPDGDD.
                </p>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm dark:shadow-xl space-y-8 text-slate-700 dark:text-slate-300 text-sm leading-relaxed">
        
        <!-- Section 1 -->
        <section class="space-y-2">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>1. Responsable del Tratamiento</span>
            </h2>
            <p>
                El responsable del tratamiento de los datos personales tratados a través de esta plataforma intranet educativa es el <strong>Centro Educativo Público</strong> en el ejercicio de sus competencias docentes, organizativas y de régimen interior conferidas por la legislación educativa vigente (Ley Orgánica 2/2006, de 3 de mayo, de Educación - LOE/LOMLOE).
            </p>
        </section>

        <!-- Section 2 -->
        <section class="space-y-2">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>2. Finalidad del Tratamiento</span>
            </h2>
            <p>
                Los datos personales del profesorado, personal no docente y alumnado son tratados exclusivamente para:
            </p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600 dark:text-slate-400">
                <li>Gestión de la organización docente, horarios lectivos y calendarios escolares.</li>
                <li>Control y registro de ausencias del profesorado y justificación de faltas.</li>
                <li>Asignación objetiva, equitativa y transparente de coberturas y turnos de guardia escolar mediante algoritmos de equidad.</li>
                <li>Elaboración del Parte Diario de Guardia y registro de incidencias pedagógicas y del alumnado en el aula.</li>
                <li>Garantía de la seguridad y supervisión del alumnado menor de edad en el recinto escolar durante los periodos lectivos y de recreo.</li>
            </ul>
        </section>

        <!-- Section 3 -->
        <section class="space-y-2">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>3. Base Jurídica (Legitimación)</span>
            </h2>
            <p>
                El tratamiento de datos se encuentra fundamentado en:
            </p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600 dark:text-slate-400">
                <li><strong>Artículo 6.1.e) del RGPD:</strong> El tratamiento es necesario para el cumplimiento de una misión realizada en interés público o en el ejercicio de poderes públicos conferidos al responsable del tratamiento.</li>
                <li><strong>Disposición Adicional vigesimotercera de la LOE:</strong> Legitimación expresa para el tratamiento de datos del profesorado y alumnado necesarios para la función docente y orientadora.</li>
            </ul>
        </section>

        <!-- Section 4 -->
        <section class="space-y-2">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>4. Destinatarios y Conservación</span>
            </h2>
            <p>
                Los datos generados en el módulo de guardias no se cederán a terceros, salvo obligación legal o requerimiento de la Inspección Educativa y la Consejería de Educación. Los datos se conservarán durante el curso escolar activo y, en su caso, durante los plazos legalmente exigibles para la rendición de cuentas administrativas.
            </p>
        </section>

        <!-- Section 5 -->
        <section class="space-y-2">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>5. Ejercicio de Derechos (ARCO)</span>
            </h2>
            <p>
                Los usuarios pueden ejercer en cualquier momento sus derechos de <strong>acceso, rectificación, supresión, limitación del tratamiento y oposición</strong> dirigiéndose por escrito a la Secretaría del Centro Educativo o a través del Delegado de Protección de Datos de la Consejería de Educación competente.
            </p>
        </section>

        <!-- Footer Notice -->
        <div class="pt-6 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-400 flex items-center justify-between">
            <span>Última actualización: Curso Escolar 2026/2027</span>
            <span>Conforme a RGPD (UE 2016/679) y LOPDGDD 3/2018</span>
        </div>

    </div>

</div>
@endsection
