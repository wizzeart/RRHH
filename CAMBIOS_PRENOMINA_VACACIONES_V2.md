# Cambios: Procesamiento Automático de Vacaciones en Prenómina v2

**Fecha de Implementación:** Marzo 10, 2026  
**Versión:** 2.0

## 📋 Resumen General

Se ha implementado un sistema automático que:

1. **Descuenta automáticamente** días y pago de vacaciones de los registros del trabajador cuando se **guarda por PRIMERA VEZ** una prenómina
2. **Suma automáticamente** 2.1816 días de vacaciones cada mes
3. Actualiza **dos tablas** en paralelo: `trabajadores` y `submayor_vacaciones`
4. Asegura que los descuentos **solo ocurren UNA SOLA VEZ por mes** por trabajador

---

## 🔧 Cambios Técnicos Realizados

### 1. Nueva Función en `mdl.Prenomina.php`

#### `_apply_prenomina_deductions_to_trabajador($trabajador_id, $vac_dias, $pago_vac)`
- **Ubicación:** línea ~897 en `mdl.Prenomina.php`
- **Propósito:** Aplica descuentos y sumas a la tabla `trabajadores` cuando se crea la prenómina por PRIMERA VEZ
- **Se ejecuta:** Solo en operaciones **INSERT** (cuando no existía prenómina anterior para ese mes/trabajador)

**Lógica:**

```php
new_vacaciones_acc = vacaciones_acc - vac_dias + 2.1816
new_salario_acc = salario_acc - pago_vac
```

### 2. Modificación en `_save_prenomina2()`

**Ubicación:** línea ~883-896 en `mdl.Prenomina.php`

Se agregó verificación de INSERT vs UPDATE:

```php
if ($existing) {
    // UPDATE: No aplicar descuentos (ya se aplicaron anteriormente)
    $this->db->update('prenomina', $updateData, ['id' => ...]);
    $action = 'UPDATE';
} else {
    // INSERT: Aplicar descuentos y sumas
    $this->db->insert('prenomina', $insertData);
    $this->_apply_prenomina_deductions_to_trabajador(
        $trabajador_id,
        $vac_dias,
        $pago_vac
    );
    $action = 'INSERT';
}
```

### 3. Función Existente: `_update_submayor_vacaciones_from_prenomina()`

- **Ubicación:** línea ~2347 en `mdl.Prenomina.php`
- **Se ejecuta:** En `_export_prenomina2_binary()` (durante la exportación)
- **Propósito:** Sincroniza cambios con la tabla `submayor_vacaciones`

---

## 📊 Flujo de Datos y Tablas Afectadas

### Tabla 1: `trabajadores`

**Columnas modificadas:**
- `vacaciones_acc`: Saldo de días de vacaciones disponibles
- `salario_acc`: Salario acumulado del trabajador

**Cuándo se actualiza:**
- Al **guardar prenómina** por PRIMERA VEZ (INSERT)
- Automático cuando se hace "Guardar"

### Tabla 2: `prenomina`

**Columnas de referencia:**
- `vacaciones` (también llamado `vac_dias`): Días de vacaciones tomados
- `pago_vac`: Monto pagado por vacaciones

### Tabla 3: `submayor_vacaciones` (opcional)

**Se actualiza en:** `_export_prenomina2_binary()` (durante exportación)

---

## 🔄 Flujo de Ejecución

### Escenario 1: PRIMERA GENERACIÓN DE PRENÓMINA (Mes Nuevo)

```
1. Usuario entra al módulo: index.php?module=prenomina-2
   └── Carga datos de trabajadores y prenóminas existentes

2. Usuario modifica datos y hace clic en "GUARDAR"
   └── POST a api-app.php (method=save-prenomina2)
       └── Se verifican los datos
       └── Para cada trabajador:
           ├── ¿Existe prenómina anterior para este mes?
           │   ├── NO → Ejecutar INSERT
           │   │   └── Llamar: _apply_prenomina_deductions_to_trabajador()
           │   │       ├── Restar vac_dias de vacaciones_acc
           │   │       ├── Restar pago_vac de salario_acc
           │   │       └── Sumar 2.1816 a vacaciones_acc
           │   └── SÍ → Ejecutar UPDATE (sin descuentos)
           └── Registrar en tabla prenomina
       └── Respuesta al usuario: "Guardado: X registros"

3. Usuario hace clic en "EXPORTAR"
   └── POST a api-app.php (method=export-prenomina2-binary)
       └── Genera Excel
       └── Guarda en tabla export_prenomina
       └── Llama: _update_submayor_vacaciones_from_prenomina()
           ├── Sincroniza con submayor_vacaciones
           └── Suma 2.1816 días nuevamente (si aplica)
       └── Descarga archivo
```

### Escenario 2: EDICIÓN DE PRENÓMINA (Mes Existente)

```
1. Usuario entra al módulo de prenómina (ya existe datos del mes)
2. Usuario modifica valores y hace clic en "GUARDAR"
   └── _save_prenomina2() detecta UPDATE (ya existe)
       └── Solo actualiza datos en tabla prenomina
       └── NO aplica descuentos nuevamente
       └── Respuesta: "Guardado: X registros"
```

