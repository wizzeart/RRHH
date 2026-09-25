/**
 * Prenómina 2 PRO - JavaScript con Undo/Redo
 */

(function () {
    'use strict';

    const State = {
        data: [],
        modified: new Set(),
        currentTab: 'Todos',
        year: new Date().getFullYear(),
        month: new Date().getMonth() + 1,
        totalsCached: {},
        undoStack: [],
        redoStack: []
    };

    // `horas` puede valer 0 legítimamente (trabajador sin horas en el período), así que NO se
    // puede aplicar el defecto de 192 con `||`: 0 es falsy y quedaría convertido en 192.
    // El defecto solo debe aplicarse cuando el valor está realmente ausente.
    function horasOrDefault(value, fallback = 192) {
        if (value === null || value === undefined || value === '') return fallback;
        const n = Number(value);
        return isNaN(n) ? fallback : n;
    }

    const Formulas = {
        // Calcular desde el salario base para evitar errores de redondeo
        a_cobrar: (row) => {
            if (row.salario_base) {
                return (horasOrDefault(row.horas) / 192) * row.salario_base;
            }
            return horasOrDefault(row.horas, 0) * (row.tarifa || 0);
        },
        sal_dev: (row) => Formulas.a_cobrar(row) + (row.bonif || 0),
        pago_vac: (row) => {
            // FÓRMULA CORRECTA: pago_vac = (tarifa * 8) * vac_dias
            // Opción 1 (frontend): usar tarifa redondeada a 2 decimales para el cálculo
            const tarifaCalc = Number(((row.tarifa || 0)).toFixed(2));
            const result = (row.vac_dias || 0) * tarifaCalc * 8;
            if (row.id && [96, 165, 167, 171, 172, 173, 174, 175, 176, 177, 196, 197].includes(row.id)) {
                console.log(`📊 FORMULA DEBUG ID ${row.id}: vac_dias=${row.vac_dias}, tarifa=${row.tarifa}, tarifaCalc=${tarifaCalc}, pago_vac=${result}`);
            }
            return result;
        },
        costo_ausencias: (row) => Math.trunc((row.ausencias || 0) * 8 * (row.tarifa || 0)),
        salario_neto: (row) => Formulas.sal_dev(row) - Formulas.costo_ausencias(row) + (row.pago_vac || 0),
        seg_social: (row) => (Formulas.sal_dev(row) + (row.pago_vac || 0)) * 0.05,
        ing_pers_3: (row) => {
            const sn = Formulas.salario_neto(row);
            if (sn >= 3260) {
                return Number(((9510 - 3260) * 0.03).toFixed(2));
            }
            return 0;
        },
        ing_pers_5: (row) => {
            const sn = Formulas.salario_neto(row);
            // Calcular 5% sobre el monto que excede 9510 (sin tope máximo)
            const base = Math.max(0, sn - 9510);
            return Number((base * 0.05).toFixed(2));
        },
        total_descuentos: (row) => Formulas.seg_social(row) + Formulas.ing_pers_3(row) + Formulas.ing_pers_5(row),
        salario_pagar: (row) => Formulas.salario_neto(row) - Formulas.total_descuentos(row)
    };

    const Format = {
        currency: (value) => new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'CUP', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Number(value || 0)),
        currencyWithDecimals: (value) => new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'CUP', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0)),
        // Tarifa uses 3 decimals for display but truncate instead of round (to show exact 41,666 for 41.6666...)
        currencyTarifa: (value) => {
            const val = Number(value || 0);
            const factor = 1000; // 3 decimals
            const truncated = Math.trunc(val * factor) / factor;
            return truncated.toLocaleString('es-ES', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
        },
        // Format con mínimo 2 decimales pero sin redondear
        // Normaliza errores de punto flotante usando un toFixed razonable (6 decimales)
        // luego elimina ceros finales y asegura al menos `min` decimales.
        formatMinDecimals: (value, min = 2) => {
            const num = Number(value || 0);
            // Use 6 decimales para limpiar ruido sin afectar los 2 primeros decimales
            const fixed = num.toFixed(6);
            // Eliminar ceros finales y posible punto sobrante
            let cleaned = fixed.replace(/(?:\.0+|(?<=\.[0-9]*?)0+)$/, '');
            // Si quedó con punto final (por ejemplo "123.") eliminarlo
            if (cleaned.endsWith('.')) cleaned = cleaned.slice(0, -1);
            // Asegurar que tenga al menos `min` decimales
            if (!cleaned.includes('.')) {
                cleaned = cleaned + '.' + '0'.repeat(min);
            } else {
                const [intPart, decPart] = cleaned.split('.');
                if ((decPart || '').length < min) {
                    cleaned = intPart + '.' + (decPart + '0'.repeat(min)).slice(0, min);
                }
            }
            return cleaned;
        }
    };

    // Trabajador dado de baja pendiente de liquidar. Sus días de vacaciones y su pago salen de
    // los acumulados del trabajador (vacaciones_acc / salario_acc) que envía el servidor, no de
    // la fórmula mensual: en una liquidación se paga TODO lo acumulado.
    function esLiquidacionPendiente(row) {
        return Number(row.es_liquidacion) === 1;
    }

    function recalculateRow(row, editedField = null) {
        row.a_cobrar = Formulas.a_cobrar(row);
        row.sal_dev = Formulas.sal_dev(row);
        // Si el usuario editó manualmente pago_vac, no lo recalcules automáticamente
        // Pero si editó otro campo, recalcula pago_vac.
        // En una liquidación pendiente nunca se recalcula: el valor viene de salario_acc.
        if (editedField !== 'pago_vac' && !esLiquidacionPendiente(row)) {
            row.pago_vac = Formulas.pago_vac(row);
        }
        row.salario_neto = Formulas.salario_neto(row);
        row.seg_social = Formulas.seg_social(row);
        row.ing_pers_3 = Formulas.ing_pers_3(row);
        row.ing_pers_5 = Formulas.ing_pers_5(row);
        row.costo_ausencias = Formulas.costo_ausencias(row);
        row.total_descuentos = Formulas.total_descuentos(row);
        row.salario_pagar = Formulas.salario_pagar(row);
        return row;
    }

    function saveStateSnapshot() {
        const snapshot = {
            data: JSON.parse(JSON.stringify(State.data)),
            modified: new Set(State.modified)
        };
        State.undoStack.push(snapshot);
        State.redoStack = [];
        if (State.undoStack.length > 50) State.undoStack.shift();
        updateUndoRedoButtons();
    }

    function undo() {
        if (State.undoStack.length === 0) {
            showAlert('No hay cambios para deshacer', 'info');
            return;
        }
        const currentSnapshot = { data: JSON.parse(JSON.stringify(State.data)), modified: new Set(State.modified) };
        State.redoStack.push(currentSnapshot);
        const previousSnapshot = State.undoStack.pop();
        State.data = previousSnapshot.data;
        State.modified = previousSnapshot.modified;
        renderTable();
        calculateTotals();
        updateUndoRedoButtons();
        showAlert('✅ Cambio deshecho', 'success');
    }

    function redo() {
        if (State.redoStack.length === 0) {
            showAlert('No hay cambios para rehacer', 'info');
            return;
        }
        const currentSnapshot = { data: JSON.parse(JSON.stringify(State.data)), modified: new Set(State.modified) };
        State.undoStack.push(currentSnapshot);
        const nextSnapshot = State.redoStack.pop();
        State.data = nextSnapshot.data;
        State.modified = nextSnapshot.modified;
        renderTable();
        calculateTotals();
        updateUndoRedoButtons();
        showAlert('✅ Cambio rehecho', 'success');
    }

    function updateUndoRedoButtons() {
        const undoBtn = document.getElementById('btn-prenom2-undo');
        const redoBtn = document.getElementById('btn-prenom2-redo');
        if (undoBtn) {
            undoBtn.disabled = State.undoStack.length === 0;
            undoBtn.style.opacity = State.undoStack.length === 0 ? '0.5' : '1';
        }
        if (redoBtn) {
            redoBtn.disabled = State.redoStack.length === 0;
            redoBtn.style.opacity = State.redoStack.length === 0 ? '0.5' : '1';
        }
    }

    // Cache Functions
    function getCacheKey() {
        const empresa = window.AppEmpresaId || 0;
        const year = State.year;
        const month = String(State.month).padStart(2, '0');
        return `prenom2_cache_empresa${empresa}_${year}${month}`;
    }

    function saveToCache() {
        try {
            const cacheKey = getCacheKey();
            let cacheData = {
                timestamp: new Date().toISOString(),
                data: []
            };

            // Intentar leer cache existente para preservarlo (merge)
            const existingCacheStr = localStorage.getItem(cacheKey);
            if (existingCacheStr) {
                try {
                    const existingCache = JSON.parse(existingCacheStr);
                    cacheData.data = existingCache.data || [];
                    // Mantener timestamp original si es reciente? No, actualizamos timestamp al guardar
                } catch (e) {
                    console.warn('Cache corrupto anterior, se sobrescribirá');
                }
            }

            // Mapa para facilitar merge
            const cacheMap = new Map();
            // 1. Cargar datos existentes del cache en el mapa
            cacheData.data.forEach(item => cacheMap.set(item.id, item));

            // 2. Actualizar/Agregar datos actuales (State.data) REEMPLAZANDO lo que había
            // Esto asegura que si deshice un cambio, el valor "viejo" (actual en pantalla) sobrescriba al "nuevo" (en cache)
            State.data.forEach(row => {
                cacheMap.set(row.id, {
                    id: row.id,
                    horas: row.horas,
                    bonif: row.bonif,
                    vac_dias: row.vac_dias,
                    pago_vac: row.pago_vac,
                    ausencias: row.ausencias
                });
            });

            // 3. Serializar de nuevo
            cacheData.data = Array.from(cacheMap.values());
            cacheData.timestamp = new Date().toISOString(); // Actualizar fecha

            localStorage.setItem(cacheKey, JSON.stringify(cacheData));
            console.log('✅ Datos guardados en cache (Merge strategy)');
        } catch (e) {
            console.error('Error guardando cache:', e);
        }
    }

    function loadFromCache() {
        try {
            const cacheKey = getCacheKey();
            const cachedStr = localStorage.getItem(cacheKey);
            if (!cachedStr) return false;

            const cache = JSON.parse(cachedStr);
            const cacheAge = (new Date() - new Date(cache.timestamp)) / 1000 / 60 / 60; // en horas

            // Solo cargar si tiene menos de 24 horas (reducido de 7 días para seguridad)
            if (cacheAge > 24) {
                console.warn('Cache expirado (>24h), limpiando...');
                clearCache();
                return false;
            }

            let changeCount = 0;
            // Aplicar datos de cache a State.data actual
            cache.data.forEach(cachedRow => {
                const row = State.data.find(r => r.id === cachedRow.id);
                // IMPORTANTE: Solo restaurar si los datos son diferentes para evitar falsos positivos
                // Aunque para "session restore" quizás queramos restaurar todo.
                // Pero lo más importante es marcar State.modified
                if (row) {
                    // Una liquidación pendiente no se restaura desde caché: sus valores los fija
                    // el servidor a partir de los acumulados del trabajador y no son editables.
                    if (esLiquidacionPendiente(row)) {
                        return;
                    }

                    // Verificar si hubo cambios reales contra los datos cargados del server (que ya están en 'row')
                    // O simplemente confiamos en el cache.
                    // Al restaurar, asumimos que es una "modificación pendiente".

                    row.horas = cachedRow.horas;
                    row.bonif = cachedRow.bonif;
                    row.vac_dias = cachedRow.vac_dias;
                    row.ausencias = cachedRow.ausencias;
                    
                    // Recalcular todos los valores basándose en los campos del caché
                    recalculateRow(row);

                    // CRÍTICO: Marcar como modificado para que se pinte en amarillo y se sepa que viene de cache
                    State.modified.add(row.id);
                    changeCount++;
                }
            });

            if (changeCount > 0) {
                console.log(`✅ ${changeCount} registros cargados desde cache y marcados como modificados`);
                return true;
            }
            return false;
        } catch (e) {
            console.error('Error cargando cache:', e);
            return false;
        }
    }

    function clearCache() {
        try {
            const cacheKey = getCacheKey();
            localStorage.removeItem(cacheKey);
            // No mostrar alert siempre, a veces es interno
            console.log('✅ Cache limpiado');
        } catch (e) {
            console.error('Error limpiando cache:', e);
        }
    }

    async function loadData(tab = 'Todos') {
        try {
            showLoading(true);
            const mesFormato = `${State.year}-${String(State.month).padStart(2, '0')}`;
            const url = `api-app.php?module=prenomina&method=list-prenomina2&tab=${encodeURIComponent(tab)}&mes=${mesFormato}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            if (!Array.isArray(data)) {
                showAlert('Error: Respuesta inválida del servidor', 'error');
                return;
            }
            // DEBUG: Ver respuesta RAW del servidor
            console.log('🔴 RESPUESTA RAW DEL SERVER:', JSON.stringify(data.slice(0, 3)));

            State.data = data.map(row => {
                row.id = parseInt(row.id);
                row.horas = horasOrDefault(row.horas);
                row.bonif = parseFloat(row.bonif || 0);
                // Guardar salario base y calcular tarifa
                row.tarifa_raw = parseFloat(row.tarifa || 0);
                // Prefer explicit salario_cargo if returned by server (already monthly floored by heuristic)
                if (row.salario_cargo && !isNaN(parseFloat(row.salario_cargo))) {
                    row.salario_base = parseFloat(row.salario_cargo);
                } else {
                    row.salario_base = parseFloat(row.tarifa_raw) * 192;
                }
                // keep tarifa as high-precision raw value for calculations; we'll format for display
                row.tarifa = row.tarifa_raw;
                if (row.tarifa_adjusted_by_heuristic) {
                    console.log(`ID ${row.id}: tarifa_raw=${row.tarifa_raw}, salario_base=${row.salario_base} (ADJUSTED by HEURISTIC)`);
                } else {
                    console.log(`ID ${row.id}: tarifa_raw=${row.tarifa_raw}, salario_base=${row.salario_base}`);
                }
                    // Mapear vacaciones a vac_dias si viene del servidor
                    row.vac_dias = parseFloat(row.vac_dias || row.vacaciones || 0);
                    row.vacaciones = parseFloat(row.vacaciones || 0);
                row.ausencias = parseFloat(row.ausencias || 0);
                // Datos de liquidación: el servidor ya devuelve en vacaciones/pago_vac los
                // acumulados cuando el trabajador está pendiente de liquidar.
                row.es_liquidacion = Number(row.es_liquidacion || 0);
                row.vacaciones_acc = parseFloat(row.vacaciones_acc || 0);
                row.salario_acc = parseFloat(row.salario_acc || 0);
                row.pago_vac = parseFloat(row.pago_vac || 0);
                
                console.log(`📌 ID ${row.id} ANTES DE RECALC: vac_dias=${row.vac_dias}, vacaciones=${row.vacaciones}, tarifa=${row.tarifa}`);
                
                // Recalcular todos los valores incluyendo pago_vac
                row = recalculateRow(row);
                
                console.log(`📌 ID ${row.id} DESPUÉS DE RECALC: pago_vac=${row.pago_vac}`);
                
                return row;
            });
            State.modified.clear();
            State.undoStack = [];
            State.redoStack = [];
            updatePeriodDisplay();
            // Intentar cargar datos desde cache
            loadFromCache();
            renderTable();
            calculateTotals();
            updateUndoRedoButtons();
            showAlert(`Datos cargados: ${State.data.length} empleados`, 'success');
        } catch (error) {
            showAlert(`Error: ${error.message}`, 'error');
        } finally {
            showLoading(false);
        }
    }

    function renderTable() {
        const tbody = document.getElementById('prenom2-tbody');
        tbody.innerHTML = '';
        State.data.forEach((row) => {
            const tr = document.createElement('tr');
            tr.dataset.id = row.id;

            // Vacaciones y Pago Vac de una liquidación pendiente no se editan: son los
            // acumulados del trabajador y los fija el servidor.
            const bloqueado = esLiquidacionPendiente(row);
            const bloqueoLiquidacion = bloqueado
                ? ' readonly disabled title="Trabajador dado de baja pendiente de liquidar: se paga el acumulado y no es editable"'
                : '';

            // Aplicar resaltado para liquidación
            if (bloqueado) {
                tr.style.backgroundColor = '#fff3cd';
            }
            // Aplicar resaltado para filas modificadas
            else if (State.modified.has(row.id)) {
                tr.style.background = '#fff9e6';
                tr.style.borderLeft = '4px solid #ffc107';
            }

            tr.innerHTML = `
                <td style="width: 30px; text-align: center;">${row.id}</td>
                <td style="width: 70px;">${row.ci || '-'}</td>
                <td style="width: 150px;"><a href="index.php?module=ficha-trabajador&id=${row.id}" style="color: inherit; text-decoration: none; cursor: pointer;" onmouseover="this.style.textDecoration='underline'; this.style.color='#10442a';" onmouseout="this.style.textDecoration='none'; this.style.color='inherit';">${row.nombre || '-'}</a></td>
                <td style="width: 70px; display: none;">${row.expediente || '-'}</td>
                <!-- Show tarifa rounded to 2 decimals for display, calculations still use row.tarifa_raw -->
                <td style="width: 60px; text-align: right;">${Format.currencyWithDecimals(row.tarifa_raw)}</td>
                <td style="width: 70px; text-align: center;"><input type="number" class="prenom2-input prenom2-horas" data-id="${row.id}" value="${Math.trunc(row.horas)}" min="0" max="999"></td>
                <td style="width: 70px; text-align: center;"><input type="number" step="0.01" class="prenom2-input prenom2-bonif" data-id="${row.id}" value="${row.bonif.toFixed(2)}" min="0" max="999999"></td>
                <td style="width: 70px; text-align: center;"><input type="number" step="0.01" class="prenom2-input prenom2-vac-dias" data-id="${row.id}" value="${row.vac_dias.toFixed(2)}" min="0" max="999"${bloqueoLiquidacion}></td>
                <td style="width: 70px; text-align: center;"><input type="number" step="0.01" class="prenom2-input prenom2-pago-vac" data-id="${row.id}" value="${Format.formatMinDecimals(row.pago_vac, 2)}" min="0" max="999999"${bloqueoLiquidacion}></td>
                <td style="width: 80px; text-align: right; cursor: pointer;" class="prenom2-cell-formula" data-formula="a_cobrar" data-id="${row.id}">${Format.currencyWithDecimals(row.a_cobrar)}</td>
                <td style="width: 80px; text-align: right; cursor: pointer;" class="prenom2-cell-formula" data-formula="sal_dev" data-id="${row.id}">${Format.currencyWithDecimals(row.sal_dev)}</td>
                <td style="width: 100px; text-align: right; font-weight: bold; cursor: pointer;" class="prenom2-cell-formula" data-formula="salario_neto" data-id="${row.id}">${Format.currencyWithDecimals(row.salario_neto)}</td>
                <td style="width: 80px; text-align: right; cursor: pointer;" class="prenom2-cell-formula" data-formula="seg_social" data-id="${row.id}">${Format.currencyWithDecimals(row.seg_social)}</td>
                <td style="width: 70px; text-align: right; cursor: pointer;" class="prenom2-cell-formula" data-formula="ing_pers_3" data-id="${row.id}">${Format.currencyWithDecimals(row.ing_pers_3)}</td>
                <td style="width: 70px; text-align: right; cursor: pointer;" class="prenom2-cell-formula" data-formula="ing_pers_5" data-id="${row.id}">${Format.currencyWithDecimals(row.ing_pers_5)}</td>
                <td style="width: 120px; text-align: right; font-weight: bold; cursor: pointer;" class="prenom2-cell-formula" data-formula="salario_pagar" data-id="${row.id}">${Format.currencyWithDecimals(row.salario_pagar)}</td>
            `;
            tbody.appendChild(tr);
        });
        attachInputListeners();
        attachFormulaListeners();
    }

    function getFormulaText(formulaName, row) {
        const formulas = {
            'a_cobrar': `=[@Salario Base] * [@Horas]/192 = ${row.salario_base.toFixed(2)} * ${Math.trunc(row.horas)}/192`,
            'sal_dev': `=[@A Cobrar] + [@Bonif] = ${row.a_cobrar.toFixed(2)} + ${Math.trunc(row.bonif)}`,
            'pago_vac': `=[@Salario Base] * [@Vac Días]/24 = ${row.salario_base.toFixed(2)} * ${Math.trunc(row.vac_dias)}/24`,
            'salario_neto': `=[@Sal Dev] - [@Costo Aus] + [@Pago Vac] = ${row.sal_dev.toFixed(2)} - ${row.costo_ausencias} + ${row.pago_vac.toFixed(2)}`,
            'seg_social': `=([@Sal Dev] + [@Pago Vac]) * 5% = (${row.sal_dev.toFixed(2)} + ${row.pago_vac.toFixed(2)}) * 0.05`,
            'ing_pers_3': row.salario_neto >= 3260 ? `=(9510 - 3260) * 3% = 6250 * 0.03` : `=0 (Salario Neto < 3260)`,
            'ing_pers_5': row.salario_neto > 9510 ? `=([@Salario Neto] - 9510) * 5% = (${row.salario_neto.toFixed(2)} - 9510) * 0.05` : `=0 (Salario Neto ≤ 9510)`,
            'salario_pagar': `=[@Salario Neto] - [@Total Desc] = ${row.salario_neto.toFixed(2)} - ${row.total_descuentos}`
        };
        return formulas[formulaName] || '';
    }

    function attachFormulaListeners() {
        document.querySelectorAll('.prenom2-cell-formula').forEach(cell => {
            cell.addEventListener('click', (e) => {
                const formulaName = e.target.dataset.formula;
                const rowId = parseInt(e.target.dataset.id);
                const row = State.data.find(r => r.id === rowId);
                if (row) {
                    const formulaText = getFormulaText(formulaName, row);
                    const formulaInput = document.getElementById('formula-input');
                    if (formulaInput) {
                        formulaInput.value = formulaText;
                    }
                }
            });
        });
    }

    function attachInputListeners() {
        document.querySelectorAll('.prenom2-horas').forEach(input => {
            input.addEventListener('blur', (e) => {
                const value = e.target.value === '' ? 0 : parseFloat(e.target.value);
                updateFieldAndRecalculate(parseInt(e.target.dataset.id), 'horas', isNaN(value) ? 0 : value);
            });
        });
        document.querySelectorAll('.prenom2-bonif').forEach(input => {
            input.addEventListener('blur', (e) => {
                const value = e.target.value === '' ? 0 : parseFloat(e.target.value);
                updateFieldAndRecalculate(parseInt(e.target.dataset.id), 'bonif', isNaN(value) ? 0 : value);
            });
        });
        document.querySelectorAll('.prenom2-vac-dias').forEach(input => {
            input.addEventListener('blur', (e) => {
                const value = e.target.value === '' ? 0 : parseFloat(e.target.value);
                updateFieldAndRecalculate(parseInt(e.target.dataset.id), 'vac_dias', isNaN(value) ? 0 : value);
            });
        });
        document.querySelectorAll('.prenom2-pago-vac').forEach(input => {
            input.addEventListener('blur', (e) => {
                const value = e.target.value === '' ? 0 : parseFloat(e.target.value);
                updateFieldAndRecalculate(parseInt(e.target.dataset.id), 'pago_vac', isNaN(value) ? 0 : value);
            });
        });
    }

    function updateFieldAndRecalculate(id, field, value) {
        const row = State.data.find(r => r.id === id);
        if (!row) return;

        // Los inputs van deshabilitados, pero el bloqueo se refuerza aquí para que sea una
        // invariante real: en una liquidación pendiente vac_dias y pago_vac son los acumulados
        // del trabajador y no pueden alterarse por ninguna vía.
        if (esLiquidacionPendiente(row) && (field === 'vac_dias' || field === 'pago_vac')) {
            return;
        }

        saveStateSnapshot();
        row[field] = value;
        recalculateRow(row, field);
        State.modified.add(id);
        renderTable();
        calculateTotals();
        saveToCache(); // Guardar en cache después de cada cambio
    }

    function calculateTotals() {
        const totals = { a_cobrar: 0, bonif: 0, salario_neto: 0, total_descuentos: 0, salario_pagar: 0, empleados: State.data.length };
        State.data.forEach(row => {
            totals.a_cobrar += row.a_cobrar || 0;
            totals.bonif += row.bonif || 0;
            totals.salario_neto += row.salario_neto || 0;
            totals.total_descuentos += row.total_descuentos || 0;
            totals.salario_pagar += row.salario_pagar || 0;
        });
        document.getElementById('total-a-cobrar').textContent = Format.currency(totals.a_cobrar);
        document.getElementById('total-bonif').textContent = Format.currency(totals.bonif);
        document.getElementById('total-neto').textContent = Format.currency(totals.salario_neto);
        document.getElementById('total-descuentos').textContent = Format.currency(totals.total_descuentos);
        document.getElementById('total-pagar').textContent = Format.currency(totals.salario_pagar);
        document.getElementById('total-empleados').textContent = totals.empleados;
        State.totalsCached = totals;
    }

    async function loadTabs() {
        try {
            const response = await fetch(`api-app.php?module=prenomina&method=list-departamentos`);
            const tabs = await response.json();
            const container = document.getElementById('prenom2-tabs-container');
            container.innerHTML = '';
            if (Array.isArray(tabs)) {
                tabs.forEach((tabName, idx) => {
                    const button = document.createElement('div');
                    button.className = 'prenom2-tab' + (idx === 0 ? ' active' : '');
                    button.textContent = tabName;
                    button.dataset.tab = tabName;
                    button.addEventListener('click', () => switchTab(tabName, button));
                    container.appendChild(button);
                });
            }
        } catch (error) {
            console.error('Error cargando tabs:', error);
        }
    }

    function switchTab(tabName, element) {
        document.querySelectorAll('.prenom2-tab').forEach(tab => tab.classList.remove('active'));
        element.classList.add('active');
        State.currentTab = tabName;
        loadData(tabName);
    }

    function showLoading(show) {
        const loading = document.getElementById('prenom2-loading');
        const tableBody = document.querySelector('.prenom2-table-body');
        if (show) {
            loading.style.display = 'block';
            tableBody.style.display = 'none';
        } else {
            loading.style.display = 'none';
            tableBody.style.display = 'block';
        }
    }

    function showAlert(message, type = 'info') {
        const container = document.getElementById('prenom2-alerts');
        const alert = document.createElement('div');
        alert.className = `prenom2-alert ${type}`;
        alert.innerHTML = `<i class="fa fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i><span>${message}</span>`;
        container.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function updatePeriodDisplay() {
        const display = document.getElementById('prenom2-period-display');
        const titleText = document.getElementById('prenom2-month-title-text');
        const monthName = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][State.month - 1];
        const fullMonthName = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][State.month - 1];
        display.textContent = `${monthName} ${State.year}`;

        // Actualizar título del mes
        if (titleText) {
            titleText.textContent = `Prenómina de ${fullMonthName} ${State.year}`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        console.log('🚀 Inicializando Prenómina 2 Pro...');
        updatePeriodDisplay(); // Actualizar título inicial
        updateUndoRedoButtons();
        document.getElementById('btn-prenom2-undo')?.addEventListener('click', () => undo());
        document.getElementById('btn-prenom2-redo')?.addEventListener('click', () => redo());
        document.getElementById('btn-prenom2-reload')?.addEventListener('click', () => location.reload());
        document.getElementById('btn-prenom2-recalc')?.addEventListener('click', () => {
            State.data.forEach(row => recalculateRow(row));
            renderTable();
            calculateTotals();
            showAlert('✅ Recálculo completado', 'success');
        });
        document.getElementById('btn-prenom2-clear-cache')?.addEventListener('click', () => {
            if (confirm('¿Estás seguro de que deseas limpiar el cache? Los cambios no guardados se perderán.')) {
                clearCache();
                location.reload();
            }
        });
        document.getElementById('btn-prenom2-save')?.addEventListener('click', async () => {
            // MODIFICADO: Guardar TODAS las filas, no solo las modificadas
            if (!State.data || State.data.length === 0) {
                showAlert('⚠️ No hay registros para guardar', 'warning');
                return;
            }

            try {
                // Recolectar TODAS las filas (no solo modificadas)
                const rows = State.data.map(row => ({
                    trabajador_id: row.id,
                    horas: horasOrDefault(row.horas),
                    tarifa: row.tarifa || 0,
                    bonif: row.bonif || 0,
                    ausencias: row.ausencias || 0,
                    vacaciones: row.vac_dias || 0,
                    a_cobrar: row.a_cobrar || 0,
                    sal_dev: row.sal_dev || 0,
                    pago_vac: row.pago_vac || 0,
                    salario_neto: row.salario_neto || 0,
                    seg_social: row.seg_social || 0,
                    ing_pers_3: row.ing_pers_3 || 0,
                    ing_pers_5: row.ing_pers_5 || 0,
                    salario_pagar: row.salario_pagar || 0
                }));

                console.log('✅ Guardando', rows.length, 'filas (TODAS)');

                const response = await fetch('api-app.php?module=prenomina&method=save-prenomina2', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ year: State.year, month: State.month, rows })
                });

                const result = await response.json();
                console.log('Respuesta del servidor:', result);

                if (result.status === 1) {
                    State.modified.clear();
                    renderTable(); // Re-render to remove yellow highlights
                    clearCache(); // Clean cache after successful save
                    const msg = `✅ Guardado exitoso: ${result.affected || rows.length} registros${result.inserted_default ? ' (' + result.inserted_default + ' por defecto)' : ''}`;
                    showAlert(msg, 'success');
                } else {
                    showAlert(`❌ Error: ${result.error || result.msg || 'Error desconocido'}`, 'error');
                }
            } catch (error) {
                console.error('Error al guardar:', error);
                showAlert(`❌ Error al guardar: ${error.message}`, 'error');
            }
        });

        // GUARDAR Y EXPORTAR - Nueva funcionalidad
        document.getElementById('btn-prenom2-save-export')?.addEventListener('click', async () => {
            if (!State.data || State.data.length === 0) {
                showAlert('⚠️ No hay registros para guardar y exportar', 'warning');
                return;
            }

            try {
                // Primero guardar
                const rows = State.data.map(row => ({
                    trabajador_id: row.id,
                    horas: horasOrDefault(row.horas),
                    tarifa: row.tarifa || 0,
                    bonif: row.bonif || 0,
                    ausencias: row.ausencias || 0,
                    // Preferir el valor cacheado 'vac_dias' si existe, si no usar 'vacaciones' del servidor
                    vacaciones: (row.vac_dias != null ? row.vac_dias : row.vacaciones) || 0,
                    a_cobrar: row.a_cobrar || 0,
                    sal_dev: row.sal_dev || 0,
                    pago_vac: row.pago_vac || 0,
                    salario_neto: row.salario_neto || 0,
                    seg_social: row.seg_social || 0,
                    ing_pers_3: row.ing_pers_3 || 0,
                    ing_pers_5: row.ing_pers_5 || 0,
                    salario_pagar: row.salario_pagar || 0
                }));

                showAlert('💾 Guardando datos...', 'info');

                const saveResponse = await fetch('api-app.php?module=prenomina&method=save-prenomina2', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ year: State.year, month: State.month, rows })
                });

                const saveResult = await saveResponse.json();

                if (saveResult.status !== 1) {
                    showAlert(`❌ Error al guardar: ${saveResult.error || saveResult.msg}`, 'error');
                    return;
                }

                showAlert(`✅ Guardado exitoso: ${saveResult.affected} registros`, 'success');

                // Luego exportar y guardar en base de datos
                showAlert('📊 Generando y guardando Excel...', 'info');

                const empresaParam = (typeof window.AppEmpresaId !== 'undefined' && window.AppEmpresaId) ? `&empresa_id=${window.AppEmpresaId}` : '';
                const exportUrl = `api-app.php?module=prenomina&method=export-prenomina2-binary&tab=${encodeURIComponent(State.currentTab)}&year=${State.year}&month=${State.month}${empresaParam}`;

                // Recolectar overrides (valores actuales en la tabla / caché)
                const overrides = State.data.map(row => ({
                    trabajador_id: row.id,
                    horas: row.horas || 0,
                    tarifa: row.tarifa || 0,
                    bonif: row.bonif || 0,
                    ausencias: row.ausencias || 0,
                        vacaciones: (row.vac_dias != null ? row.vac_dias : row.vacaciones) || 0,
                        vac_dias: (row.vac_dias != null ? row.vac_dias : row.vacaciones) || 0,
                    pago_vac: row.pago_vac || 0
                }));

                const exportResponse = await fetch(exportUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ overrides })
                });

                if (exportResponse.ok) {
                    clearCache(); // Clean cache after successful export/save
                    showAlert('✅ Excel generado y guardado correctamente', 'success');
                    // Descargar también
                    const blob = await exportResponse.blob();
                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = `Prenomina_${State.year}-${String(State.month).padStart(2, '0')}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(blobUrl);
                    document.body.removeChild(a);
                } else {
                    showAlert('❌ Error al exportar Excel', 'error');
                }
            } catch (error) {
                console.error('Error en Guardar y Exportar:', error);
                showAlert(`❌ Error: ${error.message}`, 'error');
            }
        });

        // CALENDAR - Cargar prenóminas disponibles (solo informativo)
        document.getElementById('btn-prenom2-calendar')?.addEventListener('click', async () => {
            const modal = document.getElementById('calendar-modal');
            const body = document.getElementById('calendar-modal-body');

            modal.classList.add('show');
            body.innerHTML = '<div class="calendar-loading"><i class="fa fa-spinner fa-spin"></i> Cargando meses disponibles...</div>';

            try {
                const response = await fetch('api-app.php?module=prenomina&method=list-export-prenomina');
                const data = await response.json();

                if (!Array.isArray(data) || data.length === 0) {
                    body.innerHTML = '<div class="calendar-no-data">No hay prenóminas guardadas</div>';
                    return;
                }

                let html = '<div class="calendar-months-grid">';

                data.forEach(item => {
                    // Usar year y month directamente (ya vienen de la BD)
                    const year = item.year;
                    const month = item.month;
                    const mes = item.mes; // formato: YYYY-MM
                    const monthName = new Date(year, month - 1).toLocaleString('es-ES', { month: 'long', year: 'numeric' });
                    const monthCapital = monthName.charAt(0).toUpperCase() + monthName.slice(1);
                    const totalTrabajadores = item.total_trabajadores || 0;
                    const monthStr = String(month).padStart(2, '0');

                    html += `<div class="calendar-month-item">
                        <div class="calendar-month-info">
                            <div class="calendar-month-title">${monthCapital}</div>
                            <div class="calendar-month-count">${totalTrabajadores} registros</div>
                        </div>
                        <div class="calendar-month-actions">
                            <button class="calendar-month-download" data-year="${year}" data-month="${month}" title="Descargar Prenómina">
                                <i class="fa fa-download"></i>
                            </button>
                            <button class="calendar-month-download-nomina" data-year="${year}" data-month="${month}" title="Descargar Nómina (para firmar)">
                                <i class="fa fa-download"></i>
                            </button>
                            <button class="calendar-month-download-dbf" data-year="${year}" data-month="${month}" title="Descargar DBF para el banco">
                                <i class="fa fa-university"></i>
                            </button>
                        </div>
                    </div>`;
                });

                html += '</div>';
                body.innerHTML = html;

                // Event listeners para descargar Excel
                body.querySelectorAll('.calendar-month-download').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const year = parseInt(btn.getAttribute('data-year'));
                        const month = parseInt(btn.getAttribute('data-month'));
                        const monthStr = String(month).padStart(2, '0');

                        try {
                            const exportUrl = `api-app.php?module=prenomina&method=export-prenomina2-binary&tab=Todos&year=${year}&month=${month}`;
                            const response = await fetch(exportUrl);

                            if (response.ok) {
                                const blob = await response.blob();
                                const blobUrl = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = blobUrl;
                                a.download = `Prenomina_${year}-${monthStr}.xlsx`;
                                document.body.appendChild(a);
                                a.click();
                                window.URL.revokeObjectURL(blobUrl);
                                document.body.removeChild(a);
                                showAlert('✅ Descarga iniciada', 'success');
                            } else {
                                showAlert('❌ Error al descargar', 'error');
                            }
                        } catch (error) {
                            console.error('Error descargando:', error);
                            showAlert('❌ Error: ' + error.message, 'error');
                        }
                    });
                });

                // Event listeners para descargar el Excel de Nómina (para firmar)
                body.querySelectorAll('.calendar-month-download-nomina').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const year = parseInt(btn.getAttribute('data-year'));
                        const month = parseInt(btn.getAttribute('data-month'));
                        const monthStr = String(month).padStart(2, '0');

                        try {
                            const exportUrl = `api-app.php?module=prenomina&method=export-nomina2-binary&tab=Todos&year=${year}&month=${month}`;
                            const response = await fetch(exportUrl);

                            if (response.ok) {
                                const blob = await response.blob();
                                const blobUrl = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = blobUrl;
                                a.download = `Nomina_${year}-${monthStr}.xlsx`;
                                document.body.appendChild(a);
                                a.click();
                                window.URL.revokeObjectURL(blobUrl);
                                document.body.removeChild(a);
                                showAlert('✅ Descarga iniciada', 'success');
                            } else {
                                showAlert('❌ Error al descargar', 'error');
                            }
                        } catch (error) {
                            console.error('Error descargando:', error);
                            showAlert('❌ Error: ' + error.message, 'error');
                        }
                    });
                });

                // Event listeners para descargar el DBF del banco.
                // A diferencia de los dos Excel, aquí primero se consulta con `check=1`: los
                // trabajadores sin cuenta estándar quedan FUERA del fichero de transferencias y
                // hay que avisar antes de generarlo, no después de mandarlo al banco.
                body.querySelectorAll('.calendar-month-download-dbf').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const year = parseInt(btn.getAttribute('data-year'));
                        const month = parseInt(btn.getAttribute('data-month'));
                        const monthStr = String(month).padStart(2, '0');
                        const periodo = `year=${year}&month=${month}`;

                        btn.disabled = true;
                        try {
                            const info = await (await fetch(`api/export-bancos-dbf.php?check=1&${periodo}`, {
                                credentials: 'same-origin'
                            })).json();

                            if (info.status !== 1) {
                                throw new Error(info.msg || 'Respuesta inválida al comprobar el fichero');
                            }

                            if (info.a_exportar === 0) {
                                showAlert(`⚠️ No hay trabajadores con prenómina de ${info.periodo} para exportar`, 'error');
                                return;
                            }

                            if (info.sin_cuenta && info.sin_cuenta.length > 0) {
                                let lista = info.sin_cuenta.slice(0, 15).map(t =>
                                    '  · ' + t.nombre + ' (CI ' + (t.carnet_identidad || 'sin CI') + ')'
                                ).join('\n');
                                if (info.sin_cuenta.length > 15) {
                                    lista += `\n  … y ${info.sin_cuenta.length - 15} más`;
                                }

                                const msg = `⚠️ ${info.sin_cuenta.length} trabajador(es) con prenómina de `
                                    + `${info.periodo} NO tienen cuenta estándar registrada y quedarán FUERA `
                                    + `del fichero (no se les pagará por transferencia):\n\n${lista}`
                                    + `\n\nSe exportarán ${info.a_exportar} trabajador(es).`
                                    + `\n\n¿Desea continuar de todos modos?`;

                                if (!confirm(msg)) { return; }
                            }

                            const response = await fetch(`api/export-bancos-dbf.php?${periodo}`, {
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                throw new Error(await response.text() || 'Error al generar el DBF');
                            }

                            const blob = await response.blob();
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = blobUrl;
                            a.download = `bancos_export_${year}-${monthStr}.dbf`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(blobUrl);
                            document.body.removeChild(a);
                            showAlert('✅ Descarga iniciada', 'success');
                        } catch (error) {
                            console.error('Error descargando DBF:', error);
                            showAlert('❌ Error: ' + error.message, 'error');
                        } finally {
                            btn.disabled = false;
                        }
                    });
                });

            } catch (error) {
                console.error('Error cargando disponibles:', error);
                body.innerHTML = '<div class="calendar-no-data">Error cargando datos</div>';
            }
        });

        // Cerrar modal
        document.getElementById('calendar-modal-close')?.addEventListener('click', () => {
            document.getElementById('calendar-modal').classList.remove('show');
        });

        document.getElementById('calendar-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'calendar-modal') {
                e.target.classList.remove('show');
            }
        });

        // SEARCH FUNCTIONALITY
        const searchInput = document.getElementById('prenom2-search-input');
        const searchClearBtn = document.getElementById('prenom2-search-clear');

        searchInput?.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#prenom2-tbody tr');
            let visibleCount = 0;

            if (query === '') {
                // Si está vacío, mostrar todas las filas
                rows.forEach(row => {
                    row.style.display = '';
                    visibleCount++;
                });
                searchClearBtn.classList.remove('show');
            } else {
                // Filtrar filas según los criterios
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    // Búsqueda en: nombre (índice 2), C.I. (índice 3), expediente (índice 1)
                    const nombre = cells[2]?.innerText.toLowerCase() || '';
                    const ci = cells[3]?.innerText.toLowerCase() || '';
                    const expediente = cells[1]?.innerText.toLowerCase() || '';

                    const matches = nombre.includes(query) || ci.includes(query) || expediente.includes(query);

                    if (matches) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                searchClearBtn.classList.add('show');
            }

            // Log para debug
            console.log(`🔍 Búsqueda: "${query}" - ${visibleCount} resultados`);
        });

        searchClearBtn?.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });

        loadTabs();
        loadData('Todos');
    });

})();
