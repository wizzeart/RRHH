# Resumen: sistema de vacaciones — backend + Ficha de Usuario (app móvil AllnovuWorkers)

> Documento generado leyendo directamente el código fuente del backend
> `allnovu-team` (PHP + MySQL) y del frontend móvil `AllnovuWorker_app`
> (Expo / React Native). Objetivo: que otra IA (o desarrollador) entienda
> rápido **cómo funcionan las vacaciones hoy**, y por qué la "Ficha de
> Usuario" de la app puede mostrar días de vacaciones incorrectos tras los
> últimos cambios en el backend.
>
> Fecha del análisis: 2026-07-20.

---

## 1. Arquitectura general

- **Backend (backoffice web):** PHP con un MVC ligero (`classes/mdl.*.php`
  = "modelos"/controladores por módulo, enrutados por `api-app.php` /
  `index.php`). Base de datos MySQL.
- **API REST para la app móvil:** carpeta separada `api/mobile/*.php`,
  enrutada por [`api/mobile/index.php`](api/mobile/index.php). Es un
  **router propio, independiente del backoffice**, con sus propias
  funciones (no reutiliza las clases `Vacaciones` / `Trabajadores` del
  backoffice). Autenticación por JWT (`Authorization: Bearer <jwt>`),
  sesión resuelta con `require_worker_session($app)` (trabajador) o
  `require_admin($app)` (rol admin).
- **App móvil:** Expo/React Native + TypeScript, en
  `Proyecto APP RRHH/AllnovuWorker_app`. Consume la API móvil con
  `axios` (`src/api/endpoints.ts`, tipos en `src/api/types.ts`).
  Pantallas relevantes: `app/(tabs)/vacaciones.tsx` (tab "Vacaciones"),
  `app/modals/solicitar-vacacion.tsx` (solicitar), ficha de usuario
  (tipo `Ficha` consumido desde `GET ?action=ficha`).

**Punto clave:** existen **dos implementaciones separadas** de la lógica
de vacaciones que deberían estar sincronizadas y no lo están del todo:

1. El **backoffice web** (`classes/mdl.Vacaciones.php`,
   `classes/mdl.Prenomina.php`, `classes/mdl.SubmayorVacaciones.php`,
   `classes/mdl.Trabajadores.php`) — lógica completa, con workflow
   multinivel y "congelado" de saldo.
2. La **API móvil** (`api/mobile/ficha.php`, `api/mobile/vacaciones.php`,
   `api/mobile/admin.php`) — lógica **simplificada y más antigua**, que
   no conoce varios conceptos que el backoffice introdujo después
   (ver §5).

---

## 2. Modelo de datos (tablas y columnas relevantes)

| Tabla | Columna | Significado |
|---|---|---|
| `trabajadores` | `vacaciones_acc` | **Saldo acumulado de días** de vacaciones que el trabajador ha devengado (ganado) hasta hoy. Es el número "fuente" principal. |
| `trabajadores` | `vacaciones_congeladas` | Días **reservados/comprometidos** porque ya tienen un plan de vacaciones *aprobado definitivamente*, pero que **todavía no se han restado** de `vacaciones_acc` (la resta real ocurre cuando se procesa la nómina del mes en que empiezan). Columna añadida en un cambio reciente (script [`check_vacaciones_congeladas.php`](check_vacaciones_congeladas.php)). |
| `trabajadores` | `salario_acc` | Salario acumulado pendiente de pago (relacionado, no es objeto de este documento). |
| `submayor_vacaciones` | `id_trabajador`, `vacaciones`, `pago_vacaciones` | **Ledger/mayor auxiliar independiente** de días y pago de vacaciones. Pensado como "libro de auditoría", pero en la práctica es una **segunda fuente de verdad** que puede desincronizarse de `trabajadores.vacaciones_acc` (ver §4). |
| `plan_vacaciones` | `id`, `trabajador_id`, `fecha_inicio`, `fecha_fin`, `dias` (CSV de fechas `YYYY-MM-DD`), `estado`, `observaciones`, `fecha_creacion`, `fecha_aprobacion`, `fecha_aprobacion_area`, `periodo_descuento` | **Solicitudes de vacaciones** del trabajador (lo que en la app se ve como "Solicitar vacaciones" y la lista de solicitudes). `estado` es el motor del workflow (ver §3). `periodo_descuento` (`'YYYY-MM'`) marca cuándo la prenómina ya descontó esos días. |
| `devengo_vacaciones_procesado` | `year`, `month`, `dias`, `trabajadores_afectados` | Tabla de **idempotencia** del cron mensual de devengo (§4). Evita sumar dos veces el mismo mes. |
| `prenomina` | `vacaciones` / `vac_dias`, `pago_vac` | Días de vacaciones **tomados/pagados en la nómina de ese mes** (concepto distinto: "pago de vacaciones", no el acumulado). |
| `prenomina_vacaciones_procesadas` | — | Tabla de idempotencia de una ruta de devengo **ya retirada** (histórica, en desuso). |

