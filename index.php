<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = 0;
include(__DIR__ . '/includes/config.php');
include(INCLUDES . '/functions.php');

init_app();
$app = new App();

if ($app->user_id == '')
    header("Location:login.html");
require(BASE . '/includes/controlador.php');
$load_grid = true;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php print($page['title']) ?></title>

    <!--STYLESHEET-->
    <!--=================================================-->

    <!--Open Sans Font [ OPTIONAL ] -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;subset=latin" rel="stylesheet">


    <!--Bootstrap Stylesheet [ REQUIRED ]-->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!--Custom Stylesheet -->
    <link href="css/custom.css" rel="stylesheet" />


    <!--Nifty Stylesheet [ REQUIRED ]-->
    <link href="css/nifty.css" rel="stylesheet">


    <!--Font Awesome [ OPTIONAL ]-->
    <link href="plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">

    <!-- Dashboard Dependencies -->
    <!-- Chart.js para gráficos interactivos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    
    <!-- Heatmap.js para mapas de calor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/heatmap.js/2.0.2/heatmap.min.js"></script>
    
    <!-- Date Range Picker y sus dependencias -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


    <!--Animate.css [ OPTIONAL ]-->
    <link href="plugins/animate-css/animate.min.css" rel="stylesheet">


    <!--Morris.js [ OPTIONAL ]-->
    <link href="plugins/morris-js/morris.min.css" rel="stylesheet">


    <!--Switchery [ OPTIONAL ]-->
    <link href="plugins/switchery/switchery.min.css" rel="stylesheet">


    <!--Bootstrap Select [ OPTIONAL ]-->
    <link href="plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">

    <!--Bootstrap Datepicker [ OPTIONAL ]-->
    <link href="plugins/bootstrap-datepicker/bootstrap-datepicker.css" rel="stylesheet">

    <!--Summernote [ OPTIONAL ]-->
    <link href="plugins/summernote/summernote.min.css" rel="stylesheet">

    <!--Demo script [ DEMONSTRATION ]-->
    <link href="css/demo/nifty-demo.css" rel="stylesheet">

    <!--switch config page [ OPTIONAL ]-->
    <link href="css/demo/style-switcher.css" rel="stylesheet">

    <!--Dropzone [ OPTIONAL ]-->
    <link href="plugins/dropzone/dropzone.css" rel="stylesheet">

    <!--Chosen [ OPTIONAL ]-->
    <link href="plugins/chosen/chosen.min.css" rel="stylesheet">

    <!--SCRIPT-->
    <!--=================================================-->

    <!--Page Load Progress Bar [ OPTIONAL ]-->
    <link href="plugins/pace/pace.min.css" rel="stylesheet">
    <script src="plugins/pace/pace.min.js"></script>


    <link href="css/custom.css" rel="stylesheet">
    <link href="js/jquery-ui-1.11.4.custom/jquery-ui.css" rel="stylesheet">

    <!-- CSS de Lightbox2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />

    <?php if ($load_grid) { ?>
        <!-- grid CSS -->
        <link rel="stylesheet" type="text/css" href="plugins/dhtmlxGrid/codebase/fonts/font_roboto/roboto.css" />
        <link rel="stylesheet" type="text/css" href="plugins/dhtmlxGrid/codebase/dhtmlxgrid.css" />
    <?php } ?>
    <!--
    
            REQUIRED
            You must include this in your project.
    
            RECOMMENDED
            This category must be included but you may modify which plugins or components which should be included in your project.
    
            OPTIONAL
            Optional plugins. You may choose whether to include it in your project or not.
    
            DEMONSTRATION
            This is to be removed, used for demonstration purposes only. This category must not be included in your project.
    
            SAMPLE
            Some script samples which explain how to initialize plugins or components. This category should not be included in your project.
    
    
            Detailed information and more samples can be found in the document.
    
        -->


</head>

<!-- TIPS -->
<!--You may remove all ID or Class names which contain "demo-", they are only used for demonstration. -->

