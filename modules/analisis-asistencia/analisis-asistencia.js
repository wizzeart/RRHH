/**
 * Estadisticas Asistencia (K-Means Robusto)
 * - Class-based Architecture
 * - Analyze Entry Patterns (Time vs Consistency)
 * - Normalized Clustering
 * - Advanced Visualization (Time Axis, Pie Chart)
 */

// --- Modal de vista previa de foto al pasar el mouse (análisis-asistencia) ---
(function () {
    var css = '\n'
        + '.ltr-photo-modal-overlay { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 2000; pointer-events: none; }\n'
        + '.ltr-photo-modal { pointer-events: auto; background: rgba(255,255,255,1); padding: 8px; border-radius: 6px; box-shadow: 0 8px 30px rgba(0,0,0,0.45); transition: opacity 220ms ease, transform 220ms ease; opacity: 0; transform: scale(0.96); }\n'
        + '.ltr-photo-modal.show { opacity: 1; transform: scale(1); }\n'
        + '.ltr-photo-modal img { display:block; max-width: 640px; max-height: 640px; width: auto; height: auto; border-radius:4px; }\n';

    var style = document.createElement('style');
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);

    var overlay = document.createElement('div');
    overlay.className = 'ltr-photo-modal-overlay';
    overlay.style.display = 'none';

    var modal = document.createElement('div');
    modal.className = 'ltr-photo-modal';
    var img = document.createElement('img');
    img.alt = 'Foto ampliada';
    modal.appendChild(img);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    var hideTimer = null;

    function showModal(src) {
        if (!src) return;
        img.src = src;
        overlay.style.display = 'flex';
        void modal.offsetWidth;
        modal.classList.add('show');
    }

    function hideModal() {
        modal.classList.remove('show');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function () { overlay.style.display = 'none'; img.src = ''; }, 240);
    }

    // Delegated listeners for photos in analysis tables
    document.addEventListener('mouseover', function (e) {
        var t = e.target;
        if (!t) return;
        if (t.tagName === 'IMG' && t.closest('#details-container')) {
            var src = t.getAttribute('src');
            if (src && src !== '/images/default-user.png') {
                clearTimeout(hideTimer);
                showModal(src);
            }
        }
    });

    document.addEventListener('mouseout', function (e) {
        var from = e.relatedTarget || e.toElement;
        var t = e.target;
        if (!t) return;
        if (t.tagName === 'IMG' && t.closest('#details-container')) {
            if (!from || !(from === modal || modal.contains(from))) {
                hideModal();
            }
        }
    });

    overlay.addEventListener('mouseleave', function () { hideModal(); });
    overlay.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
})();

class AttendanceAnalyzer {
    constructor() {
        // Configuration
        this.API_CONFIG = {
            // MATCHING LIST-ASISTENCIA: Plural module name
            module: 'asistencias',
            method: 'list-filter'
        };

        this.FLOT_DEPENDENCIES = [
            'plugins/flot-charts/jquery.flot.min.js',
            'plugins/flot-charts/jquery.flot.resize.min.js',
            'plugins/flot-charts/jquery.flot.selection.min.js',
            'plugins/flot-charts/jquery.flot.time.min.js',
            'plugins/flot-charts/jquery.flot.pie.min.js',
            'plugins/flot-charts/jquery.flot.symbol.min.js'
        ];

        this.CLUSTER_COLORS = [
            '#28a745', // Green (Madrugadores)
            '#007bff', // Blue (Cumplidores)
            '#dc3545', // Red (Tardes)
            '#3498db', '#9b59b6', '#e67e22', '#1abc9c', '#34495e'
        ];

        // State
        this.data = [];
        this.workers = [];
        this.clusters = [];
        this.centroids = [];
        this.k = 3;
        this.month = '';
        this.libsLoaded = false;

        // UI Refs - será inicializado en init()
        this.ui = null;

        this.init();
    }

