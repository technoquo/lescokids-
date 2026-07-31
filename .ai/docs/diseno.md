# Especificación de Diseño — LescoKids

## Resumen del Proyecto

**LescoKids** es una plataforma educativa basada en juegos interactivos, dirigida a niños sordos y oyentes, con el objetivo de fomentar el aprendizaje de la Lengua de Señas Costarricense (LESCO) de manera lúdica e inclusiva.

---

## 1. Objetivo y Alcance

### 1.1 Objetivo general

Diseñar y construir una plataforma web/móvil que permita a niños sordos y oyentes aprender LESCO mediante juegos interactivos, promoviendo la inclusión y la comunicación entre ambas comunidades.

### 1.2 Objetivos específicos

- Ofrecer contenido educativo de LESCO organizado por niveles de dificultad (básico, intermedio, avanzado).
- Proveer juegos interactivos (memoria, asociación, selección múltiple, reconocimiento visual) que refuercen el vocabulario y las estructuras de LESCO.
- Permitir seguimiento del progreso individual de cada niño.
- Facilitar la participación de padres, tutores o docentes en el proceso de aprendizaje.
- Garantizar accesibilidad visual (subtítulos, iconografía clara, videos de señas) para usuarios sordos.

### 1.3 Alcance de la primera versión (MVP)

**Incluido:**
- Registro y perfiles de usuario (niño, tutor/docente).
- Catálogo de lecciones organizadas por categorías (abecedario, números, familia, colores, animales, etc.).
- Al menos 2-3 tipos de juegos interactivos.
- Sistema de progreso y puntuación básica.
- Panel simple para tutores/docentes (ver progreso del niño).

**Fuera de alcance (fases futuras):**
- Reconocimiento de señas por cámara (IA/computer vision).
- Modo multijugador en tiempo real.
- Contenido generado por la comunidad.
- Soporte multi-idioma de señas (otras lenguas de señas además de LESCO).

---

## 2. Flujo del Usuario

### 2.1 Roles

| Rol | Descripción |
|---|---|
| **Niño (jugador)** | Usuario principal que juega y aprende. Puede tener cuenta propia o perfil vinculado a un tutor. |
| **Tutor/Padre** | Administra el/los perfiles de niño(s), revisa progreso. |
| **Docente** | Gestiona grupos de estudiantes (aulas), asigna lecciones, revisa reportes. |
| **Administrador** | Gestiona contenido (lecciones, juegos, videos de señas), usuarios y configuración general. |

### 2.2 Flujo principal (niño jugador)

1. Tutor/docente crea la cuenta y el perfil del niño (o el niño se registra si tiene edad/autonomía suficiente).
2. Niño inicia sesión (login simplificado: PIN, avatar + PIN, o código QR pensado para infancia).
3. Niño ve el mapa de niveles/categorías disponibles.
4. Selecciona una lección o categoría.
5. Se le presenta contenido introductorio (video de la seña, imagen, palabra).
6. Accede al juego interactivo asociado a esa lección.
7. Juega y recibe retroalimentación inmediata (correcto/incorrecto, refuerzo visual).
8. Al finalizar, se registra el resultado (puntaje, tiempo, aciertos/errores).
9. Se desbloquea la siguiente lección/nivel según reglas de progreso.
10. Niño puede ver su progreso (medallas, estrellas, avatar que evoluciona).

### 2.3 Flujo del tutor/docente

1. Inicia sesión con credenciales propias.
2. Accede a su panel de control.
3. Ve lista de niños/estudiantes asociados.
4. Consulta reportes de progreso por niño (lecciones completadas, tiempo jugado, errores frecuentes).
5. (Docente) Puede crear grupos/aulas y asignar lecciones específicas.
6. Recibe alertas o recomendaciones (ej. "el niño tiene dificultad con la categoría X").

### 2.4 Flujo del administrador

1. Inicia sesión en panel administrativo.
2. Gestiona catálogo de lecciones (crear, editar, eliminar, publicar/despublicar).
3. Sube contenido multimedia (videos de señas, imágenes).
4. Gestiona usuarios (tutores, docentes, niños) y permisos.
5. Revisa métricas generales de la plataforma.

---

## 3. Reglas de Negocio

