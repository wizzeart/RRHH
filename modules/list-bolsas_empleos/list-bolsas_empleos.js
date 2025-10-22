$(document).ready(function () {
    $('#btn-add-new').click(function () {
        location.href = 'index.php?module=bolsas_empleos';
    });
    $('#table-panel').on('click', '.toggle-status', function () {
        var value = '';
        if ($(this).hasClass('fa-check') == true) {
            $(this).removeClass('btn-success');
            $(this).addClass('btn-danger');
            $(this).removeClass('fa-check');
            $(this).addClass('fa-remove');
            value = 'N';
        } else {
            $(this).removeClass('btn-danger');
            $(this).addClass('btn-success');
            $(this).removeClass('fa-remove');
            $(this).addClass('fa-check');
            value = 'S';
        }
        var cmd = 'module=bolsas_empleoscheckedid=' + $(this).data('id') + '&value=' + value;
        $.ajax({url: 'api-app.php', type: 'GET', data: cmd, dataType: 'json',
            success: function (d) {
            }
        });
    });

    // Handler for curriculum download/view button
    $('#table-panel').on('click', '.fa.fa-download', function () {
        var $tr = $(this).closest('tr');
        var idx = $tr.data('index');
        var allData = $('#table-panel').bootstrapTable('getData');
        var rowData;
        
        if (typeof idx !== 'undefined' && allData && allData[idx]) {
            rowData = allData[idx];
        } else {
            var id = $(this).data('id');
            if (allData && allData.length) {
                rowData = allData.find(function (r) { return r.id == id; });
            }
        }
        
        if (!rowData || !rowData.curriculum || rowData.curriculum === '') {
            alert('No hay currículum disponible para esta postulación.');
            return;
        }
        
        // Abrir el currículum en una nueva ventana/pestaña
        window.open(rowData.curriculum, '_blank');
    });

    // Handler for contratar button -> redirect to trabajadores with prefilled params
    $('#table-panel').on('click', '.btn-contratar', function () {
        var $tr = $(this).closest('tr');
        var idx = $tr.data('index');
        var allData = $('#table-panel').bootstrapTable('getData');
        var rowData;
        if (typeof idx !== 'undefined' && allData && allData[idx]) {
            rowData = allData[idx];
        } else {
            var id = $(this).data('id');
            if (allData && allData.length) {
                rowData = allData.find(function (r) { return r.id == id; });
            }
        }
        if (!rowData) {
            alert('No se pudo obtener los datos de la postulación.');
            return;
        }
        var params = new URLSearchParams();
        params.set('module', 'trabajadores');
        params.set('b_id', rowData.id || '');
        params.set('b_nombre', rowData.nombre || '');
        params.set('b_apellidos', rowData.apellidos || '');
        params.set('b_segundos_apellidos', rowData.segundos_apellidos || '');
        params.set('b_ci', rowData.ci_bolsa_empleo || '');
        params.set('b_curriculum', rowData.curriculum || '');
        params.set('b_cargo_postulado_id', rowData.cargo_postulado_id || '');
        params.set('b_telefono', rowData.telefono || '');
        params.set('b_email', rowData.email || '');
        params.set('b_fecha_registro', rowData.fecha_registro || '');
        params.set('b_observaciones', rowData.observaciones || '');
        window.location.href = 'index.php?' + params.toString();
    });

    $('#table-panel').on('click', '.btn-del', function () {
        
                    
        var $tr = $(this).closest('tr');
        var idx = $tr.data('index');
        var allData = $('#table-panel').bootstrapTable('getData');
        var rowData;
        if (typeof idx !== 'undefined' && allData && allData[idx]) {
            rowData = allData[idx];
        } else {
            var id = $(this).data('id');
            if (allData && allData.length) {
                rowData = allData.find(function (r) { return r.id == id; });
            }
        }
        if (!rowData) {
            alert('No se pudo obtener los datos de la postulación.');
            return;
        }
        
        if (!confirm('¿Está seguro de eliminar esta postulación?')) {
            return;
        }
        

        var params = new URLSearchParams();
        params.set('module', 'bolsas_empleos');
        params.set('id', rowData.id || '');
        params.set('method', 'del');
        params.set('row', idx || '');
        $.ajax({url: 'api-app.php', type: 'GET', data: params.toString(), dataType: 'json',
            success: function (d) {
                if (d.status == 1) {
                    $('#table-panel').bootstrapTable('refresh');
                    
                }
            }
        });
    });

});






// FORMAT COLUMN
// Use "data-formatter" on HTML to format the display of bootstrap table column.
// =================================================================


// Sample Format for Order Status Column.
// =================================================================
function formatoActivo(value, row) {
    if (value == 'S')
        return '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';
}

// Format for Curriculum Download Column.
// =================================================================
function formatoToolbar(value, row) {
    var curriculumAvailable = row.curriculum && row.curriculum !== '';
    var btnClass = curriculumAvailable ? 'btn-primary' : 'btn-secondary';
    var btnDisabled = curriculumAvailable ? '' : 'disabled';
    var title = curriculumAvailable ? 'Descargar/Ver Currículum' : 'Sin currículum';

    var s = '';
    s += '<button data-id="' + row.id + '" class="btn ' + btnClass + ' btn-icon icon-sm fa fa-download" ' + btnDisabled + ' title="' + title + '"></button> ';
    s += '<button data-id="' + row.id + '" class="btn btn-success btn-icon icon-sm fa fa-briefcase btn-contratar" title="Contratar"></button>';
    s += '<button data-id="' + row.id + '" class="btn btn-danger btn-icon icon-sm fa fa-trash btn-del" title="Eliminar"></button>';

    return s;
}

function imageFormatter(value, row) {
    return '<image style="width:30px" src="/img/modelos/' + row.image + '" class="img-responsive"/>';
}

function formatoObservaciones(value, row) {
     if (value == 'S')
        return '<button data-id="' + row.xusuario_id + '" class="btn btn-success btn-icon icon-sm fa fa-check toggle-status"></button>';
    else
        return '<button data-id="' + row.xusuario_id + '" class="btn btn-danger btn-icon icon-sm fa fa-remove toggle-status"></button>';

}
        