    init() {
        console.log('AttendanceAnalyzer.init() called');
        
        // Inicializar referencias UI
        this.ui = {
            btnRun: $('#btn-ejecutar'),
            selectMonth: $('#select-mes'),
            selectK: $('#select-clusters'),
            status: $('#status-container'),
            results: $('#results-container'),
            scatter: $('#kmeans-scatter'),
            pie: $('#kmeans-pie'),
            summary: $('#clusters-summary'),
            details: $('#details-container')
        };
        
        console.log('UI elements:', this.ui);
        
        // Verificar que el botón existe
        if (this.ui.btnRun.length === 0) {
            console.error('Botón #btn-ejecutar no encontrado');
            return;
        }
        
        // Event Binding
        this.ui.btnRun.off('click').on('click', (e) => {
            console.log('Botón clickeado');
            e.preventDefault();
            this.run();
        });

        // Load Deps in background
        console.log('Iniciando carga de dependencias...');
        this.loadDependencies().then(() => {
            console.log('Statistics Module: Deps Loaded');
            this.libsLoaded = true;
            console.log('Dependencias cargadas y listas');
            // Auto-ejecutar análisis del mes actual
            console.log('Auto-ejecutando análisis...');
            this.run();
        }).catch(err => {
            console.error('Error cargando librerías:', err);
            this.showStatus('danger', 'Error cargando librerías gráficas. Recargue la página.');
        });
    }

    async run() {
        console.log('AttendanceAnalyzer.run() iniciado, libsLoaded:', this.libsLoaded);
        
        if (!this.ui) {
            console.error('UI no inicializado');
            return;
        }
        
        this.setBusy(true);
        this.clearResults();

        try {
            // Asegurar que las dependencias están cargadas
            if (!this.libsLoaded) {
                console.log('Esperando a que se carguen las dependencias...');
                this.showStatus('info', 'Cargando librerías gráficas...');
                await this.loadDependencies();
                this.libsLoaded = true;
                console.log('Dependencias cargadas');
            }

            this.month = this.ui.selectMonth.val();
            this.k = parseInt(this.ui.selectK.val());

            console.log(`Mes seleccionado: ${this.month}, K: ${this.k}`);
            
            this.showStatus('info', 'Obteniendo datos completos del mes...');

            // 1. Fetch
            const rawRows = await this.fetchData(this.month);
            console.log('Datos obtenidos:', rawRows ? rawRows.length : 0, 'registros');
            
            if (!rawRows || !rawRows.length) {
                throw new Error('No hay registros para el mes seleccionado.');
            }

            // 2. Process
            this.workers = this.processWorkers(rawRows);
            const validWorkers = this.workers.filter(w => w.valid);

            console.log(`Trabajadores válidos: ${validWorkers.length}`);

            if (validWorkers.length < this.k) {
                // Determine suffix for readability
                const suffix = validWorkers.length === 1 ? 'trabajador válido' : 'trabajadores válidos';
                throw new Error(`Insuficientes datos: Solo ${validWorkers.length} ${suffix} tienen registros suficientes para el análisis (min 3 días).`);
            }

            this.showStatus('info', `Analizando patrones de ${validWorkers.length} trabajadores...`);

            // 3. Cluster (Normalized)
            this.performClustering(validWorkers, this.k);

            // 4. Visualize
            this.renderVisuals();
            
            // Mostrar resultados
            if (this.ui.results.length > 0) {
                this.ui.results.show();
            }
            
            this.showStatus('success', 'Análisis estadístico completado exitosamente.');

        } catch (error) {
            console.error('Error en run():', error);
            this.showStatus('danger', error.message);
        } finally {
            this.setBusy(false);
        }
    }

    // --- Data Fetching ---

