<script src="https://cdn.jsdelivr.net/npm/handlebars@latest/dist/handlebars.min.js"></script>
<style>
    .bg-orange {
        background-color: #ff8c00 !important;
        color: white !important;
    }

    .bg-yellow {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }

    .text-orange {
        color: #ff8c00 !important;
    }

    .text-yellow {
        color: #ffc107 !important;
    }
</style>
<script>
    var mesActual = '<?php print($data_form['mes_actual']) ?>';
    var rol = '<?php print($app->rol) ?>';
</script>

<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="mes-selector">Mes</label>
                    <input type="month" id="mes-selector" class="form-control" value="<?php print($data_form['mes_actual']) ?>" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label" for="cargo-selector">Cargo</label>
                    <select id="cargo-selector" class="form-control">
                        <option value="all">Todos los cargos</option>
                        <?php foreach ($data_form['cargos'] as $cargo): ?>
                            <option value="<?php print($cargo['id']) ?>"><?php print(htmlspecialchars($cargo['nombre'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">&nbsp;</label><br>
                    <button id="btn-cargar" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> Cargar Evaluaciones
                    </button>
                    <button id="btn-exportar-excel" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Exportar Excel
                    </button>
                </div>
            </div>
        </div>

        <div id="alert-container"></div>

        <div id="tabla-container">
            <div class="text-center">
                <i class="fa fa-spinner fa-spin"></i> Cargando...
            </div>
        </div>

        <div id="paginacion-container">
            <!-- La paginación se insertará aquí dinámicamente -->
        </div>
    </div>
</div>

<!-- Template para la tabla de evaluaciones -->
<script id="tabla-evaluaciones-template" type="text/x-handlebars-template">
    <div class="table-responsive" style="overflow-x: auto;">
    <table class="table table-bordered table-striped table-hover" id="tabla-evaluaciones" style="white-space: nowrap;">
        <thead class="thead-dark">
            <tr>
                <th rowspan="2" class="text-center" style="vertical-align: middle; width: 150px; max-width: 150px; padding: 4px;">Trabajador</th>
                {{#each aspectos}}
                <th colspan="{{add subaspectos_count 1}}" class="text-center">{{nombre}}</th>
                {{/each}}
            </tr>
            <tr>
                {{#each aspectos}}
                {{#each subaspectos}}
                <th class="text-center" style="font-size: 0.75em; width: 80px; max-width: 80px; padding: 4px; white-space: normal;">
                    {{descripcion}}<br>
                    <small class="text-muted">(Max: {{calificacion_max}})</small>
                </th>
                {{/each}}
                <th class="text-center" style="font-size: 0.8em; background-color: #17a2b8; color: white; width: 70px; max-width: 70px; padding: 4px;">
                    Total<br>
                    <small>(Max: {{calificacion_max}})</small>
                </th>
                {{/each}}
                <th rowspan="2" class="text-center" style="vertical-align: middle; background-color: #28a745; color: white; width: 100px; max-width: 100px; padding: 4px;">Evaluación Final</th>
            </tr>
        </thead>
        <tbody>
            {{#each data}}
            <tr data-trabajador-id="{{trabajador_id}}">
                <td style="padding: 4px; max-width: none; overflow: visible; display: flex; align-items: center; gap: 16px;">
                    {{#if foto}}
                    <img src="{{foto}}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; flex-shrink: 0;" class="img-thumbnail" alt="Foto del trabajador">
                    {{else}}
                    <div style="width: 40px; height: 40px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; flex-shrink: 0;" class="img-thumbnail">
                        <i class="fa fa-user" style="font-size: 20px; color: #999;"></i>
                    </div>
                    {{/if}}
                    <a href="?module=ficha-trabajador&id={{trabajador_id}}" style="color: inherit; text-decoration: none; flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'">
                        <strong>{{trabajador_nombre}}</strong>
                    </a>
                </td>
                {{#each aspectos_agrupados}}
                {{#each subaspectos}}
                <td class="text-center" style="padding: 4px;">
                    {{#if ../../../es_mes_actual}}
                    <input type="number" 
                           class="form-control input-calificacion" 
                           min="0" 
                           max="{{calificacion_max}}" 
                           value="{{calificacion}}" 
                           data-trabajador-id="{{trabajador_id}}"
                           data-subaspecto-id="{{subaspecto_id}}"
                           data-aspecto-id="{{../aspecto_id}}"
                           data-calificacion-max="{{calificacion_max}}"
                           placeholder="-"
                           style="width: 60px; margin: 0 auto; padding: 2px; font-size: 0.85em;">
                    {{else}}
                    {{#if calificacion}}
                    <div class="badge {{color_porcentaje calificacion calificacion_max}}" style="font-size: 0.8em; padding: 2px 6px;">
                        {{calificacion}}
                    </div>
                    {{else}}
                    <span class="badge badge-secondary" style="font-size: 0.8em;">-</span>
                    {{/if}}
                    {{/if}}
                </td>
                {{/each}}
                <td class="text-center aspecto-total" data-aspecto-id="{{aspecto_id}}" data-calificacion-max="{{calificacion_max}}" style="font-weight: bold;">
                    {{#if total_calificaciones}}
                    <div class="badge total-aspecto-badge {{color_total_aspecto total_calificaciones calificacion_max}}" style="font-size: 0.9em; padding: 4px 8px;">
                        {{total_calificaciones}}
                    </div>
                    {{else}}
                    <span class="badge badge-secondary total-aspecto-badge" style="font-size: 0.9em;">-</span>
                    {{/if}}
                </td>
                {{/each}}
                <td class="text-center evaluacion-final {{color_evaluacion_final this}}" style="font-weight: bold; font-size: 1.1em;">
                    <div class="evaluacion-final-num">{{evaluacion_final this}}</div>
                    <small class="evaluacion-final-text">{{texto_evaluacion_final this}}</small>
                </td>
            </tr>
            {{/each}}
        </tbody>
    </table>
</div>

{{#unless es_mes_actual}}
<div class="alert alert-info">
    <i class="fa fa-info-circle"></i> 
    <strong>Modo de solo lectura:</strong> Estás viendo las evaluaciones del mes {{mes}}. 
    Solo se pueden editar las evaluaciones del mes actual.
</div>
{{/unless}}

{{#if es_mes_actual}}
<div class="alert alert-success">
    <i class="fa fa-edit"></i> 
    <strong>Modo de edición:</strong> Puedes editar las calificaciones del mes actual. 
    Las calificaciones deben estar entre 0 y 100.
</div>
{{/if}}
</script>