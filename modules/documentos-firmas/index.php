<?php
require_once __DIR__ . '/../../includes/config.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Documentos y Firmas</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div class="container">
    <h1>Documentos y Firmas</h1>
    <p>Listado de documentos y acciones de firma.</p>
    <div id="alerts"></div>
    <table id="docs-table" border="1" style="width:100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Path</th>
          <th>Fecha modificado</th>
          <th>Firmado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <!-- Modal simple para firmar -->
    <div id="sign-modal" style="display:none; position:fixed; left:0; top:0; right:0; bottom:0; background: rgba(0,0,0,0.5);">
      <div style="background:#fff; width:400px; margin:80px auto; padding:16px; border-radius:6px;">
        <h3>Firmar documento</h3>
        <form id="sign-form">
          <input type="hidden" name="id" id="doc-id">
          <div>
            <label>Firmante:</label>
            <input type="text" name="signer" id="signer" required>
          </div>
          <div>
            <label>Razón / Nota:</label>
            <input type="text" name="reason" id="reason">
          </div>
          <div style="margin-top:8px; text-align:right;">
            <button type="button" id="btn-cancel">Cancelar</button>
            <button type="submit" id="btn-sign">Firmar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="/modules/documentos-firmas/documents.js"></script>
</body>
</html>
