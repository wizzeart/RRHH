$(document).ready(function () {
  const DashboardStats = {
    selectors: {
      total: "#total-trabajadores",
      activos: "#trabajadores-activos",
      departamentos: "#departamentos",
      cargos: "#total-cargos",
      subcontratos: "#total-subcontratos",
      contratos: "#total-contratos",
      capacitaciones: "#total-capacitaciones",
      bolsas: "#total-bolsas",
      usuarios: "#total-usuarios",
      bajasTrabajadores: "#total-bajas-trabajadores",
    },
    init: function () {
      this.loadData();
      this.loadEstadisticasVacaciones();
      this.loadEstadisticasAsistencias();
      setInterval(() => this.loadData(), 300000);
      setInterval(() => this.loadEstadisticasVacaciones(), 300000);
      setInterval(() => this.loadEstadisticasAsistencias(), 300000);
    },

    loadData: function () {
      $.ajax({
        url: "api-app.php",
        type: "GET",
        data: {
          module: "home",
          method: "getInitialData",
        },
        dataType: "json",
        success: (response) => {
          if (response.status === 1 && response.data) {
            this.updateStats(response.data.quickStats);
            this.renderSexoStats((response.data.quickStats || {}).sexo || null);
            this.renderWorkers(response.data.workers || []);
            this.renderCargos(response.data.cargos || []);
            this.renderDepartamentos(response.data.departamentos || []);
            this.renderPases(response.data.pases || []);
          } else {
            console.error("Respuesta inválida del servidor", response);
          }
        },
        error: (xhr, status, error) => {
          console.error("Error loading data:", error);
          try {
            console.error("XHR status:", status);
            console.error("Response text:", xhr.responseText);
          } catch (e) {
            console.error("No additional XHR info available");
          }
        },
      });
    },

    updateStats: function (stats) {
      if (!stats) return;

      $(this.selectors.total).text(stats.total || 0);
      $(this.selectors.activos).text(stats.activos || 0);
      $(this.selectors.departamentos).text(stats.departamentos || 0);
      $(this.selectors.cargos).text(stats.totalCargos || 0);
      $(this.selectors.subcontratos).text(stats.totalSubcontratos || 0);
      $(this.selectors.contratos).text(stats.totalContratos || 0);
      $(this.selectors.capacitaciones).text(stats.totalCapacitaciones || 0);
      $(this.selectors.bolsas).text(stats.totalBolsas || 0);
      $(this.selectors.usuarios).text(stats.totalUsuarios || 0);
      $(this.selectors.bajasTrabajadores).text(
        stats.totalBajasTrabajadores || 0
      );
    },

    renderSexoStats: function (sexo) {
      var container = $("#sexo-stats-container");
      if (container.length === 0) return;

      if (!sexo) {
        container.html(
          '<div class="text-center" style="color:#999; padding:20px;">No hay datos</div>'
        );
        return;
      }

      var hombres = parseInt(sexo.hombres || 0, 10);
      var mujeres = parseInt(sexo.mujeres || 0, 10);
      var total = parseInt(sexo.total || 0, 10);
      var pctH =
        typeof sexo.porcentaje_hombres !== "undefined"
          ? parseFloat(sexo.porcentaje_hombres)
          : total > 0
            ? Math.round((hombres / total) * 10000) / 100
            : 0;
      var pctM =
        typeof sexo.porcentaje_mujeres !== "undefined"
          ? parseFloat(sexo.porcentaje_mujeres)
          : total > 0
            ? Math.round((mujeres / total) * 10000) / 100
            : 0;

      var colorH = "#4a90e2";
      var colorM = "#d9534f";

      var html = `
        <div style="
            background: #ffffff;
            border: 2px solid #d0d0d0;
            border-radius: 15px;
            padding: 30px;
            position: relative;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        ">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div style="
                    background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
                    border-left: 4px solid ${colorH};
                    border-radius: 8px;
                    padding: 15px;
                    text-align: center;
                    transition: all 0.3s ease;
                " onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                    <div style="font-size: 28px; font-weight: bold; color: ${colorH};">${hombres}</div>
                    <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">HOMBRES</div>
                    <div style="color: #888888; font-size: 12px; margin-top: 6px;">${pctH}%</div>
                </div>
                <div style="
                    background: linear-gradient(135deg, #ffffff 0%, #fef5f5 100%);
                    border-left: 4px solid ${colorM};
                    border-radius: 8px;
                    padding: 15px;
                    text-align: center;
                    transition: all 0.3s ease;
                " onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                    <div style="font-size: 28px; font-weight: bold; color: ${colorM};">${mujeres}</div>
                    <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">MUJERES</div>
                    <div style="color: #888888; font-size: 12px; margin-top: 6px;">${pctM}%</div>
                </div>
            </div>

            <div style="
                background: #f0f0f0;
                border-radius: 10px;
                height: 30px;
                overflow: hidden;
                margin-top: 10px;
                border: 1px solid #d0d0d0;
                position: relative;
            ">
                <div style="
                    background: #4a90e2;
                    height: 100%;
                    width: ${pctH}%;
                    transition: width 1s ease;
                    float: left;
                "></div>
                <div style="
                    background: #d9534f;
                    height: 100%;
                    width: ${pctM}%;
                    transition: width 1s ease;
                    float: left;
                "></div>
                <div style="
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #333;
                    font-weight: 600;
                    font-size: 13px;
                    letter-spacing: 1px;
                    text-shadow: 0 1px 0 rgba(255,255,255,0.6);
                "><strong>TOTAL: ${total}</strong></div>
            </div>
        </div>
      `;

      container.html(html);
    },

    renderWorkers: function (workers) {
      const $tbody = $("#trabajadores-list tbody");
      if ($tbody.length === 0) return;
      $tbody.empty();

      if (!Array.isArray(workers) || workers.length === 0) {
        $tbody.append(
          '<tr><td colspan="4" class="text-center">No hay trabajadores</td></tr>'
        );
        return;
      }

      const maxRows = 10;
      workers.slice(0, maxRows).forEach((w) => {
        const id = w.id || "";
        const nombre = (w.nombre || "").toString();
        const apellidos = (w.apellidos || "").toString();
        const cargo = (w.cargo || "").toString();

        const row = `<tr>
                    <td>${id}</td>
                    <td>${escapeHtml(nombre)}</td>
                    <td>${escapeHtml(apellidos)}</td>
                    <td>${escapeHtml(cargo)}</td>
                </tr>`;
        $tbody.append(row);
      });
    },

    renderCargos: function (cargos) {
      const $tbody = $("#cargos-list tbody");
      if ($tbody.length === 0) return;
      $tbody.empty();
      if (!Array.isArray(cargos) || cargos.length === 0) {
        $tbody.append(
          '<tr><td colspan="4" class="text-center">No hay cargos</td></tr>'
        );
        return;
      }
      cargos.forEach((c) => {
        const row = `<tr><td>${escapeHtml(
          c.nombre || ""
        )}</td><td class="text-center">${escapeHtml(
          (c.salario / 192).toFixed(2) || ""
        )}</td></tr>`;
        $tbody.append(row);
      });
    },

    renderDepartamentos: function (departamentos) {
      const $tbody = $("#departamentos-list tbody");
      if ($tbody.length === 0) return;
      $tbody.empty();
      if (!Array.isArray(departamentos) || departamentos.length === 0) {
        $tbody.append(
          '<tr><td colspan="2" class="text-center">No hay departamentos</td></tr>'
        );
        return;
      }
      departamentos.forEach((d) => {
        const row = `<tr><td>${escapeHtml(
          d.nombre || ""
        )}</td><td class="text-center">${d.cantidad || "0"}</td></tr>`;
        $tbody.append(row);
      });
    },

    renderPases: function (pases) {
      const $tbody = $("#pases-list tbody");
      if ($tbody.length === 0) return;
      $tbody.empty();
      if (!Array.isArray(pases) || pases.length === 0) {
        $tbody.append(
          '<tr><td colspan="6" class="text-center">No hay pases</td></tr>'
        );
        return;
      }
      pases.forEach((p) => {
        const row = `<tr><td>${p.id || ""}</td><td>${p.trabajador_id || ""
          }</td><td>${p.subcontrato_id || ""}</td><td>${escapeHtml(
            p.areas_acceso || ""
          )}</td><td>${escapeHtml(p.fecha_generacion || "")}</td><td>${escapeHtml(
            p.vigente || ""
          )}</td></tr>`;
        $tbody.append(row);
      });
    },

    loadEstadisticasVacaciones: function () {
      $.ajax({
        url: "api-app.php?module=submayor-vacaciones&method=estadisticas",
        type: "GET",
        dataType: "json",
        success: (response) => {
          if (response.status == 1) {
            // this.renderizarEstadisticasVacaciones(response);
          } else {
            console.error("Error cargando estadísticas:", response.msg);
          }
        },
        error: (xhr, status, error) => {
          console.error("Error AJAX estadísticas:", error);
        },
      });
    },

    renderizarEstadisticasVacaciones: function (data) {
      var container = $("#vacaciones-estadisticas-container");
      container.empty();

      var totales = data.totales;
      var topTrabajadores = data.top_trabajadores || [];

      // Crear panel futurista similar a asistencias
      var panelHtml = `
                <div style="
                    background: #ffffff;
                    border: 2px solid #d0d0d0;
                    border-radius: 15px;
                    padding: 30px;
                    position: relative;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                    width: 100%;
                ">
                    <!-- Cards de estadísticas principales -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <!-- Total Trabajadores -->
                        <div style="
                            background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
                            border-left: 4px solid #4a90e2;
                            border-radius: 8px;
                            padding: 15px;
                            text-align: center;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                            <div style="font-size: 28px; font-weight: bold; color: #4a90e2;">
                                ${totales.total_trabajadores}
                            </div>
                            <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">TOTAL EMPLEADOS</div>
                        </div>

                        <!-- Con Vacaciones Registradas -->
                        <div style="
                            background: linear-gradient(135deg, #ffffff 0%, #f0f8f0 100%);
                            border-left: 4px solid #5cb85c;
                            border-radius: 8px;
                            padding: 15px;
                            text-align: center;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                            <div style="font-size: 28px; font-weight: bold; color: #5cb85c;">
                                ${totales.con_vacaciones_registradas}
                            </div>
                            <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">CON VACACIONES</div>
                        </div>

                        <!-- Promedio Vacaciones -->
                        <div style="
                            background: linear-gradient(135deg, #ffffff 0%, #fef8f0 100%);
                            border-left: 4px solid #f0ad4e;
                            border-radius: 8px;
                            padding: 15px;
                            text-align: center;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                            <div style="font-size: 28px; font-weight: bold; color: #f0ad4e;">
                                ${totales.promedio_vac_disponibles}
                            </div>
                            <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">PROMEDIO DÍAS</div>
                        </div>
                    </div>

                    <!-- Top 5 Trabajadores con más vacaciones -->
                    ${topTrabajadores.length > 0
          ? `
                        <div style="
                            background: #f8f8f8;
                            border: 2px solid #e0e0e0;
                            border-radius: 12px;
                            padding: 20px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                        ">
                            <h4 style="
                                color: #333333;
                                margin-top: 0;
                                letter-spacing: 1px;
                                display: flex;
                                align-items: center;
                                gap: 10px;
                                font-weight: 600;
                            ">
                                <i class="fa fa-trophy" style="font-size: 16px; color: #f0ad4e;"></i>
                                TOP 5 CON MÁS VACACIONES ACUMULADAS
                            </h4>
                            <div style="
                                display: grid;
                                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                                gap: 12px;
                            ">
                                ${topTrabajadores
            .map(
              (trab, idx) => `
                                    <div style="
                                        background: #ffffff;
                                        border: 2px solid #d0d0d0;
                                        border-radius: 8px;
                                        padding: 12px;
                                        animation: slideIn ${0.3 + idx * 0.1
                }s ease forwards;
                                        opacity: 0;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                    " onmouseover="this.style.boxShadow='0 3px 10px rgba(0,0,0,0.1)'; this.style.background='#f5f5f5';" onmouseout="this.style.boxShadow='none'; this.style.background='#ffffff';">
                                        <div style="font-size: 11px; color: #333333; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            ${escapeHtml(trab.nombre_completo)}
                                        </div>
                                        <div style="font-size: 9px; color: #888888; margin: 4px 0;">
                                            ${trab.cargo_nombre || "S/C"}
                                        </div>
                                        <div style="
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: center;
                                            margin-top: 8px;
                                            padding-top: 8px;
                                            border-top: 1px solid #e0e0e0;
                                        ">
                                            <span style="font-size: 10px; color: #888888;">Vacaciones</span>
                                            <span style="font-size: 12px; color: #4a90e2; font-weight: bold;">
                                                ${trab.vacaciones_acc} días
                                            </span>
                                        </div>
                                    </div>
                                `
            )
            .join("")}
                            </div>
                        </div>
                    `
          : ""
        }
                </div>
            `;

      container.html(panelHtml);
    },

    loadEstadisticasAsistencias: function () {
      $.ajax({
        url: "api-app.php?module=asistencias&method=estadisticas",
        type: "GET",
        dataType: "json",
        success: (response) => {
          if (response.status == 1) {
            this.renderizarMatriuskaAsistencias(response);
          } else {
            console.error(
              "Error cargando estadísticas de asistencias:",
              response.msg
            );
          }
        },
        error: (xhr, status, error) => {
          console.error("Error AJAX asistencias:", error);
        },
      });
    },

    renderizarMatriuskaAsistencias: function (data) {
      var container = $("#asistencias-matriuska-container");
      container.empty();

      var totales = data.totales;
      var departamentos = data.departamentos || [];
      var ubicaciones = data.ubicaciones || [];
      var puntualidad = data.puntualidad || [];
      var porcentaje = data.porcentaje_asistencia || 0;

      // Capa 1: Núcleo Central - Estadísticas Principales
      var nucleo = crearNucleoMatriuska(totales, porcentaje);
      container.append(nucleo);

      // Capa 2: Departamentos
      if (departamentos.length > 0) {
        var capaDept = crearCapaDepartamentos(departamentos);
        container.append(capaDept);
      }

      // Capa 2.5: Ubicaciones
      if (ubicaciones.length > 0) {
        var capaUbicaciones = crearCapaUbicaciones(ubicaciones);
        container.append(capaUbicaciones);
      }

      // Capa 3: Puntualidad
      if (puntualidad.length > 0) {
        var capaPuntualidad = crearCapaPuntualidad(puntualidad);
        container.append(capaPuntualidad);
      }
    },
  };

  DashboardStats.init();

  // Funciones helper para crear elementos visuales
  function escapeHtml(str) {
    return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // ============ FUNCIONES MATRIUSKA FUTURISTA ============

  function crearNucleoMatriuska(totales, porcentaje) {
    var presentes = totales.presentes || 0;
    var ausentes = totales.ausentes || 0;
    var vacaciones = totales.vacaciones || 0;
    var especiales = totales.especiales || 0;
    var total = totales.total_trabajadores || 0;

    // Colores gris empresarial
    var colorPresentes = "#4a90e2";
    var colorAusentes = "#d9534f";
    var colorVacaciones = "#f0ad4e";
    var colorEspeciales = "#9b59b6";
    var colorTotal = "#888888";

    // Determinar color de la barra de porcentaje
    var colorBarra = "#5cb85c";
    if (porcentaje < 70) colorBarra = "#d9534f";
    else if (porcentaje < 85) colorBarra = "#f0ad4e";

    return `
            <div class="matriuska-nucleo" style="
                animation: pulseGlow 3s ease-in-out infinite;
                margin-bottom: 30px;
            ">
                <div style="
                    background: #ffffff;
                    border: 2px solid #d0d0d0;
                    border-radius: 15px;
                    padding: 30px;
                    position: relative;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                ">
                    <style>
                        @keyframes pulseGlow {
                            0%, 100% { box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
                            50% { box-shadow: 0 5px 15px rgba(0,0,0,0.15); }
                        }
                        @keyframes floatNumber {
                            0%, 100% { transform: translateY(0px); }
                            50% { transform: translateY(-5px); }
                        }
                        @keyframes slideIn {
                            from { opacity: 0; transform: translateX(-20px); }
                            to { opacity: 1; transform: translateX(0); }
                        }
                    </style>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <!-- Presentes -->
                        <div style="
                            background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
                            border-left: 4px solid ${colorPresentes};
                            border-radius: 8px;
                            padding: 15px;
                            text-align: center;
                            animation: slideIn 0.6s ease forwards;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        " onclick="document.getElementById('modal-presentes').style.display='flex'; cargarFotosPresentes(${presentes});" onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                            <div style="font-size: 28px; font-weight: bold; color: ${colorPresentes}; animation: floatNumber 2s ease-in-out infinite;">
                                ${presentes}
                            </div>
                            <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">PRESENTES</div>
                        </div>

                        <!-- Ausentes -->
                        <div style="
                            background: linear-gradient(135deg, #ffffff 0%, #fef5f5 100%);
                            border-left: 4px solid ${colorAusentes};
                            border-radius: 8px;
                            padding: 15px;
                            text-align: center;
                            animation: slideIn 0.8s ease forwards;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        " onclick="document.getElementById('modal-ausentes').style.display='flex'; cargarFotosAusentes(${ausentes});" onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                            <div style="font-size: 28px; font-weight: bold; color: ${colorAusentes}; animation: floatNumber 2s ease-in-out infinite 0.3s;">
                                ${ausentes}
                            </div>
                            <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">AUSENTES</div>
                        </div>

                        <!-- Vacaciones -->
                        <div style="
                            background: linear-gradient(135deg, #ffffff 0%, #f0f8f0 100%);
                            border-left: 4px solid ${colorVacaciones};
                            border-radius: 8px;
                            padding: 15px;
                            text-align: center;
                            animation: slideIn 1s ease forwards;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        " onclick="document.getElementById('modal-vacaciones').style.display='flex'; cargarFotosVacaciones(${vacaciones});" onmouseover="this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                            <div style="font-size: 28px; font-weight: bold; color: ${colorVacaciones}; animation: floatNumber 2s ease-in-out infinite 0.9s;">
                                ${vacaciones}
                            </div>
                            <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">VACACIONES</div>
                        </div>

                        <!-- Especiales -->
                        <div style="
                            background: linear-gradient(135deg, #ffffff 0%, #f8f0ff 100%);
                            border-left: 4px solid ${colorEspeciales};
                            border-radius: 8px;
                            padding: 15px;
                            text-align: center;
                            animation: slideIn 1.2s ease forwards;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        " onclick="document.getElementById('modal-especiales').style.display='flex'; cargarFotosEspeciales(${especiales});" onmouseover="this.style.boxShadow='0 5px 15px rgba(155,89,182,0.1)'; this.style.transform='scale(1.05)';" onmouseout="this.style.boxShadow='none'; this.style.transform='scale(1)';">
                            <div style="font-size: 28px; font-weight: bold; color: ${colorEspeciales}; animation: floatNumber 2s ease-in-out infinite 1.2s;">
                                ${especiales}
                            </div>
                            <div style="color: #666666; font-size: 11px; margin-top: 5px; letter-spacing: 1px; font-weight: 600;">ESPECIALES</div>
                        </div>
                    </div>

                    <!-- Barra de Porcentaje -->
                    <div style="
                        background: #f0f0f0;
                        border-radius: 10px;
                        height: 30px;
                        overflow: hidden;
                        margin-top: 20px;
                        border: 1px solid #d0d0d0;
                    ">
                        <div style="
                            background: ${colorBarra};
                            height: 100%;
                            width: ${porcentaje}%;
                            display: flex;
                            align-items: center;
                            justify-content: flex-end;
                            padding-right: 10px;
                            transition: width 1s ease;
                        ">
                            <span style="
                                color: white;
                                font-weight: bold;
                                text-shadow: 0 0 5px rgba(0,0,0,0.2);
                                font-size: 14px;
                            ">${porcentaje}%</span>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 10px; color: #888888; font-size: 12px; letter-spacing: 1px;">
                        TASA DE ASISTENCIA
                    </div>
                </div>
            </div>
        `;
  }

  function crearCapaDepartamentos(departamentos) {
    var html = `
            <div class="matriuska-capa-2" style="
                background: #ffffff;
                border: 2px solid #e0e0e0;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            ">
                <h4 style="
                    color: #333333;
                    margin-top: 0;
                    letter-spacing: 1px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-weight: 600;
                ">
                    <i class="fa fa-building" style="font-size: 16px; color: #666666;"></i>
                    ESTRUCTURA DEPARTAMENTAL
                </h4>
                <div style="
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                    gap: 12px;
                ">
        `;

    departamentos.forEach((dept, index) => {
      var porcentajeDept =
        dept.total > 0 ? Math.round((dept.presentes / dept.total) * 100) : 0;
      var colorBorder =
        porcentajeDept >= 85
          ? "#5cb85c"
          : porcentajeDept >= 70
            ? "#f0ad4e"
            : "#d9534f";

      html += `
                <div style="
                    background: #f8f8f8;
                    border: 2px solid ${colorBorder};
                    border-radius: 8px;
                    padding: 12px;
                    animation: slideIn ${0.3 + index * 0.1}s ease forwards;
                    opacity: 0;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                " onmouseover="this.style.boxShadow='0 3px 10px rgba(0,0,0,0.1)'; this.style.background='#ffffff';" onmouseout="this.style.boxShadow='none'; this.style.background='#f8f8f8';">
                    <div style="font-size: 12px; color: #333333; font-weight: 600; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        ${dept.departamento || "Sin Depto"}
                    </div>
                    <div style="font-size: 11px; color: #666666; margin-bottom: 8px;">
                        ${dept.presentes}/${dept.total}
                    </div>
                    <div style="
                        background: #e0e0e0;
                        height: 3px;
                        border-radius: 2px;
                        width: 100%;
                        overflow: hidden;
                    ">
                        <div style="
                            background: ${colorBorder};
                            height: 100%;
                            width: ${porcentajeDept}%;
                            transition: width 0.5s ease;
                        "></div>
                    </div>
                </div>
            `;
    });

    html += `
                </div>
            </div>
        `;

    return html;
  }

  function crearCapaUbicaciones(ubicaciones) {
    var html = `
            <div class="matriuska-capa-ubicaciones" style="
                background: #ffffff;
                border: 2px solid #e0e0e0;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            ">
                <h4 style="
                    color: #333333;
                    margin-top: 0;
                    letter-spacing: 1px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-weight: 600;
                ">
                    <i class="fa fa-map-marker" style="font-size: 16px; color: #d9534f;"></i>
                    ESTRUCTURA UBICACIONAL
                </h4>
                <div style="
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                    gap: 12px;
                ">
        `;

    ubicaciones.forEach((ubicacion, index) => {
      var porcentajeUbicacion =
        ubicacion.total > 0
          ? Math.round((ubicacion.presentes / ubicacion.total) * 100)
          : 0;
      var colorBorder =
        porcentajeUbicacion >= 85
          ? "#5cb85c"
          : porcentajeUbicacion >= 70
            ? "#f0ad4e"
            : "#d9534f";

      html += `
                <div style="
                    background: #f8f8f8;
                    border: 2px solid ${colorBorder};
                    border-radius: 8px;
                    padding: 12px;
                    animation: slideIn ${0.3 + index * 0.1}s ease forwards;
                    opacity: 0;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                " onmouseover="this.style.boxShadow='0 3px 10px rgba(0,0,0,0.1)'; this.style.background='#ffffff';" onmouseout="this.style.boxShadow='none'; this.style.background='#f8f8f8';">
                    <div style="font-size: 12px; color: #333333; font-weight: 600; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        📍 ${ubicacion.ubicacion || "Sin Ubicación"}
                    </div>
                    <div style="font-size: 11px; color: #666666; margin-bottom: 8px;">
                        ${ubicacion.presentes}/${ubicacion.total}
                    </div>
                    <div style="
                        background: #e0e0e0;
                        height: 3px;
                        border-radius: 2px;
                        width: 100%;
                        overflow: hidden;
                    ">
                        <div style="
                            background: ${colorBorder};
                            height: 100%;
                            width: ${porcentajeUbicacion}%;
                            transition: width 0.5s ease;
                        "></div>
                    </div>
                </div>
            `;
    });

    html += `
                </div>
            </div>
        `;

    return html;
  }

  function crearCapaPuntualidad(puntualidad) {
    var html = `
            <div class="matriuska-capa-3" style="
                background: #ffffff;
                border: 2px solid #e0e0e0;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            ">
                <h4 style="
                    color: #333333;
                    margin-top: 0;
                    letter-spacing: 1px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-weight: 600;
                ">
                    <i class="fa fa-clock-o" style="font-size: 16px; color: #666666;"></i>
                    TOP 5 MÁS PUNTUALES DEL DÍA
                </h4>
                <div style="
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                    gap: 12px;
                ">
        `;

    puntualidad.forEach((trab, index) => {
      var horaEntrada = trab.hora_entrada || "--:--";
      html += `
                <div style="
                    background: #f8f8f8;
                    border: 2px solid #d0d0d0;
                    border-radius: 8px;
                    padding: 10px;
                    animation: slideIn ${0.4 + index * 0.15}s ease forwards;
                    opacity: 0;
                    cursor: pointer;
                    transition: all 0.3s ease;
                " onmouseover="this.style.boxShadow='0 3px 10px rgba(0,0,0,0.1)'; this.style.background='#ffffff';" onmouseout="this.style.boxShadow='none'; this.style.background='#f8f8f8';"
                onclick="window.location.href='index.php?module=ficha-trabajador&id=' + ${trab.id}">
                    <div style="font-size: 11px; color: #333333; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer;">
                        <i class="fa fa-hand-o-up" style="margin-right:3px;"></i>${escapeHtml(trab.nombre_completo)}
                    </div>
                    <div style="font-size: 9px; color: #888888; margin: 4px 0;">
                        ${trab.departamento || "S/D"}
                    </div>
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-top: 8px;
                        padding-top: 8px;
                        border-top: 1px solid #e0e0e0;
                    ">
                        <span style="font-size: 10px; color: #888888;">Entrada</span>
                        <span style="font-size: 12px; color: #333333; font-weight: bold; font-family: monospace;">
                            ${horaEntrada}
                        </span>
                    </div>
                </div>
            `;
    });

    html += `
                </div>
            </div>
        `;

    return html;
  }

  // Las funciones cargarFotosPresentes y cargarFotosAusentes se movieron al final del archivo
  // como funciones globales (FUERA del document.ready) para ser accesibles desde HTML
  // También mostrarPreviewPresente, toggleZoomPresente, mostrarPreviewAusente, toggleZoomAusente

  // ==================== MANEJADORES DE EVENTOS DEL MODAL ====================

  // ============ CALENDARIO FUTURISTA DE VACACIONES Y CUMPLEAÑOS ============

  // ============ CALENDARIO PROFESIONAL - SIMPLE Y ROBUSTO ============

  window.Dashboard = window.Dashboard || {};

  Dashboard.calendarState = {
    mesActual: new Date().getMonth(),
    annoActual: new Date().getFullYear(),
    eventos: {},
    eventosPersonalizados: {},
    cargando: false,
    intentos: 0,
  };

  // ==================== FUNCIONES DE EVENTOS PERSONALIZADOS ====================

  Dashboard.cargarEventosPersonalizados = function () {
    var state = Dashboard.calendarState;
    var mes = state.mesActual;
    var anno = state.annoActual;

    // Calcular fecha inicio y fin del mes
    var fechaInicio = anno + "-" + String(mes + 1).padStart(2, "0") + "-01";
    var fechaFin = new Date(anno, mes + 1, 0);
    var fechaFinStr =
      anno +
      "-" +
      String(mes + 1).padStart(2, "0") +
      "-" +
      String(fechaFin.getDate()).padStart(2, "0");

    $.ajax({
      url: "api-app.php?module=home&method=obtener_eventos_personalizados",
      type: "GET",
      data: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFinStr,
      },
      dataType: "text",
      timeout: 10000,
      cache: false,
      xhrFields: { withCredentials: true },
      success: function (responseText) {
        try {
          // Intentar parsear como JSON
          var response = JSON.parse(responseText);

          if (response && response.status === 1 && response.eventos) {
            state.eventosPersonalizados = {};

            // Agrupar eventos por fecha
            $.each(response.eventos, function (idx, evento) {
              if (!state.eventosPersonalizados[evento.fecha]) {
                state.eventosPersonalizados[evento.fecha] = [];
              }
              state.eventosPersonalizados[evento.fecha].push(evento);
            });

            console.log(
              "[EVENTOS] Eventos personalizados cargados:",
              state.eventosPersonalizados
            );
            Dashboard.renderizarCalendario();
          } else {
            console.log("[EVENTOS] Sin eventos personalizados para este mes");
            state.eventosPersonalizados = {};
            Dashboard.renderizarCalendario();
          }
        } catch (e) {
          console.warn("[EVENTOS] Error parseando respuesta:", e.message);
          state.eventosPersonalizados = {};
          Dashboard.renderizarCalendario();
        }
      },
      error: function (xhr, status, error) {
        console.warn(
          "[EVENTOS] No se pudieron cargar eventos personalizados:",
          error
        );
        state.eventosPersonalizados = {};
        Dashboard.renderizarCalendario();
      },
    });
  };

  Dashboard.guardarEvento = function () {
    var eventoId = $("#eventoId").val();
    var fecha = $("#eventoFecha").val();
    var nombre = $("#eventoNombre").val();
    var descripcion = $("#eventoDescripcion").val();
    var isIncidencia = $("#eventoIsIncidencia").val() === "1";
    var color = isIncidencia ? "#000000" : $("#eventoColor").val();

    // Validación básica
    if (!fecha || !nombre) {
      alert("Fecha y nombre son requeridos");
      return;
    }

    var trabajadorId = null;
    if (isIncidencia) {
      // Si es edición de incidencia existente, no necesitamos validar trabajador
      // porque ya está asignado. Solo validar si es un nuevo evento.
      if (!eventoId) {
        // Nuevo evento - validar selección de trabajador
        trabajadorId = $("#eventoTrabajador").val();
        if (!trabajadorId) {
          alert("Debe seleccionar un trabajador para la incidencia");
          return;
        }
      } else {
        // Edición de incidencia existente - obtener trabajador_id del campo oculto
        trabajadorId = $("#eventoTrabajadorId").val();
      }
    }

    var data = {
      fecha: fecha,
      nombre: nombre,
      descripcion: descripcion,
      color: color,
      es_incidencia: isIncidencia ? 1 : 0
    };

    // Si es incidencia, agregar trabajador_id
    if (isIncidencia && trabajadorId) {
      data.trabajador_id = trabajadorId;
    }

    var url = "api-app.php?module=home&method=";
    var method = eventoId
      ? "actualizar_evento_calendario"
      : "agregar_evento_calendario";

    if (eventoId) {
      data.id = eventoId;
    }

    $.ajax({
      url: url + method,
      type: "POST",
      data: data,
      dataType: "json",
      success: function (response) {
        if (response && response.status === 1) {
          alert(response.msg);
          $("#modalEventoPersonalizado").modal("hide");
          Dashboard.limpiarFormularioEvento();
          Dashboard.cargarEventosPersonalizados();
        } else {
          alert("Error: " + (response.msg || "Error al guardar evento"));
        }
      },
      error: function (xhr, status, error) {
        alert("Error al guardar evento: " + error);
      },
    });
  };

  Dashboard.eliminarEvento = function () {
    var eventoId = $("#eventoId").val();

    if (!eventoId) {
      alert("No hay evento para eliminar");
      return;
    }

    if (!confirm("¿Está seguro de que desea eliminar este evento?")) {
      return;
    }

    $.ajax({
      url: "api-app.php?module=home&method=eliminar_evento_calendario",
      type: "POST",
      data: { id: eventoId },
      dataType: "json",
      success: function (response) {
        if (response && response.status === 1) {
          alert(response.msg);
          $("#modalEventoPersonalizado").modal("hide");
          Dashboard.limpiarFormularioEvento();
          Dashboard.cargarEventosPersonalizados();
          Dashboard.renderizarCalendario();
        } else {
          alert("Error: " + (response.msg || "Error al eliminar evento"));
        }
      },
      error: function (xhr, status, error) {
        alert("Error al eliminar evento: " + error);
      },
    });
  };

  Dashboard.abrirModalEvento = function (fecha, evento) {
    // Limpiar formulario
    Dashboard.limpiarFormularioEvento();

    // Si es edición
    if (evento) {
      $("#eventoId").val(evento.id);
      $("#eventoFecha").val(evento.fecha);
      $("#eventoNombre").val(evento.nombre);
      $("#eventoDescripcion").val(evento.descripcion);
      $("#eventoColor").val(evento.color);
      
      // Detectar si es incidencia usando la columna es_incidencia
      var esIncidencia = evento.es_incidencia === 1 || evento.es_incidencia === "1";
      
      if (esIncidencia) {
        // Modo edición de Incidencia
        $("#eventoIsIncidencia").val("1");
        $("#toggleIncidencia").prop("checked", true);
        $("#toggleIncidencia").closest(".form-group").hide(); // Ocultar toggle al editar
        $("#groupColor").hide(); // Ocultar color al editar incidencia
        
        // Guardar trabajador_id en campo oculto para usarlo al guardar
        $("#eventoTrabajadorId").val(evento.trabajador_id);
        
        // Mostrar nombre del trabajador como link en lugar del select
        if (evento.trabajador_id) {
          $("#groupTrabajador").html(`
            <div class="form-group">
              <label style="font-weight: 600;">Trabajador</label>
              <p style="margin-top: 5px; padding: 8px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px;">
                <a href="index.php?module=ficha-trabajador&id=${evento.trabajador_id}" style="color: #10442a; text-decoration: none; font-weight: 500;">
                  ${evento.trabajador_nombre || "Trabajador"} (ID: ${evento.trabajador_id})
                </a>
              </p>
            </div>
          `);
          $("#groupTrabajador").show();
        } else {
          $("#groupTrabajador").hide();
        }
        
        $("#groupNombre").show();
        $("label[for='eventoNombre']").text("Nombre de Incidencia");
        $("label[for='eventoFecha']").text("Fecha de Incidencia *");
        $("label[for='eventoDescripcion']").text("Descripción de Incidencia");
        
        // Cambiar header a negro
        $("#modalEventoHeader").css("background", "linear-gradient(135deg, #333333 0%, #1a1a1a 100%)");
        
        $("#modalEventoLabel").html(
          '<i class="fa fa-edit" style="margin-right: 10px;"></i>Editar Incidencia'
        );
        // Cambiar botón a Guardar Incidencia con color negro
        $("#btnGuardarEvento")
          .css("background", "linear-gradient(135deg, #333333 0%, #1a1a1a 100%)")
          .html('<i class="fa fa-exclamation-circle" style="margin-right: 5px;"></i>Guardar Incidencia');
      } else {
        // Modo edición de Evento Normal
        $("#toggleIncidencia").closest(".form-group").hide(); // Ocultar toggle al editar evento normal
        $("#groupTrabajador").hide();
        $("#modalEventoLabel").html(
          '<i class="fa fa-edit" style="margin-right: 10px;"></i>Editar Evento'
        );
      }
      $("#btnEliminarEvento").show();
    } else {
      // Nuevo evento
      $("#eventoFecha").val(fecha);
      $("#toggleIncidencia").closest(".form-group").show(); // Mostrar toggle para nuevo
      $("#modalEventoLabel").html(
        '<i class="fa fa-star" style="margin-right: 10px;"></i>Nuevo Evento Personalizado'
      );
      $("#btnEliminarEvento").hide();
      
      // Cargar trabajadores al crear nuevo evento
      Dashboard.cargarTrabajadoresSelect();
    }

    // Solo cargar trabajadores si es un nuevo evento (modo creación)
    // Para edición, ya estamos mostrando el trabajador como link o no lo mostramos

    $("#modalEventoPersonalizado").modal("show");
  };

  Dashboard.cargarTrabajadoresSelect = function () {
    $.ajax({
      url: "api-app.php?module=trabajadores&method=list&limit=500&order=ASC&sort=t.nombre",
      type: "GET",
      dataType: "json",
      success: function (response) {
        var select = $("#eventoTrabajador");
        select.html('<option value="">-- Seleccionar trabajador --</option>');
        
        // La respuesta viene en response.rows, no en response.data
        if (response && response.rows && Array.isArray(response.rows)) {
          $.each(response.rows, function (idx, trabajador) {
            var nombreCompleto = (trabajador.nombre || "") + " " + (trabajador.apellidos || "");
            select.append(
              '<option value="' + trabajador.id + '" data-nombre="' + nombreCompleto.trim() + '">' +
              nombreCompleto.trim() +
              '</option>'
            );
          });
        } else if (response && response.data && Array.isArray(response.data)) {
          // Fallback para otras versiones del API
          $.each(response.data, function (idx, trabajador) {
            var nombreCompleto = (trabajador.nombre || "") + " " + (trabajador.apellidos || "");
            select.append(
              '<option value="' + trabajador.id + '" data-nombre="' + nombreCompleto.trim() + '">' +
              nombreCompleto.trim() +
              '</option>'
            );
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("Error al cargar trabajadores:", error);
        console.log("Respuesta del servidor:", xhr.responseText);
      }
    });
  };

  // ==================== FUNCIONES PARA LISTA DE INCIDENCIAS ====================

  Dashboard.abrirModalListaIncidencias = function () {
    console.log("Abriendo modal de incidencias...");
    try {
      var modal = $("#modalListaIncidencias");
      if (modal.length === 0) {
        console.error("Modal #modalListaIncidencias no encontrado en el DOM");
        alert("Error: Modal no disponible. Recarga la página.");
        return;
      }
      modal.modal("show");
      console.log("Modal abierto. Cargando incidencias...");
      Dashboard.cargarListaIncidencias();
    } catch (e) {
      console.error("Error al abrir modal:", e);
      alert("Error: " + e.message);
    }
  };

  Dashboard.cargarListaIncidencias = function () {
    console.log("Iniciando carga de incidencias...");
    
    var state = Dashboard.calendarState;
    if (!state) {
      console.error("Dashboard.calendarState no está definido");
      $("#incidenciasVacias").show();
      $("#tablaIncidencias").hide();
      return;
    }
    
    var mes = state.mesActual;
    var anno = state.annoActual;

    console.log("Mes:", mes, "Año:", anno);

    // Calcular fecha inicio y fin del mes
    var fechaInicio = anno + "-" + String(mes + 1).padStart(2, "0") + "-01";
    var fechaFin = new Date(anno, mes + 1, 0);
    var fechaFinStr =
      anno +
      "-" +
      String(mes + 1).padStart(2, "0") +
      "-" +
      String(fechaFin.getDate()).padStart(2, "0");

    console.log("Fechas:", fechaInicio, "a", fechaFinStr);

    $.ajax({
      url: "api-app.php?module=home&method=obtener_eventos_personalizados",
      type: "GET",
      data: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFinStr,
      },
      dataType: "json",
      timeout: 10000,
      cache: false,
      success: function (response) {
        console.log("Respuesta API:", response);
        
        if (response && response.status === 1 && response.eventos) {
          // Filtrar solo incidencias
          var incidencias = response.eventos.filter(
            (ev) => ev.es_incidencia === 1 || ev.es_incidencia === "1"
          );

          console.log("Incidencias encontradas:", incidencias.length);

          if (incidencias.length > 0) {
            $("#tablaIncidencias").show();
            $("#incidenciasVacias").hide();
            Dashboard.renderizarTablaIncidencias(incidencias);
            Dashboard._incidenciasGlobales = incidencias; // Guardar para búsqueda y exportación
          } else {
            $("#tablaIncidencias").hide();
            $("#incidenciasVacias").show();
          }
        } else {
          console.warn("Respuesta inválida:", response);
          $("#tablaIncidencias").hide();
          $("#incidenciasVacias").show();
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en AJAX:", status, error, xhr.responseText);
        $("#tablaIncidencias").hide();
        $("#incidenciasVacias").show();
      },
    });
  };

  Dashboard.renderizarTablaIncidencias = function (incidencias) {
    var html = "";
    incidencias.forEach(function (incidencia) {
      var fecha = new Date(incidencia.fecha).toLocaleDateString("es-ES");
      var nombreTrabajador = incidencia.trabajador_nombre || "Sin asignar";
      var trabajadorId = incidencia.trabajador_id || "";

      var nombreCell =
        trabajadorId && trabajadorId > 0
          ? '<a href="index.php?module=ficha-trabajador&id=' +
            trabajadorId +
            '" style="color: #10442a; text-decoration: none; font-weight: 500;" target="_blank">' +
            nombreTrabajador +
            "</a>"
          : nombreTrabajador;

      html +=
        "<tr>" +
        '<td data-fecha="' +
        incidencia.fecha +
        '">' +
        fecha +
        "</td>" +
        '<td data-trabajador="' +
        nombreTrabajador.toLowerCase() +
        '">' +
        nombreCell +
        "</td>" +
        '<td data-nombre="' +
        incidencia.nombre.toLowerCase() +
        '">' +
        incidencia.nombre +
        "</td>" +
        '<td data-descripcion="' +
        incidencia.descripcion.toLowerCase() +
        '">' +
        (incidencia.descripcion || "-") +
        "</td>" +
        '<td>' +
        '<button class="btn btn-sm btn-info" onclick="Dashboard.abrirModalEvento(\'' +
        incidencia.fecha +
        "', " +
        JSON.stringify(incidencia).replace(/"/g, "&quot;") +
        ')" title="Editar">' +
        '<i class="fa fa-edit"></i>' +
        "</button> " +
        '<button class="btn btn-sm btn-danger" onclick="Dashboard.eliminarEvento(' +
        incidencia.id +
        ')" title="Eliminar">' +
        '<i class="fa fa-trash"></i>' +
        "</button>" +
        "</td>" +
        "</tr>";
    });
    $("#cuerpoIncidencias").html(html);
  };

  Dashboard.exportarIncidenciasPDF = function () {
    console.log("Exportando PDF...");
    
    if (!Dashboard._incidenciasGlobales || Dashboard._incidenciasGlobales.length === 0) {
      console.error("No hay incidencias para exportar:", Dashboard._incidenciasGlobales);
      alert("No hay incidencias para exportar");
      return;
    }

    console.log("Incidencias a exportar:", Dashboard._incidenciasGlobales.length);

    var state = Dashboard.calendarState;
    var meses = [
      "Enero",
      "Febrero",
      "Marzo",
      "Abril",
      "Mayo",
      "Junio",
      "Julio",
      "Agosto",
      "Septiembre",
      "Octubre",
      "Noviembre",
      "Diciembre",
    ];

    try {
      // Crear documento PDF - obtener constructor correcto
      var jsPDFConstructor = window.jspdf.jsPDF;
      if (!jsPDFConstructor) {
        console.error("jsPDF no está disponible en window.jspdf");
        alert("Error: jsPDF no está cargado correctamente");
        return;
      }
      
      var doc = new jsPDFConstructor();
      console.log("Documento PDF creado correctamente");

      // Establecer fuente compatible con caracteres acentuados
      doc.setFont("helvetica");
      
      // Fondo decorativo en header
      doc.setFillColor(33, 87, 116); // Azul oscuro profesional
      doc.rect(0, 0, 210, 30, 'F');

      // Título principal
      doc.setFontSize(20);
      doc.setFont("helvetica", "bold");
      doc.setTextColor(255, 255, 255);
      doc.text("LISTA DE INCIDENCIAS", 14, 12);

      // Período y fecha
      doc.setFontSize(11);
      doc.setFont("helvetica", "normal");
      doc.setTextColor(255, 255, 255);
      doc.text(
        "Periodo: " + meses[state.mesActual] + " " + state.annoActual,
        14,
        22
      );

      // Preparar datos para la tabla
      var tableData = Dashboard._incidenciasGlobales.map(function (inc) {
        return [
          new Date(inc.fecha).toLocaleDateString("es-ES"),
          inc.trabajador_nombre || "Sin asignar",
          inc.nombre || "",
          inc.descripcion || "-",
        ];
      });

      console.log("Datos para tabla:", tableData);

      // Verificar que autoTable esté disponible
      if (typeof doc.autoTable !== 'function') {
        console.warn("autoTable no disponible, creando tabla mejorada");
        
        // Tabla mejorada con estilos profesionales
        var yPos = 40;
        var pageHeight = doc.internal.pageSize.getHeight();
        var rowHeight = 8;
        var rowPadding = 2;
        var alternateColor = false;

        // Encabezados con fondo de color
        doc.setFillColor(51, 122, 161); // Azul más claro
        doc.rect(10, yPos - 5, 190, 8, 'F');
        doc.setFont("helvetica", "bold");
        doc.setFontSize(9);
        doc.setTextColor(255, 255, 255);
        doc.text("FECHA", 12, yPos);
        doc.text("TRABAJADOR", 35, yPos);
        doc.text("NOMBRE INC.", 75, yPos);
        doc.text("DESCRIPCION INC.", 120, yPos);
        
        // Líneas verticales separadoras en encabezado
        doc.setDrawColor(255, 255, 255);
        doc.setLineWidth(0.5);
        doc.line(32, yPos - 4, 32, yPos + 2); // Entre Fecha y Trabajador
        doc.line(72, yPos - 4, 72, yPos + 2); // Entre Trabajador y Nombre
        doc.line(117, yPos - 4, 117, yPos + 2); // Entre Nombre y Descripción
        
        yPos += 10;
        
        // Filas de datos con alternancia de colores
        doc.setFont("helvetica", "normal");
        doc.setFontSize(9);
        doc.setTextColor(33, 33, 33);

        tableData.forEach(function(row, index) {
          // Verificar si necesitamos nueva página
          if (yPos > pageHeight - 20) {
            // Pie de página en página anterior
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text("Página " + doc.internal.pages.length, 190, pageHeight - 5, {align: "right"});
            
            // Nueva página
            doc.addPage();
            yPos = 20;
            
            // Repetir encabezados en nueva página
            doc.setFillColor(51, 122, 161);
            doc.rect(10, yPos - 5, 190, 8, 'F');
            doc.setFont("helvetica", "bold");
            doc.setFontSize(9);
            doc.setTextColor(255, 255, 255);
            doc.text("FECHA", 12, yPos);
            doc.text("TRABAJADOR", 35, yPos);
            doc.text("NOMBRE INC.", 75, yPos);
            doc.text("DESCRIPCION INC.", 120, yPos);
            
            // Líneas verticales separadoras
            doc.setDrawColor(255, 255, 255);
            doc.setLineWidth(0.5);
            doc.line(32, yPos - 4, 32, yPos + 2);
            doc.line(72, yPos - 4, 72, yPos + 2);
            doc.line(117, yPos - 4, 117, yPos + 2);
            
            doc.setFont("helvetica", "normal");
            doc.setFontSize(9);
            doc.setTextColor(33, 33, 33);
            yPos += 10;
            alternateColor = false;
          }
          
          // Fondo alternado
          if (alternateColor) {
            doc.setFillColor(240, 245, 250); // Azul muy claro
            doc.rect(10, yPos - rowHeight + 2, 190, rowHeight, 'F');
          }
          
          // Borde inferior
          doc.setDrawColor(220, 220, 220);
          doc.setLineWidth(0.2);
          doc.line(10, yPos + 2, 200, yPos + 2);
          
          // Líneas verticales separadoras
          doc.setDrawColor(200, 200, 200);
          doc.setLineWidth(0.3);
          doc.line(32, yPos - 6, 32, yPos + 2); // Entre Fecha y Trabajador
          doc.line(72, yPos - 6, 72, yPos + 2); // Entre Trabajador y Nombre
          doc.line(117, yPos - 6, 117, yPos + 2); // Entre Nombre y Descripción
          
          // Texto con truncado para descripción
          var descripcion = String(row[3]).length > 40 ? String(row[3]).substring(0, 37) + "..." : String(row[3]);
          
          doc.text(String(row[0]), 12, yPos);
          doc.text(String(row[1]).substring(0, 20), 35, yPos);
          doc.text(String(row[2]).substring(0, 15), 75, yPos);
          doc.text(descripcion, 120, yPos);
          
          yPos += 8;
          alternateColor = !alternateColor;
        });

        // Resumen final
        yPos += 5;
        doc.setFillColor(240, 240, 240);
        doc.rect(10, yPos - 5, 190, 8, 'F');
        doc.setFont("helvetica", "bold");
        doc.setFontSize(10);
        doc.setTextColor(33, 87, 116);
        doc.text("TOTAL DE INCIDENCIAS: " + tableData.length, 12, yPos);

        console.log("Tabla mejorada creada");
      } else {
        // Crear tabla con autoTable si está disponible
        doc.autoTable({
          startY: 35,
          head: [["Fecha", "Trabajador", "Nombre", "Descripción"]],
          body: tableData,
          margin: { top: 35, right: 14, bottom: 20, left: 10 },
          theme: "striped",
          headStyles: {
            fillColor: [51, 122, 161],
            textColor: 255,
            fontStyle: "bold",
            fontSize: 11,
            halign: "left",
            lineColor: [100, 100, 100],
          },
          bodyStyles: {
            textColor: 33,
            fontSize: 9,
            lineColor: [220, 220, 220],
          },
          alternateRowStyles: {
            fillColor: [240, 245, 250],
          },
          columnStyles: {
            0: { cellWidth: 25 },
            1: { cellWidth: 35 },
            2: { cellWidth: 50 },
            3: { cellWidth: 60 },
          },
          didDrawPage: function(data) {
            // Pie de página
            var pageCount = doc.internal.pages.length - 1;
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text(
              "Generado: " + new Date().toLocaleDateString("es-ES") + " - Página " + pageCount,
              data.settings.margin.left,
              doc.internal.pageSize.getHeight() - 8
            );
          },
        });
        console.log("Tabla autoTable creada con estilo");
      }

      // Pie de página adicional en la última página
      var pageHeight = doc.internal.pageSize.getHeight();
      doc.setFontSize(8);
      doc.setFont("helvetica", "normal");
      doc.setTextColor(150, 150, 150);
      doc.text(
        "Generado: " + new Date().toLocaleDateString("es-ES") + " " + new Date().toLocaleTimeString("es-ES"),
        14,
        pageHeight - 8
      );

      // Descargar PDF
      var fileName =
        "Incidencias_" +
        state.annoActual +
        "_" +
        String(state.mesActual + 1).padStart(2, "0") +
        ".pdf";

      console.log("Descargando como:", fileName);
      doc.save(fileName);
      console.log("PDF generado exitosamente");
    } catch (err) {
      console.error("Error al generar PDF:", err);
      console.error("Stack trace:", err.stack);
      alert("Error al generar PDF: " + err.message);
    }
  };

  // Event listener para búsqueda
  $(document).on("keyup", "#buscarIncidencia", function () {
    var busqueda = $(this).val().toLowerCase();
    $("#cuerpoIncidencias tr").each(function () {
      var texto = $(this).text().toLowerCase();
      $(this).toggle(texto.indexOf(busqueda) > -1);
    });
  });

  Dashboard.verEventosDia = function (fechaKey) {
    var state = Dashboard.calendarState;
    var evento = state.eventos[fechaKey] || { vacaciones: [], cumpleanos: [], contratos: [] };
    var eventosPersonalizados = state.eventosPersonalizados[fechaKey] || [];

    var container = $("#listaEventosContainer");
    container.empty();

    var html = '<div class="list-group" style="margin-bottom: 0;">';

    // Vacaciones
    $.each(evento.vacaciones, function (idx, vac) {
      html +=
        '<a href="#" onclick="window.location.href=\'index.php?module=ficha-trabajador&id=' + vac.id + '\'; return false;" class="list-group-item" style="border-left: 5px solid #5bc0de; cursor: pointer;">' +
        '<h5 class="list-group-item-heading"><i class="fa fa-plane" style="margin-right: 10px; color: #5bc0de;"></i>Vacaciones</h5>' +
        '<p class="list-group-item-text">' +
        vac.nombre +
        "</p>" +
        "</a>";
    });

    // Cumpleaños
    $.each(evento.cumpleanos, function (idx, cump) {
      html +=
        '<a href="#" onclick="window.location.href=\'index.php?module=ficha-trabajador&id=' + cump.id + '\'; return false;" class="list-group-item" style="border-left: 5px solid #d9534f; cursor: pointer;">' +
        '<h5 class="list-group-item-heading"><i class="fa fa-birthday-cake" style="margin-right: 10px; color: #d9534f;"></i>Cumpleaños</h5>' +
        '<p class="list-group-item-text">' +
        cump.nombre +
        "</p>" +
        "</a>";
    });

    // Contratos que vencen
    if (evento.contratos && evento.contratos.length > 0) {
      $.each(evento.contratos, function (idx, cont) {
        html +=
          '<a href="#" onclick="window.location.href=\'index.php?module=ficha-trabajador&id=' + cont.id + '\'; return false;" class="list-group-item" style="border-left: 5px solid #8e44ad; cursor: pointer;">' +
          '<h5 class="list-group-item-heading"><i class="fa fa-file-text-o" style="margin-right: 10px; color: #8e44ad;"></i>Vencimiento Contrato</h5>' +
          '<p class="list-group-item-text">' +
          cont.nombre +
          "</p>" +
          "</a>";
      });
    }

    // Incidencias
    if (evento.incidencias && evento.incidencias.length > 0) {
      $.each(evento.incidencias, function (idx, inc) {
        html +=
          '<a href="#" class="list-group-item" style="border-left: 5px solid #000000; cursor: pointer;">' +
          '<h5 class="list-group-item-heading"><i class="fa fa-exclamation-circle" style="margin-right: 10px; color: #000000;"></i>Incidencia</h5>' +
          '<p class="list-group-item-text"><strong>' +
          inc.nombre +
          "</strong></p>" +
          (inc.descripcion ? '<p class="list-group-item-text">' + inc.descripcion + '</p>' : '') +
          "</a>";
      });
    }

    // Eventos Personalizados
    $.each(eventosPersonalizados, function (idx, evt) {
      var evtJson = JSON.stringify(evt).replace(/"/g, "&quot;");
      html +=
        '<a href="#" class="list-group-item" onclick="Dashboard.abrirModalEvento(\'' +
        fechaKey +
        "', " +
        evtJson +
        "); $('#modalListaEventos').modal('hide');\" style=\"border-left: 5px solid " +
        evt.color +
        ';">' +
        '<h5 class="list-group-item-heading"><i class="fa fa-star" style="margin-right: 10px; color: ' +
        evt.color +
        ';"></i>' +
        evt.nombre +
        "</h5>" +
        '<p class="list-group-item-text">' +
        (evt.descripcion || "Sin descripción") +
        "</p>" +
        "</a>";
    });

    html += "</div>";

    if (
      evento.vacaciones.length === 0 &&
      evento.cumpleanos.length === 0 &&
      (evento.contratos ? evento.contratos.length : 0) === 0 &&
      (evento.incidencias ? evento.incidencias.length : 0) === 0 &&
      eventosPersonalizados.length === 0
    ) {
      html =
        '<div class="text-center" style="padding: 20px; color: #777;">No hay eventos para este día.</div>';
    }

    container.html(html);

    // Configurar botón de nuevo evento
    $("#btnNuevoEventoDia")
      .off("click")
      .on("click", function () {
        $("#modalListaEventos").modal("hide");
        Dashboard.abrirModalEvento(fechaKey);
      });

    $("#modalListaEventos").modal("show");
  };

  Dashboard.limpiarFormularioEvento = function () {
    $("#eventoId").val("");
    $("#eventoFecha").val("");
    $("#eventoNombre").val("");
    $("#eventoDescripcion").val("");
    $("#eventoColor").val("#f0ad4e");
    $("#eventoTrabajador").val("");
    $("#toggleIncidencia").prop("checked", false);
    $("#eventoIsIncidencia").val("0");
    $("#groupTrabajador").hide();
    $("#groupNombre").show();
    $("#groupColor").show();
    $("#toggleIncidencia").closest(".form-group").show(); // Mostrar toggle
    
    // Restaurar etiquetas a valores por defecto
    $("label[for='eventoNombre']").text("Nombre del Evento *");
    $("label[for='eventoFecha']").text("Fecha del Evento *");
    $("label[for='eventoDescripcion']").text("Descripción");
    
    $("#modalEventoHeader").css("background", "linear-gradient(135deg, #f0ad4e 0%, #f9a825 100%)");
    $("#btnGuardarEvento").css("background", "linear-gradient(135deg, #f0ad4e 0%, #f9a825 100%)").html('<i class="fa fa-save" style="margin-right: 5px;"></i>Guardar Evento');
  };

  // ==================== FIN FUNCIONES DE EVENTOS PERSONALIZADOS ====================

  Dashboard.cargarCalendarioEventos = function () {
    var state = Dashboard.calendarState;

    // Evitar múltiples llamadas simultáneas
    if (state.cargando && state.intentos < 1) return;

    state.cargando = true;
    state.intentos++;

    console.log(`[CALENDARIO] Intento #${state.intentos}`);

    $.ajax({
      url: "api-app.php?module=home&method=calendario_eventos",
      type: "GET",
      dataType: "json",
      timeout: 10000,
      cache: false,
      xhrFields: { withCredentials: true },
      success: function (response) {
        console.log("[CALENDARIO] ✓ Respuesta recibida", response);

        state.cargando = false;
        state.intentos = 0;

        if (response && response.status === 1) {
          state.eventos = response.eventos || {};
          state.annoActual = response.anno || new Date().getFullYear();
          Dashboard.cargarEventosPersonalizados();
        } else {
          console.log("[CALENDARIO] Sin datos, mostrando calendario vacío");
          state.eventos = {};
          Dashboard.cargarEventosPersonalizados();
        }
      },
      error: function (xhr, status, error) {
        console.error("[CALENDARIO] ✗ Error:", status, error);
        state.cargando = false;

        // Mostrar calendario aunque no haya datos
        if (state.intentos >= 1) {
          state.eventos = {};
          Dashboard.cargarEventosPersonalizados();
        }
      },
    });
  };

  Dashboard.renderizarCalendario = function () {
    console.log("[CALENDARIO] Renderizando...");
    var container = $("#calendario-container");
    if (!container.length) {
      console.log("[CALENDARIO] Contenedor no encontrado");
      return;
    }

    var state = Dashboard.calendarState;
    var mes = state.mesActual;
    var anno = state.annoActual;

    var meses = [
      "Enero",
      "Febrero",
      "Marzo",
      "Abril",
      "Mayo",
      "Junio",
      "Julio",
      "Agosto",
      "Septiembre",
      "Octubre",
      "Noviembre",
      "Diciembre",
    ];

    var html =
      '<div class="calendario-header">' +
      '<button class="btn-nav-calendar" onclick="Dashboard.navCalendario(-1)">◀ Anterior</button>' +
      '<h2 id="mes-label">' +
      meses[mes] +
      " " +
      anno +
      "</h2>" +
      '<button class="btn-nav-calendar" onclick="Dashboard.abrirModalListaIncidencias()" style="background: #333333; margin-right: 15px;">📋 Lista de Incidencias</button>' +
      '<button class="btn-nav-calendar" onclick="Dashboard.navCalendario(1)">Siguiente ▶</button>' +
      "</div>";

    html += '<div class="calendario-grid">';

    // Headers
    var diasSemana = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];
    for (var i = 0; i < 7; i++) {
      html += '<div class="calendario-dia-header">' + diasSemana[i] + "</div>";
    }

    // Calcular días del mes
    var primerDia = new Date(anno, mes, 1);
    var ultimoDia = new Date(anno, mes + 1, 0);
    var comienza = primerDia.getDay() === 0 ? 6 : primerDia.getDay() - 1;
    var diasMes = ultimoDia.getDate();

    var diaNum = 1 - comienza;
    var hoy = new Date();

    for (var semana = 0; semana < 6; semana++) {
      for (var dia = 0; dia < 7; dia++) {
        var fechaObj = new Date(anno, mes, diaNum);
        var esOtroMes = diaNum < 1 || diaNum > diasMes;
        var esHoy =
          !esOtroMes &&
          fechaObj.getDate() === hoy.getDate() &&
          fechaObj.getMonth() === hoy.getMonth() &&
          fechaObj.getFullYear() === hoy.getFullYear();

        var fechaKey =
          fechaObj.getFullYear() +
          "-" +
          String(fechaObj.getMonth() + 1).padStart(2, "0") +
          "-" +
          String(fechaObj.getDate()).padStart(2, "0");

        var evento = state.eventos[fechaKey] || {
          vacaciones: [],
          cumpleanos: [],
          contratos: [],
          incidencias: []
        };
        var eventosPersonalizados = state.eventosPersonalizados[fechaKey] || [];

        var clases = "calendario-dia";
        if (esOtroMes) clases += " otro-mes";
        if (esHoy) clases += " hoy";

        html +=
          '<div class="' +
          clases +
          '" onclick="Dashboard.abrirModalEvento(\'' +
          fechaKey +
          '\')" style="cursor: pointer;">' +
          '<div class="numero-dia">' +
          (esOtroMes ? "" : diaNum) +
          "</div>";

        // Calcular total de eventos
        var totalEventos =
          evento.vacaciones.length +
          evento.cumpleanos.length +
          (evento.contratos ? evento.contratos.length : 0) +
          (evento.incidencias ? evento.incidencias.length : 0) +
          eventosPersonalizados.length;

        // Container para eventos del día
        if (totalEventos > 0) {
          html +=
            '<div class="eventos-dia-container" style="margin-top: 5px; font-size: 10px; line-height: 1.3;">';

          // Mostrar vacaciones con nombre
          for (var v = 0; v < evento.vacaciones.length; v++) {
            var nombreVac = evento.vacaciones[v].nombre;
            // Truncar nombre si es muy largo
            var nombreCorto =
              nombreVac.length > 15
                ? nombreVac.substring(0, 13) + "..."
                : nombreVac;
            html +=
              '<div class="evento-item" onclick="event.stopPropagation(); Dashboard.verEventosDia(\'' +
              fechaKey +
              '\');" style="cursor: pointer; padding: 2px 4px; margin-bottom: 2px; background: rgba(91, 192, 222, 0.15); border-left: 3px solid #5bc0de; border-radius: 3px; display: flex; align-items: center; gap: 4px;">';
            html += '<span style="font-size: 11px;">🏖️</span>';
            html +=
              '<span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #31708f; font-weight: 500;">' +
              nombreCorto +
              "</span>";
            html += "</div>";
          }

          // Mostrar cumpleaños con nombre
          for (var c = 0; c < evento.cumpleanos.length; c++) {
            var nombreCump = evento.cumpleanos[c].nombre;
            var nombreCorto =
              nombreCump.length > 15
                ? nombreCump.substring(0, 13) + "..."
                : nombreCump;
            html +=
              '<div class="evento-item" onclick="event.stopPropagation(); Dashboard.verEventosDia(\'' +
              fechaKey +
              '\');" style="cursor: pointer; padding: 2px 4px; margin-bottom: 2px; background: rgba(217, 83, 79, 0.15); border-left: 3px solid #d9534f; border-radius: 3px; display: flex; align-items: center; gap: 4px;">';
            html += '<span style="font-size: 11px;">🎂</span>';
            html +=
              '<span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #a94442; font-weight: 500;">' +
              nombreCorto +
              "</span>";
            html += "</div>";
          }

          // Mostrar contratos que vencen
          if (evento.contratos) {
            for (var k = 0; k < evento.contratos.length; k++) {
              var nombreCont = evento.contratos[k].nombre;
              var nombreCorto = nombreCont.length > 15 ? nombreCont.substring(0, 13) + "..." : nombreCont;
              html +=
                '<div class="evento-item" onclick="event.stopPropagation(); Dashboard.verEventosDia(\'' +
                fechaKey +
                '\');" style="cursor: pointer; padding: 2px 4px; margin-bottom: 2px; background: rgba(142, 68, 173, 0.15); border-left: 3px solid #8e44ad; border-radius: 3px; display: flex; align-items: center; gap: 4px;">';
              html += '<span style="font-size: 11px;">📄</span>';
              html +=
                '<span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #8e44ad; font-weight: 500;">' +
                nombreCorto +
                "</span>";
              html += "</div>";
            }
          }

          // Mostrar incidencias
          if (evento.incidencias) {
            for (var inc = 0; inc < evento.incidencias.length; inc++) {
              var nombreInc = evento.incidencias[inc].nombre;
              var nombreCorto = nombreInc.length > 15 ? nombreInc.substring(0, 13) + "..." : nombreInc;
              html +=
                '<div class="evento-item" onclick="event.stopPropagation(); Dashboard.verEventosDia(\'' +
                fechaKey +
                '\');" style="cursor: pointer; padding: 2px 4px; margin-bottom: 2px; background: rgba(51, 51, 51, 0.15); border-left: 3px solid #000000; border-radius: 3px; display: flex; align-items: center; gap: 4px;">';
              html += '<span style="font-size: 11px;">⚠️</span>';
              html +=
                '<span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #333333; font-weight: 500;">' +
                nombreCorto +
                "</span>";
              html += "</div>";
            }
          }

          // Mostrar eventos personalizados con nombre
          for (var i = 0; i < eventosPersonalizados.length; i++) {
            var evt = eventosPersonalizados[i];
            var nombreCorto =
              evt.nombre.length > 15
                ? evt.nombre.substring(0, 13) + "..."
                : evt.nombre;
            html +=
              '<div class="evento-item" onclick="event.stopPropagation(); Dashboard.abrirModalEvento(\'' +
              fechaKey +
              "', " +
              JSON.stringify(evt).replace(/"/g, "&quot;") +
              ');" style="cursor: pointer; padding: 2px 4px; margin-bottom: 2px; background: ' +
              evt.color +
              "1A; border-left: 3px solid " +
              evt.color +
              '; border-radius: 3px; display: flex; align-items: center; gap: 4px;">';
            html += '<span style="font-size: 11px;">⭐</span>';
            html +=
              '<span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: ' +
              evt.color +
              '; font-weight: 500;">' +
              nombreCorto +
              "</span>";
            html += "</div>";
          }

          // Mostrar contador si hay demasiados eventos (más de 4)
          if (totalEventos > 4) {
            html +=
              '<div class="evento-count-small" onclick="event.stopPropagation(); Dashboard.verEventosDia(\'' +
              fechaKey +
              '\');" style="cursor: pointer; text-align: center; padding: 3px; background: #f0f0f0; color: #666; border-radius: 3px; font-size: 9px; margin-top: 2px; font-weight: 600;">Ver todos (' +
              totalEventos +
              ")</div>";
          }

          html += "</div>";
        }

        html += "</div>";
        diaNum++;
      }
    }

    html += "</div>";

    // Leyenda
    html +=
      '<div class="calendario-leyenda">' +
      '<div class="leyenda-item"><span class="leyenda-badge vacaciones"></span><span>Vacaciones</span></div>' +
      '<div class="leyenda-item"><span class="leyenda-badge cumpleanos"></span><span>Cumpleaños</span></div>' +
      '<div class="leyenda-item"><span class="leyenda-badge contratos" style="background-color: #8e44ad;"></span><span>Vencimientos</span></div>' +
      '<div class="leyenda-item"><span class="leyenda-badge incidencias" style="background-color: #000000;"></span><span>Incidencias</span></div>' +
      '<div class="leyenda-item"><span class="leyenda-badge personalizado"></span><span>Eventos Personalizados</span></div>' +
      "</div>";

    container.html(html);
    console.log("[CALENDARIO] ✓ Renderizado");
  };

  Dashboard.navCalendario = function (offset) {
    var state = Dashboard.calendarState;
    state.mesActual += offset;

    if (state.mesActual > 11) {
      state.mesActual = 0;
      state.annoActual++;
    } else if (state.mesActual < 0) {
      state.mesActual = 11;
      state.annoActual--;
    }

    Dashboard.cargarEventosPersonalizados();
    Dashboard.renderizarCalendario();
  };

  // ==================== MANEJADORES DE EVENTOS DEL MODAL ====================

  $(document)
    .off("click", "#btnGuardarEvento")
    .on("click", "#btnGuardarEvento", function () {
      Dashboard.guardarEvento();
    });

  $(document)
    .off("click", "#btnEliminarEvento")
    .on("click", "#btnEliminarEvento", function () {
      Dashboard.eliminarEvento();
    });

  // Evento para toggle de Incidencia
  $(document)
    .off("change", "#toggleIncidencia")
    .on("change", "#toggleIncidencia", function () {
      var isIncidencia = $(this).is(":checked");
      var eventoId = $("#eventoId").val();
      
      if (isIncidencia) {
        // Mostrar modo Incidencia
        $("#eventoIsIncidencia").val("1");
        $("#groupTrabajador").show();
        $("#groupNombre").hide();
        $("#groupColor").hide();
        $("#eventoNombre").removeAttr("required");
        $("#eventoTrabajador").attr("required", "required");
        
        // Cambiar etiquetas de campos
        $("label[for='eventoFecha']").text("Fecha de Incidencia *");
        $("label[for='eventoDescripcion']").text("Descripción de Incidencia");
        
        // Cambiar header a negro
        $("#modalEventoHeader").css("background", "linear-gradient(135deg, #333333 0%, #1a1a1a 100%)");
        
        // Cambiar título según sea nuevo o edición
        if (!eventoId) {
          // Nuevo evento
          $("#modalEventoLabel").html(
            '<i class="fa fa-exclamation-circle" style="margin-right: 10px;"></i>Nueva Incidencia'
          );
        } else {
          // Edición
          $("#modalEventoLabel").html(
            '<i class="fa fa-edit" style="margin-right: 10px;"></i>Editar Incidencia'
          );
        }
        
        // Cambiar botón a Guardar Incidencia
        $("#btnGuardarEvento")
          .css("background", "linear-gradient(135deg, #333333 0%, #1a1a1a 100%)")
          .html('<i class="fa fa-exclamation-circle" style="margin-right: 5px;"></i>Guardar Incidencia');
      } else {
        // Mostrar modo Evento Normal
        $("#eventoIsIncidencia").val("0");
        $("#groupTrabajador").hide();
        $("#groupNombre").show();
        $("#groupColor").show();
        $("#eventoTrabajador").removeAttr("required");
        $("#eventoNombre").attr("required", "required");
        
        // Cambiar etiquetas de campos de vuelta a original
        $("label[for='eventoFecha']").text("Fecha del Evento *");
        $("label[for='eventoDescripcion']").text("Descripción");
        
        // Cambiar header a amarillo
        $("#modalEventoHeader").css("background", "linear-gradient(135deg, #f0ad4e 0%, #f9a825 100%)");
        
        // Cambiar título según sea nuevo o edición
        if (!eventoId) {
          // Nuevo evento
          $("#modalEventoLabel").html(
            '<i class="fa fa-star" style="margin-right: 10px;"></i>Nuevo Evento Personalizado'
          );
        } else {
          // Edición
          $("#modalEventoLabel").html(
            '<i class="fa fa-edit" style="margin-right: 10px;"></i>Editar Evento'
          );
        }
        
        // Cambiar botón a Guardar Evento
        $("#btnGuardarEvento")
          .css("background", "linear-gradient(135deg, #f0ad4e 0%, #f9a825 100%)")
          .html('<i class="fa fa-save" style="margin-right: 5px;"></i>Guardar Evento');
      }
    });

  // Evento para cambio en select de trabajador
  $(document)
    .off("change", "#eventoTrabajador")
    .on("change", "#eventoTrabajador", function () {
      var selectedOption = $(this).find("option:selected");
      var trabajadorNombre = selectedOption.data("nombre");
      var trabajadorId = $(this).val();
      
      if (trabajadorId) {
        // Construir el nombre de la incidencia
        var nombreIncidencia = "Incidencia de " + trabajadorNombre;
        $("#eventoNombre").val(nombreIncidencia);
      } else {
        $("#eventoNombre").val("");
      }
    });

  // ==================== INICIALIZACIÓN ====================

  // Inicializar calendario después de un delay corto
  setTimeout(function () {
    Dashboard.cargarCalendarioEventos();
  }, 500);

  // Refresh cada 10 minutos
  setInterval(function () {
    if (!Dashboard.calendarState.cargando) {
      Dashboard.cargarCalendarioEventos();
    }
  }, 600000);
});
document.querySelectorAll(".panel-card").forEach((el) => {
  el.addEventListener("mousemove", (event) => {
    const card = event.currentTarget;
    const rect = card.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotX = ((y - centerY) / centerY) * 20;
    const rotY = ((x - centerX) / centerX) * -20;
    card.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg)`;
    card.style.transition = "transform 0.05s linear";
  });
  el.addEventListener("mouseout", (event) => {
    const card = event.currentTarget;
    card.style.transform = `rotateX(0deg) rotateY(0deg)`;
    card.style.transition = "transform 0.3s ease-out";
  });
});
// ===================== FUNCIONES GLOBALES PARA MODALES =====================
// Estas funciones DEBEN estar fuera de $(document).ready) para ser accesibles desde HTML

// Función para escapar HTML - GLOBAL
function escapeHtml(str) {
  if (!str) return "";
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Variable global para rastrear el estado del zoom de presentes
let previewPresente = {
  zoomed: false,
  foto: null,
  nombre: null,
  ubicacion: null,
  departamento: null,
};

// Variable global para rastrear el estado del zoom de ausentes
let previewAusente = {
  zoomed: false,
  foto: null,
  nombre: null,
  ubicacion: null,
  departamento: null,
};

function cargarFotosPresentes(count) {
  // Mostrar modal
  document.getElementById("modal-presentes").style.display = "flex";

  $.ajax({
    url: "api-app.php?module=asistencias&method=list-presentes",
    type: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Presentes Response:", response);

      if (Array.isArray(response) && response.length > 0) {
        var html = "";

        response.forEach((ubicacion) => {
          if (ubicacion.trabajadores && ubicacion.trabajadores.length > 0) {
            ubicacion.trabajadores.forEach((trab) => {
              var fotoBase64 = "";
              if (
                trab.foto &&
                typeof trab.foto === "string" &&
                trab.foto.length > 20
              ) {
                fotoBase64 = trab.foto;
              }

              var nombreCompleto =
                (trab.nombre || "") + " " + (trab.apellidos || "");
              var iniciales =
                ((trab.nombre && trab.nombre.charAt(0)) || "T") +
                ((trab.apellidos && trab.apellidos.charAt(0)) || "T");

              var fotoUrl = fotoBase64
                ? "data:image/jpeg;base64," + fotoBase64
                : "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%234a90e2%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E" +
                iniciales.toUpperCase() +
                "%3C/text%3E%3C/svg%3E";

              html += `
                            <div style="
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                animation: fadeIn 0.5s ease-out;
                            " onmouseover="
                                this.style.transform='scale(1.12)';
                                this.querySelector('img').style.boxShadow='0 8px 20px rgba(74,144,226,0.4)';
                                this.querySelector('div:last-child').style.color='#4a90e2';
                            " onmouseout="
                                this.style.transform='scale(1)';
                                this.querySelector('img').style.boxShadow='0 3px 10px rgba(0,0,0,0.1)';
                                this.querySelector('div:last-child').style.color='#666';
                            " onclick="window.location.href='index.php?module=ficha-trabajador&id=${trab.id}';">
                                <img src="${fotoUrl}" style="
                                    width: 70px;
                                    height: 70px;
                                    border-radius: 50%;
                                    border: 3px solid #4a90e2;
                                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                                    transition: all 0.3s ease;
                                    object-fit: cover;
                                    background: #f5f5f5;
                                " onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%234a90e2%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E${iniciales.toUpperCase()}%3C/text%3E%3C/svg%3E'" />
                                <div style="
                                    font-size: 11px;
                                    color: #666;
                                    margin-top: 8px;
                                    text-align: center;
                                    max-width: 75px;
                                    word-break: break-word;
                                    line-height: 1.3;
                                    font-weight: 500;
                                    transition: all 0.3s ease;
                                ">${escapeHtml(
                nombreCompleto.substring(0, 20)
              )}</div>
                            </div>
                            `;
            });
          }
        });
        $("#modal-presentes-content").html(html);
      } else {
        $("#modal-presentes-content").html(
          '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;"><i class="fa fa-inbox fa-2x"></i><p>No hay presentes para mostrar</p></div>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
      $("#modal-presentes-content").html(
        '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #4a90e2;"><i class="fa fa-warning fa-2x"></i><p>Error al cargar presentes</p></div>'
      );
    },
  });
}

function cargarFotosEspeciales(count) {
  // Mostrar modal
  document.getElementById("modal-especiales").style.display = "flex";

  $.ajax({
    url: "api-app.php?module=asistencias&method=list-especiales",
    type: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Especiales Response:", response);

      if (Array.isArray(response) && response.length > 0) {
        var html = "";

        response.forEach((ubicacion) => {
          if (ubicacion.trabajadores && ubicacion.trabajadores.length > 0) {
            ubicacion.trabajadores.forEach((trab) => {
              var fotoBase64 = "";
              if (
                trab.foto &&
                typeof trab.foto === "string" &&
                trab.foto.length > 20
              ) {
                fotoBase64 = trab.foto;
              }

              var nombreCompleto =
                (trab.nombre || "") + " " + (trab.apellidos || "");
              var iniciales =
                ((trab.nombre && trab.nombre.charAt(0)) || "T") +
                ((trab.apellidos && trab.apellidos.charAt(0)) || "T");

              var fotoUrl = fotoBase64
                ? "data:image/jpeg;base64," + fotoBase64
                : "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%239b59b6%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E" +
                iniciales.toUpperCase() +
                "%3C/text%3E%3C/svg%3E";

              html += `
                            <div style="
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                animation: fadeIn 0.5s ease-out;
                            " onmouseover="
                                this.style.transform='scale(1.12)';
                                this.querySelector('img').style.boxShadow='0 8px 20px rgba(155,89,182,0.4)';
                                this.querySelector('div:last-child').style.color='#9b59b6';
                            " onmouseout="
                                this.style.transform='scale(1)';
                                this.querySelector('img').style.boxShadow='0 3px 10px rgba(0,0,0,0.1)';
                                this.querySelector('div:last-child').style.color='#666';
                            " onclick="window.location.href='index.php?module=ficha-trabajador&id=${trab.id}';">
                                <img src="${fotoUrl}" style="
                                    width: 70px;
                                    height: 70px;
                                    border-radius: 50%;
                                    border: 3px solid #9b59b6;
                                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                                    transition: all 0.3s ease;
                                    object-fit: cover;
                                    background: #f5f5f5;
                                " onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%239b59b6%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E${iniciales.toUpperCase()}%3C/text%3E%3C/svg%3E'" />
                                <div style="
                                    font-size: 11px;
                                    color: #666;
                                    margin-top: 8px;
                                    text-align: center;
                                    max-width: 75px;
                                    word-break: break-word;
                                    line-height: 1.3;
                                    font-weight: 500;
                                    transition: all 0.3s ease;
                                ">${escapeHtml(
                nombreCompleto.substring(0, 20)
              )}</div>
                            </div>
                            `;
            });
          }
        });
        $("#modal-especiales-content").html(html);
      } else {
        $("#modal-especiales-content").html(
          '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;"><i class="fa fa-inbox fa-2x"></i><p>No hay especiales para mostrar</p></div>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
      $("#modal-especiales-content").html(
        '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;"><i class="fa fa-warning fa-2x"></i><p>Error al cargar especiales</p></div>'
      );
    },
  });
}

function cargarFotosVacaciones(count) {
  // Mostrar modal
  document.getElementById("modal-vacaciones").style.display = "flex";

  $.ajax({
    url: "api-app.php?module=asistencias&method=list-vacaciones",
    type: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Vacaciones Response:", response);

      if (Array.isArray(response) && response.length > 0) {
        var html = "";

        response.forEach((ubicacion) => {
          if (ubicacion.trabajadores && ubicacion.trabajadores.length > 0) {
            ubicacion.trabajadores.forEach((trab) => {
              var fotoBase64 = "";
              if (
                trab.foto &&
                typeof trab.foto === "string" &&
                trab.foto.length > 20
              ) {
                fotoBase64 = trab.foto;
              }

              var nombreCompleto =
                (trab.nombre || "") + " " + (trab.apellidos || "");
              var iniciales =
                ((trab.nombre && trab.nombre.charAt(0)) || "T") +
                ((trab.apellidos && trab.apellidos.charAt(0)) || "T");

              var fotoUrl = fotoBase64
                ? "data:image/jpeg;base64," + fotoBase64
                : "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23f0ad4e%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E" +
                iniciales.toUpperCase() +
                "%3C/text%3E%3C/svg%3E";

              html += `
                            <div style="
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                animation: fadeIn 0.5s ease-out;
                            " onmouseover="
                                this.style.transform='scale(1.12)';
                                this.querySelector('img').style.boxShadow='0 8px 20px rgba(240,173,78,0.4)';
                                this.querySelector('div:last-child').style.color='#f0ad4e';
                            " onmouseout="
                                this.style.transform='scale(1)';
                                this.querySelector('img').style.boxShadow='0 3px 10px rgba(0,0,0,0.1)';
                                this.querySelector('div:last-child').style.color='#666';
                            " onclick="window.location.href='index.php?module=ficha-trabajador&id=${trab.id}';">
                                <img src="${fotoUrl}" style="
                                    width: 70px;
                                    height: 70px;
                                    border-radius: 50%;
                                    border: 3px solid #f0ad4e;
                                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                                    transition: all 0.3s ease;
                                    object-fit: cover;
                                    background: #f5f5f5;
                                " onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23f0ad4e%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E${iniciales.toUpperCase()}%3C/text%3E%3C/svg%3E'" />
                                <div style="
                                    font-size: 11px;
                                    color: #666;
                                    margin-top: 8px;
                                    text-align: center;
                                    max-width: 75px;
                                    word-break: break-word;
                                    line-height: 1.3;
                                    font-weight: 500;
                                    transition: all 0.3s ease;
                                ">${escapeHtml(
                nombreCompleto.substring(0, 20)
              )}</div>
                            </div>
                            `;
            });
          }
        });
        $("#modal-vacaciones-content").html(html);
      } else {
        $("#modal-vacaciones-content").html(
          '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;"><i class="fa fa-inbox fa-2x"></i><p>No hay vacaciones para mostrar</p></div>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
      $("#modal-vacaciones-content").html(
        '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;"><i class="fa fa-warning fa-2x"></i><p>Error al cargar vacaciones</p></div>'
      );
    },
  });
}

function cargarFotosAusentes(count) {
  // Mostrar modal
  document.getElementById("modal-ausentes").style.display = "flex";

  $.ajax({
    url: "api-app.php?module=asistencias&method=list-ausentes",
    type: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Ausentes Response:", response);

      if (Array.isArray(response) && response.length > 0) {
        var html = "";

        response.forEach((ubicacion) => {
          if (ubicacion.trabajadores && ubicacion.trabajadores.length > 0) {
            ubicacion.trabajadores.forEach((trab) => {
              var fotoBase64 = "";
              if (
                trab.foto &&
                typeof trab.foto === "string" &&
                trab.foto.length > 20
              ) {
                fotoBase64 = trab.foto;
              }

              var nombreCompleto =
                (trab.nombre || "") + " " + (trab.apellidos || "");
              var iniciales =
                ((trab.nombre && trab.nombre.charAt(0)) || "T") +
                ((trab.apellidos && trab.apellidos.charAt(0)) || "T");

              var fotoUrl = fotoBase64
                ? "data:image/jpeg;base64," + fotoBase64
                : "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23d9534f%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E" +
                iniciales.toUpperCase() +
                "%3C/text%3E%3C/svg%3E";

              // Determinar si tiene justificación
              var tieneJustificacion = trab.tipo_ausencia && trab.tipo_ausencia !== '' && trab.justificacion && trab.justificacion.trim() !== '';
              var justificacionIcono = tieneJustificacion ? 
                '<div style="position: absolute; top: -5px; right: -5px; background: #5cb85c; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">✓</div>' : 
                '';

              html += `
                            <div style="
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                animation: fadeIn 0.5s ease-out;
                                opacity: 0.8;
                                position: relative;
                            " onmouseover="
                                this.style.transform='scale(1.12)';
                                this.style.opacity='1';
                                this.querySelector('img').style.boxShadow='0 8px 20px rgba(217,83,79,0.4)';
                                this.querySelector('div:last-child').style.color='#d9534f';
                            " onmouseout="
                                this.style.transform='scale(1)';
                                this.style.opacity='0.8';
                                this.querySelector('img').style.boxShadow='0 3px 10px rgba(0,0,0,0.1)';
                                this.querySelector('div:last-child').style.color='#666';
                            " onclick="editarAsistencia(${JSON.stringify(trab).replace(/"/g, '&quot;')});">
                                ${justificacionIcono}
                                <img src="${fotoUrl}" style="
                                    width: 70px;
                                    height: 70px;
                                    border-radius: 50%;
                                    border: 3px solid #d9534f;
                                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                                    transition: all 0.3s ease;
                                    object-fit: cover;
                                    background: #f5f5f5;
                                " onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23d9534f%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E${iniciales.toUpperCase()}%3C/text%3E%3C/svg%3E'" />
                                <div style="
                                    font-size: 11px;
                                    color: #666;
                                    margin-top: 8px;
                                    text-align: center;
                                    max-width: 75px;
                                    word-break: break-word;
                                    line-height: 1.3;
                                    font-weight: 500;
                                    transition: all 0.3s ease;
                                ">${escapeHtml(
                nombreCompleto.substring(0, 20)
              )}</div>
                            </div>
                            `;
            });
          }
        });
        $("#modal-ausentes-content").html(html);
      } else {
        $("#modal-ausentes-content").html(
          '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;"><i class="fa fa-smile-o fa-2x"></i><p>¡Todos presentes hoy! 🎉</p></div>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
      $("#modal-ausentes-content").html(
        '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #d9534f;"><i class="fa fa-warning fa-2x"></i><p>Error al cargar ausentes</p></div>'
      );
    },
  });
}

// Variable global para rastrear el estado del zoom de presentes - YA DECLARADA ARRIBA
function mostrarPreviewPresente(foto, nombre, ubicacion, departamento) {
  previewPresente.foto = foto;
  previewPresente.nombre = nombre;
  previewPresente.ubicacion = ubicacion || "";
  previewPresente.departamento = departamento || "";
  previewPresente.zoomed = false;

  $("#preview-presente").show();
  $("#preview-presente-img").attr("src", foto);
  $("#preview-presente-nombre").text(nombre);

  // Mostrar información adicional si existe
  let infoExtra = "";
  if (ubicacion)
    infoExtra +=
      '<div style="color: #666; font-size: 12px; margin-top: 5px;"><i class="fa fa-map-marker"></i> ' +
      ubicacion +
      "</div>";
  if (departamento)
    infoExtra +=
      '<div style="color: #666; font-size: 12px; margin-top: 3px;"><i class="fa fa-briefcase"></i> ' +
      departamento +
      "</div>";

  $("#preview-presente-info").html(infoExtra);

  // Hacer la imagen clickeable para zoom
  $("#preview-presente-img")
    .css("cursor", "pointer")
    .off("click")
    .on("click", function () {
      toggleZoomPresente();
    });
}

function toggleZoomPresente() {
  previewPresente.zoomed = !previewPresente.zoomed;
  const img = $("#preview-presente-img");

  if (previewPresente.zoomed) {
    img.css({
      "max-width": "400px",
      "max-height": "400px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 8px 20px rgba(0,0,0,0.3))",
    });
  } else {
    img.css({
      "max-width": "200px",
      "max-height": "200px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 5px 15px rgba(0,0,0,0.2))",
    });
  }
}

// Variable global para rastrear el estado del zoom de ausentes - YA DECLARADA
function mostrarPreviewAusente(foto, nombre, ubicacion, departamento) {
  previewAusente.foto = foto;
  previewAusente.nombre = nombre;
  previewAusente.zoomed = false;

  $("#preview-ausente").show();
  $("#preview-ausente-img").attr("src", foto);
  $("#preview-ausente-nombre").text(nombre);

  // Mostrar información adicional si existe
  let infoExtra = "";
  if (ubicacion)
    infoExtra +=
      '<div style="color: #666; font-size: 12px; margin-top: 5px;"><i class="fa fa-map-marker"></i> ' +
      ubicacion +
      "</div>";
  if (departamento)
    infoExtra +=
      '<div style="color: #666; font-size: 12px; margin-top: 3px;"><i class="fa fa-briefcase"></i> ' +
      departamento +
      "</div>";

  $("#preview-ausente-info").html(infoExtra);

  // Hacer la imagen clickeable para zoom
  $("#preview-ausente-img")
    .css("cursor", "pointer")
    .off("click")
    .on("click", function () {
      toggleZoomAusente();
    });
}

function toggleZoomAusente() {
  previewAusente.zoomed = !previewAusente.zoomed;
  const img = $("#preview-ausente-img");

  if (previewAusente.zoomed) {
    img.css({
      "max-width": "400px",
      "max-height": "400px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 8px 20px rgba(0,0,0,0.3))",
    });
  } else {
    img.css({
      "max-width": "200px",
      "max-height": "200px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 5px 15px rgba(0,0,0,0.2))",
    });
  }
}

// Función para mostrar preview de especiales
function mostrarPreviewEspecial(id, nombre, cargo, departamento, fotoUrl) {
  document.getElementById("preview-especial").style.display = "block";
  document.getElementById("preview-especial-img").src = fotoUrl;
  document.getElementById("preview-especial-nombre").textContent = nombre;
  document.getElementById("preview-especial-info").innerHTML = `
    <strong>Cargo:</strong> ${cargo}<br>
    <strong>Departamento:</strong> ${departamento}<br>
    <strong>Estado:</strong> <span style="color: #9b59b6; font-weight: bold;">Horario Especial</span><br>
    <strong>Tipo:</strong> Trabajador con horario especial (tipo 3)
  `;
}

// Función para toggle zoom de especiales
function toggleZoomEspecial() {
  previewEspecial.zoomed = !previewEspecial.zoomed;
  const img = $("#preview-especial-img");

  if (previewEspecial.zoomed) {
    img.css({
      "max-width": "400px",
      "max-height": "400px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 8px 20px rgba(155,89,182,0.3))",
    });
  } else {
    img.css({
      "max-width": "200px",
      "max-height": "200px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 5px 15px rgba(155,89,182,0.25))",
    });
  }
}

// Función para mostrar preview de vacaciones
function mostrarPreviewVacacion(id, nombre, cargo, departamento, fotoUrl) {
  document.getElementById("preview-vacacion").style.display = "block";
  document.getElementById("preview-vacacion-img").src = fotoUrl;
  document.getElementById("preview-vacacion-nombre").textContent = nombre;
  document.getElementById("preview-vacacion-info").innerHTML = `
    <strong>Cargo:</strong> ${cargo}<br>
    <strong>Departamento:</strong> ${departamento}<br>
    <strong>Estado:</strong> <span style="color: #f0ad4e; font-weight: bold;">De Vacaciones</span><br>
    <strong>Período:</strong> Vacaciones aprobadas
  `;
}

// Función para toggle zoom de vacaciones
function toggleZoomVacacion() {
  previewVacacion.zoomed = !previewVacacion.zoomed;
  const img = $("#preview-vacacion-img");

  if (previewVacacion.zoomed) {
    img.css({
      "max-width": "400px",
      "max-height": "400px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 8px 20px rgba(240,173,78,0.3))",
    });
  } else {
    img.css({
      "max-width": "200px",
      "max-height": "200px",
      transition: "all 0.3s ease",
      filter: "drop-shadow(0 5px 15px rgba(240,173,78,0.25))",
    });
  }
}

// ---------------------------
// Funciones para justificar ausencia
// ---------------------------

window.editarAsistencia = function (row) {
    console.log('Editando asistencia:', row);

    // Asegurarse de que row es un objeto
    if (typeof row === 'string') {
        try {
            row = JSON.parse(row.replace(/"/g, '"'));
        } catch (e) {
            console.error('Error al parsear datos de la fila:', e);
            return;
        }
    }

    // Mostrar el modal
    var $modal = $('#modalAsistencia');

    // Llenar el formulario con los datos
    $modal.find('#asistencia_id').val(row.registro_id || '');
    $modal.find('#trabajador_id').val(row.trabajador_id || '');
    $modal.find('#fecha').val(row.fecha || new Date().toISOString().split('T')[0]);
    $modal.find('#hora_entrada').val(row.hora_entrada || '');
    $modal.find('#hora_salida').val(row.hora_salida || '');
    
    // Mostrar foto y nombre del trabajador
    var nombreCompleto = (row.nombre || '') + ' ' + (row.apellidos || '');
    var fotoUrl = '';
    if (row.foto) {
        fotoUrl = row.foto.startsWith('data:') ? row.foto : 'data:image/jpeg;base64,' + row.foto;
    } else {
        // Generar avatar con iniciales si no hay foto
        var iniciales = ((row.nombre && row.nombre.charAt(0)) || 'T') + ((row.apellidos && row.apellidos.charAt(0)) || 'T');
        fotoUrl = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23d9534f%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2236%22 font-weight=%22bold%22 font-family=%22Arial%22%3E' + iniciales.toUpperCase() + '%3C/text%3E%3C/svg%3E';
    }
    
    $('#trabajador-foto').attr('src', fotoUrl);
    $('#trabajador-nombre').text(nombreCompleto);
    $('#trabajador-info').text((row.departamento || '') + ' - ' + (row.ubicacion || ''));
    
    $modal.find('#tipo_ausencia').val(row.tipo_ausencia || '');
    $modal.find('#justificacion').val(row.justificacion || '');

    // Actualizar título del modal
    $modal.find('.modal-title').text('Justificar Ausencia');

    // Mostrar el modal
    $modal.modal('show');
};

// Eventos para el modal de asistencia
$(document).ready(function() {
    // Guardar asistencia
    $('#btn-guardar-asistencia').click(function () {
        var formData = $('#formAsistencia').serialize();
        var method = 'save';

        $.ajax({
            url: 'api-app.php?module=asistencias&method=' + method,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log('Respuesta del servidor:', response);
                if (response.status == 1) {
                    $('#modalAsistencia').modal('hide');
                    // Recargar los datos de ausentes
                    cargarFotosAusentes();
                    // Mostrar mensaje de éxito
                    alert('Ausencia justificada correctamente');
                } else {
                    alert('Error: ' + (response.message || 'No se pudo guardar la asistencia'));
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                alert('Error de conexión al guardar la asistencia');
            }
        });
    });
});