<body>
    <div id="container" class="effect mainnav-lg">

        <!--NAVBAR-->
        <!--===================================================-->
        <header id="navbar">
            <div id="navbar-container" class="boxed">

                <!--Brand logo & name-->
                <!--================================-->
                <div class="navbar-header">
                    <a href="index.php" class="navbar-brand">
                        <img src="img/logo.png" alt="Logo" class="brand-icon">
                        <div class="brand-title">
                            <span class="brand-text"></span>
                        </div>
                    </a>
                </div>
                <!--================================-->
                <!--End brand logo & name-->


                <!--Navbar Dropdown-->
                <!--================================-->
                <div class="navbar-content clearfix">
                    <ul class="nav navbar-top-links pull-left">

                        <!--Navigation toogle button-->
                        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                        <li class="tgl-menu-btn">
                            <a class="mainnav-toggle" href="javascript:void(0);">
                                <i class="fa fa-navicon fa-lg"></i>
                            </a>
                        </li>
                        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->                        <!--End Navigation toogle button-->
                    </ul>
                    <ul class="nav navbar-top-links pull-right">

                        <!--City selector-->
                        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                        <?php //require_once(INCLUDES . DS . 'city-selector.php');  ?>
                        <!-- Botón de notificaciones/Chat -->
                        <li>
                            <a id="btn-navbar-chat" href="index.php?module=chat" title="Chat / Notificaciones">
                                <i class="fa fa-comments fa-lg"></i>
                                <span id="notif-count" class="badge badge-danger" style="display:none; margin-left:6px;">0</span>
                            </a>
                        </li>

                        <!--City selector-->
                        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                        <!--End city selector-->

                        <!--User dropdown-->

                        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                        <?php require_once(INCLUDES . DS . 'dropdown-user.php'); ?>
                        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                        <!--End user dropdown-->

                    </ul>
                </div>
                <!--================================-->
                <!--End Navbar Dropdown-->

            </div>
        </header>
        <!--===================================================-->
        <!--END NAVBAR-->

        <div class="boxed">

            <!--CONTENT CONTAINER-->
            <!--===================================================-->
            <div id="content-container">

                <!--Page Title-->
                <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                <div id="page-title">
                    <h1 class="page-header text-overflow"><?php print($page['title']); ?></h1>
                </div>
                <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                <!--End page title-->


                <!--Breadcrumb-->
                <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                <?php //require_once(INCLUDES . DS . 'breadcrumbs.php');  
                ?>
                <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                <!--End breadcrumb-->




                <!--Page content-->
                <!--===================================================-->
                <div id="page-content">
                    <?php
                    if (isset($_REQUEST['module'])) {
                        $module = BASE . '/modules/' . $_REQUEST['module'] . '/' . $_REQUEST['module'] . '.php';
                        if (file_exists($module))
                            require($module);
                        else {
                            print('Módulo inexistente.');
                        }
                    } else {
                        print('Módulo inexistente.');
                    }
                    ?>
                </div>
                <!--===================================================-->
                <!--End page content-->


            </div>
            <!--===================================================-->
            <!--END CONTENT CONTAINER-->



            <!--MAIN NAVIGATION-->
            <!--===================================================-->
            <?php if (isset($_REQUEST['module'])) require_once(INCLUDES . DS . 'sidebar.php'); ?>
            <!--===================================================-->
            <!--END MAIN NAVIGATION-->
        </div>



        <!-- FOOTER -->
        <!--===================================================-->
        <footer id="footer">

            <!-- Visible when footer positions are fixed -->
            <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
            <div class="show-fixed pull-right">
                <ul class="footer-list list-inline">
                    <li>
                        <p class="text-sm">SEO Proggres</p>
                        <div class="progress progress-sm progress-light-base">
                            <div style="width: 80%" class="progress-bar progress-bar-danger"></div>
                        </div>
                    </li>

                    <li>
                        <p class="text-sm">Online Tutorial</p>
                        <div class="progress progress-sm progress-light-base">
                            <div style="width: 80%" class="progress-bar progress-bar-primary"></div>
                        </div>
                    </li>
                    <li>
                        <button class="btn btn-sm btn-dark btn-active-success">Checkout</button>
                    </li>
                </ul>
            </div>



            <!-- Visible when footer positions are static -->
            <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
            <div class="hide-fixed pull-right pad-rgt"></div>



            <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
            <!-- Remove the class name "show-fixed" and "hide-fixed" to make the content always appears. -->
            <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->

            <p class="pad-lft">&#0169; <?php print(date('Y')) ?> Recursos Humanos IML</p>



        </footer>
        <!--===================================================-->
        <!-- END FOOTER -->


        <!-- SCROLL TOP BUTTON -->
        <!--===================================================-->
        <button id="scroll-top" class="btn"><i class="fa fa-chevron-up"></i></button>
        <!--===================================================-->



    </div>
    <!--===================================================-->
    <!-- END OF CONTAINER -->

    <!--JAVASCRIPT-->
    <!--=================================================-->

    <!--jQuery [ REQUIRED ]-->
    <script src="js/jquery-2.1.1.min.js"></script>

    <script src="js/jquery-ui-1.11.4.custom/jquery-ui.js" type="text/javascript"></script>

    <!--BootstrapJS [ RECOMMENDED ]-->
    <script src="js/bootstrap.min.js"></script>


    <!--Fast Click [ OPTIONAL ]-->
    <script src="plugins/fast-click/fastclick.min.js"></script>


    <!--Nifty Admin [ RECOMMENDED ]-->
    <script src="js/nifty.js"></script>


    <!--Morris.js [ OPTIONAL ]-->
    <script src="plugins/morris-js/morris.min.js"></script>
    <script src="plugins/morris-js/raphael-js/raphael.min.js"></script>


    <!--Sparkline [ OPTIONAL ]-->
    <script src="plugins/sparkline/jquery.sparkline.min.js"></script>


    <!--Skycons [ OPTIONAL ]-->
    <script src="plugins/skycons/skycons.min.js"></script>


    <!--Switchery [ OPTIONAL ]-->
    <script src="plugins/switchery/switchery.min.js"></script>


    <!--Bootstrap Select [ OPTIONAL ]-->
    <script src="plugins/bootstrap-select/bootstrap-select.min.js"></script>

    <!--Bootstrap Table [ OPTIONAL ]-->
    <script src="plugins/bootstrap-table/bootstrap-table.js"></script>
    <script src="plugins/bootstrap-table/locale/bootstrap-table-es-ES.js"></script>
    <script src="plugins/bootstrap-table/extensions/accent-neutralise/bootstrap-table-accent-neutralise.min.js" type="text/javascript"></script>
    <link href="plugins/bootstrap-table/bootstrap-table.css" rel="stylesheet">

    <!--Bootstrap Datepicker [ OPTIONAL ]-->
    <script src="plugins/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    <script src="plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.js"></script>

    <!--Summernote [ OPTIONAL ]-->
    <script src="plugins/summernote/summernote.min.js"></script>

    <!--Dropzone [ OPTIONAL ]-->
    <script src="plugins/dropzone/dropzone.min.js"></script>

    <!--Chosen [ OPTIONAL ]-->
    <script src="plugins/chosen/chosen.jquery.min.js"></script>

    <script src="js/jquery.number.min.js"></script>

    <?php if ($load_grid) { ?>
        <script src="plugins/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>
    <?php } ?>

    <!-- JS de Lightbox2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    <!--Demo script [ DEMONSTRATION ]-->
    <script src="js/demo/nifty-demo.js"></script>
    <!--Specify page [ SAMPLE ]-->
    <!-- <script src="js/demo/dashboard.js"></script> -->

    <?php
    if (isset($_REQUEST['module'])) {
        if (file_exists(BASE . '/modules/' . $_REQUEST['module'] . '/' . $_REQUEST['module'] . '.js')) {
            if ($debug == 1)
                print('<script src="/modules/' . $_REQUEST['module'] . '/' . $_REQUEST['module'] . '.js"></script>');
            else
                print('<script src="/modules/' . $_REQUEST['module'] . '/' . $_REQUEST['module'] . '.js?' . time() . '"></script>');
        }
    }
    ?>
    <!-- Common helpers for list modules -->
    <script src="/js/list-common.js?<?php print time(); ?>"></script>
    
    <script>
        var alv_module = '<?php if (isset($_REQUEST['module'])) print($_REQUEST['module']); ?>';
    </script>
    <!--
            You must include this in your project.
    
            RECOMMENDED
            This category must be included but you may modify which plugins or components which should be included in your project.
    
            OPTIONAL
            Optional plugins. You may choose whether to include it in your project or not.
    
            DEMONSTRATION
            This is to be removed, used for demonstration purposes only. This category must not be included in your project.
    
            SAMPLE
            Some script samples which explain how to initialize plugins or components. This category should not be included in your project.
    
    
            Detailed information and more samples can be found in the document.
    
        -->
</body>

</html>