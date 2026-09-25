/**
 * Control Custodios Module
 * Interactive pie charts for registro_asistencia_horas visualization
 * Each worker gets a card with a doughnut chart + hourly timeline
 * Colors: light for daytime hours, dark for nighttime hours
 */

class ControlCustodios {
    constructor() {
        this.charts = {};
        this.data = [];
        this.filteredData = [];

        // Day/Night color palette mapped to 24 hours
        // Hours 6-17 (6AM-5PM) = warm/light colors (daytime)
        // Hours 18-5 (6PM-5AM) = cool/dark colors (nighttime)
        this.HOUR_COLORS = {
            0:  '#1a237e',  // 12 AM - deep navy
            1:  '#1a1a4e',  // 1 AM  - dark indigo
            2:  '#0d1b3e',  // 2 AM  - midnight blue
            3:  '#1b2838',  // 3 AM  - dark slate
            4:  '#263238',  // 4 AM  - dark blue-grey
            5:  '#37474f',  // 5 AM  - pre-dawn grey
            6:  '#ff9800',  // 6 AM  - sunrise orange
            7:  '#ffb74d',  // 7 AM  - light orange
            8:  '#ffd54f',  // 8 AM  - golden yellow
            9:  '#fff176',  // 9 AM  - soft yellow
            10: '#fff9c4',  // 10 AM - pale yellow
            11: '#ffe082',  // 11 AM - warm yellow
            12: '#ffca28',  // 12 PM - noon gold
            13: '#ffc107',  // 1 PM  - amber
            14: '#ffb300',  // 2 PM  - dark amber
            15: '#ff8f00',  // 3 PM  - tangerine
            16: '#ef6c00',  // 4 PM  - dark orange
            17: '#e65100',  // 5 PM  - sunset orange
            18: '#bf360c',  // 6 PM  - sunset red
            19: '#4e342e',  // 7 PM  - dusk brown
            20: '#3e2723',  // 8 PM  - dark brown
            21: '#2c2c54',  // 9 PM  - twilight purple
            22: '#1e1e3f',  // 10 PM - night purple
            23: '#0d0d2b'   // 11 PM - deep night
        };

        this.init();
    }

    init() {
        this.ui = {
            container: $('#custodios-charts-container'),
            fecha: $('#custodios-fecha'),
            buscar: $('#custodios-buscar'),
            btnFiltrar: $('#btn-filtrar-custodios'),
            tipoHorario: $('#custodios-tipo-horario'),
            total: $('#custodios-total')
        };

        // Bind events
        this.ui.btnFiltrar.off('click').on('click', () => this.loadData());
        this.ui.fecha.off('change').on('change', () => this.loadData());
        this.ui.buscar.off('input').on('input', () => this.applyClientFilters());
        this.ui.tipoHorario.off('change').on('change', () => this.loadData());

        // Auto-load when tab is shown
        $('a[href="#tab-custodios"]').on('shown.bs.tab', () => {
            setTimeout(() => this.loadData(), 200);
        });

        // Load immediately if tab is already active
        if ($('#tab-custodios').hasClass('active') || $('#tab-custodios').hasClass('in')) {
            this.loadData();
        }
    }

    loadData() {
        const fecha = this.ui.fecha.val();
        if (!fecha) return;

        const tipoHorario = this.ui.tipoHorario.val();

        this.ui.container.html(`
            <div class="col-md-12 text-center" style="padding: 60px 20px;">
                <i class="fa fa-spinner fa-spin fa-3x text-muted"></i>
                <p class="text-muted" style="margin-top: 15px;">Cargando registros de custodios...</p>
            </div>
        `);

        const params = {
            module: 'asistencias',
            method: 'list-horas-custodios',
            fecha: fecha
        };
        if (tipoHorario !== '') {
            params.tipo_horario = tipoHorario;
        }

        $.ajax({
            url: 'api-app.php',
            type: 'GET',
            data: params,
            dataType: 'json',
            success: (response) => {
                if (response && Array.isArray(response)) {
                    this.data = response;
                } else if (response && response.data) {
                    this.data = response.data;
                } else {
                    this.data = [];
                }
                this.applyClientFilters();
            },
            error: (xhr, status, error) => {
                console.error('Error cargando datos de custodios:', error);
                this.renderEmpty('Error al cargar los datos. Intente nuevamente.');
            }
        });
    }