**Estados posibles de `plan_vacaciones.estado`:**
`Pendiente` → `Aprobado Area` / `Rechazado Area` (paso opcional, jefe de
área, rol 4) → `Aprobado` / `Rechazado` (aprobación final, admin rol 1)
→ `Procesada` (cuando la prenómina ya descontó esos días del saldo).

---

## 3. Workflow de aprobación y "congelado" de saldo (backoffice web)

Archivo: [`classes/mdl.Vacaciones.php`](classes/mdl.Vacaciones.php).

1. **Solicitar** (`_save`): el trabajador (o quien solicita por él) elige
   fechas o días sueltos. Se valida:
   - Tope de **24 días/año** sumando planes `Pendiente`, `Aprobado`,
     `Aprobado Area`, `Procesada` del mismo año (excluye rechazados).
   - Saldo real disponible = `vacaciones_acc − vacaciones_congeladas − días
     de planes 'Pendiente' del trabajador`, más una proyección del devengo
     mensual (2.18/mes) entre el mes actual y el mes de la solicitud,
     tope 24.
   - Que no se solape con otro plan no rechazado.
   - Inserta el plan con `estado = 'Pendiente'`.
2. **Aprobación por jefe de área** (`_aprobar_area`, rol 4, solo si tiene
   asignado ese departamento/ubicación): `Pendiente → Aprobado Area`.
   No toca el saldo todavía.
3. **Aprobación final** (`_aprobar_final`, rol 1 admin, o rol 4 si ya
   pasó por área): `Pendiente|Aprobado Area → Aprobado`. **Aquí se
   "congela" el saldo**:
   ```
   trabajadores.vacaciones_congeladas += días_del_plan
   ```
   Importante: **no se resta nada de `vacaciones_acc` en este paso.** La
   resta real ocurre después, cuando la prenómina procesa ese mes
   (`mdl.Prenomina.php`, ver §4) y marca el plan como `Procesada`
   (`periodo_descuento`).
4. **Rechazo** (`_rechazar_area` / `_rechazar_final`): cambia estado, no
   afecta saldo.
5. **Eliminar un plan** (`_del`): si estaba `Aprobado` y **aún no fue
   procesado** en nómina (`periodo_descuento` vacío), libera lo
   congelado: `vacaciones_congeladas -= días` (con `GREATEST(0, …)`).

**Fórmula "canónica" de días disponibles** usada en el backoffice
(dos sitios coinciden en esto):

```
dias_disponibles = MAX(0, trabajadores.vacaciones_acc − trabajadores.vacaciones_congeladas)
```

- [`classes/mdl.Vacaciones.php`](classes/mdl.Vacaciones.php) →
  `_get_dias_disponibles()`.
- [`classes/mdl.Trabajadores.php`](classes/mdl.Trabajadores.php) →
  `_get_vacaciones_acc()` (usada al mostrar la ficha del trabajador en
  el panel web de RRHH).

---

## 4. Devengo mensual del acumulado (`vacaciones_acc`) — historia reciente