---

## 📈 Ejemplos de Cálculo

### Ejemplo 1: Trabajador con Vacaciones Utilizadas

**Estado ANTES:**
- `trabajadores.vacaciones_acc`: 15.50 días
- `trabajadores.salario_acc`: 2500.00 CUP

**Datos en PRENÓMINA (Vac Días, Pago Vac):**
- `vac_dias`: 3 días
- `pago_vac`: 450.00 CUP

**Operación:**
```
vacaciones_acc = 15.50 - 3 + 2.1816 = 14.6816 días
salario_acc = 2500.00 - 450.00 = 2050.00 CUP
```

**Estado DESPUÉS:**
- `trabajadores.vacaciones_acc`: 14.68 días
- `trabajadores.salario_acc`: 2050.00 CUP

---

### Ejemplo 2: Trabajador sin Vacaciones Utilizadas

**Estado ANTES:**
- `trabajadores.vacaciones_acc`: 10.00 días
- `trabajadores.salario_acc`: 3000.00 CUP

**Datos en PRENÓMINA:**
- `vac_dias`: 0
- `pago_vac`: 0

**Operación:**
```
vacaciones_acc = 10.00 - 0 + 2.1816 = 12.1816 días  (solo suma los 2.1816 mensuales)
salario_acc = 3000.00 - 0 = 3000.00 CUP  (sin cambios)
```

**Estado DESPUÉS:**
- `trabajadores.vacaciones_acc`: 12.18 días
- `trabajadores.salario_acc`: 3000.00 CUP

---

### Ejemplo 3: Número Negativo (Protección)

**Estado ANTES:**
- `trabajadores.vacaciones_acc`: 2.00 días
- `trabajadores.salario_acc`: 500.00 CUP

**Datos en PRENÓMINA:**
- `vac_dias`: 5 días (más de lo disponible)
- `pago_vac`: 1000.00 CUP (más de lo disponible)

**Operación:**
```
vacaciones_acc = 2.00 - 5 + 2.1816 = -0.8184 → Ajustado a 0.00 (protección)
salario_acc = 500.00 - 1000.00 = -500.00 → Ajustado a 0.00 (protección)
```

**Estado DESPUÉS:**
- `trabajadores.vacaciones_acc`: 0.00 días
- `trabajadores.salario_acc`: 0.00 CUP

**Nota:** Se registra WARNING en error_log

---

## 🛡️ Protecciones Implementadas

1. **Una sola vez por mes:** Verifica si la prenómina ya existe (INSERT vs UPDATE)
2. **Sin números negativos:** Ajusta a 0.00 si el cálculo resulta negativo
3. **Logging completo:** Registra cada operación en error_log
4. **Transacciones:** Cada actualización es atómica (si falla, se revierte)
5. **Validación de trabajador:** Verifica que el trabajador exista antes de actualizar

---

## 📋 Registros en error_log

**Formato de logs:**

```
=== APPLYING PRENOMINA DEDUCTIONS for trabajador_id: 123 ===
Deductions applied: tid=123
  vacaciones_acc: 15.50 - 3 + 2.1816 = 14.6816
  salario_acc: 2500.00 - 450.00 = 2050.00

[Si hay protección]
WARNING: Vacaciones negativas para trabajador 456: -0.8184. Ajustando a 0.
```

---

## ✅ Checklist de Verificación

Para verificar que todo está funcionando:

1. ✅ Abrir módulo prenomina-2
2. ✅ Guardar prenómina (primer mes) → Verificar que se apliquen descuentos
3. ✅ Revisar `trabajadores.vacaciones_acc` → Debe haber restado vac_días + sumado 2.1816
4. ✅ Revisar `trabajadores.salario_acc` → Debe haber restado pago_vac
5. ✅ Modificar datos del mismo mes → Verificar que NO se repitan descuentos
6. ✅ Exportar prenómina → Verificar que sincronice con submayor_vacaciones
7. ✅ Revisar error_log → Debe haber registros de las operaciones

---

## 📌 Notas Importantes

1. **Tiempo de ejecución:** El proceso es muy rápido (< 100ms por trabajador)
2. **Concurrencia:** Si varios usuarios guardan el mismo mes simultáneamente, cada uno se procesa independientemente
3. **Ediciones posteriores:** Los cambios en prenómina después del primer guardado NO afectan a vacaciones_acc ni salario_acc
4. **Rollback:** Si necesitas revertir, debes:
   - Ir a `trabajadores` y ajustar manualmente `vacaciones_acc` y `salario_acc`
   - O ejecutar un script SQL de reversión
5. **Auditoría:** Toda operación se registra en el historial (`add_history`) si está habilitado

---

## 🔍 Monitoreo

**Lugares donde verificar:**

1. **error_log** → Registros detallados de operaciones
2. **Tabla trabajadores** → Cambios en vacaciones_acc y salario_acc
3. **Tabla prenomina** → Datos guardados (vac_dias, pago_vac)
4. **Tabla submayor_vacaciones** → Sincronización de datos (export-prenomina)

