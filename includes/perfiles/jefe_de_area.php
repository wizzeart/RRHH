<!--Menu list item for JEFE DE AREA role (id=4)-->

<!--NEW MENU DASHBOARD-->
<li class="list-divider"></li>
<li class="<?php if ($_GET['module'] == 'home') print('active-link') ?>">
    <a href="?module=home">
        <i class="fa fa-dashboard"></i>
        <span class="menu-title">
            <strong>Inicio</strong>
        </span>
    </a>
</li>

<!--NEW MENU TRABAJADORES-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores', 'delete-trabajadores', 'bajas-trabajadores'))) print('active-link') ?>">
    <a href="?module=list-trabajadores">
        <i class="fa fa-users"></i>
        <span class="menu-title">
            <strong>Trabajadores</strong>
        </span>
    </a>
</li>

<!--NEW MENU EVALUACION-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-evaluaciones', 'evaluaciones'))) print('active-link') ?>">
    <a href="?module=list-evaluaciones">
        <i class="fa fa-file-text-o"></i>
        <span class="menu-title">
            <strong>Evaluaciones</strong>
        </span>
    </a>
</li>

<!--NEW MENU REGISTRO DE ASISTENCIAS-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-asistencias', 'asistencias'))) print('active-link') ?>">
    <a href="?module=list-asistencias">
        <i class="fa fa-calendar"></i>
        <span class="menu-title">
            <strong>Registro de Asistencias</strong>
        </span>
    </a>
</li>