Este es el punto que más cambió últimamente (ver
[`ANALISIS_AUMENTO_VACACIONES_PRENOMINA.md`](ANALISIS_AUMENTO_VACACIONES_PRENOMINA.md)
para el análisis completo, ya existente en el repo).

**Antes (histórico, retirado):** el acumulado se sumaba dentro de la
prenómina, en **tres sitios distintos**, con **dos constantes distintas**
(`2.18` y `2.1816`) y sin una única fuente de verdad. Generaba doble
devengo.

**Desde 2026-06-25 (estado actual):**

- El devengo mensual de `trabajadores.vacaciones_acc` lo hace
  **únicamente** el script
  [`cron_devengo_vacaciones.php`](cron_devengo_vacaciones.php):
  - Suma una constante fija `$DIAS_POR_MES = 2.18` a **todos los
    trabajadores activos, de todas las empresas**, en un único `UPDATE`
    masivo.
  - Se ejecuta el **día 1 de cada mes** y acredita el **mes anterior**
    (mes vencido).
  - Es **idempotente**: registra el período en
    `devengo_vacaciones_procesado` (`UNIQUE(year, month)`).
  - Dentro de `mdl.Prenomina.php` se **retiró** el `+2.1816`/`+2.18` que
    antes se sumaba a `vacaciones_acc`; ahora esa lógica **solo resta**
    los días tomados (`vac_dias`) al guardar la prenómina por primera vez
    (`_apply_prenomina_deductions_to_trabajador`, solo en INSERT, nunca
    en UPDATE de una prenómina ya existente).

- **`submayor_vacaciones` NO se tocó en este cambio** y sigue siendo
  alimentada por
  `_update_submayor_vacaciones_from_prenomina()`, que se ejecuta **solo
  al exportar la prenómina a Excel** y sigue usando la constante vieja
  `+2.1816` (no `2.18`), **sin control de idempotencia** (reexportar el
  mismo mes vuelve a sumar). Es decir:

  > `trabajadores.vacaciones_acc` y `submayor_vacaciones.vacaciones`
  > **son dos números que pueden divergir** entre sí: distinta
  > constante, distinto disparador (cron vs. exportar prenómina),
  > distinta idempotencia.

Esto está documentado como pendiente conocido en el propio repo
(sección "Pendientes" del análisis citado arriba).

---

## 5. API móvil (`api/mobile/*`) — lo que realmente consume la app

Router: [`api/mobile/index.php`](api/mobile/index.php). Rutas relevantes
(formato `?action=...`, todas requieren `Authorization: Bearer <jwt>`
salvo login):

| Acción | Archivo | Función | Uso en la app |
|---|---|---|---|
| `GET ficha` | `ficha.php` | `ficha_handler()` | **Ficha de Usuario** (perfil) — trae todos los datos del trabajador, incluidas `vacaciones_acumuladas`, `vacaciones_usadas`, `vacaciones_disponibles`. |
| `GET vacaciones` | `vacaciones.php` | `vacaciones_list()` | Tab **"Vacaciones"** (`app/(tabs)/vacaciones.tsx`) — cards "Disponibles / Usadas / Acumuladas" + lista de solicitudes. |
| `POST vacaciones/solicitar` | `vacaciones.php` | `vacaciones_solicitar()` | Modal "Solicitar vacaciones". |
| `GET admin/resumen`, `GET admin/vacaciones`, `POST admin/vacaciones/resolver` | `admin.php` | — | Panel admin dentro de la app (tab "Admin", solo rol 1). |

### 5.1 `ficha.php` — cómo calcula las vacaciones de la Ficha de Usuario

```php
// Prioriza submayor_vacaciones; si falla, cae a trabajadores.vacaciones_acc
$acc = submayor_vacaciones.vacaciones  ?? trabajadores.vacaciones_acc;

vacaciones_acumuladas  = $acc;
vacaciones_usadas      = 0;     // <-- SIEMPRE 0, comentario dice
                                 //     "no hay tabla de uso en esta BD"
vacaciones_disponibles = $acc;  // <-- SIEMPRE igual al acumulado
```

