<?php
/**
 * Prenómina 2 (IML) - Editor Profesional Tipo Spreadsheet
 * Interfaz ultra-interactiva estilo Google Sheets / Excel
 */

// Obtener nombre de la empresa activa
$empresa_nombre = '';
if (isset($app->empresa_id) && $app->empresa_id > 0) {
    $empresa_row = $app->db->fetchRow(
        "SELECT nombre FROM empresa WHERE id = :id LIMIT 1",
        ['id' => $app->empresa_id]
    );
    if ($empresa_row && isset($empresa_row['nombre'])) {
        $empresa_nombre = $empresa_row['nombre'];
    }
}
?>

<style>
    :root {
        --excel-green: #217346;
        --excel-dark-green: #10442a;
        --ribbon-bg: #f3f2f1;
        --border-color: #d4d4d4;
        --header-bg: #e6e6e6;
        --selection-border: #217346;
        --text-color: #262626;
    }

    body {
        font-family: 'Segoe UI', 'Calibri', sans-serif;
        background-color: #fff;
        color: var(--text-color);
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .prenom2-container {
        background: #fff;
        border: 1px solid var(--border-color);
        margin: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Excel Title Bar */
    .excel-title-bar {
        background: var(--excel-green);
        color: white;
        padding: 8px 15px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .excel-title-bar span {
        font-weight: 500;
    }

    /* Ribbon Interface */
    .prenom2-toolbar {
        background: var(--ribbon-bg);
        padding: 10px 15px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .prenom2-toolbar span {
        display: none; /* Hide old title */
    }

    /* Ribbon Buttons */
    .btn {
        background: transparent;
        border: 1px solid transparent;
        color: var(--text-color);
        padding: 6px 12px;
        font-family: 'Segoe UI', sans-serif;
        font-size: 13px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        border-radius: 3px;
    }

    .btn i {
        font-size: 16px;
        color: var(--excel-green);
    }

    .btn:hover {
        background: #c5c5c5;
        border-color: #b0b0b0;
    }
    
    #btn-prenom2-save {
        /* Highlight Save button slightly */
        background: rgba(33, 115, 70, 0.1);
        border: 1px solid rgba(33, 115, 70, 0.2);
    }

    /* Formula Bar */
    .excel-formula-bar {
        display: flex;
        align-items: center;
        padding: 5px 10px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        font-family: 'Segoe UI', sans-serif;
        font-size: 13px;
    }
    
    .formula-label {
        color: #666;
        font-style: italic;
        margin-right: 10px;
        font-weight: bold;
        font-family: 'Times New Roman', serif;
    }
    
    .formula-input {
        flex-grow: 1;
        border: none;
        outline: none;
        padding: 4px;
        color: var(--text-color);
    }

    /* Selects */
    select {
        background: #fff;
        border: 1px solid var(--border-color);
        padding: 4px 8px;
        font-family: 'Segoe UI', sans-serif;
        font-size: 13px;
        border-radius: 2px;
    }
    
    select:hover {
        border-color: var(--excel-green);
    }

    .prenom2-period-info {

        padding: 4px 10px;
        font-size: 13px;
        color: #666;
        margin-left: auto;
    }

    /* Search Bar */
    .prenom2-search-container {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: 20px;
    }

    .prenom2-search-input {
        background: white;
        border: 1px solid var(--border-color);
        padding: 6px 12px;
        border-radius: 4px;
        font-family: 'Segoe UI', sans-serif;
        font-size: 13px;
        width: 200px;
        color: var(--text-color);
    }

    .prenom2-search-input:focus {
        outline: none;
        border-color: var(--excel-green);
        box-shadow: 0 0 3px rgba(33, 115, 70, 0.2);
    }

    .prenom2-search-input::placeholder {
        color: #999;
    }

    .prenom2-search-clear {
        background: transparent;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 14px;
        padding: 4px 8px;
        display: none;
    }

    .prenom2-search-clear:hover {
        color: var(--text-color);
    }

    .prenom2-search-clear.show {
        display: block;
    }

    /* Alert Styles */
    .prenom2-alert {
        padding: 8px 12px;
        margin: 4px 10px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .prenom2-alert.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .prenom2-alert.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .prenom2-alert.info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .prenom2-alert.warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    /* Calendar Modal Styles */
    .calendar-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .calendar-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .calendar-modal-content {
        background: white;
        border-radius: 8px;
        padding: 20px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .calendar-modal-header {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--excel-green);
        padding-bottom: 10px;
        position: relative;
    }

    .calendar-modal-header h2 {
        margin: 0;
        color: var(--excel-green);
        font-size: 18px;
        text-align: center;
        flex: 1;
    }

    .calendar-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #999;
        cursor: pointer;
        position: absolute;
        right: 0;
        top: 0;
    }

    .calendar-modal-close:hover {
        color: #333;
    }

    .calendar-months-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 15px;
    }

    .calendar-month-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .calendar-month-item:hover {
        background: #f0f0f0;
        border-color: #999;
    }

    .calendar-month-info {
        flex: 1;
        cursor: default;
    }

    .calendar-month-title {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        text-transform: capitalize;
    }

    .calendar-month-count {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
    }

    .calendar-month-btn {
        flex: 1;
        background: #f3f2f1;
        border: 1px solid var(--border-color);
        padding: 12px;
        text-align: center;
        cursor: pointer;
        border-radius: 4px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .calendar-month-btn:hover {
        background: #e1e1e1;
        border-color: var(--excel-green);
    }

    .calendar-month-btn.available {
        background: #d4edda;
        border-color: #28a745;
        color: #155724;
        font-weight: 600;
    }

    .calendar-month-btn.available:hover {
        background: #c3e6cb;
    }

    .calendar-month-btn.disabled {
        background: #f8f9fa;
        border-color: #ccc;
        color: #999;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Columna de acciones del mes: el botón de Prenómina (verde), debajo el de Nómina
       (amarillo) para imprimir y firmar, y debajo el del DBF (azul) para el banco. */
    .calendar-month-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex-shrink: 0;
    }

    .calendar-month-download {
        background: #28a745;
        color: white;
        border: none;
        padding: 10px 14px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .calendar-month-download:hover {
        background: #218838;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .calendar-month-download:active {
        transform: scale(0.98);
    }

    .calendar-month-download-nomina {
        background: #ffc107;
        color: #212529;
        border: none;
        padding: 10px 14px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .calendar-month-download-nomina:hover {
        background: #e0a800;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .calendar-month-download-nomina:active {
        transform: scale(0.98);
    }

    .calendar-month-download-dbf {
        background: #0d6efd;
        color: white;
        border: none;
        padding: 10px 14px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .calendar-month-download-dbf:hover {
        background: #0b5ed7;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .calendar-month-download-dbf:active {
        transform: scale(0.98);
    }

    .calendar-month-download-dbf:disabled {
        opacity: 0.6;
        cursor: wait;
    }

    .calendar-loading {
        text-align: center;
        padding: 20px;
        color: #666;
    }

    .calendar-no-data {
        text-align: center;
        padding: 20px;
        color: #999;
        font-size: 14px;
    }

    /* Fixed Header Table Styles */
    .prenom2-table-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .prenom2-table-header {
        flex-shrink: 0;
        overflow: hidden;
        border-bottom: 2px solid var(--border-color);
    }

    .prenom2-table-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: auto;
    }

    .prenom2-spreadsheet {
        width: 100%;
        border-collapse: collapse;
        background: white;
        table-layout: fixed; /* Fixed width for better grid feel */
    }

    .prenom2-spreadsheet thead th {
        background: var(--header-bg);
        color: #444;
        font-weight: normal;
        padding: 4px 8px;
        border: 1px solid #c7c7c7;
        text-align: center;
        font-size: 13px;
        position: relative;
    }
    
    /* Column letters simulation */
    .prenom2-spreadsheet thead th::after {
        content: '';
        position: absolute;
        right: 0;
        top: 20%;
        height: 60%;
        width: 1px;
        background: #d4d4d4;
    }

    .prenom2-spreadsheet tbody tr {
        background: white;
    }

    .prenom2-spreadsheet tbody tr:hover {
        background: rgba(33, 115, 70, 0.05) !important;
    }

    .prenom2-spreadsheet td {
        padding: 4px 8px;
        border: 1px solid #d4d4d4;
        color: var(--text-color);
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Active Cell Simulation */
    .prenom2-spreadsheet td:focus-within {
        border: 2px solid var(--selection-border);
        outline: none;
        z-index: 10;
        position: relative;
    }

    /* Inputs */
    .prenom2-input {
        background: transparent;
        border: none;
        padding: 0;
        font-family: 'Segoe UI', sans-serif;
        font-size: 13px;
        text-align: right;
        width: 100%;
        outline: none;
    }

    /* Tabs (Sheet Tabs) */
    .prenom2-tabs {
        background: #f3f2f1;
        border-top: 1px solid var(--border-color);
        padding: 0 10px;
        display: flex;
        gap: 2px;
        margin-top: auto; /* Push to bottom */
    }

    .prenom2-tab {
        background: transparent;
        border: none;
        color: #444;
        padding: 6px 15px;
        font-size: 13px;
        cursor: pointer;
        position: relative;
    }

    .prenom2-tab.active {
        background: #fff;
        color: var(--excel-green);
        font-weight: 600;
        border-bottom: 3px solid var(--excel-green);
    }
    
    .prenom2-tab:hover {
        background: #e1e1e1;
    }

    /* Summary Cards (Status Bar / Bottom Summary) */
    .prenom2-summary {
        padding: 5px 20px;
        background: #f3f2f1;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 30px;
        align-items: center;
        font-size: 12px;
        color: #444;
    }

    .prenom2-summary-card {
        background: transparent;
        border: none;
        box-shadow: none;
        width: auto;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0;
    }
    
    .prenom2-summary-card::before, .prenom2-summary-card::after {
        display: none; /* Remove XP/Futuristic pseudo-elements */
    }

    .prenom2-summary-label {
        display: block;
        font-weight: normal;
        color: #666;
        text-transform: uppercase;
        font-size: 11px;
    }

    .prenom2-summary-value {
        font-family: 'Segoe UI', sans-serif;
        font-size: 14px;
        font-weight: 600;
        padding: 0;
        background: transparent;
        border: none;
        text-align: left;
        color: var(--text-color);
    }

    .prenom2-summary-card.income .prenom2-summary-value { color: var(--text-color); }
    .prenom2-summary-card.discount .prenom2-summary-value { color: var(--text-color); }
    .prenom2-summary-card.net .prenom2-summary-value { color: var(--text-color); }
    .prenom2-summary-card.final .prenom2-summary-value { color: var(--text-color); }
    
    /* Status Bar Right Side */
    .status-bar-right {
        margin-left: auto;
        display: flex;
        gap: 15px;
        color: #666;
    }

    /* Hide ID Column */
    .prenom2-spreadsheet th:first-child,
    .prenom2-spreadsheet td:first-child {
        display: none;
    }

    /* Month Title */
    .prenom2-month-title {
        background: #217346;
        color: white;
        padding: 12px 15px;
        font-size: 16px;
        font-weight: 600;
        text-align: center;
        border-bottom: 2px solid #10442a;
    }

    .prenom2-month-title i {
        margin-right: 8px;
        font-size: 18px;
    }

</style>

<div class="prenom2-container">
    <!-- EXCEL TITLE BAR -->
    <div class="excel-title-bar">
        <i class="fa fa-table"></i>
        <span>Prenómina</span>
    </div>

    <!-- MONTH TITLE -->
    <div class="prenom2-month-title">
        <i class="fa fa-calendar-o"></i>
        <span id="prenom2-month-title-text">Generando Prenómina...</span>
    </div>

    <!-- TOOLBAR (RIBBON) -->
    <div class="prenom2-toolbar">
        <button id="btn-prenom2-undo" class="btn" disabled style="opacity: 0.5;">
            <i class="fa fa-undo"></i> Deshacer
        </button>
        
        <button id="btn-prenom2-redo" class="btn" disabled style="opacity: 0.5;">
            <i class="fa fa-repeat"></i> Rehacer
        </button>
        
        <div style="width: 1px; height: 24px; background: #d4d4d4; margin: 0 10px;"></div>
        
        <button id="btn-prenom2-reload" class="btn">
            <i class="fa fa-refresh"></i> Recargar
        </button>
        
        <button id="btn-prenom2-recalc" class="btn">
            <i class="fa fa-calculator"></i> Recalcular
        </button>
        
        <button id="btn-prenom2-save-export" class="btn">
            <i class="fa fa-file-excel-o"></i> Guardar y Exportar
        </button>

        <button id="btn-prenom2-calendar" class="btn">
            <i class="fa fa-calendar"></i> Calendario
        </button>
        
        <button id="btn-prenom2-clear-cache" class="btn" title="Limpiar cache de ediciones">
            <i class="fa fa-trash"></i> Limpiar Cache
        </button>
        
        <div style="width: 1px; height: 24px; background: #d4d4d4; margin: 0 10px;"></div>
        
        <div class="prenom2-period-info" >
            <span id="prenom2-period-display">Cargando...</span>
        </div>

        <div class="prenom2-search-container">
            <i class="fa fa-search" style="color: #999; font-size: 14px;"></i>
            <input type="text" id="prenom2-search-input" class="prenom2-search-input" placeholder="Buscar empleado (nombre, C.I., expediente)...">
            <button id="prenom2-search-clear" class="prenom2-search-clear" title="Limpiar búsqueda">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    
    <!-- FORMULA BAR -->
    <div class="excel-formula-bar">
        <span class="formula-label">fx</span>
        <input type="text" class="formula-input" id="formula-input" value="" readonly placeholder="Select a cell...">
    </div>

    <!-- ALERTAS (Added back) -->
    <div id="prenom2-alerts" style="padding: 0 10px;"></div>

    <!-- TABS (Restored ID) -->
    <div class="prenom2-tabs" id="prenom2-tabs-container">
        <!-- Tabs injected by JS -->
    </div>

    <!-- TABLA -->
    <div style="flex-grow: 1; overflow: hidden; background: #e6e6e6; padding: 0; position: relative;" class="prenom2-table-wrapper">
        <!-- LOADING (Added back) -->
        <div id="prenom2-loading" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 100;">
            <i class="fa fa-spinner fa-spin"></i> Cargando datos...
        </div>

        <!-- Fixed Header -->
        <div class="prenom2-table-header">
            <table id="table-prenomina2-header" class="prenom2-spreadsheet">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">ID</th>
                        <th style="width: 70px;">C.I.</th>
                        <th style="width: 150px;">Nombre</th>
                        <th style="width: 70px; display: none;">Expediente</th>
                        <th style="width: 60px; text-align: right;">Tarifa</th>
                        <th style="width: 70px; background: #fff3cd; text-align: center;"><i class="fa fa-edit"></i> Horas</th>
                        <th style="width: 70px; background: #fff3cd; text-align: center;"><i class="fa fa-edit"></i> Bonif</th>
                        <th style="width: 70px; background: #fff3cd; text-align: center;"><i class="fa fa-edit"></i> Vac Días</th>
                        <th style="width: 80px; background: #fff3cd; text-align: center;"><i class="fa fa-edit"></i> Pago Vac</th>
                        <th style="width: 80px; text-align: right; background: #e7f3ff;">A Cobrar</th>
                        <th style="width: 80px; text-align: right; background: #e7f3ff;">Sal Dev</th>
                        <th style="width: 100px; text-align: right; background: #e7f3ff;">Sal Neto</th>
                        <th style="width: 80px; text-align: right; background: #ffe7e7;">Seg Social</th>
                        <th style="width: 70px; text-align: right; background: #ffe7e7;">IP 3%</th>
                        <th style="width: 70px; text-align: right; background: #ffe7e7;">IP 5%</th>
                        <th style="width: 120px; text-align: right; background: #e7ffe7; font-weight: bold;">SALARIO PAGAR</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="prenom2-table-body">
            <table id="table-prenomina2" class="prenom2-spreadsheet">
                <tbody id="prenom2-tbody">
                    <!-- Se genera dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

<script>
    // Expose current empresa_id to the module scripts if available
    window.AppEmpresaId = <?php echo (isset($app->empresa_id) ? intval($app->empresa_id) : 0); ?>;
</script>

    <!-- SUMMARY (STATUS BAR) -->
    <div class="prenom2-summary">
        <div class="prenom2-summary-card income">
            <div class="prenom2-summary-label">SUM: A Cobrar</div>
            <div class="prenom2-summary-value" id="total-a-cobrar">0.00</div>
        </div>
        
        <div class="prenom2-summary-card income">
            <div class="prenom2-summary-label">SUM: Bonif</div>
            <div class="prenom2-summary-value" id="total-bonif">0.00</div>
        </div>
        
        <div class="prenom2-summary-card net">
            <div class="prenom2-summary-label">SUM: Neto</div>
            <div class="prenom2-summary-value" id="total-neto">0.00</div>
        </div>
        
        <div class="prenom2-summary-card discount">
            <div class="prenom2-summary-label">SUM: Desc</div>
            <div class="prenom2-summary-value" id="total-descuentos">0.00</div>
        </div>
        
        <div class="prenom2-summary-card final">
            <div class="prenom2-summary-label">SUM: PAGAR</div>
            <div class="prenom2-summary-value" id="total-pagar">0.00</div>
        </div>
        
        <div class="prenom2-summary-card">
            <div class="prenom2-summary-label">COUNT:</div>
            <div class="prenom2-summary-value" id="total-empleados">0</div>
        </div>
        
        <div class="status-bar-right">
            <span>Average: --</span>
            <span>Count: --</span>
            <span>Ready</span>
        </div>
    </div>
</div>

<!-- CALENDAR MODAL -->
<div id="calendar-modal" class="calendar-modal">
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <h2><i class="fa fa-calendar"></i> Descargar Prenóminas Anteriores<?php echo ($empresa_nombre ? ' (' . htmlspecialchars($empresa_nombre) . ')' : ''); ?></h2>
            <button class="calendar-modal-close" id="calendar-modal-close">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div id="calendar-modal-body">
            <div class="calendar-loading">
                <i class="fa fa-spinner fa-spin"></i> Cargando disponibles...
            </div>
        </div>
    </div>
</div>

<script src="modules/prenomina-2/prenomina-2-pro.js"></script>

<script>
    // EXCEL MECHANICS
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Formula Bar Interaction
        const formulaInput = document.getElementById('formula-input');
        
        // Delegate click event on table to update formula bar
        document.getElementById('table-prenomina2').addEventListener('click', function(e) {
            const cell = e.target.closest('td');
            if (cell) {
                const row = cell.parentElement;
                const rowIndex = Array.from(row.parentElement.children).indexOf(row) + 1;
                const colIndex = Array.from(cell.parentElement.children).indexOf(cell);
                const colName = String.fromCharCode(65 + colIndex); // A, B, C...
                
                let value = cell.innerText;
                // If it's an input, get the value
                const input = cell.querySelector('input');
                if (input) value = input.value;
                
                formulaInput.value = value;
                formulaInput.placeholder = `${colName}${rowIndex}`;
            }
        });

        // 2. Simple Status Bar Updates (Count / Average)
        // Hook into the existing updateTotales function via a MutationObserver on the total fields
        // because we can't easily overwrite the internal function without reloading the JS file.
        // Or we can just rely on the existing totals which update the DOM.
        
        // Let's add a simple hover effect for rows to highlight the row number (if we had one)
        
    });
</script>
