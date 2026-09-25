<div class="panel">
<div class="panel-heading">
    <h3 class="panel-title"><?php print($page['subtitle']); ?></h3>
</div>
<div class="panel-body">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">Gestión de Prenómina</h4>
        </div>
        <div class="panel-body">
            <div class="row" style="margin-bottom:10px;">
                <div class="col-sm-2">
                    <label>Año</label>
                    <select id="prenom-year" class="form-control input-sm" disabled="disabled">
                        <?php $cy = intval(date('Y')); ?>
                        <option value="<?php echo $cy; ?>" selected><?php echo $cy; ?></option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label>Mes</label>
                    <select id="prenom-month" class="form-control input-sm">
                        <?php $cm = intval(date('n')); for($m=$cm; $m<=12; $m++){ ?>
                        <option value="<?php echo $m; ?>" <?php echo ($m==$cm?'selected':''); ?>><?php echo $m; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label>&nbsp;</label>
                    <button id="btn-prenom-save" class="btn btn-primary btn-sm btn-block"><i class="fa fa-save"></i> Guardar</button>
                </div>
                <div class="col-sm-2">
                    <label>&nbsp;</label>
                    <button id="btn-prenom-export" class="btn btn-success btn-sm btn-block"><i class="fa fa-file-excel-o"></i> Exportar Excel</button>
                </div>
            </div>
            <ul class="nav nav-tabs" id="tabs-prenomina"></ul>
            <br />
            <table 
                id="table-prenomina"
                data-toggle="table"
                data-url="api-app.php?module=prenomina&method=list-prenomina"
                data-search="true"
                data-show-refresh="true"
                data-show-toggle="false"
                data-show-columns="true"
                data-sort-name="expediente"
                data-page-list="[20, 50, 100]"
                data-page-size="50"
                data-pagination="true"
                data-unique-id="id"
                class="table table-striped">
                <thead>
                    <tr>
                        <!-- <th data-field="expediente" data-sortable="true" data-width="70">No. Exp</th> -->
                        <th data-field="nombre" data-sortable="true" data-width="180">Nombre</th>
                        <th data-field="ci" data-sortable="true" data-width="100">C.I</th>
                        <th data-field="horas" data-align="right" data-sortable="true" data-formatter="hoursInputFormatter" data-width="90">Horas</th>
                        <th data-field="tarifa" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="40">Tarifa/h</th>
                        <th data-field="a_cobrar" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="90">A Cobrar</th>
                        <th data-field="bonif" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="80">Bonif</th>
                        <th data-field="sal_dev" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="80">Sal. Dev</th>
                        <th data-field="vacaciones" data-align="right" data-sortable="true" data-formatter="number2Formatter" data-width="90">Vac.</th>
                        <th data-field="pago_vac" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="100">P. Vac.</th>
                        <th data-field="salario_neto" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="100">S. Neto</th>
                        <th data-field="seg_social" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="100">SNC225</th>
                        <th data-field="ing_pers_3" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="80">I.P. 3%</th>
                        <th data-field="ing_pers_5" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="80">I.P. 5%</th>
                        <th data-field="salario_pagar" data-align="right" data-sortable="true" data-formatter="currencyFormatter" data-width="100">A Pagar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

</div>
