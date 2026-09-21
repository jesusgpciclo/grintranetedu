# Manual de Funcionamiento: Módulo de Guardias y Ausencias

## 1. Introducción y Propósito

El **Módulo de Guardias y Ausencias** es un sistema integral diseñado para la gestión en tiempo real de las sustituciones docentes, la notificación anticipada de ausencias, el seguimiento pedagógico del alumnado y la distribución equitativa de las cargas de trabajo en el centro educativo.

Su diseño está optimizado para su uso en dispositivos móviles y de escritorio, permitiendo a los profesores gestionar sus ausencias y confirmar coberturas de guardia directamente desde cualquier lugar del centro en cuestión de segundos ("contexto de pasillo").

---

## 2. Estructura y Navegación del Módulo

Dentro de la barra lateral de la intranet, el módulo se encuentra organizado bajo el apartado principal **Guardias**, que contiene las siguientes secciones:

```
Guardias
 ├── Parte de Guardia        (Seguimiento en tiempo real y confirmación de coberturas)
 ├── Ausencias               (Listado, calendario y registro de ausencias del profesorado)
 ├── Mis Horas de Guardia    (Historial y cómputo individual del docente)
 ├── Cuadrante Semanal       (Matriz general de disponibilidad del claustro)
 ├── Estadísticas y Equidad  (Panel de control y KPIs para el Equipo Directivo)
 └── Configuración Guardias  (Ajustes de dificultad de grupos y aforos para Directiva)
```

---

## 3. Registro y Gestión de Ausencias

### 3.1. Procedimiento de Notificación
Cuando un docente prevé su falta de asistencia (por motivos médicos, deber inexcusable, formación, etc.), accede a la opción **+ Notificar Ausencia**.

El formulario solicita los siguientes datos:
1. **Fecha de la ausencia**: Día exacto en el que tendrá lugar la falta.
2. **Tramos Horarios Afectados**: Permite selección múltiple de tramos (por ejemplo, marcar 1ª y 2ª hora de una sola vez).
3. **Grupo y Aula/Zona**: Localización del alumnado para orientar al docente sustituto.
4. **Asignación Pedagógica de Tareas (Obligatorio)**: Descripción detallada del trabajo, ejercicios o actividades que el alumnado debe realizar durante la sesión.
5. **Enlace a Material Online (Opcional)**: Vínculo directo a Google Classroom, Moodle o Google Drive para que el profesor de guardia pueda proyectar o compartir las actividades con un solo clic.
6. **Ausencia en Tramo de Guardia**: Si el docente falta durante una hora en la que tenía asignada una guardia, puede marcar la casilla *🛡️ Es mi hora de Guardia*. Esto retira automáticamente su disponibilidad del cuadrante para no ser seleccionado por el sistema.

### 3.2. Reglas de Modificación y Borrado
- **Docente titular**: Solo puede editar o eliminar una ausencia si la hora lectiva correspondiente **aún no ha comenzado**.
- **Equipo Directivo / Administrador**: Tiene control total para registrar ausencias en nombre de terceros, modificar cualquier dato o cancelarlas en cualquier momento.

---

## 4. El "Parte de Guardia" en Tiempo Real

El **Parte de Guardia** es la pantalla operativa central del sistema durante la jornada escolar.

### 4.1. Detección Automática de la Hora Actual
Al acceder al Parte de Guardia en el día en curso, el sistema identifica automáticamente la hora del reloj y resalta con una insignia luminosa (**HORA ACTUAL**) el tramo lectivo activo, situando la información prioritaria al alcance inmediato del usuario.

### 4.2. Sistema Semafórico de Estados
Cada necesidad de sustitución se muestra en una tarjeta visual con un código de colores intuitivo:

| Color | Estado | Significado |
| :--- | :--- | :--- |
| 🔴 **Rojo** | **Sin Cubrir** | Aula desatendida. Requiere que un profesor de guardia confirme su asistencia. |
| 🟢 **Verde** | **Cubierta** | La guardia ha sido confirmada por un docente. Se muestra su nombre y hora de registro. |
| 🟣 **Púrpura** | **Ausencia de Guardia** | El docente titular tenía turno de guardia asignado pero está ausente. |
| ⚪ **Gris** | **No Requiere** | Grupos con dificultad especial negativa que no exigen sustitución presencial. |

### 4.3. Confirmación de Guardia en 1 Toque
Al personarse en el aula o zona asignada:
1. El docente de guardia pulsa el botón **CONFIRMAR GUARDIA**.
2. El sistema vincula inmediatamente su usuario a la ausencia, registra la marca temporal y cambia el estado a **CUBIERTA (Verde)**.
3. El grupo queda marcado como atendido para todo el claustro en tiempo real.
4. En caso de error, el docente que confirmó o un miembro del equipo directivo puede pulsar **Liberar** para deshacer la confirmación.

---

## 5. Algoritmo de Equidad y Reparto de Cargas

Para garantizar un reparto justo y transparente de las sustituciones, el sistema incorpora un motor algorítmico de asignación inteligente.

### 5.1. Fórmula de Carga Acumulada
Cada vez que se genera una necesidad de cobertura en un tramo horario, el sistema calcula la carga acumulada ($P$) de todos los profesores que tienen guardia en esa franja:

$$P = \sum (G \times D_g)$$

Donde:
- $G$: Cada guardia realizada y confirmada por el docente.
- $D_g$: Coeficiente de dificultad asignado al grupo o zona cubierta.

