# 📖 Manual de Usuario: Módulo de Gestión de Pasillos (GrIntranet EDU)

---

## Índice
1. [Introducción y Objetivos](#1-introducción-y-objetivos)
2. [Roles y Permisos en el Módulo](#2-roles-y-permisos-en-el-módulo)
3. [Panel Principal de Pasillos (Gestor de Salidas)](#3-panel-principal-de-pasillos-gestor-de-salidas)
   - 3.1. [Búsqueda y Filtros de Clase (Chips de Nivel y Favoritos)](#31-búsqueda-y-filtros-de-clase-chips-de-nivel-y-favoritos)
   - 3.2. [Autorizar Salidas de Alumnos (Rápida y Personalizada)](#32-autorizar-salidas-de-alumnos-rápida-y-personalizada)
   - 3.3. [Avisos Inteligentes de Aforo y Frecuencia](#33-avisos-inteligentes-de-aforo-y-frecuencia)
   - 3.4. [Control de Alumnos Fuera y Registro de Regreso](#34-control-de-alumnos-fuera-y-registro-de-regreso)
4. [Monitor de Pasillos en Tiempo Real](#4-monitor-de-pasillos-en-tiempo-real)
5. [Historial y Reportes de Salidas](#5-historial-y-reportes-de-salidas)
   - 5.1. [Filtros Avanzados (Fechas y Franjas Horarias)](#51-filtros-avanzados-fechas-y-franjas-horarias)
   - 5.2. [Vista Adaptada en Dispositivos Móviles](#52-vista-adaptada-en-dispositivos-móviles)
   - 5.3. [Descarga en Excel y PDF](#53-descarga-en-excel-y-pdf)
   - 5.4. [Eliminación Individual y Masiva de Registros](#54-eliminación-individual-y-masiva-de-registros)
6. [Preguntas Frecuentes (FAQ)](#6-preguntas-frecuentes-faq)

---

## 1. Introducción y Objetivos

El **Módulo de Pasillos** de GrIntranet EDU está diseñado para controlar de forma ágil, visual y centralizada los pases al pasillo del alumnado durante el horario lectivo. Permite:
- Reducir el tiempo necesario para registrar una salida a menos de 2 segundos.
- Controlar en tiempo real cuántos alumnos están fuera del aula en todo el centro.
- Evitar aglomeraciones en baños y zonas comunes mediante avisos de aforo.
- Disponer de un histórico fiable de salidas por alumno, grupo y tramo horario.

---

## 2. Roles y Permisos en el Módulo

El sistema adapta su interfaz según el rol del usuario conectado:
* **Profesorado:** Puede autorizar salidas de sus alumnos, ver el historial de salidas del día, marcar el regreso de sus alumnos y consultar el monitor de pasillos.
* **Profesorado de Guardia / Controlador de Pasillos:** Además de lo anterior, puede marcar el regreso de cualquier alumno del centro desde el Monitor de Pasillos.
* **Conserjería:** Dispone de acceso directo en modo solo lectura al Monitor de Pasillos para consultar quién está autorizado a estar fuera.
* **Equipo Directivo / Administrador:** Acceso total, incluyendo el historial general, exportación de reportes y eliminación de registros.

---

## 3. Panel Principal de Pasillos (Gestor de Salidas)

Para acceder al gestor, pulsa en el menú lateral en **Pasillos** (o accede a la ruta `/salidas`).

[Captura de imagen: Vista general del panel principal de Pasillos mostrando la barra superior con estadísticas de alumnos activos/hoy, el buscador, el desplegable de clase y el listado de alumnos del grupo seleccionado]

### 3.1. Búsqueda y Filtros de Clase (Chips de Nivel y Favoritos)

Para localizar al alumno o grupo deseado dispones de dos herramientas complementarias:

1. **Chips de filtro por Nivel (`Todos`, `1º`, `2º`, `3º`, `4º`...):**
   - Pulsa sobre el chip del curso deseado (por ejemplo, **2º**).
   - El desplegable de clases se filtrará automáticamente para mostrarte únicamente los grupos de ese nivel (ej. *2º ESO A, 2º ESO B, etc.*).
   - La selección queda memorizada en tu dispositivo para que no tengas que repetirla cada vez.

[Captura de imagen: Detalle de los botones de chips de nivel (Todos, 1º, 2º, 3º...) con uno de ellos seleccionado en color azul y el desplegable de clases filtrado desplegado]

2. **Grupos Favoritos (Icono Estrella ⭐):**
   - Si impartes clase habitualmente en varios grupos, selecciónalos y pulsa el icono de la **estrella** situado a la derecha del desplegable.
   - Esos grupos quedarán fijados en la parte superior del selector bajo el apartado **"⭐ Favoritos"** y se sincronizarán en todos tus dispositivos.

3. **Buscador Rápido de Alumno:**
   - Si no recuerdas el grupo exacto, escribe el nombre o apellido del alumno en el cuadro de búsqueda superior.

---

### 3.2. Autorizar Salidas de Alumnos (Rápida y Personalizada)

Los alumnos aparecen ordenados por **apellidos y nombre** para facilitar su localización en la lista de clase.

[Captura de imagen: Fila o tarjeta de un alumno con el avatar, nombre completo, curso, contador de salidas previas del día y los dos botones de acción: el icono azul de Baño y el icono gris con el símbolo más (+)]

Dispones de dos formas de autorizar una salida:

#### A) Salida directa a "Baño" (1 solo clic)
* Pulsa el botón con el **icono de servicio/baño (azul)** en la tarjeta del alumno.
* La salida se registrará de inmediato y el alumno pasará a estado activo con su cronómetro en marcha.

#### B) Salida por otros motivos (Botón "+")
* Pulsa el botón con el **icono más (+)** para abrir la ficha detallada del alumno.
* En la ventana emergente dispones de:
  1. **Motivos rápidos:** Baño, Agua, Enfermedad o Material.
  2. **Otro motivo:** Campo de texto libre para detallar salidas especiales (ej: *Biblioteca, Secretaría, Jefatura*).
  3. **Historial de hoy:** Consulta de todas las veces que ha salido hoy el alumno con indicación de la hora, motivo y el profesor que lo autorizó. Si fuiste tú quien autorizó una salida previa, dispones del botón **✏️ Editar tiempo** para corregir la duración o las horas si fuera necesario.

[Captura de imagen: Ventana modal de opciones del alumno abierta, mostrando los botones de motivos rápidos en colores, el campo de 'Otro motivo' con su botón azul y la lista inferior con el historial de salidas del día de ese alumno]

---

### 3.3. Avisos Inteligentes de Aforo y Frecuencia

El sistema vela por la convivencia en el centro mediante dos sistemas de aviso no bloqueantes:

1. **Aviso de Salidas Frecuentes del Alumno:**
   - Si un alumno ya ha salido **2 o más veces en el mismo día**, al intentar autorizar una nueva salida el sistema mostrará una advertencia informativa indicando cuántas veces ha salido y a qué hora fue la última.
   - El docente decide si pulsa **"Autorizar salida"** o cancela.

[Captura de imagen: Ventana de confirmación 'Aviso de Salidas Frecuentes' alertando de que el alumno ya ha salido 2 veces hoy, con el botón amarillo/naranja 'Autorizar salida']

2. **Aviso de Aforo del Grupo:**
   - Si en el grupo ya hay **2 o más alumnos fuera de clase a la vez**, la aplicación avisará al profesor indicando que se ha alcanzado el límite recomendado.
   - **No se bloquea la salida:** El profesor puede pulsar **"Sí, autorizar salida"** en caso de necesidad justificada o urgencia médica, quedando registrada con normalidad.

[Captura de imagen: Ventana modal 'Aviso: Alumnos fuera en este grupo' preguntando si se desea autorizar la salida de todos modos, con los botones Cancelar y 'Sí, autorizar salida']

---

### 3.4. Control de Alumnos Fuera y Registro de Regreso

Cuando un alumno se encuentra fuera del aula:
* Su tarjeta se resalta con borde ámbar y se sitúa automáticamente **al principio de la lista**.
* Se activa un **cronómetro en tiempo real** que indica los minutos y segundos transcurridos.
* Aparece el botón verde **"Regresar"**:
  - Al regresar el alumno al aula, pulsa **"Regresar"** para cerrar el pase.
* **Regreso múltiple:** Si necesitas cerrar todos los pases del grupo a la vez (por ejemplo, al finalizar la hora de clase), pulsa el botón rojo inferior **"Regresar a todos los alumnos"**.

[Captura de imagen: Detalle de dos alumnos en estado activo fuera de clase, mostrando el cronómetro en marcha en color naranja, el motivo de la salida y el botón verde 'Regresar']

---

## 4. Monitor de Pasillos en Tiempo Real

El **Monitor de Pasillos** (icono del ojo en la barra superior o ruta `/salidas/monitor`) está pensado para proyectarse o consultarse desde conserjería, guardias o pasillos.

[Captura de imagen: Vista completa de la pantalla del Monitor de Pasillos en modo tabla compacta, mostrando el total de alumnos fuera actualmente, las columnas de Alumno, Grupo, Motivo, Profesor autorizador, Tiempo y el botón verde de regreso]

* **Información que muestra:**
  - Alumno (nombre y apellidos en tamaño destacado).
  - Grupo / Curso.
  - Motivo de la salida con chip identificativo.
  - Nombre del profesor o profesora que le ha autorizado a salir.
  - Tiempo transcurrido con cronómetro dinámico.
* **Acciones disponibles:**
  - Los profesores de guardia o usuarios con rol *Controlador de pasillo* pueden registrar el regreso del alumno directamente desde esta pantalla si lo encuentran en el pasillo o regresa a su zona.
  - El personal de conserjería dispone de esta vista como visor de consulta rápida sin peligro de modificar datos por error.

---

## 5. Historial y Reportes de Salidas

Accediendo desde el botón con el icono del reloj (o ruta `/salidas/history`), el equipo directivo y personal autorizado pueden auditar todo el registro de movimientos del centro.

[Captura de imagen: Vista del Historial de Pasillos en ordenador con el buscador principal, el botón 'Fecha y hora', los accesos a 'Descargar Excel' y 'Descargar PDF', y la tabla de registros]

### 5.1. Filtros Avanzados (Fechas y Franjas Horarias)

Para mantener la pantalla despejada, los filtros de fecha y hora se encuentran **ocultos por defecto**.

1. Pulsa en el botón **"Fecha y hora"**:
   - Se desplegará el panel con los campos:
     - **Fecha Desde:** Introduce un día para consultar solo esa fecha.
     - **Fecha Hasta (opcional):** Rellena ambas para consultar un rango (ej: toda una semana o mes).
     - **Hora Desde / Hora Hasta:** Filtra por tramos horarios lectivos (ej: salidas entre las `10:15` y las `11:45`).
   - Botón **"📅 Solo Hoy"**: Rellena automáticamente la fecha actual con un solo clic.
   - Pulsa **"Aplicar"** para ejecutar la búsqueda.

[Captura de imagen: Panel desplegable de 'Filtros por Fecha y Rango de Horas' abierto, mostrando los 4 selectores de fecha y hora junto a los botones 'Solo Hoy', 'Borrar fechas/horas' y 'Aplicar']

2. **Resumen de filtros activos:**
   - Si cierras el panel o navegas con filtros puestos, debajo del buscador verás unas etiquetas informativas (ej: `📅 15/10/2026 | ⏰ 09:00 → 11:30`) y el botón `Quitar fechas/horas` para volver al listado general en cualquier momento.

---

### 5.2. Vista Adaptada en Dispositivos Móviles

Si consultas el historial desde un teléfono móvil o tablet:
* La tabla tradicional de ordenador se sustituye por **tarjetas táctiles optimizadas**.
* Cada tarjeta muestra:
  - Nombre completo del alumno en letra grande y legible.
  - Curso y grupo.
  - Motivo de salida y duración (o indicador `Activo` animado si aún no ha vuelto).
  - Fecha exacta, hora de inicio y hora de fin.
  - Nombre del profesor que autorizó el pase.
  - Checkbox para selección múltiple y botón de papelera para eliminar.

[Captura de imagen: Pantalla de móvil mostrando el Historial de Pasillos con dos tarjetas de alumnos perfectamente adaptadas a la pantalla vertical con sus etiquetas de colores, horas y profesor]

---

### 5.3. Descarga en Excel y PDF

En la barra de herramientas del Historial dispones de dos opciones de reporte:
* **Descargar Excel:** Exporta un archivo `.csv` compatible con Excel con todos los registros filtrados (fecha, alumno, clase, motivo, duración en minutos y profesor).
* **Descargar PDF:** Abre una vista limpia y maquetada lista para imprimir o guardar en PDF, incluyendo la cabecera oficial del centro, fecha de expedición y el detalle de los filtros aplicados.

[Captura de imagen: Vista previa de impresión del Reporte en PDF con el membrete institucional, los filtros aplicados en el encabezado y la tabla maquetada]

---

### 5.4. Eliminación Individual y Masiva de Registros

Los usuarios administradores disponen de herramientas para depuración de registros:
* **Eliminar una salida:** Pulsa el icono de la papelera en la fila o tarjeta correspondiente y confirma en la ventana emergente.
* **Eliminar seleccionadas:** Marca las casillas de los registros deseados; aparecerá el botón rojo **"Eliminar seleccionadas (N)"**.
* **Eliminar todo el historial:** Botón para vaciar los registros históricos de cursos anteriores.

---

## 6. Preguntas Frecuentes (FAQ)

### ¿Qué ocurre si un alumno no regresa y el profesor olvida cerrar el pase?
El sistema cuenta con un proceso automático que, al cambiar de jornada lectiva, cierra automáticamente todas las salidas que quedaron pendientes del día anterior, evitando que queden bloqueadas indefinidamente.

### ¿Puede un alumno tener dos pases activos al mismo tiempo?
No. El sistema comprueba que el alumno no tenga ya un pase abierto y emitirá un mensaje de aviso si se intenta duplicar.

### ¿Se pueden editar las horas de una salida ya realizada?
Sí. El profesor que autorizó la salida (o el equipo directivo) puede abrir la ficha del alumno con el botón `+`, localizar la salida en el "Historial de hoy" y pulsar en **✏️ Editar tiempo** para corregir la hora de inicio o la duración en minutos.

### ¿Se pierden mis grupos favoritos si cambio de navegador?
No. Aunque los favoritos se guardan en el navegador para una carga instantánea, también se sincronizan automáticamente con tu cuenta en el servidor de la intranet.

---
*Manual actualizado para la versión vigente de GrIntranet EDU.*
