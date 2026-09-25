<?php
// Validar sesión antes de mostrar contenido
if (!isset($app->user_id) || empty($app->user_id)) {
    echo '<div class="alert alert-danger">Sesión no válida. Por favor, recargue la página.</div>';
    return;
}
?>

<style>
    /* THEME 2: Custodios (Orange/Gold) - #ebaa4b */
    .empresa-theme-2 .panel-primary {
        border-color: #ebaa4b !important;
    }
    .empresa-theme-2 .panel-primary > .panel-heading {
        background-color: #ebaa4b !important;
        border-color: #ebaa4b !important;
    }
    .empresa-theme-2 .btn-success {
        background-color: #ebaa4b !important;
        border-color: #ebaa4b !important;
    }
    .empresa-theme-2 .btn-success:hover, 
    .empresa-theme-2 .btn-success:focus, 
    .empresa-theme-2 .btn-success:active {
        background-color: #d99a3a !important; /* Darker shade */
        border-color: #d99a3a !important;
    }
    .empresa-theme-2 .text-primary {
        color: #ebaa4b !important;
    }

    /* THEME 3: S24 (Green) - #8dba60 */
    .empresa-theme-3 .panel-primary {
        border-color: #8dba60 !important;
    }
    .empresa-theme-3 .panel-primary > .panel-heading {
        background-color: #8dba60 !important;
        border-color: #8dba60 !important;
    }
    .empresa-theme-3 .btn-success {
        background-color: #8dba60 !important;
        border-color: #8dba60 !important;
    }
    .empresa-theme-3 .btn-success:hover, 
    .empresa-theme-3 .btn-success:focus, 
    .empresa-theme-3 .btn-success:active {
        background-color: #7ca850 !important; /* Darker shade */
        border-color: #7ca850 !important;
    }
    .empresa-theme-3 .text-primary {
        color: #8dba60 !important;
    }
</style>

<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-key"></i> Cambiar Contraseña</h3>
            </div>
            <div class="panel-body">

                <div id="alert-zone"></div>

                <form id="frmChangePassword" class="form-horizontal" autocomplete="off">
                    <!-- User ID oculto -->
                    <input type="hidden" name="user_id" value="<?php echo $app->user_id; ?>">

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Usuario Conectado</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">
                                <strong><?php echo isset($_SESSION['gname']) ? $_SESSION['gname'] : 'Usuario'; ?></strong>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="passwordActual" class="col-sm-4 control-label">Contraseña Actual <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" id="passwordActual" name="passwordActual"
                                placeholder="Escribe tu contraseña actual" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="passwordNueva" class="col-sm-4 control-label">Nueva Contraseña <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" id="passwordNueva" name="passwordNueva"
                                placeholder="Mínimo 8 caracteres" required>
                            <span id="help-password" class="help-block text-muted" style="font-size: 0.9em;">
                                <i class="fa fa-info-circle"></i> Debe tener al menos 8 caracteres.
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="passwordConfirma" class="col-sm-4 control-label">Confirmar Nueva <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" id="passwordConfirma" name="passwordConfirma"
                                placeholder="Repite la nueva contraseña" required>
                            <span id="match-status" class="help-block" style="display:none;"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-success" id="btnSave">
                                <i class="fa fa-save"></i> Guardar Nueva Contraseña
                            </button>
                            <a href="?module=home" class="btn btn-default">
                                <i class="fa fa-reply"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        if (typeof jQuery === 'undefined') {
            if (document.getElementById('alert-zone')) {
                document.getElementById('alert-zone').innerHTML = '<div class="alert alert-danger">Error: jQuery no cargado.</div>';
            }
            return;
        }
        var $ = jQuery;
        $(document).ready(function () {

            // Referencias DOM
            const $form = $('#frmChangePassword');
            const $pwdActual = $('#passwordActual');
            const $pwdNueva = $('#passwordNueva');
            const $pwdConfirma = $('#passwordConfirma');
            const $btnSave = $('#btnSave');
            const $alertZone = $('#alert-zone');
            const $matchStatus = $('#match-status');

            // Helper para mostrar alertas
            function showAlert(type, msg) {
                let icon = type === 'success' ? 'fa-check' : 'fa-warning';
                let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

                // Usar Nifty Noty si está disponible, sino fallback a alerta bootstrap
                if (typeof $.niftyNoty === 'function') {
                    $.niftyNoty({
                        type: type,
                        container: 'page',
                        html: `<div class="media-left"><span class="icon-wrap icon-wrap-xs icon-circle alert-icon"><i class="fa ${icon} fa-lg"></i></span></div><div class="media-body"><h4 class="alert-title">${type.toUpperCase()}</h4><p class="alert-message">${msg}</p></div>`,
                        timer: 5000
                    });
                } else {
                    $alertZone.html(`
                <div class="alert ${alertClass} alert-dismissible fade in">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    <i class="fa ${icon}"></i> ${msg}
                </div>
            `);
                }
            }

            // Validación visual de coincidencia
            function checkMatch() {
                let val1 = $pwdNueva.val();
                let val2 = $pwdConfirma.val();

                if (val2 === '') {
                    $matchStatus.hide();
                    return;
                }

                if (val1 === val2) {
                    $matchStatus.html('<i class="fa fa-check text-success"></i> Las contraseñas coinciden').css('color', 'green').show();
                } else {
                    $matchStatus.html('<i class="fa fa-times text-danger"></i> No coinciden').css('color', 'red').show();
                }
            }

            $pwdNueva.on('keyup', checkMatch);
            $pwdConfirma.on('keyup', checkMatch);

            // Manejo del Submit
            $form.on('submit', function (e) {
                e.preventDefault();

                // 1. Validaciones Frontend
                let pActual = $pwdActual.val().trim();
                let pNueva = $pwdNueva.val().trim();
                let pConf = $pwdConfirma.val().trim();

                if (!pActual || !pNueva || !pConf) {
                    showAlert('danger', 'Por favor complete todos los campos.');
                    return;
                }

                if (pNueva.length < 8) {
                    showAlert('danger', 'La nueva contraseña es muy corta. Mínimo 8 caracteres.');
                    $pwdNueva.focus();
                    return;
                }

                if (pNueva !== pConf) {
                    showAlert('danger', 'Las contraseñas nuevas no coinciden.');
                    $pwdConfirma.focus();
                    return;
                }

                if (pActual === pNueva) {
                    showAlert('danger', 'La nueva contraseña debe ser diferente a la actual.');
                    return;
                }

                // 2. Envío AJAX
                let originalText = $btnSave.html();
                $btnSave.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

                $.ajax({
                    url: 'api-app.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        module: 'cambiar-contrasena',
                        method: 'cambiar', // Método que espera tu router backend
                        user_id: $('input[name="user_id"]').val(),
                        passwordActual: pActual,
                        passwordNueva: pNueva
                    },
                    success: function (resp) {
                        if (resp.status == 1) {
                            showAlert('success', resp.msg);
                            $form[0].reset();
                            $matchStatus.hide();

                            // Retraso para que el usuario lea el mensaje
                            setTimeout(function () {
                                // Opcional: Redirigir o simplemente dejar en la página limpia
                                window.location.href = '?module=home';
                            }, 2000);
                        } else {
                            showAlert('danger', resp.msg);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr);
                        let msg = 'Error de comunicación con el servidor.';
                        if (xhr.responseJSON && xhr.responseJSON.msg) {
                            msg = xhr.responseJSON.msg;
                        }
                        showAlert('danger', msg);
                    },
                    complete: function () {
                        $btnSave.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    });
</script>