Esto **no resta `vacaciones_congeladas` ni nada de `plan_vacaciones`**.
El comentario en el código (`// no hay tabla de uso en esta BD`) está
**desactualizado**: `plan_vacaciones` sí existe y sí se usa en otras
partes del sistema (incluida la propia API móvil, en `vacaciones.php`).

### 5.2 `vacaciones.php` — cómo calcula el resumen del tab "Vacaciones"

```php
// Igual que ficha.php: prioriza submayor_vacaciones sobre vacaciones_acc
$acumuladas = submayor_vacaciones.vacaciones ?? trabajadores.vacaciones_acc;

// "usadas" = suma de días de TODAS las solicitudes cuyo estado
// contenga "aprobad" (y no contenga "rechaz")
foreach (plan_vacaciones as $s) {
    if (stripos($s.estado, 'aprobad') !== false
        && stripos($s.estado, 'rechaz') === false) {
        $usadas += dias($s);
    }
}

$disponibles = max(0, $acumuladas - $usadas);
```

Problemas de esta fórmula frente al workflow real (§3):

- `stripos($estado, 'aprobad')` **también hace match con `"Aprobado
  Area"`**, que es solo la aprobación intermedia del jefe de área, **no**
  la aprobación final. Un plan en `Aprobado Area` (esperando aprobación
  del admin) ya se cuenta como "usado", cuando en el backoffice ese
  estado **todavía no congela ni descuenta nada**.
- No usa `vacaciones_congeladas` en ningún momento.
- No distingue `Procesada` (ya descontado de verdad en nómina) de
  `Aprobado` (solo congelado, aún no descontado): los trata igual.
- No aplica el tope de 24 días/año que sí aplica el backoffice al
  solicitar.

### 5.3 `admin.php` — aprobar/rechazar desde la app (`admin_vacaciones_resolver`)

Esta es una **tercera implementación**, paralela a `mdl.Vacaciones.php`,
mucho más simple:

```php
// Solo conoce DOS estados finales: Aprobado / Rechazado.
// No existe el paso "Aprobado Area" aquí.
if ($accion === 'aprobar') {
    UPDATE plan_vacaciones SET estado='Aprobado', fecha_aprobacion=hoy WHERE id=:id;
} else {
    UPDATE plan_vacaciones SET estado='Rechazado' WHERE id=:id;
}
```

**No actualiza `trabajadores.vacaciones_congeladas`.** Es decir: si un
admin aprueba una solicitud **desde la app móvil**, el plan queda
`Aprobado` pero el saldo **no se congela**, a diferencia de aprobar
**desde el panel web** (`_aprobar_final`, que sí hace
`vacaciones_congeladas += días`). Esto deja el sistema en un estado
inconsistente según por dónde se apruebe la misma solicitud.

---

## 6. Por qué "no salen correctamente" las vacaciones — diagnóstico

Con el código anterior, hay **cuatro fuentes de discrepancia
concretas**, todas verificables en el repo:

1. **Dos acumulados que pueden no coincidir:** la app (`ficha.php` y
   `vacaciones.php`) muestra `submayor_vacaciones.vacaciones` como
   "acumuladas" con preferencia sobre `trabajadores.vacaciones_acc`,
   pero **todo el workflow nuevo** (cron de devengo, congelado, tope de
   24 días, panel web de RRHH) opera sobre `trabajadores.vacaciones_acc`.
   Si `submayor_vacaciones` quedó desactualizada (p. ej. porque nadie
   volvió a exportar la prenómina, o porque se exportó dos veces y quedó
   inflada), **la app mostrará un número distinto al que maneja RRHH**.
2. **`vacaciones_congeladas` no existe para la app:** ni `ficha.php` ni
   `vacaciones.php` la leen. Si un trabajador tiene un plan `Aprobado`
   (congelado en el backoffice), el **panel web** le mostrará menos días
   disponibles (`acc − congeladas`), pero **la app le seguirá mostrando
   el acumulado completo** (`ficha.php`) o un cálculo distinto basado en
   contar solicitudes "aprobadas" (`vacaciones.php`) — los tres números
   pueden no coincidir entre sí.
