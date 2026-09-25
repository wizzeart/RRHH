$(document).ready(function () {
    var $table = $('#table-cumpleanos');
    var allData = []; // Store all data

    // Formatter para la columna de días para cumpleaños
    window.diasCumpleanosFormatter = function (value, row, index) {
        if (row.es_hoy) {
            const confetti = new JSConfetti()
            confetti.addConfetti({
                emojis: ['🎉', '🎊', '⭐', '✨', '🎈', '🎁', '🌟', '💫'],
                emojiSize: 40,
                velocity: 50
            })

            return '<span style="background-color: #90EE90; padding: 5px 10px; border-radius: 3px; font-weight: bold; color: #006400;">¡Cumple Años Hoy!</span>';
        } else {
            return value + ' días';
        }
    };

    // Load cumpleaños data
    $table.on('load-success.bs.table', function (e, data) {
        console.log('Cumpleaños data loaded:', data);

        // Store all data
        allData = data;

        // Auto-filter by current month after data loads
        var currentMonth = new Date().getMonth() + 1;
        var filteredData = data.filter(function (row) {
            return row.mes == currentMonth;
        });
        $table.bootstrapTable('load', filteredData);

        // Update the select value
        $('#filterMesCumpleanos').val(currentMonth);
    });

    // Month filter button
    $('#btn-filter-cumpleanos').on('click', function () {
        var mes = $('#filterMesCumpleanos').val();
        if (mes) {
            var filteredData = allData.filter(function (row) {
                return parseInt(row.mes) === parseInt(mes);
            });
            $table.bootstrapTable('load', filteredData);
        } else {
            $table.bootstrapTable('load', allData);
        }
    });

    // Reset month filter
    $('#btn-reset-cumpleanos').on('click', function () {
        $('#filterMesCumpleanos').val('');
        $table.bootstrapTable('load', allData);
    });



    // Auto-filter on change
    $('#filterMesCumpleanos').on('change', function () {
        var mes = $(this).val();
        if (mes) {
            var filteredData = allData.filter(function (row) {
                return parseInt(row.mes) === parseInt(mes);
            });
            $table.bootstrapTable('load', filteredData);
        } else {
            $table.bootstrapTable('load', allData);
        }
    });
});

// Formatter for clickable name
function formatoNombreCompleto(value, row) {
    var id = row.trabajador_id || row.id;
    var nombreCompleto = (row.nombre || '') + ' ' + (row.apellidos || '');
    if (value && !nombreCompleto.trim()) { nombreCompleto = value; }

    // Si value ya es el nombre completo
    if (!row.nombre && !row.apellidos && value) {
        nombreCompleto = value;
    }

    if (id) {
        return '<a href="index.php?module=ficha-trabajador&id=' + id + '" style="cursor: pointer; color: inherit; text-decoration: none;" onmouseover="this.style.textDecoration=\'underline\'; this.style.color=\'#337ab7\';" onmouseout="this.style.textDecoration=\'none\'; this.style.color=\'inherit\';">' + nombreCompleto + '</a>';
    }
    return nombreCompleto;
}
