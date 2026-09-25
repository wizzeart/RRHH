# Cambios: Actualización Automática de Vacaciones en Prenómina

**Fecha de Implementación:** Marzo 10, 2026

## Descripción General
Se ha implementado un sistema automático que actualiza la tabla `submayor_vacaciones` cada vez que se guarda y exporta una prenómina exitosamente.

## Cambios Realizados

### 1. Nueva Función en `mdl.Prenomina.php`
**Función:** `_update_submayor_vacaciones_from_prenomina($year, $month)`
- **Ubicación:** línea ~2347 en `mdl.Prenomina.php`
- **Propósito:** Actualiza automáticamente la tabla `submayor_vacaciones` después de guardar una prenómina

#### Lógica de Actualización:
Para cada trabajador con datos en la prenómina del mes:

1. **Suma de días mensuales:** Siempre suma `2.1816 días` a la columna `vacaciones`
2. **Resta de vacaciones utilizadas:** Si `vac_días > 0` en la prenómina, resta esos días
3. **Resta de pago de vacaciones:** Si `pago_vac > 0` en la prenómina, resta ese monto del campo `pago_vacaciones`

#### Valores Especiales:
- Si los campos están vacíos o en `0`, NO se realiza ningún cálculo para esos datos
- Si el trabajador no existe en `submayor_vacaciones` aún, se crea un nuevo registro
- Si el trabajador ya existe, se actualiza el registro existente

### 2. Integración en `_export_prenomina2_binary()`
**Ubicación:** línea ~2325 en `mdl.Prenomina.php`

```php
// ✅ ACTUALIZAR submayor_vacaciones después de guardar exitosamente
$this->_update_submayor_vacaciones_from_prenomina($year, $month);
```

Esta llamada se ejecuta **después de** guardar exitosamente el archivo Excel en la tabla `export_prenomina`.

## Comportamiento

### Cuándo se ejecuta:
- Solo cuando se **guarda Y exporta** una prenómina exitosamente
- Una única vez por mes (la exportación solo ocurre una vez)
- Automático, sin intervención del usuario

### Qué sucede:
1. Se extrae el fichero Excel para descargar
2. Se guarda en la tabla `export_prenomina`
3. Se actualiza la tabla `submayor_vacaciones` con los datos de esa prenómina
4. Se descarga el archivo al usuario

### Registros en el log:
Se registran los siguientes eventos en error_log:

```
=== STARTING submayor_vacaciones UPDATE for YYYY-MM ===
submayor_vacaciones UPDATE: tid=1, vac_dias=2, pago_vac=100.50, new_vac=4.1816
submayor_vacaciones INSERT: tid=5, vac_dias=0, pago_vac=0, new_vac=2.1816
=== FINISH submayor_vacaciones UPDATE: X trabajadores actualizados ===
```

## Ejemplos de Actualización

### Ejemplo 1: Trabajador con vacaciones utilizadas
- Trabajador ID: 1
- Datos en prenómina para Marzo 2026:
  - Vacaciones días: 2
  - Pago Vac: 500.00
- Registro anterior en submayor_vacaciones:
  - vacaciones: 10
  - pago_vacaciones: 5000.00
- **Resultado:**
  - vacaciones: 10 + 2.1816 - 2 = 10.1816
  - pago_vacaciones: 5000.00 - 500.00 = 4500.00

### Ejemplo 2: Trabajador nuevo (sin registro anterior)
- Trabajador ID: 10
- Datos en prenómina para Marzo 2026:
  - Vacaciones días: 0
  - Pago Vac: 0
- **Resultado (nuevo registro):**
  - vacaciones: 2.1816 (solo la acumulación mensual)
  - pago_vacaciones: NULL

### Ejemplo 3: Trabajador con ceros
- Trabajador ID: 7
- Datos en prenómina para Marzo 2026:
  - Vacaciones días: 0
  - Pago Vac: 0
- Registro anterior en submayor_vacaciones:
  - vacaciones: 5
  - pago_vacaciones: 2000.00
- **Resultado:**
  - vacaciones: 5 + 2.1816 = 7.1816 (sin restar, pues vac_días = 0)
  - pago_vacaciones: 2000.00 (sin restar, pues pago_vac = 0)

## Estructura de `submayor_vacaciones`

```sql
CREATE TABLE `submayor_vacaciones` (
  `id_trabajador` int(11) NOT NULL,
  `vacaciones` int(11) NULL DEFAULT NULL,
  `pago_vacaciones` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_trabajador`),
  CONSTRAINT `fk_submayor_trabajador` FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
```

## Tabla `prenomina` relevante

Campos utilizados:
- `trabajador_id`: Identificador del trabajador
- `year`: Año de la prenómina
- `month`: Mes de la prenómina
- `vacaciones` (alias vac_días): Días de vacaciones utilizados
- `pago_vac`: Monto pagado por vacaciones

## Notas Importantes

1. **Frecuencia:** El proceso se ejecuta una sola vez por mes, cuando se exporta la prenómina
2. **Atomicidad:** Cada trabajador se actualiza individuálmente, si uno falla, continúa con los demás
3. **Logging:** Todos los eventos se registran en error_log para auditoría
4. **Validaciones:** Se valida que los valores sean > 0 antes de usarlos para cálculos
5. **Tipos de datos:** Se convierten valores a float para los cálculos numéricos

## Posibles Mejoras Futuras

1. Agregar una columna de audit (fecha_actualización) en `submayor_vacaciones`
2. Crear una tabla de historial de cambios de vacaciones
3. Permitir reversar actualizaciones si se necesita
4. Dashboard que muestre el estado actual de vacaciones por trabajador

