<?php
// Chat module UI (include this file where you want the chat box displayed)
?>
<div class="row" style="margin-top:20px;" id="chat-module-root">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Chat Interno</h3>
            </div>
            <div class="panel-body">

                <div style="position:static; max-height: 400px; background: #fff; border-radius: 8px; display: flex; flex-direction: column;">
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
                    <form id="chat-form" class="mt-3" style="padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px;">
                        <input type="text" id="mensaje-texto" class="form-control" maxlength="200" placeholder="Escribe tu mensaje..." required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include the chat JS file -->
<script type="text/javascript" src="modules/chat/chat.js"></script>
<?php
// Expose current session user to JS for coloring
if (session_status() == PHP_SESSION_NONE) session_start();
$cu = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
?>
<script type="text/javascript">
    window.CHAT_CURRENT_USER = <?php echo json_encode($cu); ?>;
</script>
<style>
    /* Small colored badge for user label */
    .chat-user-badge {
        display:inline-block;
        width:12px;height:12px;border-radius:2px;margin-right:8px;vertical-align:middle;
    }
    /* Highlight current user's messages */
    tr.table-primary td {
        background-color: rgba(47, 128, 237, 0.08) !important;
    }
</style>
</style>
