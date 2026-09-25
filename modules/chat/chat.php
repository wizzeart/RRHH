<?php
// Chat module UI (include this file where you want the chat box displayed)
?>
<style>
    /* THEME 2: Custodios (Orange/Gold) - #ebaa4b */
    .empresa-theme-2 .panel-primary {
        border-color: #ebaa4b !important;
    }

    .empresa-theme-2 .panel-primary>.panel-heading {
        background-color: #ebaa4b !important;
        border-color: #ebaa4b !important;
    }

    .empresa-theme-2 .btn-primary {
        background-color: #ebaa4b !important;
        border-color: #ebaa4b !important;
    }

    .empresa-theme-2 .btn-primary:hover,
    .empresa-theme-2 .btn-primary:focus {
        background-color: #d99a3a !important;
        border-color: #d99a3a !important;
    }

    .empresa-theme-2 tr.table-primary td {
        background-color: rgba(235, 170, 75, 0.1) !important;
    }

    /* THEME 3: S24 (Green) - #8dba60 */
    .empresa-theme-3 .panel-primary {
        border-color: #8dba60 !important;
    }

    .empresa-theme-3 .panel-primary>.panel-heading {
        background-color: #8dba60 !important;
        border-color: #8dba60 !important;
    }

    .empresa-theme-3 .btn-primary {
        background-color: #8dba60 !important;
        border-color: #8dba60 !important;
    }

    .empresa-theme-3 .btn-primary:hover,
    .empresa-theme-3 .btn-primary:focus {
        background-color: #7ca850 !important;
        border-color: #7ca850 !important;
    }

    .empresa-theme-3 tr.table-primary td {
        background-color: rgba(141, 186, 96, 0.1) !important;
    }
</style>
<div class="row" style="margin-top:20px;" id="chat-module-root">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="panel-title" style="margin: 0;">Chat Interno</h3>
                    <button type="button" class="btn btn-sm btn-default" onclick="history.back();">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <div
                    style="position:static; max-height: 400px; background: #fff; border-radius: 8px; display: flex; flex-direction: column;">
                    <!-- Área de mensajes: la cabecera queda fija y el cuerpo hace scroll -->
                    <div style="padding: 0 10px;">
                        <table class="table table-hover table-striped" style="margin-bottom: 0; width:100%;">
                            <thead style="position: sticky; top: 0; background: #f8f8f8; z-index: 2;">
                                <tr>
                                    <th style="width: 15%">Usuario</th>
                                    <th style="width: 65%">Mensaje</th>
                                    <th class="text-center" style="width: 20%">Fecha</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 10px;">
                        <table class="table table-hover table-striped" style="margin-bottom: 0; width:100%;">
                            <tbody>
                                <!-- Los mensajes se cargarán aquí via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Formulario de envío -->
                    <form id="chat-form" class="mt-3"
                        style="padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px;">
                        <input type="text" id="mensaje-texto" class="form-control" maxlength="200"
                            placeholder="Escribe tu mensaje..." required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12" style="margin-top: 15px;">
        <div class="panel panel-info">
            <div class="panel-body" style="padding: 12px 15px;">
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <i class="fa fa-info-circle" style="font-size: 16px; color: #31708f; margin-top: 2px; flex-shrink: 0;"></i>
                    <div>
                        <p style="margin: 0 0 6px 0; font-weight: 600; font-size: 13px; color: #31708f;">Comunicación Privada con Recursos Humanos</p>
                        <p style="margin: 0; font-size: 12px; color: #555;">Este chat es solo de uso interno entre el trabajador y Recursos Humanos de la Empresa</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include the chat JS file -->
<script type="text/javascript" src="modules/chat/chat.js"></script>
<?php
// Expose current session user to JS for coloring
if (session_status() == PHP_SESSION_NONE)
    session_start();
$cu = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
?>
<script type="text/javascript">
    window.CHAT_CURRENT_USER = <?php echo json_encode($cu); ?>;
</script>
<style>
    /* Small colored badge for user label */
    .chat-user-badge {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 8px;
        vertical-align: middle;
    }

    /* Highlight current user's messages */
    tr.table-primary td {
        background-color: rgba(47, 128, 237, 0.08) !important;
    }
</style>
</style>