3. **`ficha.php.vacaciones_usadas` siempre es `0`:** un valor
   hard-codeado y con un comentario obsoleto. En la Ficha de Usuario esto
   hace que "disponibles" nunca refleje nada usado o congelado.
4. **Aprobar desde la app no congela saldo:** si el flujo real de tu
   empresa es aprobar solicitudes desde el panel admin de la app móvil
   (`admin/vacaciones/resolver`), esas aprobaciones **nunca pasan por
   `_aprobar_final` del backoffice**, así que `vacaciones_congeladas`
   nunca sube por esa vía, aunque el estado quede en `Aprobado`. Esto
   puede hacer que el panel web "descuadre" respecto a lo que en teoría
   debería estar congelado.

En resumen: **el backend introdujo (2026-06-25 y alrededores) un sistema
nuevo de devengo por cron + saldo congelado + workflow multinivel, pero
la capa de API móvil (`api/mobile/ficha.php`, `vacaciones.php`,
`admin.php`) se quedó con la lógica anterior** y no fue actualizada para
usar `vacaciones_congeladas` ni la fuente de verdad correcta
(`trabajadores.vacaciones_acc`). Ese desfase entre "lo que sabe el
backoffice" y "lo que sabe la API móvil" es la explicación más probable
de que la Ficha de Usuario muestre datos de vacaciones incorrectos.

---

## 7. Contrato de datos actual con el frontend (para no romper la app al arreglar el backend)

`src/api/types.ts` en la app espera exactamente estos campos — cualquier
fix en el backend debe seguir devolviéndolos con estos nombres:

```ts
// Respuesta de GET ?action=ficha (campo `ficha`)
interface Ficha {
  // ...resto de campos del trabajador...
  vacaciones_acumuladas: number;
  vacaciones_usadas: number;
  vacaciones_disponibles: number;
}

// Respuesta de GET ?action=vacaciones
interface VacacionesResponse {
  status: 1;
  resumen: { acumuladas: number; usadas: number; disponibles: number };
  solicitudes: VacacionItem[]; // incluye id, fecha_inicio, fecha_fin, dias,
                                // estado, observaciones, fecha_creacion, fecha_aprobacion
}
```

La pantalla `app/(tabs)/vacaciones.tsx` pinta directamente
`data.resumen.disponibles / .usadas / .acumuladas` en tres `<Stat>`.

---

## 8. Recomendación de fix (para aplicar en otro proyecto/sesión)

Objetivo: que **`ficha.php`, `vacaciones.php` y `admin.php`** usen la
misma fuente de verdad y la misma fórmula que ya usa el backoffice
(`mdl.Vacaciones.php` / `mdl.Trabajadores.php`), en vez de reinventar el
cálculo:

1. **Fuente única del acumulado:** usar siempre
   `trabajadores.vacaciones_acc` (no `submayor_vacaciones`, que es solo
   un libro auxiliar/auditoría desincronizado). Si se quiere seguir
   mostrando `submayor_vacaciones` en algún reporte, dejarlo separado y
   con otro nombre de campo, nunca como "acumuladas" del trabajador.
2. **Fórmula única de disponibles**, igual a la del backoffice:
   ```
   disponibles = MAX(0, vacaciones_acc - vacaciones_congeladas)
   ```
3. **"Usadas" bien definidas**, evitando el falso positivo de
   `Aprobado Area`:
   - *Congeladas/comprometidas* (aprobadas pero no descontadas aún):
     `estado = 'Aprobado'` (final) — hoy corresponde a
     `vacaciones_congeladas` en `trabajadores`.
   - *Ya descontadas de verdad*: `estado = 'Procesada'`
     (`periodo_descuento` no nulo).
   - `Aprobado Area` **no** debería contarse como "usado" en la ficha;
     es solo un paso intermedio pendiente de aprobación final.