    fetchData(month) {
        return new Promise((resolve, reject) => {
            const [y, m] = month.split('-');
            const lastDay = new Date(y, m, 0).getDate();
            const fechaDesde = `${y}-${m}-01`;
            const fechaHasta = `${y}-${m}-${lastDay}`;

            // api-app.php handles the request
            // We use the same parameters as list-asistencia
            const params = {
                module: this.API_CONFIG.module,
                method: this.API_CONFIG.method,
                fecha_desde: fechaDesde,
                fecha_hasta: fechaHasta,
                limit: 10000
            };

            $.ajax({
                url: 'api-app.php',
                type: 'GET',
                data: params,
                dataType: 'text', // Read as text first to catch HTML errors
                success: (res) => {
                    try {
                        const json = JSON.parse(res);
                        let rows = [];

                        // Handle different response structures
                        if (Array.isArray(json)) {
                            rows = json;
                        } else if (json && json.rows) {
                            rows = json.rows;
                        } else if (json && json.data) {
                            rows = json.data; // Sometimes 'data' holds the array
                        }

                        // Check logic error from server
                        if (json.status === 0 && json.msg) {
                            throw new Error(json.msg);
                        }

                        resolve(rows || []);
                    } catch (e) {
                        // Check if HTML error
                        if (res && res.trim().startsWith('<')) {
                            console.warn("Raw Server Response:", res);
                            // extract visible text from HTML for better error msg
                            let tagless = res.replace(/<[^>]*>/g, ' ').substring(0, 150);
                            reject(new Error('Error del Servidor (HTML): ' + tagless));
                        } else {
                            reject(e);
                        }
                    }
                },
                error: (xhr, s, e) => {
                    console.error("AJAX Error:", xhr.responseText);
                    reject(new Error(`Error de Red: ${s} - ${e}`));
                }
            });
        });
    }

    // --- Processing ---

    processWorkers(rows) {
        const map = {};

        rows.forEach(r => {
            const id = r.trabajador_id || r.id;
            if (!id) return;

            let t = r.hora_entrada ? r.hora_entrada.trim() : '';
            if (!t || t === '00:00:00') return;

            const parts = t.split(':');
            if (parts.length < 2) return;

            const mins = parseInt(parts[0]) * 60 + parseInt(parts[1]);

            if (!map[id]) {
                map[id] = {
                    id: id,
                    name: (r.nombre || r.trabajador_nombre || 'Desconocido') + ' ' + (r.apellidos || r.trabajador_apellidos || ''),
                    role: r.cargo_nombre || r.cargo || 'Sin Cargo',
                    times: []
                };
            }
            map[id].times.push(mins);
        });

        const result = [];
        Object.values(map).forEach(w => {
            if (w.times.length < 3) {
                w.valid = false;
            } else {
                const n = w.times.length;

                // Calculate Median
                w.times.sort((a, b) => a - b);
                let median = 0;
                if (n % 2 === 0) {
                    median = (w.times[n / 2 - 1] + w.times[n / 2]) / 2;
                } else {
                    median = w.times[Math.floor(n / 2)];
                }

                // Variance (Standard Deviation)
                const realMean = w.times.reduce((a, b) => a + b, 0) / n;
                const variance = w.times.reduce((a, b) => a + Math.pow(b - realMean, 2), 0) / n;
                const stdDev = Math.sqrt(variance);

                w.stats = { mean: median, stdDev, n }; // 'mean' property now holds Median
                w.valid = true;
            }
            result.push(w);
        });

        return result;
    }

    // --- Clustering (Normalized K-Means) ---

    performClustering(workers, k) {
        // STRICT RULE BASED CLASSIFICATION (Requested by User)
        // Group 1: < 8:00 (480 mins)
        // Group 2: 8:00 - 9:00 (480 - 540 mins)
        // Group 3: > 9:00 (540 mins)

        const groups = [[], [], []];

        workers.forEach(w => {
            const t = w.stats.mean; // This is the Median (from processWorkers)
            if (t < 480) {
                groups[0].push(w);
            } else if (t <= 540) {
                groups[1].push(w);
            } else {
                groups[2].push(w);
            }
        });

        // Store Clusters
        this.clusters = groups;

        // Calculate Pseudo-Centroids for visualization/labels
        this.centroids = groups.map((grp, i) => {
            if (grp.length === 0) {
                // Fallbacks if empty
                return { mean: [470, 510, 560][i], stdDev: 0 };
            }
            // Average of the group for plotting the center? 
            // Or just use the group definition median?
            // Let's calc actual avg of the group for the centroid stats
            const sumM = grp.reduce((acc, w) => acc + w.stats.mean, 0);
            const sumD = grp.reduce((acc, w) => acc + w.stats.stdDev, 0);
            return {
                mean: sumM / grp.length,
                stdDev: sumD / grp.length
            };
        });
    }

