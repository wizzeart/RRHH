# Análisis: aumento de vacaciones dentro de prenómina

> Documento de **análisis / referencia**. Generado a partir de la lectura directa del código.
>
> **Actualización (2026-06-25):** el devengo mensual de días sobre `trabajadores.vacaciones_acc`
> se **sacó de la prenómina** y ahora lo realiza un **cron independiente por mes vencido**
> (ver [§8](#8-flujo-vigente-devengo-por-cron-mes-vencido)). Las secciones 3–5 describen el
> comportamiento **histórico** (antes del cambio) y conservan el diagnóstico que motivó la
> refactorización; al final de cada una se indica qué quedó resuelto.

## 1. Resumen

- **Stack:** PHP (MVC ligero) + MySQL.
- **Lógica de negocio:** [`classes/mdl.Prenomina.php`](classes/mdl.Prenomina.php).
- **Fórmulas de UI:** [`modules/prenomina-2/prenomina-2-pro.js`](modules/prenomina-2/prenomina-2-pro.js).
- **Enrutamiento de métodos (API):** [`mdl.Prenomina.php:33-45`](classes/mdl.Prenomina.php#L33-L45).

Bajo el término "vacaciones" conviven **dos conceptos distintos**:

1. **Pago de vacaciones del período** (`pago_vac`): dinero que se suma al salario de ese mes.
2. **Acumulado de días** (`trabajadores.vacaciones_acc` / `submayor_vacaciones.vacaciones`): saldo
   de días que el trabajador va ganando (devenga) mes a mes.

El "aumento de vacaciones" se refiere principalmente al **devengo mensual** del acumulado de días.

## 2. Pago de vacaciones (efecto en el salario del mes)

Fórmulas del frontend en [`prenomina-2-pro.js:28-55`](modules/prenomina-2/prenomina-2-pro.js#L28-L55):

- `pago_vac = vac_dias × tarifa × 8` — paga los días de vacaciones a tarifa horaria por jornada de 8h.
- `salario_neto = sal_dev − costo_ausencias + pago_vac` → el pago de vacaciones **incrementa** el neto.
- `seg_social = (sal_dev + pago_vac) × 5%` → también sube la base del seguro social.

Persistencia en `_save_prenomina2()`
([`mdl.Prenomina.php:966-981`](classes/mdl.Prenomina.php#L966-L981)): columnas `vacaciones`,
`pago_vac`, `salario_neto`, `seg_social`, `ing_pers_3`, `ing_pers_5`, `salario_pagar`, etc.

> Nota: el cliente puede enviar el número de días como `vacaciones` o como `vac_dias`; el backend
> los normaliza ([`mdl.Prenomina.php:965`](classes/mdl.Prenomina.php#L965)).

## 3. Aumento del acumulado de días — las tres rutas (histórico)

> **Histórico.** Las dos primeras rutas (sobre `vacaciones_acc`) **se retiraron** en la
> refactorización del 2026-06-25; ver [§8](#8-flujo-vigente-devengo-por-cron-mes-vencido).

El devengo mensual de días estaba implementado en **tres funciones distintas**, con **dos
constantes diferentes** y **dos destinos de datos**:

| Función | Constante | Destino | Disparador | Condición | Estado |
|---|---|---|---|---|---|
| `_agregar_dias_vacaciones()` ([L2133](classes/mdl.Prenomina.php#L2133)) | **+2.18** | `trabajadores.vacaciones_acc` | `save-horas` → `_save_horas` ([L900](classes/mdl.Prenomina.php#L900)) | 1×/año-mes (tabla `prenomina_vacaciones_procesadas`) | **RETIRADO** — llamada eliminada; función marcada `@deprecated` |
| `_apply_prenomina_deductions_to_trabajador()` ([L1057](classes/mdl.Prenomina.php#L1057)) | **+2.1816** (− `vac_dias`) | `trabajadores.vacaciones_acc` | `save-prenomina2` ([L1008](classes/mdl.Prenomina.php#L1008)) | solo en INSERT (1ª vez por trabajador/mes) | **DEVENGO RETIRADO** — se quitó el `+2.1816`; conserva solo `− vac_dias` |
| `_update_submayor_vacaciones_from_prenomina()` ([L2537](classes/mdl.Prenomina.php#L2537)) | **+2.1816** (− `vac_dias`) | `submayor_vacaciones.vacaciones` | `export-prenomina2-binary` ([L2510](classes/mdl.Prenomina.php#L2510)) | al exportar a Excel | **SIN CAMBIOS** (fuera de alcance; ver [§7](#7-pendientes)) |

Detalle de cada cálculo (histórico):

- **`_agregar_dias_vacaciones`:** sumaba `vacaciones_acc = vacaciones_acc + 2.18` para cada
  trabajador de la prenómina. Idempotente por período (tabla `prenomina_vacaciones_procesadas`).
  *Ya no se invoca.*
- **`_apply_prenomina_deductions_to_trabajador`:** hacía
  `vacaciones_acc = vacaciones_acc − vac_dias + 2.1816` y `salario_acc = salario_acc − pago_vac`.
  *Ahora:* `vacaciones_acc = vacaciones_acc − vac_dias` (sin devengo); el descuento de salario y
  los topes a 0 se conservan.
- **`_update_submayor_vacaciones_from_prenomina`:** `vacaciones = vacaciones + 2.1816 − vac_dias`
  y `pago_vacaciones = pago_vacaciones − pago_vac` en `submayor_vacaciones`. *Sin cambios.*

## 4. Flujo activo real (frontend PRO)

- El frontend PRO solo invoca `save-prenomina2` y `export-prenomina2-binary`
  ([`prenomina-2-pro.js:591`](modules/prenomina-2/prenomina-2-pro.js#L591),
  [`644`](modules/prenomina-2/prenomina-2-pro.js#L644),
  [`663`](modules/prenomina-2/prenomina-2-pro.js#L663),
  [`757`](modules/prenomina-2/prenomina-2-pro.js#L757)).
- **No se encontró ningún llamador de `save-horas` en el JS**, por lo que
  `_agregar_dias_vacaciones` (+2.18) parece una ruta legada/separada, aunque el endpoint
  `save-horas` sigue siendo invocable vía `api-app.php`.

Secuencia del flujo (actualizada tras el cambio):

```
1. Usuario abre Prenómina 2 (PRO)
2. Calcula en cliente: pago_vac, salario_neto, seg_social, impuestos...
3. Guardar  → POST save-prenomina2
   └─ INSERT: _apply_prenomina_deductions_to_trabajador()
              actualiza trabajadores.vacaciones_acc (SOLO −vac_dias) y salario_acc (−pago_vac)
              [antes también sumaba +2.1816 → eliminado]
   └─ UPDATE: NO reajusta trabajadores
4. Exportar → POST export-prenomina2-binary
   └─ _update_submayor_vacaciones_from_prenomina()
              actualiza submayor_vacaciones (+2.1816 −vac_dias; −pago_vac)  [sin cambios]

* El devengo mensual de +2.18 sobre vacaciones_acc ya NO ocurre aquí:
  lo realiza el cron cron_devengo_vacaciones.php (ver §8).
```

## 5. Riesgos y observaciones (con estado tras el cambio)

1. **Doble devengo potencial en `vacaciones_acc`.** *(diagnóstico histórico)* `save-horas` (+2.18)
   y `save-prenomina2` (+2.1816) sumaban al mismo campo con controles de idempotencia
   independientes. → **RESUELTO:** ambos devengos se retiraron de la prenómina; el devengo lo
   centraliza el cron ([§8](#8-flujo-vigente-devengo-por-cron-mes-vencido)).
2. **Dos fuentes de verdad desincronizadas.** `trabajadores.vacaciones_acc` y
   `submayor_vacaciones.vacaciones` pueden divergir. → **PARCIAL:** `vacaciones_acc` ahora lo
   alimenta solo el cron; el **submayor** sigue actualizándose al exportar (sin unificar todavía).
3. **Constante inconsistente.** Coexistían `2.18` y `2.1816`. El comentario "26 días / 12 meses"
   no produce ninguno (26 / 12 = 2.1667). → **RESUELTO para `vacaciones_acc`:** el cron usa una
   única constante (`$DIAS_POR_MES = 2.18`). El submayor aún usa `2.1816` (pendiente).
4. **Reajuste solo en INSERT.** `_apply_prenomina_deductions_to_trabajador` corre solo en la
   primera generación; al corregir una prenómina (UPDATE) el descuento de `vac_dias`/`pago_vac`
   **no se reajusta**. → **VIGENTE** (no abarcado por este cambio).
5. **Idempotencia parcial al exportar.** `_update_submayor_vacaciones_from_prenomina` **no
   registra el período procesado**; reexportar el mismo mes vuelve a sumar 2.1816 y a restar
   `vac_dias` sobre el submayor. → **VIGENTE** (no abarcado por este cambio).

## 6. Tablas y campos involucrados

| Tabla | Campos relevantes | Rol |
|---|---|---|
| `prenomina` | `vacaciones`, `pago_vac`, `salario_neto`, `seg_social`, `salario_pagar` | Prenómina mensual por trabajador (única por `trabajador_id, year, month`) |
| `trabajadores` | `vacaciones_acc`, `salario_acc` | Saldo acumulado de días y de salario pendiente |
| `submayor_vacaciones` | `vacaciones`, `pago_vacaciones` | Mayor auxiliar (auditoría) del acumulado |
| `plan_vacaciones` | `dias`, `fecha_aprobacion`, `trabajador_id` | Días aprobados que alimentan `vac_dias` |
| `prenomina_vacaciones_procesadas` | `year`, `month`, `fecha_proceso` | Control de idempotencia de `_agregar_dias_vacaciones` (ya en desuso) |
| `devengo_vacaciones_procesado` | `year`, `month`, `dias`, `trabajadores_afectados`, `fecha_proceso` | Control de idempotencia del **cron** de devengo (nuevo) |

## 7. Pendientes

Focos que **siguen vigentes** (no abarcados por el cambio del cron):

- **Submayor:** unificar el devengo del submayor (`+2.1816`) con la constante del cron (`2.18`),
  o decidir su rol respecto a `vacaciones_acc`.
- **Reajuste en UPDATE:** que la corrección de una prenómina (UPDATE) reajuste el descuento de
  `vac_dias`/`pago_vac` sobre `trabajadores`.
- **Idempotencia del submayor:** registrar el período procesado en
  `_update_submayor_vacaciones_from_prenomina` para evitar doble suma al reexportar.

## 8. Flujo vigente: devengo por cron (mes vencido)

Desde 2026-06-25 el devengo mensual de días sobre `trabajadores.vacaciones_acc` lo realiza un
script independiente: [`cron_devengo_vacaciones.php`](cron_devengo_vacaciones.php).

- **Cantidad fija:** `$DIAS_POR_MES = 2.18` (constante única, configurable al inicio del script).
- **Mes vencido:** se ejecuta el **día 1 de cada mes** y acredita el **mes anterior** (el que
  terminó). Ej.: al correr el 1 de julio acredita junio. Por defecto el período es
  `strtotime('first day of last month')`; admite override CLI: `php cron_devengo_vacaciones.php <year> <month>`.
- **Alcance:** **todos los activos, todas las empresas** —
  `WHERE (trabajador_eliminado = 0 OR trabajador_eliminado IS NULL) AND estatus = 'activo'`
  (sin filtro de `empresa_id`). Aplica un único `UPDATE` masivo y atómico.
- **Idempotencia:** registra el período en `devengo_vacaciones_procesado` (`UNIQUE(year, month)`);
  reejecutar el mismo período no vuelve a sumar.
- **Crontab (Linux):**
  `0 6 1 * * /usr/bin/php /ruta/al/proyecto/cron_devengo_vacaciones.php >> /ruta/al/proyecto/logs/cron_devengo_vacaciones.log 2>&1`

Bootstrap basado en el patrón de [`cron_notificar_ausencias.php`](cron_notificar_ausencias.php)
(sin `App`/sesión; conexión directa con `MsSql`).