1. **Un niño siempre debe estar vinculado a al menos un tutor o docente responsable.** No se permite un perfil de niño huérfano (sin adulto asociado), por temas de seguridad y protección infantil.
2. **El progreso es acumulativo y no se puede retroceder de nivel** una vez desbloqueado, aunque el niño puede repetir lecciones ya completadas.
3. **El desbloqueo de una nueva lección/categoría requiere completar un porcentaje mínimo de aciertos** (ej. 70%) en la lección anterior de la misma categoría.
4. **Los juegos deben ofrecer retroalimentación inmediata** (no se acumulan errores sin mostrar corrección), dado el perfil pedagógico e infantil del producto.
5. **El contenido de señas (videos/imágenes) debe estar validado por un experto en LESCO** antes de publicarse; ningún contenido se marca como "publicado" sin este flujo de aprobación.
6. **Un docente solo puede ver el progreso de los niños que pertenecen a sus grupos/aulas asignadas.** Un tutor solo puede ver a los niños que él mismo registró o que fueron vinculados formalmente a su cuenta.
7. **Los niños no tienen acceso a funciones administrativas ni a datos de otros niños.**
8. **El sistema de puntuación/medallas es no punitivo:** no se penaliza fuertemente el error, se prioriza el refuerzo positivo (importante en el diseño pedagógico para niños con y sin discapacidad auditiva).
9. **Cuentas de niños menores de cierta edad no requieren correo electrónico propio;** se gestionan bajo la cuenta del tutor (cumplimiento de buenas prácticas de protección de datos de menores).
10. **Toda lección debe tener asociado al menos un recurso audiovisual de la seña correspondiente**, no solo texto, por el enfoque de accesibilidad para niños sordos.

---

## 4. Modelo de Datos (propuesta preliminar)

> Nota: este es un modelo conceptual inicial: nombres de tablas y relaciones sujetos a ajuste durante el diseño detallado.

### 4.1 Entidades principales

**users**
- id
- name
- email (nullable si es perfil de niño gestionado por tutor)
- password
- role (enum: admin, tutor, docente, niño)
- created_at / updated_at

**child_profiles**
- id
- user_id (FK → users, nullable si el niño no tiene cuenta propia)
- guardian_id (FK → users, tutor o docente responsable)
- name
- avatar
- birth_date
- login_pin (para acceso simplificado)
- created_at / updated_at

**guardians_children** (tabla pivote, por si un niño puede tener más de un tutor/docente)
- id
- guardian_id (FK → users)
- child_profile_id (FK → child_profiles)
- relationship_type (tutor, docente, etc.)

**classrooms** (aulas, para el rol docente)
- id
- docente_id (FK → users)
- name
- description
- created_at / updated_at

**classroom_students** (pivote)
- id
- classroom_id (FK → classrooms)
- child_profile_id (FK → child_profiles)

**categories**
- id
- name (ej. Abecedario, Números, Familia, Animales)
- description
- order
- icon

**lessons**
- id
- category_id (FK → categories)
- title
- description
- difficulty_level (básico, intermedio, avanzado)
- order
- status (borrador, revisión, publicado)
- created_by (FK → users, admin)
- approved_by (FK → users, experto LESCO, nullable hasta aprobación)
- created_at / updated_at

**lesson_resources**
- id
- lesson_id (FK → lessons)
- type (video, imagen, audio)
- url / path
- description

**games**
- id
- lesson_id (FK → lessons)
- type (memoria, selección múltiple, asociación, etc.)
- config (JSON con parámetros del juego)
- created_at / updated_at

**game_attempts**
- id
- child_profile_id (FK → child_profiles)
- game_id (FK → games)
- score
- correct_answers
- wrong_answers
- duration_seconds
- completed_at

**progress**
- id
- child_profile_id (FK → child_profiles)
- lesson_id (FK → lessons)
- status (no iniciado, en progreso, completado)
- best_score
- unlocked_at
- completed_at

**badges / achievements**
- id
- name
- description
- icon
- criteria (JSON: condición para obtenerla)

**child_badges** (pivote)
- id
- child_profile_id (FK → child_profiles)
- badge_id (FK → badges)
- earned_at