    kMeansAlgorithm(points, k) {
        // Deterministic Initialization
        // We know we want 3 clusters: Early, OnTime, Late.
        // Approx times: 8:00 (480m), 8:40 (520m), 9:30 (570m)
        // Normalized (assuming approx range 5:00-11:00 => 300 to 660, range=360)
        // 0.5, 0.6, 0.75 on Y axis? No, points are [normTime, normDev]
        // Let's reuse real centroids logic but start with specific indices if possible 
        // OR better: Pick initial centroids based on sorting by time to guarantee "Early", "Mid", "Late" seeds.

        points.sort((a, b) => a[0] - b[0]); // Sort by Time dimension temporarily to pick seeds

        let centroids = [];
        if (k === 3) {
            // Pick 10%, 50%, 90% percentiles as seeds
            centroids.push([...points[Math.floor(points.length * 0.1)]]);
            centroids.push([...points[Math.floor(points.length * 0.5)]]);
            centroids.push([...points[Math.floor(points.length * 0.9)]]);
        } else {
            // Fallback for other K
            const step = Math.floor(points.length / k);
            for (let i = 0; i < k; i++) {
                centroids.push([...points[Math.min(points.length - 1, i * step + step / 2)]]);
            }
        }

        let clusters = [];
        let changed = true;
        let iter = 0;

        while (changed && iter++ < 100) {
            changed = false;
            clusters = Array(k).fill().map(() => []);

            // Assign
            points.forEach((p, pIdx) => {
                let minDist = Infinity, cIdx = 0;
                centroids.forEach((c, i) => {
                    const d = Math.sqrt(Math.pow(p[0] - c[0], 2) + Math.pow(p[1] - c[1], 2));
                    if (d < minDist) { minDist = d; cIdx = i; }
                });
                clusters[cIdx].push(pIdx);
            });

            // Update
            const newCentroids = clusters.map((indices, cIdx) => {
                if (!indices.length) return centroids[cIdx];
                const sum = [0, 0];
                indices.forEach(i => { sum[0] += points[i][0]; sum[1] += points[i][1]; });
                return [sum[0] / indices.length, sum[1] / indices.length];
            });

            // Check diff
            const diff = newCentroids.reduce((acc, nc, i) => {
                return acc + Math.sqrt(Math.pow(nc[0] - centroids[i][0], 2) + Math.pow(nc[1] - centroids[i][1], 2));
            }, 0);

            if (diff > 0.001) {
                centroids = newCentroids;
                changed = true;
            }
        }
        return { clusters, centroids };
    }

    // --- Visualization ---

    renderVisuals() {
        this.ui.results.show();
        this.filterIdx = -1; // Reset filter
        this.renderScatter();
        this.renderPie();
        this.renderSummary();
        this.renderTable();
    }