### 5.2. Orden de Prioridad y Recomendación
1. El sistema filtra los profesores disponibles en ese tramo (descartando a quienes tengan ausencia registrada).
2. Ordena la lista de menor a mayor puntuación de carga ($P$).
3. Marca con la etiqueta **"Prioridad 1 / Recomendado por Equidad"** al docente que menos guardias o de menor dificultad haya realizado.
4. El equipo directivo puede asignar directamente al docente recomendado o a cualquiera de los disponibles en el desplegable.

---

## 6. Módulos Especializados: Convivencia y Zonas

### 6.1. Gestión del Aula de Convivencia ("C")
Los docentes designados para atender el Aula de Convivencia tienen asignada la letra **"C"** en su cuadrante.
- El algoritmo les añade **+100 puntos virtuales** a su orden de llamada.
- De este modo, se sitúan al final de la lista de sustitución ordinaria, garantizando que permanezcan en Convivencia salvo que no exista ningún otro profesor disponible en el centro.

### 6.2. Zonas y Reagrupamiento de Alumnado
- El sistema permite configurar zonas comunes (Biblioteca, Patio, Gimnasio, Pasillos) con sus respectivos aforos máximos.
- En jornadas con exceso de ausencias simultáneas, la dirección puede orientar el reagrupamiento de varios grupos en un espacio común amplio supervisado por un único docente de guardia.

---

## 7. Espacio Personal: "Mis Horas de Guardia"

Cada docente dispone de un panel privado con su balance individual:
- **Total de Guardias Realizadas**: Cómputo global de intervenciones durante el curso.
- **Puntuación Acumulada Ponderada ($P$)**: Suma total de puntos según la dificultad de los grupos cubiertos.
- **Historial Detallado**: Registro cronológico que indica la fecha, tramo, grupo, aula, docente sustituido y la hora de confirmación.

---

## 8. Cuadrante Semanal de Disponibilidad

Muestra la matriz completa de lunes a viernes con todos los tramos horarios del centro. Permite a cualquier miembro del claustro consultar qué compañeros se encuentran de guardia en cada momento de la semana, identificando con distintivos visuales a los responsables de Convivencia (**C**) y las diferentes zonas.

---

## 9. Panel Directivo y Estadísticas (Business Intelligence)

El Equipo Directivo cuenta con una sección analítica para la supervisión y toma de decisiones organizativas:

### 9.1. Indicadores Clave de Desempeño (KPIs)
- **Total de Ausencias**: Número global de faltas en el rango de fechas seleccionado.
- **Tasa de Cobertura (%)**: Porcentaje de guardias que han sido cubiertas presencialmente.
- **Tasa de Justificación (%)**: Porcentaje de ausencias con justificación documental entregada.
- **Media de Guardias por Docente**: Promedio del claustro para evaluar la equidad.

### 9.2. Análisis Gráfico de Patrones
- **Distribución por Día de la Semana**: Gráficas de barras comparativas (Lunes a Viernes) para identificar picos de absentismo.
- **Distribución por Tramo Horario**: Frecuencia de ausencias por cada periodo lectivo para ajustar el número de docentes de guardia necesarios.

### 9.3. Control de Desbalance de Equidad
El sistema calcula la desviación de cada profesor respecto a la media del claustro. Si la diferencia supera las **2 horas efectivas**, el sistema emite un aviso de **⚠️ Desbalance > 2h**, facilitando a la jefatura de estudios el reequilibrio en las asignaciones.

### 9.4. Control Administrativo de Justificaciones
Una tabla dedicada permite al equipo directivo revisar cada ausencia y conmutar el estado del justificante con el botón **Marcar Justificada / Pendiente**, manteniendo el registro administrativo al día.

---

## 10. Configuración del Módulo (Administración)

Desde el panel **Configuración Guardias**, la dirección puede personalizar los parámetros del algoritmo:
- **Plantilla de Horario Activa**: Seleccionar la plantilla de horario oficial con la que funcionará el cuadrante, el parte de guardia y el registro de ausencias del centro.
- **Dificultad de Grupos ($D_g$)**: Asignar valores (ej. 1 para grupos ordinarios, 2 o 3 para grupos de mayor ratio o complejidad conductual, o valores negativos como -2 para grupos que no requieren cobertura).
- **Dificultad y Aforo de Zonas**: Definir el peso de vigilancia de pasillos, patios o biblioteca y su capacidad máxima de alumnos.

---

## 11. Resumen de Permisos por Rol

| Funcionalidad | Profesorado | Equipo Directivo / Admin |
| :--- | :---: | :---: |
| Consultar Parte de Guardia en vivo | ✅ | ✅ |
| Confirmar / Cancelar su propia guardia | ✅ | ✅ |
| Registrar sus propias ausencias | ✅ | ✅ |
| Borrar su ausencia (antes del inicio) | ✅ | ✅ |
| Asignar tareas y enlaces pedagógicos | ✅ | ✅ |
| Consultar "Mis Horas de Guardia" | ✅ | ✅ |
| Consultar el Cuadrante Semanal | ✅ | ✅ |
| Registrar o editar ausencias de terceros | ❌ | ✅ |
| Asignar manualmente guardias a cualquier docente | ❌ | ✅ |
| Acceso a KPIs, Gráficos y Ranking de Equidad | ❌ | ✅ |
| Validar entrega de Justificantes | ❌ | ✅ |
| Configurar dificultades ($D_g$) y aforos | ❌ | ✅ |