### 4.2 Relaciones clave

- Un `user` (tutor/docente) puede tener muchos `child_profiles` asociados.
- Un `child_profile` puede pertenecer a uno o más `guardians` (vía `guardians_children`).
- Una `category` tiene muchas `lessons`.
- Una `lesson` tiene uno o más `lesson_resources` y uno o más `games`.
- Un `child_profile` tiene muchos `game_attempts` y registros de `progress`.

---

## 5. Validaciones

### 5.1 Validaciones de usuarios
- Email único y con formato válido (solo aplica a tutores, docentes y admins).
- Contraseña con longitud mínima y complejidad razonable (ej. mínimo 8 caracteres).
- El rol debe ser uno de los valores permitidos (enum controlado, no texto libre).
- Fecha de nacimiento del niño debe ser coherente (no futura, rango de edad razonable, ej. 3-12 años).

### 5.2 Validaciones de perfiles de niño
- Todo `child_profile` debe tener al menos un `guardian_id` válido al momento de creación (regla de negocio #1).
- El PIN de acceso debe tener un formato simple y validado (ej. 4 dígitos numéricos), pensado para niños.
- El avatar debe seleccionarse de un catálogo predefinido (no carga libre de imágenes, por seguridad infantil).

### 5.3 Validaciones de contenido (lecciones/juegos)
- Una lección no puede publicarse sin al menos un recurso audiovisual asociado (regla de negocio #10).
- Una lección no puede pasar a estado "publicado" sin `approved_by` definido (regla de negocio #5).
- El campo `config` (JSON) de cada juego debe validarse contra un esquema específico según el `type` de juego (ej. un juego de memoria requiere pares de tarjetas válidos).
- El `order` dentro de una categoría debe ser único por categoría (para evitar lecciones con el mismo orden).

### 5.4 Validaciones de intentos y progreso
- `score`, `correct_answers`, `wrong_answers` deben ser valores no negativos.
- `duration_seconds` debe ser mayor a cero y dentro de un rango razonable (para detectar datos corruptos o manipulados).
- No se permite registrar un `game_attempt` para un juego que no está publicado.
- El desbloqueo de una lección (cambio de estado en `progress`) solo ocurre si se cumple el umbral definido en la regla de negocio #3.

---

## 6. Manejo de Duplicados

| Escenario | Estrategia propuesta |
|---|---|
| **Email duplicado al registrar tutor/docente** | Validación a nivel de base de datos (constraint `unique`) + validación en el formulario con mensaje claro. Se ofrece opción de "recuperar cuenta" si el email ya existe. |
| **Niño registrado dos veces por error (mismo tutor)** | Validación por combinación de `guardian_id` + `name` + `birth_date` con advertencia (no bloqueo total, ya que puede haber niños con nombres similares); se muestra alerta de "posible duplicado" antes de confirmar creación. |
| **Mismo niño vinculado a dos tutores que lo registran por separado** | Requiere revisión manual/administrativa; se propone un flujo de "fusión de perfiles" en el panel admin para consolidar historial de progreso. |
| **Intentos de juego duplicados (doble clic, doble envío de formulario)** | Uso de *idempotency key* o *debounce* en frontend + validación backend que descarta envíos duplicados en una ventana de tiempo corta (ej. 2 segundos). |
| **Lecciones o categorías con el mismo nombre** | No se bloquea (puede haber lecciones similares en diferentes niveles), pero se valida que el `order` sea único dentro de la misma categoría. |
| **Badges otorgados más de una vez al mismo niño** | Constraint único en la tabla pivote `child_badges` sobre la combinación `child_profile_id` + `badge_id`. |

---

## 7. Arquitectura Laravel Propuesta

### 7.1 Estructura general

- **Laravel** como backend/API (Laravel 11 o superior).
- **Base de datos:** MySQL.
- **Frontend:** por definir entre:
  - Blade + Livewire/Alpine.js (más simple, menos separación).
  - Vue/React vía Inertia.js (recomendado si se busca una experiencia de juego más rica e interactiva).
  - App móvil nativa/Flutter consumiendo una API Laravel pura (si se planea versión móvil desde el inicio).

> **Decisión pendiente:** definir si LescoKids será una SPA con Inertia, una API pura consumida por app móvil, o ambas.

### 7.2 Organización de dominios (siguiendo convenciones Laravel, con enfoque modular)

```
app/
├── Models/
│   ├── User.php
│   ├── ChildProfile.php
│   ├── Classroom.php
│   ├── Category.php
│   ├── Lesson.php
│   ├── LessonResource.php
│   ├── Game.php
│   ├── GameAttempt.php
│   ├── Progress.php
│   └── Badge.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Admin/          (gestión de contenido)
│   │   ├── Tutor/          (panel de tutores)
│   │   ├── Docente/        (panel de docentes/aulas)
│   │   └── Game/           (endpoints de juego para niños)
│   ├── Requests/           (Form Requests para validación)
│   └── Resources/          (API Resources para respuestas JSON)
├── Services/
│   ├── ProgressService.php     (lógica de desbloqueo de niveles)
│   ├── BadgeService.php        (lógica de otorgamiento de logros)
│   └── DuplicateCheckService.php
├── Policies/
│   ├── ChildProfilePolicy.php
│   ├── LessonPolicy.php
│   └── ClassroomPolicy.php
└── Enums/
    ├── UserRole.php
    ├── LessonStatus.php
    └── GameType.php
```

### 7.3 Componentes clave a nivel de arquitectura

- **Autorización:** uso de Laravel Policies/Gates para controlar acceso según rol (niño, tutor, docente, admin), reforzando la regla de negocio #6 y #7.
- **Form Requests** dedicados por acción, para mantener las validaciones (sección 5) separadas de los controladores.
- **Service classes** para lógica de negocio compleja (desbloqueo de niveles, cálculo de progreso, otorgamiento de medallas), evitando "controladores gordos".
- **Eventos y Listeners:** ej. evento `LessonCompleted` que dispara listener para evaluar desbloqueo de siguiente lección y posible otorgamiento de badges.
- **Jobs/Queues:** para procesamiento de video (ej. compresión/transcodificación de videos de señas subidos por admin) sin bloquear la petición.
- **API Resources:** para estructurar las respuestas JSON consumidas por el frontend (especialmente si se usa Inertia o una SPA separada).
- **Multi-guard de autenticación (opcional):** posible guard separado para el login simplificado de niños (PIN) vs. login estándar de tutores/docentes/admin.

### 7.4 Consideraciones de accesibilidad e infraestructura

- Almacenamiento de videos: usar Laravel Storage con disco configurado (local en desarrollo, S3 o similar en producción) dado el peso de archivos multimedia.
- Cacheo del catálogo de lecciones/categorías (poco cambiante) para mejorar rendimiento en el flujo de juego.
- Diseño de API pensado para bajo tiempo de respuesta, ya que la experiencia de niños pequeños es sensible a la latencia percibida.

---

## 8. Estrategia de Pruebas

### 8.1 Niveles de prueba

| Nivel | Enfoque | Herramientas sugeridas |
|---|---|---|
| **Unitarias** | Lógica de negocio aislada: cálculo de progreso, desbloqueo de niveles, validación de duplicados, otorgamiento de badges. | PHPUnit / Pest |
| **Feature (integración)** | Flujos completos vía HTTP: registro de tutor, creación de perfil de niño, completar una lección, obtener reportes de progreso. | PHPUnit/Pest + Laravel Testing (RefreshDatabase) |
| **Autorización** | Verificar que cada rol solo accede a lo permitido (niño no accede a panel admin, docente no ve niños fuera de su aula). | Pest + Policies testing |
| **Validación de formularios** | Casos límite: PIN inválido, email duplicado, fecha de nacimiento futura, JSON de configuración de juego malformado. | Pest/PHPUnit |
| **Pruebas de extremo a extremo (E2E)** | Simular el flujo completo del niño jugando una lección desde el frontend. | Laravel Dusk o Playwright/Cypress (según stack frontend elegido) |
| **Pruebas de accesibilidad** | Validar que el contenido cumple criterios básicos de accesibilidad (contraste, tamaño de texto, subtítulos en videos). | Herramientas automatizadas (axe, Lighthouse) + revisión manual con usuarios reales de la comunidad sorda |
| **Pruebas de carga (fase posterior)** | Validar comportamiento con múltiples niños jugando simultáneamente (ej. en un aula). | k6 / Apache JMeter |

### 8.2 Casos de prueba prioritarios (ejemplos)

1. Un niño sin tutor asociado **no puede** crearse (regla de negocio #1).
2. Un niño con menos de 70% de aciertos **no desbloquea** la siguiente lección.
3. Un docente **no puede** ver el progreso de un niño fuera de sus aulas asignadas.
4. Una lección sin `approved_by` **no puede** cambiar a estado "publicado".
5. Un intento de juego duplicado (doble envío) **no genera** dos registros en `game_attempts`.
6. Un badge ya otorgado **no se duplica** para el mismo niño.
7. El sistema **rechaza** un email duplicado al registrar un nuevo tutor.

### 8.3 Enfoque general

- Priorizar pruebas sobre las **reglas de negocio críticas** (sección 3) antes que cobertura exhaustiva de UI.
- Dado que el producto es para niños con discapacidad auditiva, se recomienda **validación con usuarios reales** (pruebas de usabilidad con niños sordos y oyentes, y con expertos en LESCO) además de las pruebas automatizadas.

---

## 9. Riesgos y Decisiones Pendientes

### 9.1 Riesgos

| Riesgo | Impacto | Mitigación propuesta |
|---|---|---|
| **Contenido de señas incorrecto o mal validado** | Alto — riesgo pedagógico y de credibilidad del proyecto. | Flujo obligatorio de aprobación por experto en LESCO antes de publicar (ya contemplado en reglas de negocio). |
| **Protección de datos de menores** | Alto — implicaciones legales y éticas. | Minimizar datos personales recolectados de niños, evitar emails propios para menores, definir política de privacidad clara, posible revisión legal. |
| **Baja adopción por complejidad de uso** | Medio — el público es infantil, la interfaz debe ser extremadamente simple. | Pruebas de usabilidad tempranas con niños reales; iterar el diseño de UI antes de invertir en funcionalidades avanzadas. |
| **Peso de archivos multimedia (videos de señas)** | Medio — puede afectar rendimiento y costos de almacenamiento. | Estrategia de compresión/transcodificación, uso de CDN si el presupuesto lo permite. |
| **Accesibilidad insuficiente para niños sordos** | Alto — es el público central del proyecto. | Involucrar a la comunidad sorda y expertos en LESCO desde etapas tempranas de diseño, no solo como validadores finales. |
| **Dependencia de un solo desarrollador o equipo pequeño** | Medio — riesgo de continuidad del proyecto. | Documentación clara del proyecto (como este documento), decisiones registradas, código mantenible. |

### 9.2 Decisiones pendientes (requieren definición antes de avanzar a implementación)

1. **Stack de frontend:** ¿Blade + Livewire, Inertia + Vue/React, o app móvil nativa/Flutter con API pura?
2. **Estrategia de autenticación para niños:** ¿PIN simple, código QR, selección de avatar + PIN, o cuenta completa vinculada al tutor?
3. **Alojamiento de video:** ¿almacenamiento propio (S3/similar) o integración con una plataforma externa (YouTube privado, Vimeo)?
4. **Modelo de aprobación de contenido LESCO:** ¿quién será el experto validador? ¿Se define un rol formal "revisor_lesco" en el sistema?
5. **Alcance real de "docente" en el MVP:** ¿se incluye desde la primera versión o se pospone a fase 2, dado que aumenta la complejidad del modelo de datos (aulas, grupos)?
6. **Política de privacidad y cumplimiento legal** aplicable a menores en Costa Rica (y si se planea uso fuera del país, también otras jurisdicciones).
7. **Modelo de monetización (si aplica):** ¿el proyecto es gratuito, tiene versión institucional para escuelas, o algún modelo freemium? Esto puede afectar el modelo de datos (planes, suscripciones).
8. **Nivel de gamificación:** ¿solo medallas/puntaje, o se contemplan mecánicas más elaboradas (rachas diarias, tienda de recompensas, etc.) desde el MVP?

---

*Documento de especificación de diseño — versión inicial. Sujeto a revisión conforme avancen las decisiones pendientes.*
