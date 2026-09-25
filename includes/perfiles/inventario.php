<!--Menu list item for INVENTARIO role (id=3)-->

<!--NEW MENU RECURSOS - Full access-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-recursos', 'gestion-recursos'))) print('active-link') ?>">
    <a href="?module=list-recursos">
        <i class="fa fa-cube"></i>
        <span class="menu-title">
            <strong>Recursos</strong>
        </span>
    </a>
</li>

<!--NEW MENU TRABAJADORES - Limited access-->
<li class="list-divider"></li>
<li class="<?php if (in_array($_GET['module'], array('list-trabajadores', 'trabajadores'))) print('active-link') ?>">
    <a href="?module=list-trabajadores">
        <i class="fa fa-users"></i>
        <span class="menu-title">
            <strong>Trabajadores</strong>
        </span>
    </a>
</li>
