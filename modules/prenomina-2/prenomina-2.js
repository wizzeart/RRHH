/**
 * Módulo Prenomina 2 (IML)
 * Basado en fórmulas del archivo Prenomina IML (3).xlsx
 * 
 * Estructura: Lee de tablas trabajadores y prenomina
 * Utiliza same business logic del módulo original pero con cálculos mejorados
 */

$(document).ready(function() {
    
    // ========================================
    // FORMATTERS PARA LA TABLA
    // ========================================
    
    window.currencyFormatter = function(value) {
        var num = Number(value || 0);
        return new Intl.NumberFormat('es-ES', { 
            style: 'currency', 
            currency: 'CUP', 
            minimumFractionDigits: 0, 
            maximumFractionDigits: 0 
        }).format(num);
    };

    window.numberFormatter = function(value) {
        var num = Number(value || 0);
        return new Intl.NumberFormat('es-ES', { 
            minimumFractionDigits: 0, 
            maximumFractionDigits: 0 
        }).format(num);
    };

    window.horasFormatter2 = function(value, row) {
        var v = (value == null || value === '') ? 192 : parseInt(value);
        return '<input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="3" ' +
               'class="prenom2-horas" data-id="' + row.id + '" value="' + v + '" ' +
               'style="width:60px; text-align:center; padding:4px; border:1px solid #ccc; ' +
               'border-radius:3px; font-size:12px; font-weight:bold;" />';
    };

    // ========================================
    // FUNCIONES DE CÁLCULO (Fórmulas IML)
    // ========================================
    
    /**
     * Recalcular todos los valores de una fila según fórmulas IML
     * Esta es la función CORE que contiene la lógica de Prenomina IML
     * 
     * IMPORTANTE: Si existe un valor de pago_vac guardado en caché, se respeta ese valor
     * El caché tiene PRIORIDAD sobre el cálculo automático
     */
    window.recalcularFila2 = function(row) {
        // Valores de entrada
        var horas = Number(row.horas || 192);
        var tarifa = Number(row.tarifa || 0);
        var bonif = Number(row.bonif || 0);
        var ausencias = Number(row.ausencias || 0);
        var vacaciones = Number(row.vacaciones || 0);
        
        // FÓRMULA 1: A COBRAR = Horas * Tarifa
        var a_cobrar = Math.trunc(horas * tarifa);
        
        // FÓRMULA 2: SAL DEV (Salario Devengado) = A Cobrar + Bonificaciones
        var sal_dev = Math.trunc(a_cobrar + bonif);
        
        // FÓRMULA 3: PAGO VACACIONES = Vacaciones días * (tarifa * 8)
        // ⚠️ PRIORIDAD AL CACHÉ: Si existe pago_vac guardado (no 0 y no null), usar ese valor
        var pago_vac_calculado = vacaciones * tarifa * 8;
        var pago_vac = (row.pago_vac !== undefined && row.pago_vac !== null && Number(row.pago_vac) !== 0) 
            ? Number(row.pago_vac) 
            : pago_vac_calculado;
        
        // FÓRMULA 4: SALARIO NETO = Sal Dev + Pago Vac
        var salario_neto = sal_dev + pago_vac;
        
        // FÓRMULA 5: SEG SOCIAL = Salario Neto * 5%
        var seg_social = Math.trunc(salario_neto * 0.05);
        
        // FÓRMULA 6 y 7: IMPUESTO PERSONAL (3% y 5%)
        // Rango: 3260-9510 paga 3% fijo
        // Sobre 9510: 3% del rango + 5% del exceso
        var ing_pers_3 = 0;
        var ing_pers_5 = 0;
        
        if (salario_neto >= 3260 && salario_neto <= 9510) {
            // Rango completo: 3% fijo del rango
            ing_pers_3 = Math.trunc((9510 - 3260) * 0.03);
        } else if (salario_neto > 9510) {
            // 3% del rango 3260-9510
            ing_pers_3 = Math.trunc((9510 - 3260) * 0.03);
            // 5% del exceso sobre 9510
            ing_pers_5 = Math.trunc((salario_neto - 9510) * 0.05);
        }
        
        // FÓRMULA 8: COSTO AUSENCIAS = Ausencias días * 8 horas * tarifa
        var costo_ausencias = Math.trunc(ausencias * 8 * tarifa);
        
        // FÓRMULA 9: TOTAL DESCUENTOS = Seg Social + IP 3% + IP 5% + Ausencias
        var total_descuentos = Math.trunc(seg_social + ing_pers_3 + ing_pers_5 + costo_ausencias);
        
        // FÓRMULA 10: SALARIO A PAGAR = Sal Neto - Total Descuentos
        var salario_pagar = Math.trunc(salario_neto - total_descuentos);
        
        return {
            a_cobrar: a_cobrar,
            sal_dev: sal_dev,
            pago_vac: pago_vac,
            salario_neto: salario_neto,
            seg_social: seg_social,
            ing_pers_3: ing_pers_3,
            ing_pers_5: ing_pers_5,
            costo_ausencias: costo_ausencias,
            total_descuentos: total_descuentos,
            salario_pagar: salario_pagar
        };
    };

    // ========================================
    // GESTIÓN DE TABS (Departamentos)
    // ========================================
    
    function setActiveTab(tabEl) {
        tabEl.closest('ul').querySelectorAll('li').forEach(function(li) { 
            li.classList.remove('active'); 
        });
        tabEl.parentElement.classList.add('active');
    }

    function reloadForTab(tabName) {
        var $table = $('#table-prenomina2');
        if (!$table.data('bootstrap.table')) { 
            $table.bootstrapTable(); 
        }
        
        var year = parseInt($('#prenom2-year').val(), 10) || new Date().getFullYear();
        var month = parseInt($('#prenom2-month').val(), 10) || (new Date().getMonth() + 1);
        
        var url = 'api-app.php?module=prenomina&method=list-prenomina2&tab=' + encodeURIComponent(tabName) +
                  '&year=' + year + '&month=' + month;
        
        console.log('Recargando tabla con URL:', url);
        $table.bootstrapTable('refreshOptions', { url: url });
    }

    function buildTabs() {
        fetch('api-app.php?module=prenomina&method=list-departamentos')
            .then(function(r) { return r.json(); })
            .then(function(list) {
                var $ul = $('#tabs-prenomina2');
                $ul.empty();
                
                if (Array.isArray(list) && list.length) {
                    list.forEach(function(name, idx) {
                        var active = idx === 0 ? ' class="active"' : '';
                        var li = '<li' + active + '><a href="#" data-tab="' + (name || '') + '">' + 
                                 (name || '') + '</a></li>';
                        $ul.append(li);
                    });
                    
                    var first = $ul.find('li.active a');
                    if (first.length) {
                        setTimeout(function() { 
                            reloadForTab(first.data('tab')); 
                        }, 0);
                    }
                } else {
                    // Sin tabs, cargar todo
                    var $table = $('#table-prenomina2');
                    if (!$table.data('bootstrap.table')) { 
                        $table.bootstrapTable(); 
                    }
                    $table.bootstrapTable('refreshOptions', { 
                        url: 'api-app.php?module=prenomina&method=list-prenomina2' 
                    });
                }
            })
            .catch(function(err) {
                console.error('Error cargando departamentos:', err);
                var $table = $('#table-prenomina2');
                if (!$table.data('bootstrap.table')) { 
                    $table.bootstrapTable(); 
                }
                $table.bootstrapTable('refreshOptions', { 
                    url: 'api-app.php?module=prenomina&method=list-prenomina2' 
                });
            });
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================

    $(document).on('click', '#tabs-prenomina2 a', function(e) {
        e.preventDefault();
        var tabName = $(this).data('tab');
        setActiveTab(this);
        reloadForTab(tabName);
    });

    // Input de horas - solo números
    $(document).on('input', '#table-prenomina2 .prenom2-horas', function() {
        var $inp = $(this);
        var value = $inp.val().replace(/[^0-9]/g, '');
        $inp.val(value);
    });

    // Cambio de horas - recalcular
    $(document).on('change', '#table-prenomina2 .prenom2-horas', function() {
        var $inp = $(this);
        var id = $inp.data('id');
        var horas = parseInt($inp.val() || 0, 10);
        
        var $table = $('#table-prenomina2');
        var row = $table.bootstrapTable('getRowByUniqueId', id);
        
        if (!row) return;
        
        row.horas = horas;
        var updates = recalcularFila2(row);
        
        console.log('Recalculando ID:', id, 'Horas:', horas, 'Updates:', updates);
        
        $table.bootstrapTable('updateByUniqueId', { id: id, row: updates });
        
        // Actualizar totales
        actualizarTotales();
    });

    // Botón Recargar
    $(document).on('click', '#btn-prenom2-reload', function(e) {
        e.preventDefault();
        var $active = $('#tabs-prenomina2 li.active a');
        var tabName = $active.length ? $active.data('tab') : '';
        
        console.log('Recargando...');
        reloadForTab(tabName);
    });

    // Botón Recalcular - recalcular TODAS las filas
    $(document).on('click', '#btn-prenom2-recalc', function(e) {
        e.preventDefault();
        
        var $table = $('#table-prenomina2');
        var rows = $table.bootstrapTable('getData');
        
        console.log('Recalculando', rows.length, 'filas');
        
        $.each(rows, function(idx, row) {
            var updates = recalcularFila2(row);
            $.extend(row, updates);
            $table.bootstrapTable('updateByUniqueId', { id: row.id, row: row });
        });
        
        actualizarTotales();
        alert('Recálculo completado para ' + rows.length + ' filas');
    });

    // Botón Guardar - MODIFICADO para guardar TODAS las filas incluso sin cambios
    $(document).on('click', '#btn-prenom2-save', function(e) {
        e.preventDefault();
        
        var year = parseInt($('#prenom2-year').val(), 10) || new Date().getFullYear();
        var month = parseInt($('#prenom2-month').val(), 10) || (new Date().getMonth() + 1);
        
        var $table = $('#table-prenomina2');
        var allRowData = $table.bootstrapTable('getData');
        
        if (!allRowData || allRowData.length === 0) {
            alert('⚠️ No hay registros en la tabla. Por favor, cargue los datos primero.');
            return;
        }
        
        // Recolectar TODAS las filas - incluyendo las que no tienen cambios
        var rows = [];
        allRowData.forEach(function(row) {
            // Guardar el pago_vac original del caché
            var pago_vac_cached = Number(row.pago_vac) || 0;
            
            // Primero recalcular por si hay nuevos valores de horas
            var calculados = recalcularFila2(row);
            
            // Si había un pago_vac guardado en caché (>0), restaurarlo (tiene PRIORIDAD)
            if (pago_vac_cached > 0) {
                calculados.pago_vac = pago_vac_cached;
            }
            
            rows.push({
                trabajador_id: row.id,
                horas: row.horas || 192,
                tarifa: row.tarifa || 0,
                bonif: row.bonif || 0,
                ausencias: row.ausencias || 0,
                vacaciones: row.vacaciones || 0,
                a_cobrar: calculados.a_cobrar,
                sal_dev: calculados.sal_dev,
                pago_vac: calculados.pago_vac,
                salario_neto: calculados.salario_neto,
                seg_social: calculados.seg_social,
                ing_pers_3: calculados.ing_pers_3,
                ing_pers_5: calculados.ing_pers_5,
                salario_pagar: calculados.salario_pagar
            });
        });
        
        console.log('✅ GUARDANDO', rows.length, 'filas (TODAS, incluyendo sin cambios)');
        console.log('Año:', year, 'Mes:', month);
        console.log('Primeras 3 filas:', rows.slice(0, 3));
        
        // Mostrar indicador de carga
        var originalText = $(this).html();
        $(this).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
        $(this).prop('disabled', true);
        
        fetch('api-app.php?module=prenomina&method=save-prenomina2', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                year: year, 
                month: month, 
                rows: rows 
            })
        })
        .then(function(r) { 
            console.log('Respuesta recibida, status:', r.status);
            return r.json(); 
        })
        .then(function(resp) {
            console.log('JSON parseado:', resp);
            
            // Restaurar botón
            $('#btn-prenom2-save').html(originalText);
            $('#btn-prenom2-save').prop('disabled', false);
            
            if (resp && resp.status === 1) {
                var msg = '✅ Guardado exitoso!\n\n';
                msg += 'Registros procesados: ' + (resp.affected || 0) + '\n';
                if (resp.inserted_default) {
                    msg += 'Insertados por defecto: ' + resp.inserted_default + '\n';
                }
                if (resp.errors && resp.errors.length > 0) {
                    msg += '\n⚠️ Errores:\n' + resp.errors.join('\n');
                }
                alert(msg);
                
                // Recargar tabla
                $('#table-prenomina2').bootstrapTable('refresh');
                console.log('✅ Tabla recargada');
            } else {
                alert('❌ Error en el servidor:\n' + (resp.error || resp.msg || 'Desconocido'));
                console.error('Error response:', resp);
            }
        })
        .catch(function(err) {
            console.error('❌ Error de red:', err);
            
            // Restaurar botón
            $('#btn-prenom2-save').html(originalText);
            $('#btn-prenom2-save').prop('disabled', false);
            
            alert('❌ Error de comunicación con el servidor:\n' + err);
        });
    });

    // Botón Exportar Excel
    $(document).on('click', '#btn-prenom2-export', function(e) {
        e.preventDefault();
        
        var year = parseInt($('#prenom2-year').val(), 10) || new Date().getFullYear();
        var month = parseInt($('#prenom2-month').val(), 10) || (new Date().getMonth() + 1);
        var $active = $('#tabs-prenomina2 li.active a');
        var tabName = $active.length ? $active.data('tab') : '';
        
        var url = 'api-app.php?module=prenomina&method=export-prenomina2&year=' + year + 
                  '&month=' + month;
        if (tabName) {
            url += '&tab=' + encodeURIComponent(tabName);
        }
        if (typeof window.AppEmpresaId !== 'undefined' && window.AppEmpresaId) {
            url += '&empresa_id=' + window.AppEmpresaId;
        }
        
        console.log('Exportando con URL:', url);

        // Recolectar valores actuales de la tabla (incluye cambios y pago_vac en caché)
        var $table = $('#table-prenomina2');
        var rows = $table.bootstrapTable('getData') || [];
        var overrides = [];
        rows.forEach(function(r){
            overrides.push({
                trabajador_id: r.id,
                horas: r.horas || 0,
                tarifa: r.tarifa || 0,
                bonif: r.bonif || 0,
                ausencias: r.ausencias || 0,
                vacaciones: r.vacaciones || 0,
                pago_vac: r.pago_vac || 0
            });
        });

        // Enviar mediante POST para incluir overrides en el export (descarga)
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'overrides';
        input.value = JSON.stringify(overrides);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    });

    // ========================================
    // FUNCIONES AUXILIARES
    // ========================================

    function actualizarTotales() {
        var $table = $('#table-prenomina2');
        var rows = $table.bootstrapTable('getData');
        
        var totals = {
            a_cobrar: 0,
            seg_social: 0,
            ing_pers_3: 0,
            ing_pers_5: 0,
            salario_pagar: 0,
            salario_neto: 0
        };
        
        $.each(rows, function(idx, row) {
            totals.a_cobrar += Number(row.a_cobrar || 0);
            totals.seg_social += Number(row.seg_social || 0);
            totals.ing_pers_3 += Number(row.ing_pers_3 || 0);
            totals.ing_pers_5 += Number(row.ing_pers_5 || 0);
            totals.salario_pagar += Number(row.salario_pagar || 0);
            totals.salario_neto += Number(row.salario_neto || 0);
        });
        
        var total_descuentos = totals.seg_social + totals.ing_pers_3 + totals.ing_pers_5;
        
        $('#total-a-cobrar').text(window.currencyFormatter(totals.a_cobrar));
        $('#total-descuentos').text(window.currencyFormatter(total_descuentos));
        $('#total-neto').text(window.currencyFormatter(totals.salario_neto));
        $('#total-pagar').text(window.currencyFormatter(totals.salario_pagar));
    }

    // Initializar cuando la tabla carga
    $(document).on('post-body.bs.table', '#table-prenomina2', function() {
        console.log('Tabla cargada, actualizando totales');
        actualizarTotales();
    });

    // ========================================
    // INICIALIZACIÓN
    // ========================================
    
    console.log('Prenomina 2 inicializado');
    buildTabs();
});