    applyClientFilters() {
        const term = this.ui.buscar.val().trim().toLowerCase();

        if (term.length > 0) {
            this.filteredData = this.data.filter(worker => {
                const fullName = ((worker.nombre || '') + ' ' + (worker.apellidos || '')).toLowerCase();
                return fullName.includes(term);
            });
        } else {
            this.filteredData = [...this.data];
        }

        this.render(this.filteredData);
    }

    render(workers) {
        this.destroyCharts();
        this.ui.container.empty();

        if (!workers || workers.length === 0) {
            this.renderEmpty('No se encontraron custodios con registros para los filtros seleccionados.');
            this.ui.total.text('0');
            return;
        }

        this.ui.total.text(workers.length);

        workers.forEach((worker, idx) => {
            const card = this.createWorkerCard(worker, idx);
            this.ui.container.append(card);
        });

        // Render charts after DOM insertion
        setTimeout(() => {
            workers.forEach((worker, idx) => {
                this.renderPieChart(worker, idx);
            });
        }, 100);
    }

    createWorkerCard(worker, idx) {
        const horas = worker.horas || [];
        const nombre = ((worker.nombre || '') + ' ' + (worker.apellidos || '')).trim();
        const chartId = `custodio-chart-${idx}`;
        const wId = worker.trabajador_id;

        // Avatar
        let avatarHtml;
        if (worker.foto) {
            const fotoSrc = worker.foto.startsWith('data:') ? worker.foto : 'data:image/jpeg;base64,' + worker.foto;
            avatarHtml = `<img src="${fotoSrc}" class="custodio-avatar" alt="${nombre}">`;
        } else {
            avatarHtml = `<div class="custodio-avatar-placeholder"><i class="fa fa-user"></i></div>`;
        }

        // Horario badge
        let horarioBadge = '';
        const th = worker.tipo_horario;
        if (th == 0) {
            horarioBadge = '<span class="label label-success custodio-horario-badge"><i class="fa fa-star"></i> Especial</span>';
        } else if (th == 2) {
            horarioBadge = '<span class="label label-info custodio-horario-badge"><i class="fa fa-moon-o"></i> Nocturno</span>';
        } else {
            horarioBadge = '<span class="label label-warning custodio-horario-badge"><i class="fa fa-sun-o"></i> Diurno</span>';
        }

        // Hour list items
        let horasListHtml = '';
        horas.forEach((h, i) => {
            const hourIdx = this.getHourIndex(h.hora);
            const color = this.getColorForHour(hourIdx);
            const horaFormatted = this.formatHora(h.hora);
            const label = i === 0 ? 'Entrada' : (i === horas.length - 1 && horas.length > 1 ? 'Salida' : `Reg. ${i + 1}`);

            horasListHtml += `
                <div class="custodio-hora-item" style="border-left-color: ${color};">
                    <span class="hora-bullet" style="background: ${color};"></span>
                    <span class="hora-text">${horaFormatted}</span>
                    <span class="hora-label">${label}</span>
                </div>
            `;
        });

        if (horas.length === 0) {
            horasListHtml = `
                <div class="text-center text-muted" style="padding: 30px 10px;">
                    <i class="fa fa-clock-o fa-2x" style="margin-bottom: 6px;"></i>
                    <p style="margin: 0; font-size: 12px;">Sin registros</p>
                </div>
            `;
        }

        const nameLink = wId ? `<a href="index.php?module=ficha-trabajador&id=${wId}">${nombre}</a>` : nombre;

        return `
            <div class="col-md-6 col-lg-4">
                <div class="custodio-card">
                    <div class="custodio-card-header">
                        ${avatarHtml}
                        <div class="custodio-info">
                            <p class="custodio-name">${nameLink}</p>
                            <p class="custodio-registros-count">
                                <i class="fa fa-clock-o"></i> ${horas.length} registro${horas.length !== 1 ? 's' : ''} &nbsp; ${horarioBadge}
                            </p>
                        </div>
                    </div>
                    <div class="custodio-card-body">
                        <div class="custodio-chart-wrapper">
                            <canvas id="${chartId}" class="custodio-chart-canvas" width="160" height="160"></canvas>
                        </div>
                        <div class="custodio-horas-list">
                            ${horasListHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderPieChart(worker, idx) {
        const chartId = `custodio-chart-${idx}`;
        const canvas = document.getElementById(chartId);
        if (!canvas) return;

        const horas = worker.horas || [];
        if (horas.length === 0) {
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ccc';
            ctx.font = '13px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Sin datos', canvas.width / 2, canvas.height / 2);
            return;
        }

        const segments = this.buildChartSegments(horas);

        const chart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: segments.labels,
                datasets: [{
                    data: segments.data,
                    backgroundColor: segments.colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverBorderColor: '#333',
                    hoverBorderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '35%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(50,50,50,0.92)',
                        titleColor: '#fff',
                        bodyColor: '#ddd',
                        titleFont: { weight: 'bold', size: 13 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 6,
                        displayColors: true,
                        callbacks: {
                            title: (items) => items[0].label,
                            label: (context) => {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = ((context.parsed / total) * 100).toFixed(1);
                                const mins = context.parsed;
                                return ` ${mins} min (${pct}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 700,
                    easing: 'easeOutQuart'
                },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        this.highlightHoraItem(idx, elements[0].index);
                    }
                }
            }
        });

        this.charts[chartId] = chart;
    }

    buildChartSegments(horas) {
        const labels = [];
        const data = [];
        const colors = [];

        if (horas.length === 1) {
            const h = horas[0];
            const hourIdx = this.getHourIndex(h.hora);
            labels.push(this.formatHora(h.hora));
            data.push(60);
            colors.push(this.getColorForHour(hourIdx));
            return { labels, data, colors };
        }

        for (let i = 0; i < horas.length; i++) {
            const h = horas[i];
            const hourIdx = this.getHourIndex(h.hora);
            labels.push(this.formatHora(h.hora));
            colors.push(this.getColorForHour(hourIdx));

            if (i < horas.length - 1) {
                const currentMins = this.horaToMinutes(h.hora);
                const nextMins = this.horaToMinutes(horas[i + 1].hora);
                let gap = nextMins - currentMins;
                // Handle midnight crossing (e.g. 23:33 PM -> 02:34 AM)
                if (gap <= 0) gap += 1440; // add 24 hours in minutes
                data.push(gap);
            } else {
                data.push(30);
            }
        }

        return { labels, data, colors };
    }

    highlightHoraItem(workerIdx, horaIdx) {
        const card = $('.custodio-card').eq(workerIdx);
        const items = card.find('.custodio-hora-item');

        items.css('background', '');
        items.eq(horaIdx).css('background', 'rgba(51,122,183,0.12)');

        setTimeout(() => {
            items.eq(horaIdx).css('background', '');
        }, 2000);
    }

    renderEmpty(message) {
        this.ui.container.html(`
            <div class="col-md-12">
                <div class="custodios-empty">
                    <i class="fa fa-shield"></i>
                    <h4>${message}</h4>
                    <p class="text-muted" style="margin-top: 8px;">Seleccione una fecha diferente o ajuste los filtros.</p>
                </div>
            </div>
        `);
        this.ui.total.text('0');
    }

    destroyCharts() {
        Object.keys(this.charts).forEach(key => {
            if (this.charts[key]) {
                this.charts[key].destroy();
                delete this.charts[key];
            }
        });
    }

    // === Helpers ===

    getHourIndex(horaStr) {
        if (!horaStr) return 0;
        return parseInt(horaStr.split(':')[0]) || 0;
    }

    getColorForHour(hour) {
        return this.HOUR_COLORS[hour] || '#999';
    }

    horaToMinutes(horaStr) {
        if (!horaStr) return 0;
        const parts = horaStr.split(':');
        return (parseInt(parts[0]) || 0) * 60 + (parseInt(parts[1]) || 0);
    }

    formatHora(horaStr) {
        if (!horaStr) return '--:--';
        const parts = horaStr.split(':');
        const h = parseInt(parts[0]) || 0;
        const m = parseInt(parts[1]) || 0;
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h > 12 ? h - 12 : (h === 0 ? 12 : h);
        return `${h12}:${m < 10 ? '0' : ''}${m} ${ampm}`;
    }
}
