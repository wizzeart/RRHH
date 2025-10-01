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
                    <!-- Área de mensajes -->
                    <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 10px;">
                        <table class="table table-hover table-striped" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Usuario</th>
                                    <th style="width: 65%;">Mensaje</th>
                                    <th style="width: 20%;">Fecha</th>
                                </tr>
                            </thead>
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
