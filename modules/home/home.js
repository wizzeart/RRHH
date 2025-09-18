$(document).ready(function() {
    const Dashboard = {
        selectors: {
            total: '#total-trabajadores',
            activos: '#trabajadores-activos',
            edad: '#promedio-edad',
            cargos: '#total-cargos'
        },
        init: function() {
            this.loadData();
            setInterval(() => this.loadData(), 300000);
        },

        loadData: function() {
            $.ajax({
                url: 'api-app.php',
                type: 'GET',
                data: {
                    module: 'home',
                    method: 'getInitialData'
                },
                dataType: 'json',
                success: (response) => {
                    if (response.status === 1 && response.data) {
                        this.updateStats(response.data.quickStats);
                        this.renderWorkers(response.data.workers || []);
                        this.renderCargos(response.data.cargos || []);
                        this.renderDepartamentos(response.data.departamentos || []);
                        this.renderPases(response.data.pases || []);
                    } else {
                        console.error('Respuesta inválida del servidor', response);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading data:', error);
                    try {
                        console.error('XHR status:', status);
                        console.error('Response text:', xhr.responseText);
                    } catch (e) {
                        console.error('No additional XHR info available');
                    }
                }
            });
        },

        updateStats: function(stats) {
            if (!stats) return;

            $(this.selectors.total).text(stats.total || 0);
            $(this.selectors.activos).text(stats.activos || 0);
            $(this.selectors.edad).text(stats.promedioEdad || 0);
            $(this.selectors.cargos).text(stats.totalCargos || 0);
        },

        renderWorkers: function(workers) {
            const $tbody = $('#trabajadores-list tbody');
            if ($tbody.length === 0) return;
            $tbody.empty();

            if (!Array.isArray(workers) || workers.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center">No hay trabajadores</td></tr>');
                return;
            }

            workers.forEach(w => {
                const id = w.id || '';
                const nombre = (w.nombre || '').toString();
                const apellidos = (w.apellidos || '').toString();
                const cargo = (w.cargo || '').toString();

                const row = `<tr>
                    <td>${id}</td>
                    <td>${escapeHtml(nombre)}</td>
                    <td>${escapeHtml(apellidos)}</td>
                    <td>${escapeHtml(cargo)}</td>
                </tr>`;
                $tbody.append(row);
            });
        }

        ,renderCargos: function(cargos) {
            const $tbody = $('#cargos-list tbody');
            if ($tbody.length === 0) return;
            $tbody.empty();
            if (!Array.isArray(cargos) || cargos.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center">No hay cargos</td></tr>');
                return;
            }
            cargos.forEach(c => {
                const row = `<tr><td>${c.id||''}</td><td>${escapeHtml(c.departamento_id||'')}</td><td>${escapeHtml(c.nombre||'')}</td><td>${escapeHtml(c.salario||'')}</td></tr>`;
                $tbody.append(row);
            });
        }

        ,renderDepartamentos: function(departamentos) {
            const $tbody = $('#departamentos-list tbody');
            if ($tbody.length === 0) return;
            $tbody.empty();
            if (!Array.isArray(departamentos) || departamentos.length === 0) {
                $tbody.append('<tr><td colspan="2" class="text-center">No hay departamentos</td></tr>');
                return;
            }
            departamentos.forEach(d => {
                const row = `<tr><td>${d.id||''}</td><td>${escapeHtml(d.nombre||'')}</td></tr>`;
                $tbody.append(row);
            });
        }

        ,renderPases: function(pases) {
            const $tbody = $('#pases-list tbody');
            if ($tbody.length === 0) return;
            $tbody.empty();
            if (!Array.isArray(pases) || pases.length === 0) {
                $tbody.append('<tr><td colspan="6" class="text-center">No hay pases</td></tr>');
                return;
            }
            pases.forEach(p => {
                const row = `<tr><td>${p.id||''}</td><td>${p.trabajador_id||''}</td><td>${p.subcontrato_id||''}</td><td>${escapeHtml(p.areas_acceso||'')}</td><td>${escapeHtml(p.fecha_generacion||'')}</td><td>${escapeHtml(p.vigente||'')}</td></tr>`;
                $tbody.append(row);
            });
        }
    };

    Dashboard.init();

    // Simple HTML-escape utility to avoid injecting raw HTML
    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