    renderScatter() {
        // Tooltip container
        const tip = $('#kmeans-scatter-tooltip');

        const series = this.clusters.map((group, i) => {
            const label = this.getClusterLabel(this.centroids[i], i);

            // Interaction: Filter view
            let color = this.CLUSTER_COLORS[i];
            let opacity = 0.6;

            if (this.filterIdx !== undefined && this.filterIdx !== -1 && this.filterIdx !== i) {
                color = "#dddddd";
                opacity = 0.2;
            }

            return {
                data: group.map(w => {
                    // X = Minutes (Raw), Y = StdDev
                    return [w.stats.mean, w.stats.stdDev, w.id];
                }),
                label: `Gr.${i + 1}: ${label}`,
                color: color,
                points: { show: true, radius: 4, fill: opacity }
            };
        });

        // No Date Base needed anymore - using raw minutes
        $.plot(this.ui.scatter, series, {
            grid: { hoverable: true, clickable: true, borderColor: '#eee', borderWidth: 1 },
            xaxis: {
                // Manual Time Formatting (robust against timezone)
                tickFormatter: (v) => this.formatTime(v),
                tickSize: 30, // Every 30 mins
                min: 330, // 5:30 AM
                max: 750  // 12:30 PM
            },
            yaxis: {
                tickFormatter: (v) => v.toFixed(0) + "m",
                min: 0,
                title: "Variabilidad"
            },
            legend: { position: 'ne', backgroundOpacity: 0.8 }
        });

        this.ui.scatter.off('plotclick').on('plotclick', (e, pos, item) => {
            if (item) {
                const wId = item.series.data[item.dataIndex][2];
                // Navigate to worker profile
                location.href = `index.php?module=ficha-trabajador&id=${wId}`;
            }
        });

        // Tooltip
        this.ui.scatter.off('plothover');
        this.ui.scatter.bind('plothover', (e, pos, item) => {
            if (item) {
                const wId = item.series.data[item.dataIndex][2];
                const grp = this.clusters[item.seriesIndex];
                const w = grp.find(x => x.id === wId);

                if (w) {
                    const timeStr = this.formatTime(w.stats.mean);
                    tip.html(`
                        <div style="padding:8px">
                            <b>${w.name}</b><br/>
                            <span class="text-muted">${w.role}</span><br/><hr style="margin:4px 0"/>
                            Mediana: <b>${timeStr}</b><br/>
                            Var: +/- <b>${w.stats.stdDev.toFixed(1)}m</b><br/>
                            <i class="fa fa-mouse-pointer" style="font-size:0.8em;color:#aaa"> Click para ver ficha</i>
                        </div>
                    `)
                        .css({ top: item.pageY - this.ui.scatter.offset().top + 10, left: item.pageX - this.ui.scatter.offset().left + 10 })
                        .show();
                }
            } else {
                tip.hide();
            }
        });
    }

    renderPie() {
        const data = this.clusters.map((group, i) => {
            return {
                label: `G${i + 1}`,
                data: group.length,
                color: this.CLUSTER_COLORS[i]
            };
        });

        $.plot(this.ui.pie, data, {
            series: {
                pie: {
                    show: true,
                    radius: 1,
                    label: {
                        show: true,
                        radius: 2 / 3,
                        formatter: (label, series) => `<div style="font-size:8pt;text-align:center;padding:2px;color:white;">${label}<br/>${Math.round(series.percent)}%</div>`,
                        background: { opacity: 0.5 }
                    }
                }
            },
            legend: { show: false }
        });
    }

    renderSummary() {
        this.ui.summary.empty();
        let html = '';

        this.centroids.forEach((c, i) => {
            const count = this.clusters[i].length;
            const label = this.getClusterLabel(c, i);
            const color = this.CLUSTER_COLORS[i];

            // Interactive Styles
            const isActive = (this.filterIdx === undefined || this.filterIdx === -1 || this.filterIdx === i);
            const opacity = isActive ? 1 : 0.5;
            const style = isActive && this.filterIdx === i
                ? `border: 3px solid #333; transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,0,0,0.2);`
                : `border-top:3px solid ${color};`;

            html += `
            <div class="col-md-${Math.floor(12 / this.k)}">
                <div class="panel" style="${style} cursor:pointer; opacity:${opacity}; transition:all 0.2s;" onclick="attendanceAnalyzer.toggleFilter(${i})">
                    <div class="panel-heading" style="background:${color};color:white">
                        <b>Grupo ${i + 1}</b> <span class="badge pull-right" style="background:rgba(0,0,0,0.2); font-size:1.1em">${count}</span>
                    </div>
                    <div class="panel-body text-center">
                        <h2 style="margin-top:10px; margin-bottom:10px;">
                            ${i === 0 ? '<i class="fa fa-star text-success"></i>' : ''}
                            ${i === 1 ? '<i class="fa fa-check text-info"></i>' : ''}
                            ${i === 2 ? '<i class="fa fa-exclamation-triangle text-danger"></i>' : ''}
                        </h2>
                        <h4 style="font-weight:bold; margin-top:0;">${label}</h4>
                        <small class="text-muted">Total: ${count}</small>
                    </div>
                </div>
            </div>`;
        });

        this.ui.summary.html(html);
    }

