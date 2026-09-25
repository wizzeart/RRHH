<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['guser_id'])) {
    header('Location: ../../login.html');
    exit;
}

header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow', true);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <title>Manual De Sistema de Recursos Humanos Allnovu</title>
  <style>
    :root{
      --azul:#16255c;--azul-claro:#287fc0;--celeste:#19b8df;--crema:#fbf7ef;
      --papel:#f5f7fa;--blanco:#fff;--texto:#24324a;--suave:#5f6f84;
      --linea:#d9e2ec;--verde:#568a45;--verde-fondo:#eef7e9;
      --amarillo:#9b6815;--amarillo-fondo:#fff6df;--rojo:#a94442;--rojo-fondo:#fff0ed;
      --sombra:0 14px 38px rgba(22,37,92,.10);--radio:18px;
    }
    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{margin:0;background:var(--papel);color:var(--texto);font:17px/1.7 "Segoe UI",Tahoma,sans-serif}
    a{color:#126d9b;text-underline-offset:3px}
    strong{color:#142653}
    .lateral{position:fixed;inset:0 auto 0 0;width:295px;background:var(--azul);color:#fff;padding:25px 18px;overflow:auto;z-index:10}
    .marca{display:flex;gap:12px;align-items:center;padding:0 8px 19px;border-bottom:1px solid rgba(255,255,255,.16)}
    .marca img{width:62px;filter:brightness(0) invert(1)}
    .marca b{display:block;font-size:15px;line-height:1.25}.marca small{color:#c4d2eb;font-size:12px}
    .lateral nav{margin-top:17px}.lateral nav a{display:block;color:#d2def2;text-decoration:none;padding:8px 10px;border-radius:9px;font-size:13px;line-height:1.35}
    .lateral nav a:hover,.lateral nav a:focus{background:rgba(25,184,223,.17);color:#fff}
    .lateral .grupo{margin:18px 10px 5px;color:#77def4;font-size:10px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}
    main{margin-left:295px}
    .portada{min-height:700px;color:#fff;position:relative;overflow:hidden;background:linear-gradient(130deg,#111d49,#1c3973 62%,#117a9d)}
    .portada-contenido{display:grid;grid-template-columns:1fr 1.08fr;align-items:center;min-height:700px;gap:42px;padding:64px max(6vw,48px)}
    .portada h1{font-family:Georgia,"Times New Roman",serif;font-size:clamp(44px,5vw,74px);line-height:1.04;letter-spacing:-.03em;margin:16px 0 24px}
    .portada p{font-size:21px;color:#dce8fa;max-width:650px}
    .etiqueta{font-size:12px;font-weight:800;letter-spacing:.15em;text-transform:uppercase;color:#78e0f7}
    .portada-imagen{margin:0;border:8px solid rgba(255,255,255,.13);border-radius:26px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,.28);transform:rotate(1deg)}
    .portada-imagen img{width:100%;display:block}
    .pagina{max-width:1160px;margin:auto;padding:70px 50px}
    section{scroll-margin-top:24px;margin-bottom:88px}
    .encabezado{display:grid;grid-template-columns:78px 1fr;gap:22px;margin-bottom:28px;align-items:start}
    .numero{width:68px;height:68px;border-radius:50%;display:grid;place-items:center;background:#e4f6fb;color:#097da5;font:700 27px Georgia,serif}
    h2,h3,h4{color:var(--azul);line-height:1.22}
    h2{font:700 38px/1.12 Georgia,"Times New Roman",serif;margin:3px 0 8px;letter-spacing:-.02em}
    h3{font-size:24px;margin:34px 0 12px}h4{font-size:18px;margin:22px 0 8px}
    .bajada{font-size:19px;color:var(--suave);margin:0;max-width:800px}
    .tarjetas{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin:24px 0}
    .tarjeta{background:#fff;border:1px solid var(--linea);border-radius:var(--radio);padding:23px;box-shadow:0 5px 18px rgba(22,37,92,.045)}
    .tarjeta .icono{font-size:31px;line-height:1;margin-bottom:12px}.tarjeta h3,.tarjeta h4{margin-top:0}.tarjeta p:last-child{margin-bottom:0}
    .aviso{margin:22px 0;padding:19px 22px;border-left:6px solid var(--celeste);background:#eaf8fc;border-radius:0 14px 14px 0}
    .aviso.bien{border-color:#76a95e;background:var(--verde-fondo)}.aviso.atencion{border-color:#e1a73b;background:var(--amarillo-fondo)}.aviso.cuidado{border-color:#dd746a;background:var(--rojo-fondo)}
    .pasos{counter-reset:paso;list-style:none;padding:0;margin:20px 0}
    .pasos li{counter-increment:paso;position:relative;background:#fff;border:1px solid var(--linea);border-radius:14px;padding:17px 18px 17px 64px;margin:12px 0;min-height:58px}
    .pasos li:before{content:counter(paso);position:absolute;left:17px;top:15px;width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:var(--azul);color:#fff;font-weight:800}
    .revision{list-style:none;padding:0;margin:18px 0}.revision li{position:relative;padding:11px 12px 11px 43px;background:#fff;border-bottom:1px solid var(--linea)}.revision li:before{content:"✓";position:absolute;left:13px;top:8px;color:var(--verde);font-size:23px;font-weight:900}
    .figura{background:#fff;border:1px solid var(--linea);border-radius:22px;overflow:hidden;margin:28px 0;box-shadow:var(--sombra)}
    .figura img{width:100%;height:auto;display:block}.figura figcaption{padding:13px 18px;border-top:1px solid var(--linea);font-size:14px;color:var(--suave)}
    .botones{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin:22px 0}
    .boton-ejemplo{background:#fff;border:1px solid var(--linea);border-radius:14px;padding:17px 10px;text-align:center}.boton-ejemplo b{display:block;font-size:28px;margin-bottom:4px}.boton-ejemplo span{font-size:13px;color:var(--suave)}
    .flujo{display:flex;gap:9px;align-items:stretch;flex-wrap:wrap;margin:23px 0}.flujo div{flex:1;min-width:150px;background:#fff;border:1px solid var(--linea);border-top:5px solid var(--celeste);border-radius:13px;padding:15px;text-align:center;font-weight:750;color:var(--azul)}.flujo i{align-self:center;color:var(--celeste);font-style:normal;font-size:25px}
    .dos{display:grid;grid-template-columns:1fr 1fr;gap:20px}.tres{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border:1px solid var(--linea);border-radius:15px;overflow:hidden;margin:20px 0;font-size:15px}
    th{background:#eaf0f7;color:var(--azul);text-align:left;font-size:13px}th,td{padding:13px 15px;border-bottom:1px solid var(--linea);vertical-align:top}tr:last-child td{border-bottom:0}
    .pantalla{background:#fff;border:1px solid #cad7e5;border-radius:20px;overflow:hidden;box-shadow:var(--sombra);margin:26px 0}
    .pantalla-barra{background:#17275c;color:#fff;padding:11px 17px;font-size:13px}.pantalla-cuerpo{display:grid;grid-template-columns:180px 1fr;min-height:310px}.pantalla-menu{background:#203470;color:#d8e4f8;padding:19px 14px}.pantalla-menu span{display:block;padding:8px;border-radius:8px;font-size:13px}.pantalla-menu .activo{background:#1aaad2;color:#fff}.pantalla-area{padding:24px;background:#f3f6fa}.pantalla-area h3{margin-top:0}.resumenes{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.resumen{padding:16px;background:#fff;border-radius:11px;border-left:5px solid var(--celeste)}.calendario{margin-top:14px;background:#fff;border-radius:11px;padding:16px;min-height:110px}
    .problema{display:grid;grid-template-columns:190px 1fr;gap:18px;background:#fff;border:1px solid var(--linea);border-radius:14px;padding:18px;margin:12px 0}.problema b{color:var(--azul)}
    .pie{background:#101a42;color:#c2cee7;padding:50px max(6vw,48px)}.pie strong{color:#fff}.pie-contenido{display:grid;grid-template-columns:2fr 1fr;gap:30px}
    .solo-impresion{display:none}
    @media(max-width:1000px){.lateral{position:relative;width:auto}.lateral nav{columns:2}main{margin-left:0}.portada-contenido{grid-template-columns:1fr;min-height:auto}.portada{min-height:auto}.portada-imagen{max-width:760px}.tarjetas{grid-template-columns:1fr 1fr}.botones{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:650px){body{font-size:16px}.pagina{padding:48px 18px}.lateral nav{columns:1}.portada-contenido{padding:45px 22px}.portada h1{font-size:42px}.encabezado{grid-template-columns:1fr}.tarjetas,.dos,.tres{grid-template-columns:1fr}.botones{grid-template-columns:1fr 1fr}.pantalla-cuerpo{grid-template-columns:1fr}.pantalla-menu{display:none}.problema{grid-template-columns:1fr}.pie-contenido{grid-template-columns:1fr}}
    @media print{@page{size:A4;margin:14mm}.lateral{display:none}main{margin:0}.portada{min-height:265mm;break-after:page;background:#16255c!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.portada-contenido{display:block;padding:30mm 10mm}.portada-imagen{margin-top:25mm}.pagina{max-width:none;padding:8mm 0}section{margin-bottom:18mm}.tarjeta,.aviso,.figura,.pasos li,table,.problema{break-inside:avoid;box-shadow:none}.solo-impresion{display:block}h2{font-size:30px}.pie{break-before:page}}
  </style>
</head>
<body>
  <aside class="lateral">
    <div class="marca"><img src="../../img/logo.png" alt="Allnovu"><div><b>Manual de Recursos Humanos</b><small>Guía sencilla, paso a paso</small></div></div>
    <nav aria-label="Índice del manual">
      <div class="grupo">Primeros pasos</div>
      <a href="#antes">1 · Antes de comenzar</a><a href="#entrada">2 · Entrar al sistema</a><a href="#inicio">3 · Pantalla de inicio</a><a href="#accesos">4 · Tipos de acceso</a>
      <div class="grupo">Trabajo diario</div>
      <a href="#organizacion">5 · Cargos y áreas</a><a href="#trabajadores">6 · Trabajadores</a><a href="#ficha">7 · Ficha del trabajador</a><a href="#asistencia">8 · Asistencia</a><a href="#vacaciones">9 · Vacaciones</a><a href="#prenomina">10 · Preparación del pago</a>
      <div class="grupo">Otras tareas</div>
      <a href="#recursos">11 · Recursos entregados</a><a href="#evaluaciones">12 · Evaluaciones</a><a href="#bolsa">13 · Bolsa de empleo</a><a href="#mensajes">14 · Mensajes</a><a href="#documentos">15 · Contratos y documentos</a>
      <div class="grupo">Ayuda</div>
      <a href="#cierres">16 · Revisiones y reportes</a><a href="#problemas">17 · Problemas frecuentes</a>
    </nav>
  </aside>

  <main>
    <header class="portada">
      <div class="portada-contenido">
        <div><span class="etiqueta">Allnovu · Guía para el trabajo diario</span><h1>Manual De Sistema de Recursos Humanos Allnovu</h1><p>Explicado con palabras sencillas, imágenes y pasos cortos para realizar las tareas de Recursos Humanos con seguridad.</p><p class="solo-impresion">Edición para usuarias y usuarios del sistema.</p></div>
        <figure class="portada-imagen"><img src="ilustracion-bienvenida.png?v=2" alt="Dos trabajadoras de Recursos Humanos aprendiendo juntas a usar el sistema"></figure>
      </div>
    </header>

    <article class="pagina">
      <section id="antes">
        <div class="encabezado"><div class="numero">1</div><div><h2>Antes de comenzar</h2><p class="bajada">No necesita saber de informática. Solo lea con calma, revise los datos y guarde cuando esté segura.</p></div></div>
        <div class="tarjetas">
          <div class="tarjeta"><div class="icono">☝️</div><h3>Un clic a la vez</h3><p>Pulse una sola vez cada botón y espere unos segundos. Evite pulsarlo muchas veces seguidas.</p></div>
          <div class="tarjeta"><div class="icono">👀</div><h3>Revise antes de guardar</h3><p>Compruebe nombres, fechas, números y la empresa seleccionada antes de confirmar.</p></div>
          <div class="tarjeta"><div class="icono">❓</div><h3>Pida ayuda</h3><p>El botón con el signo de interrogación abre este manual desde cualquier pantalla.</p></div>
        </div>
        <h3>Botones que verá con frecuencia</h3>
        <div class="botones"><div class="boton-ejemplo"><b>＋</b><span>Añadir una persona o registro</span></div><div class="boton-ejemplo"><b>✎</b><span>Corregir o actualizar</span></div><div class="boton-ejemplo"><b>👁</b><span>Consultar la ficha</span></div><div class="boton-ejemplo"><b>⌕</b><span>Buscar</span></div><div class="boton-ejemplo"><b>⇩</b><span>Descargar un reporte</span></div></div>
        <div class="aviso bien"><strong>Consejo:</strong> si una pantalla no responde de inmediato, espere. Muchas tareas revisan bastante información antes de mostrar el resultado.</div>
      </section>

      <section id="entrada">
        <div class="encabezado"><div class="numero">2</div><div><h2>Cómo entrar al sistema</h2><p class="bajada">Use su correo y su contraseña personal. Nunca preste su acceso a otra persona.</p></div></div>
        <figure class="figura"><img src="captura-login-real.png" alt="Pantalla real para entrar al portal de Recursos Humanos"><figcaption>Pantalla de entrada: escriba su correo, su contraseña y pulse <strong>Entrar</strong>.</figcaption></figure>
        <ol class="pasos"><li>Abra el Portal de Recursos Humanos.</li><li>Escriba su correo en la primera casilla.</li><li>Escriba su contraseña en la segunda casilla. Los caracteres quedarán ocultos para protegerla.</li><li>Pulse <strong>Entrar</strong> una sola vez.</li><li>Espere hasta que aparezca la pantalla de inicio.</li></ol>
        <div class="aviso cuidado"><strong>No comparta su contraseña.</strong> Si otra persona necesita trabajar en el sistema, debe tener su propio acceso.</div>
        <h3>Cambiar la contraseña</h3><p>Abra su menú personal, elija <strong>Cambiar contraseña</strong>, escriba primero la contraseña actual y después la nueva dos veces. Use una contraseña que pueda recordar, pero que otras personas no puedan adivinar.</p>
      </section>

      <section id="inicio">
        <div class="encabezado"><div class="numero">3</div><div><h2>La pantalla de inicio</h2><p class="bajada">Aquí puede ver un resumen del personal y los asuntos que requieren atención.</p></div></div>
        <div class="pantalla"><div class="pantalla-barra">Ejemplo sencillo de la pantalla principal</div><div class="pantalla-cuerpo"><div class="pantalla-menu"><span class="activo">Inicio</span><span>Trabajadores</span><span>Asistencias</span><span>Vacaciones</span><span>Preparación del pago</span><span>Recursos</span><span>Evaluaciones</span></div><div class="pantalla-area"><h3>Resumen de Recursos Humanos</h3><div class="resumenes"><div class="resumen"><strong>Trabajadores</strong><br><small>Personal registrado</small></div><div class="resumen"><strong>Asistencia</strong><br><small>Situación del día</small></div><div class="resumen"><strong>Vacaciones</strong><br><small>Personas ausentes</small></div></div><div class="calendario"><strong>Calendario</strong><p>Cumpleaños · vacaciones · vencimientos · recordatorios</p></div></div></div></div>
        <h3>Qué puede revisar</h3><ul><li>Cantidad de trabajadores y personal activo.</li><li>Personas presentes, ausentes o de vacaciones.</li><li>Cumpleaños próximos.</li><li>Vacaciones aprobadas.</li><li>Contratos próximos a terminar.</li><li>Recordatorios creados por Recursos Humanos.</li></ul>
        <div class="aviso"><strong>Antes de comenzar:</strong> mire el nombre de la empresa que aparece arriba. Toda la información que verá pertenecerá a esa empresa.</div>
      </section>

      <section id="accesos">
        <div class="encabezado"><div class="numero">4</div><div><h2>Qué puede ver cada persona</h2><p class="bajada">El sistema muestra solamente las opciones que corresponden al trabajo de cada persona.</p></div></div>
        <table><thead><tr><th>Tipo de acceso</th><th>Uso habitual</th></tr></thead><tbody><tr><td><strong>Administrador</strong></td><td>Organiza el personal, revisa pagos, vacaciones, asistencia, recursos, evaluaciones y mensajes.</td></tr><tr><td><strong>Trabajador</strong></td><td>Consulta su propia información y cambia su contraseña.</td></tr><tr><td><strong>Encargado de recursos</strong></td><td>Controla los artículos entregados y devueltos por los trabajadores.</td></tr><tr><td><strong>Jefe de Área</strong></td><td>Consulta al personal de sus áreas, revisa asistencia, evaluaciones y solicitudes de vacaciones.</td></tr></tbody></table>
        <div class="aviso atencion"><strong>Si no ve una opción:</strong> puede deberse al tipo de acceso que tiene o a la empresa seleccionada. Consulte con la persona administradora antes de repetir la tarea.</div>
        <h3>Cambiar de empresa</h3><ol class="pasos"><li>Pulse el símbolo de configuración en la parte superior.</li><li>Elija la empresa con la que va a trabajar.</li><li>Espere a que la pantalla se actualice.</li><li>Compruebe el nombre de la empresa antes de guardar cualquier dato.</li></ol>
      </section>

      <section id="organizacion">
        <div class="encabezado"><div class="numero">5</div><div><h2>Cargos, departamentos y ubicaciones</h2><p class="bajada">Estas listas ayudan a ordenar correctamente a cada trabajador.</p></div></div>
        <div class="tarjetas"><div class="tarjeta"><div class="icono">💼</div><h3>Cargos</h3><p>Nombre del puesto, descripción, salario y documento con sus funciones.</p></div><div class="tarjeta"><div class="icono">🏢</div><h3>Departamentos</h3><p>Área de trabajo a la que pertenece cada persona.</p></div><div class="tarjeta"><div class="icono">📍</div><h3>Ubicaciones</h3><p>Lugar físico donde trabaja la persona.</p></div></div>
        <h3>Añadir o corregir un elemento</h3><ol class="pasos"><li>Entre en <strong>General</strong> y elija la lista que necesita.</li><li>Pulse <strong>Añadir</strong> para crear uno nuevo o el lápiz para corregir uno existente.</li><li>Escriba la información con cuidado.</li><li>Pulse <strong>Guardar</strong>.</li><li>Busque el nombre en la lista y confirme que quedó correcto.</li></ol>
        <div class="aviso cuidado"><strong>Cuidado al cambiar un salario:</strong> ese cambio puede afectar a los trabajadores que tienen ese cargo. Confírmelo antes con la persona responsable de nómina.</div>
      </section>

      <section id="trabajadores">
        <div class="encabezado"><div class="numero">6</div><div><h2>Registrar y buscar trabajadores</h2><p class="bajada">Esta es la sección principal para mantener actualizada la plantilla.</p></div></div>
        <figure class="figura"><img src="ilustracion-alta-trabajador.png?v=2" alt="Trabajadora de Recursos Humanos recibiendo a una persona recién contratada"><figcaption>El alta reúne los datos personales, laborales y los documentos del nuevo trabajador.</figcaption></figure>
        <h3>Buscar una persona</h3><ol class="pasos"><li>Abra <strong>Trabajadores</strong>.</li><li>Escriba el nombre, apellido o número de identidad en la búsqueda.</li><li>Si la lista es larga, elija cargo, departamento o ubicación.</li><li>Pulse buscar y revise los resultados.</li><li>Use el ojo para abrir la ficha o el lápiz para corregir.</li></ol>
        <h3>Registrar un nuevo trabajador</h3><ol class="pasos"><li>Pulse <strong>Añadir trabajador</strong>.</li><li>Complete nombres, apellidos, sexo y número de identidad.</li><li>Escriba dirección, provincia, municipio, correo y teléfono.</li><li>Elija nivel de estudios y licencias de conducción, cuando corresponda.</li><li>Seleccione cargo, departamento, ubicación y fecha de contratación.</li><li>Añada una foto clara y reciente.</li><li>Complete la tarjeta de salario y la cuenta bancaria, si ya están disponibles.</li><li>Revise toda la información y pulse <strong>Guardar</strong>.</li></ol>
        <h3>Antes de guardar</h3><ul class="revision"><li>Nombres y apellidos escritos correctamente.</li><li>Número de identidad completo.</li><li>Cargo, departamento, ubicación y empresa correctos.</li><li>Fecha de contratación correcta.</li><li>Teléfono y correo actualizados.</li><li>Foto perteneciente a la persona registrada.</li></ul>
        <div class="aviso atencion"><strong>El sistema puede avisarle</strong> si ya existe una persona con esos datos, si falta información necesaria o si la foto no es adecuada. Lea el mensaje, corrija y vuelva a guardar.</div>
      </section>

      <section id="ficha">
        <div class="encabezado"><div class="numero">7</div><div><h2>La ficha del trabajador</h2><p class="bajada">Es el expediente digital donde se reúne toda la información de una persona.</p></div></div>
        <div class="tarjetas"><div class="tarjeta"><h3>Datos personales</h3><p>Nombre, identidad, dirección, contacto, foto y situación laboral.</p></div><div class="tarjeta"><h3>Vida laboral</h3><p>Cargo, contratos, asistencia, vacaciones, evaluaciones y pagos anteriores.</p></div><div class="tarjeta"><h3>Entregas y documentos</h3><p>Recursos asignados, documentos y mensajes enviados.</p></div></div>
        <h3>Corregir información</h3><ol class="pasos"><li>Busque a la persona.</li><li>Pulse el lápiz.</li><li>Cambie únicamente la información necesaria.</li><li>Revise y guarde.</li><li>Vuelva a abrir la ficha para confirmar.</li></ol>
        <h3>Cambio de cargo</h3><p>Al cambiar el cargo, revise también salario, departamento, ubicación y documento laboral. No cierre la pantalla hasta confirmar que los datos nuevos son correctos.</p>
        <h3>Salida de la empresa</h3><ol class="pasos"><li>Confirme con Nómina los pagos pendientes.</li><li>Revise vacaciones y otros importes que deban pagarse.</li><li>Compruebe que los recursos entregados hayan sido devueltos.</li><li>Realice la liquidación y después la baja.</li><li>Revise la fecha de baja y el estado final de la ficha.</li></ol>
        <div class="aviso cuidado"><strong>La baja forzada debe ser excepcional.</strong> Úsela solamente con autorización de la persona responsable, porque puede cerrar la ficha sin la revisión normal del pago.</div>
        <h3>Volver a contratar</h3><p>Busque la ficha anterior, elija la opción de reincorporación, seleccione empresa, cargo y nueva fecha de contratación. Revise los datos anteriores y actualice los que hayan cambiado.</p>
      </section>

      <section id="asistencia">
        <div class="encabezado"><div class="numero">8</div><div><h2>Asistencia y ausencias</h2><p class="bajada">Permite comprobar entradas, salidas, tardanzas, ausencias, turnos especiales y vacaciones.</p></div></div>
        <h3>Consultar la asistencia</h3><ol class="pasos"><li>Abra <strong>Registro de Asistencias</strong>.</li><li>Elija la fecha inicial y final.</li><li>Si lo necesita, elija trabajador, ubicación o situación.</li><li>Pulse buscar.</li><li>Revise cada fila antes de realizar cambios.</li></ol>
        <table><thead><tr><th>Situación</th><th>Qué significa</th></tr></thead><tbody><tr><td><strong>Presente</strong></td><td>La persona tiene registrada su entrada.</td></tr><tr><td><strong>Ausente</strong></td><td>No aparece una entrada y no tiene vacaciones aprobadas.</td></tr><tr><td><strong>Vacaciones</strong></td><td>La persona tiene un periodo aprobado para esa fecha.</td></tr><tr><td><strong>Horario especial</strong></td><td>La persona trabaja con un horario diferente al habitual.</td></tr><tr><td><strong>Tardanza</strong></td><td>La entrada fue posterior al horario establecido.</td></tr></tbody></table>
        <h3>Justificar una ausencia o tardanza</h3><ol class="pasos"><li>Localice a la persona y la fecha.</li><li>Abra la opción de corregir o justificar.</li><li>Elija el motivo correcto.</li><li>Escriba una explicación corta y clara.</li><li>Guarde y vuelva a revisar el registro.</li></ol>
        <div class="aviso"><strong>Buena práctica:</strong> no cambie una ausencia sin tener el motivo o documento que la justifica.</div>
      </section>

      <section id="vacaciones">
        <div class="encabezado"><div class="numero">9</div><div><h2>Solicitudes de vacaciones</h2><p class="bajada">Las vacaciones pasan por revisión antes de quedar aprobadas y aparecer en el pago.</p></div></div>
        <div class="flujo"><div>Solicitud pendiente</div><i>→</i><div>Revisión del área</div><i>→</i><div>Aprobación final</div><i>→</i><div>Incluida en el periodo correspondiente</div></div>
        <h3>Crear una solicitud</h3><ol class="pasos"><li>Abra <strong>Vacaciones</strong> o <strong>Planificación de Vacaciones</strong>.</li><li>Elija al trabajador.</li><li>Seleccione la fecha de comienzo y de regreso, o los días que correspondan.</li><li>Revise la cantidad de días y el saldo disponible.</li><li>Compruebe que no exista otra solicitud para las mismas fechas.</li><li>Guarde la solicitud.</li></ol>
        <h3>Revisar y aprobar</h3><ol class="pasos"><li>Abra la lista de solicitudes pendientes.</li><li>Compruebe nombre, área, fechas y cantidad de días.</li><li>El Jefe de Área realiza la primera revisión cuando corresponde.</li><li>La persona administradora realiza la aprobación final o el rechazo.</li><li>Revise que la nueva situación aparezca en la lista.</li></ol>
        <div class="aviso atencion"><strong>Importante:</strong> una aprobación reserva esos días para la persona. Si la solicitud se cancela antes de comenzar, asegúrese de eliminarla correctamente para devolver los días al saldo.</div>
        <h3>Qué debe comprobar</h3><ul class="revision"><li>Las fechas son correctas y corresponden a días laborables.</li><li>No hay otra solicitud para las mismas fechas.</li><li>La persona tiene días disponibles.</li><li>El área conoce y puede cubrir la ausencia.</li><li>La solicitud aparece con la situación correcta.</li></ul>
      </section>

      <section id="prenomina">
        <div class="encabezado"><div class="numero">10</div><div><h2>Preparación del pago mensual</h2><p class="bajada">Aquí se revisan horas, ausencias, bonificaciones, vacaciones y el importe que recibirá cada trabajador.</p></div></div>
        <figure class="figura"><img src="ilustracion-cierre-mensual.png?v=2" alt="Dos trabajadoras revisando juntas el calendario, la asistencia y el resumen mensual"><figcaption>Antes de terminar el mes, revise asistencia, vacaciones y pagos junto con la documentación disponible.</figcaption></figure>
        <h3>Preparar el mes</h3><ol class="pasos"><li>Abra <strong>Prenómina</strong>.</li><li>Elija el mes, el año y confirme la empresa.</li><li>Revise un departamento cada vez.</li><li>Compruebe las horas trabajadas y las ausencias.</li><li>Añada bonificaciones solamente cuando estén autorizadas.</li><li>Revise los días y el pago de vacaciones.</li><li>Compruebe el total de cada persona.</li><li>Guarde primero. Descargue el reporte solamente después de revisar.</li></ol>
        <div class="aviso bien"><strong>No necesita calcular a mano:</strong> el sistema realiza los cálculos. Su tarea es confirmar que las horas, ausencias, bonificaciones y vacaciones usadas como base sean correctas.</div>
        <h3>Antes de terminar</h3><ul class="revision"><li>Mes, año y empresa correctos.</li><li>Todos los departamentos revisados.</li><li>Ausencias justificadas y vacaciones aprobadas.</li><li>Bonificaciones respaldadas por autorización.</li><li>Personas en proceso de baja correctamente revisadas.</li><li>Totales comparados con la información del mes.</li></ul>
        <h3>Cuentas y ayudas</h3><p>En <strong>Cuentas Bancarias</strong> puede comprobar la tarjeta de salario y la cuenta. En <strong>Ayudas a Trabajadores</strong> puede registrar el tipo de ayuda, importe, moneda, fecha y explicación. Revise siempre la autorización antes de guardar.</p>
        <div class="aviso cuidado"><strong>No descargue el reporte varias veces sin necesidad.</strong> Si detecta un error después de guardar, corríjalo y consulte con Nómina antes de preparar una nueva versión.</div>
      </section>

      <section id="recursos">
        <div class="encabezado"><div class="numero">11</div><div><h2>Recursos entregados a trabajadores</h2><p class="bajada">Sirve para controlar equipos, herramientas u otros artículos entregados a una persona.</p></div></div>
        <div class="flujo"><div>Recurso disponible</div><i>→</i><div>Entrega al trabajador</div><i>→</i><div>Recurso asignado</div><i>→</i><div>Devolución</div></div>
        <h3>Entregar un recurso</h3><ol class="pasos"><li>Abra <strong>Recursos</strong>.</li><li>Busque el artículo y confirme que está disponible.</li><li>Pulse asignar.</li><li>Elija al trabajador y la fecha de entrega.</li><li>Complete marca, modelo, color u otra información útil.</li><li>Guarde y compruebe que aparezca como asignado.</li></ol>
        <h3>Registrar una devolución</h3><ol class="pasos"><li>Busque el recurso asignado.</li><li>Abra la opción de devolución.</li><li>Confirme trabajador, artículo y fecha.</li><li>Guarde.</li><li>Compruebe que el recurso vuelva a estar disponible.</li></ol>
        <div class="aviso atencion"><strong>Antes de una baja:</strong> revise la ficha del trabajador y confirme que todos los recursos hayan sido devueltos.</div>
      </section>

      <section id="evaluaciones">
        <div class="encabezado"><div class="numero">12</div><div><h2>Evaluaciones de desempeño</h2><p class="bajada">Permite valorar el trabajo de cada persona usando los aspectos definidos por la empresa.</p></div></div>
        <h3>Realizar una evaluación</h3><ol class="pasos"><li>Abra <strong>Evaluaciones → Listado</strong>.</li><li>Busque a la persona o elija su cargo.</li><li>Lea cada aspecto con calma.</li><li>Escriba una puntuación que no supere el máximo indicado.</li><li>Revise el total.</li><li>Guarde y confirme que la puntuación permanezca visible.</li></ol>
        <table><thead><tr><th>Resultado mostrado</th><th>Interpretación sencilla</th></tr></thead><tbody><tr><td><strong>Mal</strong></td><td>Necesita atención y un plan de mejora.</td></tr><tr><td><strong>Regular</strong></td><td>Cumple parcialmente; deben revisarse los aspectos más bajos.</td></tr><tr><td><strong>Bien</strong></td><td>Buen desempeño general.</td></tr><tr><td><strong>Excelente</strong></td><td>Desempeño destacado.</td></tr></tbody></table>
        <div class="aviso"><strong>Sea justa y consistente:</strong> use el mismo criterio para personas con funciones semejantes y conserve las observaciones que respaldan la evaluación.</div>
      </section>

      <section id="bolsa">
        <div class="encabezado"><div class="numero">13</div><div><h2>Bolsa de empleo</h2><p class="bajada">Guarda los datos de candidatos y facilita su incorporación cuando sean seleccionados.</p></div></div>
        <h3>Registrar un candidato</h3><ol class="pasos"><li>Abra <strong>Bolsa de Empleo</strong>.</li><li>Pulse añadir.</li><li>Escriba nombres, cargo al que aspira y teléfono.</li><li>Añada su currículum.</li><li>Escriba observaciones claras.</li><li>Guarde y confirme que aparezca en la lista.</li></ol>
        <h3>Contratar desde la bolsa</h3><ol class="pasos"><li>Busque al candidato.</li><li>Revise su currículum y observaciones.</li><li>Pulse <strong>Contratar</strong>.</li><li>Complete los datos laborales que faltan.</li><li>Revise y guarde la nueva ficha de trabajador.</li></ol>
        <div class="aviso bien"><strong>Ventaja:</strong> los datos ya escritos y el currículum acompañan el proceso de contratación, por lo que no necesita comenzar desde cero.</div>
      </section>

      <section id="mensajes">
        <div class="encabezado"><div class="numero">14</div><div><h2>Mensajes a trabajadores</h2><p class="bajada">Puede enviar avisos a una persona o a grupos organizados por departamento y ubicación.</p></div></div>
        <h3>Enviar un mensaje a un grupo</h3><ol class="pasos"><li>Abra <strong>Notificaciones SMS</strong>.</li><li>Elija departamentos y ubicaciones.</li><li>Pulse la vista previa para comprobar cuántas personas lo recibirán.</li><li>Escriba un mensaje corto, claro y respetuoso.</li><li>Revise destinatarios y texto.</li><li>Confirme el envío una sola vez.</li></ol>
        <h3>Enviar a una sola persona</h3><p>Abra la ficha del trabajador, busque la opción de mensaje, escriba el texto y confirme el número de teléfono antes de enviarlo.</p>
        <div class="aviso cuidado"><strong>Proteja la privacidad:</strong> no envíe salarios, datos bancarios, resultados de evaluaciones ni información personal delicada por mensaje.</div>
        <h3>Historial</h3><p>El historial permite revisar fecha, persona, teléfono y mensaje enviado. Consúltelo antes de repetir un aviso.</p>
      </section>

      <section id="documentos">
        <div class="encabezado"><div class="numero">15</div><div><h2>Contratos, documentos y capacitación</h2><p class="bajada">La ficha conserva los documentos importantes de la relación laboral.</p></div></div>
        <h3>Contratos</h3><ol class="pasos"><li>Abra la ficha del trabajador y entre en Contratos.</li><li>Revise el tipo de contrato, cargo, salario y fechas.</li><li>Complete la información solicitada.</li><li>Use la vista previa para leer el documento.</li><li>Corrija cualquier error antes de guardarlo o imprimirlo.</li><li>Compruebe que el contrato aparezca en la ficha.</li></ol>
        <h3>Cambios de cargo o salario</h3><p>Cuando exista un cambio, revise el documento adicional que deja constancia de las nuevas condiciones. No elimine el contrato anterior; forma parte del historial laboral.</p>
        <h3>Otros documentos</h3><p>La ficha puede guardar currículum y otros documentos del trabajador. Use nombres claros, compruebe que el archivo pertenece a la persona correcta y evite guardar copias innecesarias.</p>
        <h3>Capacitación</h3><p>Cuando la sección esté disponible, permite registrar tema, participantes, responsable, fecha, forma de realización y duración, además de conservar la finalización y el certificado.</p>
      </section>

      <section id="cierres">
        <div class="encabezado"><div class="numero">16</div><div><h2>Revisiones y reportes</h2><p class="bajada">Las listas de comprobación ayudan a cerrar el día y el mes sin olvidar tareas.</p></div></div>
        <div class="dos"><div class="tarjeta"><h3>Al terminar el día</h3><ul class="revision"><li>Entradas y ausencias revisadas.</li><li>Justificaciones guardadas.</li><li>Solicitudes nuevas atendidas.</li><li>Datos urgentes corregidos.</li><li>Mensajes importantes comprobados.</li></ul></div><div class="tarjeta"><h3>Al terminar el mes</h3><ul class="revision"><li>Todos los departamentos revisados.</li><li>Vacaciones y ausencias confirmadas.</li><li>Bonificaciones autorizadas.</li><li>Personas de baja revisadas.</li><li>Preparación del pago guardada.</li><li>Reporte final descargado y protegido.</li></ul></div></div>
        <h3>Reportes disponibles</h3><table><thead><tr><th>Sección</th><th>Qué puede obtener</th></tr></thead><tbody><tr><td>Trabajadores</td><td>Listado de personal según la búsqueda realizada.</td></tr><tr><td>Asistencia</td><td>Detalle por persona y fechas, además del listado diario de presentes.</td></tr><tr><td>Preparación del pago</td><td>Resumen mensual organizado por departamentos.</td></tr><tr><td>Cuentas bancarias</td><td>Información necesaria para preparar el pago bancario.</td></tr><tr><td>Recursos</td><td>Artículos que continúan asignados.</td></tr><tr><td>Evaluaciones</td><td>Resultados de desempeño.</td></tr><tr><td>Contratos</td><td>Documento para revisar, guardar o imprimir.</td></tr></tbody></table>
        <div class="aviso atencion"><strong>Proteja los reportes:</strong> contienen información personal. Guárdelos en el lugar autorizado y no los envíe a personas que no deban recibirlos.</div>
      </section>

      <section id="problemas">
        <div class="encabezado"><div class="numero">17</div><div><h2>Problemas frecuentes</h2><p class="bajada">Pruebe estas soluciones sencillas antes de repetir una operación o pedir ayuda.</p></div></div>
        <div class="problema"><b>No puedo entrar</b><span>Revise el correo, compruebe mayúsculas y vuelva a escribir la contraseña con calma. Si continúa, solicite restablecerla.</span></div>
        <div class="problema"><b>No encuentro a una persona</b><span>Limpie la búsqueda, confirme la empresa y pruebe con un solo apellido o el número de identidad.</span></div>
        <div class="problema"><b>No veo una opción</b><span>Puede no corresponder a su tipo de acceso o a la empresa seleccionada. Consulte con la persona administradora.</span></div>
        <div class="problema"><b>Guardé un dato incorrecto</b><span>No cree otro registro. Busque el existente, pulse el lápiz y corrija solamente lo necesario.</span></div>
        <div class="problema"><b>La pantalla tarda</b><span>Espere unos segundos. No pulse Guardar repetidamente. Si no responde, anote lo que estaba haciendo y pida ayuda.</span></div>
        <div class="problema"><b>Necesito volver al manual</b><span>Pulse el botón azul con el signo <strong>?</strong> en la barra superior. El manual se abrirá sin cerrar su trabajo.</span></div>
        <h3>Reglas de cuidado</h3><ul class="revision"><li>Cierre su sesión al alejarse del puesto.</li><li>No comparta contraseñas.</li><li>No deje reportes impresos a la vista.</li><li>Confirme la persona y empresa antes de guardar.</li><li>Pida ayuda antes de usar una opción que no conozca.</li></ul>
        <div class="aviso bien"><strong>Recuerde:</strong> trabajar despacio y revisar antes de guardar evita la mayoría de los errores.</div>
      </section>
    </article>

    <footer class="pie"><div class="pie-contenido"><div><strong>Manual De Sistema de Recursos Humanos Allnovu</strong><p>Guía sencilla para las tareas cotidianas de Recursos Humanos.</p></div><div><strong>Edición</strong><p>Agosto de 2026<br>Uso interno</p></div></div></footer>
  </main>
</body>
</html>
