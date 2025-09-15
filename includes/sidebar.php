<nav id="mainnav-container">
    <div id="mainnav">

        <!--Menu-->
        <!--================================-->
        <div id="mainnav-menu-wrap">
            <div class="nano">
                <div class="nano-content">
                    <ul id="mainnav-menu" class="list-group">

                        <!--Category name-->
                        <li class="list-header">Menú</li>

                        <?php
                        if ($app->rol == '8') { //CHOFERS
                            require_once(INCLUDES . '/perfiles/chofer.php');
                        }
                        if ($app->rol == '7') { //REPARTIDORES
                            require_once(INCLUDES . '/perfiles/repartidor.php');
                        }
                        if ($app->rol == '6') { //DISEÑADORES
                            require_once(INCLUDES . '/perfiles/disenador.php');
                        }
                        if ($app->rol == '4') { //REVENDEDORES
                            require_once(INCLUDES . '/perfiles/revendedor.php');
                        }
                        if ($app->rol == '2') { //PROVEEDORES ETIQUETAS 
                            require_once(INCLUDES . '/perfiles/proveedor-etiquetas.php');
                        }
                        if ($app->rol == '4' || $app->rol == '5') { //CALL CENTER 
                            require_once(INCLUDES . '/perfiles/call-center.php');
                        }
                        if ($app->rol == '3') {//GESTOR ALMACEN 
                            require_once(INCLUDES . '/perfiles/gestor-almacen.php');
                        }
                        if ($app->rol == '16') {//ADMINISTRADOR ALMACEN 
                            require_once(INCLUDES . '/perfiles/administrador-almacen.php');
                        }
                        if ($app->rol == '9') {//TIENDA 60  
                            require_once(INCLUDES . '/perfiles/tienda-60.php');
                        }
                        if ($app->rol == '17') {//FACTURACIÓN TIENDA
                            require_once(INCLUDES . '/perfiles/facturacion-tienda.php');
                        }
                        if ($app->rol == '10') {//PUNTO DE VENTA FACTURACIÓN
                            require_once(INCLUDES . '/perfiles/punto-venta-facturacion.php');
                        }
                        if ($app->rol == '11') {//COMERCIAL
                            require_once(INCLUDES . '/perfiles/comercial.php');
                        }
                        if ($app->rol == '12') {//INVENTARIO
                            require_once(INCLUDES . '/perfiles/inventario.php');
                        }
                        if ($app->rol == '18') {//GESTOR COMERCIALES
                            require_once(INCLUDES . '/perfiles/gestor-comerciales.php');
                        }
                        if ($app->rol == '19') {//COMPRAS
                            require_once(INCLUDES . '/perfiles/compras.php');
                        }
                        if (in_array($app->rol, array(20, 21))) { //COORDINADOR SAT TÉCNICO SAT
                            require_once(INCLUDES . '/perfiles/sat.php');
                        }
                        if ($app->rol == '1') {//ADMINISTRADORES
                            require_once(INCLUDES . '/perfiles/administrador.php');
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <!--================================-->
        <!--End menu-->

    </div>
</nav>