    toggleFilter(i) {
        if (this.filterIdx === i) {
            this.filterIdx = -1; // Reset
        } else {
            this.filterIdx = i; // Activate
        }
        this.renderScatter();
        this.renderSummary();
    }

    renderTable() {
        let tabs = '<ul class="nav nav-tabs">';
        let content = '<div class="tab-content">';

        this.clusters.forEach((group, i) => {
            const active = i === 0 ? 'active' : '';
            const color = this.CLUSTER_COLORS[i];
            tabs += `<li class="${active}"><a href="#tab-g${i}" data-toggle="tab" style="color:${color}"><i class="fa fa-circle"></i> Grupo ${i + 1}</a></li>`;

            // Sort by Mean Time (Calculated Mean) as requested
            const sorted = [...group].sort((a, b) => a.stats.mean - b.stats.mean);

            const rows = sorted.map(w => `
                <tr>
                    <td><img src="${w.foto || '/images/default-user.png'}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;" /></td>
                    <td><a href="index.php?module=ficha-trabajador&id=${w.id}" style="font-weight:bold;text-decoration:underline;">${w.name}</a></td>
                    <td>${w.role}</td>
                    <td><span class="label label-info">${this.formatTime(w.stats.mean)}</span></td>
                    <td>${w.stats.stdDev.toFixed(1)} min</td>
                    <td>${w.stats.n}</td>
                </tr>
             `).join('');

            content += `
             <div class="tab-pane fade ${active ? 'active in' : ''}" id="tab-g${i}">
                 <div class="table-responsive" style="margin-top:10px;background:#fff;padding:10px;">
                    <table class="table table-hover table-striped">
                        <thead><tr><th>Foto</th><th>Trabajador</th><th>Cargo</th><th>H. Entrada Prom.</th><th>Variabilidad</th><th>Registros</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                 </div>
             </div>`;
        });

        this.ui.details.html(`<div class="panel panel-default"><div class="panel-body">${tabs}</ul>${content}</div></div></div>`);
    }

    // --- Helpers ---

    getClusterLabel(c, index) {
        // Strict mapping by index (since we sorted by time in performClustering)
        if (typeof index !== 'undefined') {
            if (index === 0) return "Excelente";
            if (index === 1) return "Cumplidor";
            if (index === 2) return "No Cumplidor";
        }

        // Fallback logic if needed
        return "Indefinido";
    }

    formatTime(minutes) {
        let h = Math.floor(minutes / 60) % 24;
        let m = Math.floor(minutes % 60);
        if (isNaN(h)) h = 0;
        if (isNaN(m)) m = 0;
        return `${h < 10 ? '0' : ''}${h}:${m < 10 ? '0' : ''}${m}`;
    }

    setBusy(busy) {
        if (!this.ui || !this.ui.btnRun || this.ui.btnRun.length === 0) return;
        if (busy) this.ui.btnRun.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando');
        else this.ui.btnRun.prop('disabled', false).html('<i class="fa fa-play"></i> Analizar');
    }

    showStatus(type, msg) {
        console.log(`Status [${type}]: ${msg}`);
        if (!this.ui) {
            console.warn('UI no inicializado');
            return;
        }
        const alertHtml = `<div class="alert alert-${type}">${msg}</div>`;
        if (this.ui.status.length > 0) {
            this.ui.status.html(alertHtml);
        } else {
            console.warn('Status container not found');
        }
    }

    clearResults() {
        if (!this.ui) return;
        if (this.ui.results.length > 0) {
            this.ui.results.hide();
        }
        if (this.ui.status.length > 0) {
            this.ui.status.empty();
        }
    }

    loadDependencies() {
        // Sequential Loading helper
        const load = (src) => new Promise((resolve, reject) => {
            if ($(`script[src="${src}"]`).length) return resolve();
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });

        // Reduce promise chain
        return this.FLOT_DEPENDENCIES.reduce((p, src) => p.then(() => load(src)), Promise.resolve());
    }
}