4. **`ficha.php`**: reemplazar el bloque que fija
   `vacaciones_usadas = 0` por una consulta real (por ejemplo,
   reutilizar la misma lógica que `_get_vacaciones_acc()` de
   `mdl.Trabajadores.php`), y exponer también `vacaciones_congeladas` si
   la UI quiere diferenciar "congelado" de "usado".
5. **`admin_vacaciones_resolver` (admin.php):** al aprobar
   (`accion=aprobar`), replicar lo que hace `_aprobar_final()` del
   backoffice: sumar los días del plan a
   `trabajadores.vacaciones_congeladas` (de forma idempotente, solo si
   el plan no estaba ya `Aprobado`). Si se quiere mantener el flujo
   simplificado de 2 estados en la app, al menos debe **tocar el mismo
   campo `vacaciones_congeladas`** que usa el resto del sistema para no
   descuadrar el saldo.
6. **Opcional pero recomendable:** exponer en `GET ?action=vacaciones`
   también el desglose (`congeladas`, `procesadas_este_anio`,
   `pendientes`) para que la UI pueda mostrar algo más rico que solo
   "usadas", igual que ya hace el backoffice internamente.

---

## 9. Archivos clave para referencia rápida

**Backend — backoffice (lógica "correcta"/canónica):**
- [`classes/mdl.Vacaciones.php`](classes/mdl.Vacaciones.php) — workflow completo (solicitar, aprobar por área, aprobar final, rechazar, eliminar, congelado).
- [`classes/mdl.Trabajadores.php`](classes/mdl.Trabajadores.php) — `_get_vacaciones_acc()` (línea ~2771): fórmula canónica `acc − congeladas`.
- [`classes/mdl.Prenomina.php`](classes/mdl.Prenomina.php) — descuento de `vac_dias` al guardar prenómina, y sincronización con `submayor_vacaciones` al exportar.
- [`classes/mdl.SubmayorVacaciones.php`](classes/mdl.SubmayorVacaciones.php) — CRUD manual del ledger auxiliar, estadísticas, calendario de vacaciones.
- [`cron_devengo_vacaciones.php`](cron_devengo_vacaciones.php) — devengo mensual único y oficial (+2.18/mes, idempotente).
- [`check_vacaciones_congeladas.php`](check_vacaciones_congeladas.php) — script que crea el esquema de `vacaciones_congeladas` / `periodo_descuento` / estado `Procesada`.
- [`ANALISIS_AUMENTO_VACACIONES_PRENOMINA.md`](ANALISIS_AUMENTO_VACACIONES_PRENOMINA.md) — análisis previo ya existente en el repo sobre el devengo (más detalle histórico).

**Backend — API móvil (lógica desactualizada, causa probable del bug):**
- [`api/mobile/index.php`](api/mobile/index.php) — router de acciones.
- [`api/mobile/ficha.php`](api/mobile/ficha.php) — Ficha de Usuario (`vacaciones_disponibles` siempre = acumulado; `usadas` siempre 0).
- [`api/mobile/vacaciones.php`](api/mobile/vacaciones.php) — tab Vacaciones (cuenta mal `Aprobado Area` como usado; ignora congeladas).
- [`api/mobile/admin.php`](api/mobile/admin.php) — aprobar/rechazar desde la app (no congela saldo).

**App móvil (frontend):**
- `Proyecto APP RRHH/AllnovuWorker_app/src/api/types.ts` — contratos `Ficha`, `VacacionesResponse`.
- `Proyecto APP RRHH/AllnovuWorker_app/src/api/endpoints.ts` — `fetchVacaciones`, `solicitarVacaciones`, `fetchAdminVacaciones`, `resolverVacacion`.
- `Proyecto APP RRHH/AllnovuWorker_app/app/(tabs)/vacaciones.tsx` — pantalla que pinta Disponibles/Usadas/Acumuladas.
- `Proyecto APP RRHH/AllnovuWorker_app/app/modals/solicitar-vacacion.tsx` — modal de solicitud.
