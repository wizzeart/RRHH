<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Articulo {

    var $app;
    var $db;
    var $action;
    var $page;
    var $total;
    var $unidades;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'archived':
                $data = $this->_archived($param);
                print(json_encode($data));
                break;
            case 'archived-componente':
                $data = $this->_archived_componente($param);
                print(json_encode($data));
                break;
            case 'copy-img-an':
                $data = $this->_copy_img_an($param);
                print(json_encode($data));
                break;
            case 'get-stock':
                $data = $this->_get_stock_pv($param);
                print(json_encode($data));
                break;
            case 'almacen-detall':
                $data = $this->_list_stock_almacenes($param);
                print(json_encode($data));
                break;
            case 'print-ticket':
                $data = $this->_list_componentes($param);
                $this->_dl_inventario_ticket($data);
                //print(json_encode($data));
                break;
            case 'print-a4':
                $data = $this->_list_componentes($param);
                $this->_dl_inventario_a4($data);
                //print(json_encode($data));
                break;
            case 'add-stock':
                $this->_add_stock($param);
                break;
            case 'add-traspaso':
                $this->_add_traspaso($param);
                break;
            case 'dl-pdf-revendedor':
                $this->_dl_pdf_revendedor($param);
                break;
            case 'create-images':
                $data = $this->_create_images($param);
                print(json_encode($data));
                break;
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'list-componentes':
                $data = $this->_list_componentes($param);
                print(json_encode($data));
                break;
            case 'list-almacenes':
                $data = $this->_list_almacenes($param);
                print(json_encode($data));
                break;
            case 'precios':
                $data = $this->_precios($param);
                print(json_encode($data));
                break;
            case 'precios-coste':
                $data = $this->_precios_coste($param);
                print(json_encode($data));
                break;
            case 'precios-coste-componente':
                $data = $this->_precios_coste_componente($param);
                print(json_encode($data));
                break;
            case 'recargas':
                $data = $this->_recargas($param);
                print(json_encode($data));
                break;
            case 'checked':
                $this->_checked($param);
                break;
            case 'checked-componente':
                $this->_checked_componente($param);
                break;
            case 'checked-destacado':
                $this->_checked_destacado($param);
                break;
            case 'checked-venta':
                $this->_checked_venta($param);
                break;
            case 'checked-web':
                $this->_checked_web($param);
                break;
            case 'checked-destacado-mr':
                $this->_checked_destacado_mr($param);
                break;
            case 'checked-venta-mr':
                $this->_checked_venta_mr($param);
                break;
            case 'checked-web-mr':
                $this->_checked_web_mr($param);
                break;
            case 'checked-web-an':
                $this->_checked_web_an($param);
                break;
            case 'checked-rev':
                $this->_checked_rev($param);
                break;
            case 'checked-servicio':
                $this->_checked_servicio($param);
                break;
            case 'checked-preventa':
                $this->_checked_preventa($param);
                break;
            case 'del':
                $this->_del($param);
                break;
            case 'del-componente':
                $this->_del_componente($param);
                break;
            case 'save':
                $data = $this->_save($param);
                print(json_encode($data));
                break;
            case 'save-componente':
                $data = $this->_save_componente($param);
                print(json_encode($data));
                break;
            case 'save-precios':
                $this->_save_precios($param);
                break;
            case 'save-precios-coste':
                $this->_save_precios_coste($param);
                break;
            case 'save-precios-coste-componente':
                $this->_save_precios_coste_componente($param);
                break;
            case 'copy':
                $this->_copy($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'list-precios':
                $data = array();
                $page['title'] = 'Precios de Venta Artículos';
                $page['subtitle'] = 'Listado de Precios de Venta Artículos';
                break;
            case 'list-precios-coste':
                $data = array();
                $page['title'] = 'Precios de Coste Artículos';
                $page['subtitle'] = 'Listado de Precios de Coste Artículos';
                break;
            case 'list-precios-coste-com':
                $data = array();
                $page['title'] = 'Precios de Coste Componentes';
                $page['subtitle'] = 'Listado de Precios de Coste Componentes';
                break;
            case 'list-articulos':
                $data = array();
                $page['title'] = 'Artículos';
                $page['subtitle'] = 'Listado de Productos';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                $data_form['categorias'] = $this->app->get_list_categorias($filtro);

                break;
            case 'list-componentes':
                $data = array();
                $page['title'] = 'Componentes';
                $page['subtitle'] = 'Listado de Componentes';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                $data_form['almacenes'] = $this->app->get_list_almacenes($filtro);

                break;

            case 'list-inventario':
                $data = array();
                $page['title'] = 'Inventario';
                $page['subtitle'] = 'Listado de Componentes';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                $data_form['almacenes'] = $this->app->get_list_almacenes($filtro);

                break;
            case 'componentes':
                $data = array();
                $page['title'] = 'Nuevo componente';
                $page['subtitle'] = 'Ficha del Componente';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                //$data_form['categorias'] = $this->app->get_list_categorias();
                $data_form['proveedores'] = $this->app->get_list_proveedores($filtro);
                $data_form['almacenes'] = $this->app->get_list_almacenes($filtro);
                //$data_form['repartidores'] = $this->app->get_list_repartidores($filtro);
                //print_r($data_form['categorias']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición del componente';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    /*
                      $sql = "select a.*,b.xusuario as xusuario_modificado
                      ,date_format(a.xdatemodif,'%d/%m/%Y %H:%i:%s') as xdatemodif_format"
                      . " from ms_articulos a"
                      . " left join ms_usuarios b on a.xusermodif_id=b.xusuario_id"
                      . " where a.xarticulo_id=:id";
                     * 
                     */
                    $sql = "select a.*,b.xusuario as xusuario_modificado"
                            . ",date_format(a.xdatemodif,'%d/%m/%Y %H:%i:%s') as xdatemodif_format"
                            . " from ms_componentes a"
                            . " left join ms_usuarios b on a.xusermodif_id=b.xusuario_id"
                            . " where a.xcomponente_id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $page['title'] = "{$row['xcomponente_id']} {$row['xcomponente']}";

                        $row['articulos-componentes'] = $this->app->get_list_componentes_articulos($row['xcomponente_id']);

                        $row['xcoste'] = number_format($row['xcoste'], 2, ',', '');
                        $row['xprecio'] = number_format($row['xprecio'], 2, ',', '');

                        $sql = "select a.*,b.xalmacen"
                                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                                . ",c.xusuario"
                                . " from ms_movimientos a"
                                . " left join ms_almacenes b on a.xalmacen_id=b.xalmacen_id"
                                . " left join ms_usuarios c on a.xusuario_id=c.xusuario_id"
                                . " where a.xcomponente_id={$row['xcomponente_id']}"
                                . " order by a.xmov_id desc"
                                . " limit 200";
                        //print($sql);
                        //die();
                        $row['movimientos'] = $this->db->fetchAll($sql);

                        $sql = "select a.*,b.xalmacen"
                                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                                . " from ms_movimientos a"
                                . " left join ms_almacenes b on a.xalmacen_id=b.xalmacen_id"
                                . " where a.xtype='E' and a.xcomponente_id={$row['xcomponente_id']}"
                                . " order by a.xmov_id desc"
                                . " limit 100";
                        //print($sql);
                        //die();
                        $row['xentradas_componente'] = $this->db->fetchAll($sql);

                        $sql = "select a.*,b.xalmacen"
                                . ",date_format(a.xfecha,'%d/%m/%Y %H:%i:%s') as xfecha_format"
                                . " from ms_movimientos a"
                                . " left join ms_almacenes b on a.xalmacen_id=b.xalmacen_id"
                                . " where a.xtype='S' and a.xcomponente_id={$row['xcomponente_id']}"
                                . " order by a.xmov_id desc"
                                . " limit 100";
                        //print($sql);
                        //die();
                        $row['xsalidas_componente'] = $this->db->fetchAll($sql);

                        $sql = "select a.*,b.xalmacen"
                                . " from ms_componentes_almacen a"
                                . " left join ms_almacenes b on a.xalmacen_id=b.xalmacen_id"
                                . " where a.xcomponente_id={$row['xcomponente_id']}"
                                . " order by a.xalmacen_id";
                        //print($sql);
                        //die();
                        $row['xstocks_componente'] = $this->db->fetchAll($sql);

                        $data = $row;
                        $page['subtitle'] = 'Componente: ' . $row['xcomponente_id'] . ' - ' . $row['xcomponente'];
                    }
                } else {
                    $data['xactivo'] = 'S';
                    $data['xcoste'] = '0,00';
                    $data['xprecio'] = '0,00';
                    $data['xarchivado'] = 'N';
                    $data['articulos-componentes'] = array();
                }
                break;
            case 'articulos':
                $data = array();
                $page['title'] = 'Nuevo artículo';
                $page['subtitle'] = 'Ficha de Artículo';

                $filtro = array(
                    'activo' => 'S'
                );

                $data_form = array();
                $data_form['categorias'] = $this->app->get_list_categorias();
                $data_form['proveedores'] = $this->app->get_list_proveedores($filtro);
                $data_form['repartidores'] = $this->app->get_list_repartidores($filtro);
                $data_form['componentes'] = $this->app->get_list_componentes($filtro);
                $data_form['revendedores'] = $this->app->get_list_revendedores($filtro);
                $data_form['config'] = $this->app->get_configuraciones();

                $filtro = array(
                    'almacenes' => '1,2'
                );
                $data_form['almacenes'] = $this->app->get_list_almacenes($filtro);

                //print_r($data_form['componentes']);
                //die();

                $action = 'insert';
                if (isset($param['id'])) {
                    $page['title'] = 'Edición producto';
                    $action = 'update';

                    $val = array(
                        'id' => $param['id']
                    );
                    $sql = "select a.*,b.xusuario as xusuario_modificado
                            ,date_format(a.xdatemodif,'%d/%m/%Y %H:%i:%s') as xdatemodif_format"
                            . " from ms_articulos a"
                            . " left join ms_usuarios b on a.xusermodif_id=b.xusuario_id"
                            . " where a.xarticulo_id=:id";
                    //print($sql);
                    //die();
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) {
                        $page['title'] = "{$row['xarticulo_id']} {$row['xarticulo']}";

                        $row['ximage'] = 'img/no-image.jpg';
                        if (file_exists(IMG_ARTICULOS . "/{$row['xarticulo_id']}.jpg"))
                            $row['ximage'] = "/img/msp/{$row['xarticulo_id']}.jpg?" . time();

                        $row['ximage_rev'] = 'img/no-image.jpg';
                        if (file_exists(IMG_REVENDEDORES . "/{$row['xarticulo_id']}.jpg"))
                            $row['ximage_rev'] = "/img/msrev/{$row['xarticulo_id']}.jpg?" . time();

                        $row['ximage_mr'] = 'img/no-image.jpg';
                        if (file_exists(IMG_ARTICULOS . "/{$row['xarticulo_id']}-mr.jpg"))
                            $row['ximage_mr'] = "/img/msp/{$row['xarticulo_id']}-mr.jpg?" . time();
                        $row['ximage_an'] = 'img/no-image.jpg';
                        if (file_exists(IMG_ARTICULOS . "/{$row['xarticulo_id']}-an.jpg"))
                            $row['ximage_an'] = "/img/msp/{$row['xarticulo_id']}-an.jpg?" . time();

                        $row['xseguro'] = number_format($row['xseguro'], 2, ',', '');
                        $row['xcoste'] = number_format($row['xcoste'], 2, ',', '');
                        $row['xprecio'] = number_format($row['xprecio'], 2, ',', '');
                        $row['xprecio_mr'] = number_format($row['xprecio_mr'], 2, ',', '');
                        $row['xprecio_old_mr'] = number_format($row['xprecio_old_mr'], 2, ',', '');
                        $row['xprecio_sd'] = number_format($row['xprecio_sd'], 2, ',', '');
                        $row['xprecio_old_sd'] = number_format($row['xprecio_old_sd'], 2, ',', '');
                        $row['xprecio_an'] = number_format($row['xprecio_an'], 2, ',', '');
                        $row['xprecio_old_an'] = number_format($row['xprecio_old_an'], 2, ',', '');
                        $row['xprecio1_rev'] = number_format($row['xprecio1_rev'], 2, ',', '');
                        $row['xprecio2_rev'] = number_format($row['xprecio2_rev'], 2, ',', '');
                        $row['xprecio3_rev'] = number_format($row['xprecio3_rev'], 2, ',', '');
                        $row['xprecio4_rev'] = number_format($row['xprecio4_rev'], 2, ',', '');
                        $row['xcoste_cup'] = number_format($row['xcoste_cup'], 2, ',', '');
                        $row['xcoste_reparto_cup'] = number_format($row['xcoste_reparto_cup'], 2, ',', '');
                        $row['xcategorias'] = $this->app->get_list_articulos_categorias($row['xarticulo_id']);
                        $row['xcomponentes'] = $this->app->get_list_articulos_componentes($row['xarticulo_id']);
                        //print_r($row['xcomponentes']);
                        //die();

                        $row['xprecio1_cup'] = number_format($row['xprecio1_rev'] * $data_form['config']['xvalor_cup'], 2, ',', '');
                        $row['xprecio2_cup'] = number_format($row['xprecio2_rev'] * $data_form['config']['xvalor_cup'], 2, ',', '');
                        $row['xprecio3_cup'] = number_format($row['xprecio3_rev'] * $data_form['config']['xvalor_cup'], 2, ',', '');
                        $row['xprecio4_cup'] = number_format($row['xprecio4_rev'] * $data_form['config']['xvalor_cup'], 2, ',', '');
                        $row['xprecio1_mlc'] = number_format($row['xprecio1_rev'] * $data_form['config']['xvalor_mlc'], 2, ',', '');
                        $row['xprecio2_mlc'] = number_format($row['xprecio2_rev'] * $data_form['config']['xvalor_mlc'], 2, ',', '');
                        $row['xprecio3_mlc'] = number_format($row['xprecio3_rev'] * $data_form['config']['xvalor_mlc'], 2, ',', '');
                        $row['xprecio4_mlc'] = number_format($row['xprecio4_rev'] * $data_form['config']['xvalor_mlc'], 2, ',', '');

                        $row['xvalor_cup_format'] = number_format($data_form['config']['xvalor_cup'], 2, ',', '');
                        $row['xvalor_mlc_format'] = number_format($data_form['config']['xvalor_mlc'], 2, ',', '');

                        $row['xprecio_fijo_cup_format'] = number_format($row['xprecio_fijo_cup'], 2, ',', '');

                        $row['xpermiso_agencias'] = explode(',', $row['xpermiso_agencias']);

                        $data = $row;
                        $page['subtitle'] = 'Artículo: ' . $row['xarticulo_id'] . ' - ' . $row['xarticulo'];

                        //print_r($data);
                        //die();
                    }
                } else {
                    $data['xactivo'] = 'N';
                    $data['xactivo_rev'] = 'N';
                    $data['xactivo_web'] = 'N';
                    $data['xactivo_web_mr'] = 'N';
                    $data['xactivo_web_sd'] = 'N';
                    $data['xactivo_web_an'] = 'N';
                    $data['xeditable_coste_cup'] = 'N';
                    $data['xseguro'] = '0,00';
                    $data['xcoste'] = '0,00';
                    $data['xprecio'] = '0,00';
                    $data['xprecio_mr'] = '0,00';
                    $data['xprecio_old_mr'] = '0,00';
                    $data['xprecio_sd'] = '0,00';
                    $data['xprecio_old_sd'] = '0,00';
                    $data['xprecio_an'] = '0,00';
                    $data['xprecio_old_an'] = '0,00';
                    $data['xprecio1_rev'] = '0,00';
                    $data['xprecio2_rev'] = '0,00';
                    $data['xprecio3_rev'] = '0,00';
                    $data['xcoste_cup'] = '0,00';
                    $data['xcoste_reparto_cup'] = '0,00';
                    $data['xpreventa'] = 'N';
                    $data['xdestacado'] = 'N';
                    $data['xdestacado_mr'] = 'N';
                    $data['xservicio'] = 'N';
                    $data['xrecarga'] = 'N';
                    $data['xinfadd_activo'] = 'N';
                    $data['ximage'] = 'img/no-image.jpg';
                    $data['ximage_mr'] = 'img/no-image.jpg';
                    $data['ximage_sd'] = 'img/no-image.jpg';
                    $data['ximage_rev'] = 'img/no-image.jpg';
                    $data['ximage_an'] = 'img/no-image.jpg';
                }
                break;
        }
    }

    private function _create_images($param) {
        $data = array(
            'status' => 1,
            'msg' => 'Imagen creada para todos los revendedor'
        );

        if ($data['status'] == 1) {
            $sql = "select * from ms_revendedores where xeliminado=0 and xactivo='S'";
            $rr = $this->db->fetchAll($sql);

            $src = IMG_REVENDEDORES . "/{$param['id']}.jpg";

            if (!file_exists($src)) {
                $data['status'] = 0;
                $data['msg'] = "Imagen del producto no encontrado.";
            }

            foreach ($rr as $k => $v) {

                $logo = IMG_REVENDEDORES_ADMIN . "/{$v['xrevendedor_id']}.jpg";

                if (file_exists($logo)) {
                    $dst = IMG_REVENDEDORES . "/{$param['id']}-rev-{$v['xrevendedor_id']}.jpg";
                    //copy($src,$dst);

                    $src_width = 0;
                    $src_height = 0;
                    $logo_width = 0;
                    $logo_height = 0;

                    $src_img = imagecreatefromjpeg($src);
                    list($src_width, $src_height) = getimagesize($src);
                    $logo_img = imagecreatefromjpeg($logo);
                    list($logo_width, $logo_height) = getimagesize($logo);

                    $montaje = imagecreatetruecolor($src_width, $src_height);

                    imagecopyresized($montaje, $src_img, 0, 0, 0, 0, $src_width, $src_height, $src_width, $src_height);
                    imagecopyresized($montaje, $logo_img, 2, 20, 0, 0, 140, 38, $logo_width, $logo_height);

                    imagejpeg($montaje, $dst);

                    imagedestroy($src_img);
                    imagedestroy($logo_img);
                    imagedestroy($montaje);
                }
            }
        }

        return $data;
    }

    private function _dl_pdf_revendedor($param) {
        $catalogo = array();

        $val = array(
            'id' => '1'
        );
        $sql = "select *"
                . " from ms_configuraciones"
                . " where xconfig_id=:id";
        $cfg = $this->db->fetchRow($sql, $val);
        //print_r($cfg['xporc_servicio']);
        //die();

        $sql = "select * from ms_articulos where xeliminado=0 and xarticulo_id in ({$param['arts']})";
        $catalogo[]['items'] = $this->db->fetchAll($sql);
        $tarifa = 3;
        /*
         * DE ESTA MANERA SALE LOS CATÁLOGOS POR CATEGORÍAS Y PUEDEN REPETIRSE LOS PRODUCTOS EN CADA CATEGORIA
          $sql = "select xcategoria_id,xcategoria from ms_categorias where xcategoria_id in ({$param['cats']})";
          //print($sql);
          //die();
          $catalogo = $this->db->fetchAll($sql);
          foreach ($catalogo as $k => $v) {
          $sql = "select * from ms_articulos where xeliminado=0 and xarticulo_id in ({$param['arts']})"
          . " and xarticulo_id in (select xarticulo_id from ms_articulos_categorias where xcategoria_id={$v['xcategoria_id']})";
          $catalogo[$k]['items'] = $this->db->fetchAll($sql);
          }
         * 
         */
        //print(json_encode($catalogo));
        //die();
        //print(json_encode($data));
        //die();

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(10, 10);

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetXY(10, 10);
        $pdf->Cell(190, 8, utf8_decode('CATÁLOGO'), 'B', 1, 'C');
        $pdf->Ln(5);

        //$pdf->MultiCell(60, 5, "{$lbl['xnombre']} {$lbl['xnombre2']} {$lbl['xapellido1']} {$lbl['xapellido2']}", 0, 'L');

        foreach ($catalogo as $kc => $vc) {
            $pdf->SetFont('Arial', 'B', 16);
            //$pdf->Cell(190, 8, utf8_decode($vc['xcategoria']), 'B', 1, 'C');
            foreach ($vc['items'] as $k => $v) {
                $precio_servicio = $v['xprecio' . $tarifa . '_rev'] * (1 + ($cfg['xporc_servicio'] / 100));
                $img = IMG_REVENDEDORES . '/' . $v['xarticulo_id'] . '.jpg';
                if (file_exists($img)) {
                    $pdf->Image($img, 10, $pdf->GetY(), 50, null, 'JPEG');
                }
                $pdf->setX(60);
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(100, 8, utf8_decode($v['xarticulo_rev']), 0, 2, 'L');
                $pdf->SetFont('Arial', 'B', 14);
                //$pdf->Ln(3);
                $pdf->Cell(50, 8, utf8_decode('Precio: ' . number_format($v['xprecio' . $tarifa . '_rev'], 2, ',', '.')), 0, 0, 'L');
                //$pdf->Cell(60, 8, utf8_decode('Precio ' . number_format($cfg['xporc_servicio'], 2, ',', '') . '%: ' . number_format($precio_servicio, 2, ',', '.')), 0, 1, 'L');
                $pdf->Cell(60, 8, '', 0, 1, 'L');
                $pdf->Ln(25);
                $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
                $pdf->Ln(5);

                if (($k + 1) % 6 == 0) {
                    $pdf->AddPage();
                    $pdf->SetAutoPageBreak(FALSE, 0);
                    $pdf->SetMargins(10, 10);
                    $pdf->SetFont('Arial', 'B', 16);
                    $pdf->SetXY(10, 10);
                    $pdf->Cell(190, 8, utf8_decode('CATÁLOGO'), 'B', 1, 'C');
                    $pdf->Ln(5);
                }
            }
        }




        $pdf->Output('catálogo-revendedor.pdf', 'D');
    }

    private function _recargas($param) {
        $data = array();
        $cond = '';
        if (isset($param['id']) && $param['id'] != '') {
            $cond .= " and b.xarticulo_id={$param['id']}";
        }

        if ($cond != '') {
            $sql = "select a.*,ifnull(b.xrecargas,'') as xrecargas"
                    . ",ifnull(b.xcoste,0) as xcoste"
                    . " from ms_proveedores a"
                    . " left join ms_proveedores_recargas b on a.xproveedor_id=b.xproveedor_id$cond"
                    . " where a.xeliminado=0"
                    . " order by a.xproveedor_id";
            //print($sql);
            //die();
        } else {
            $sql = "select a.*,'' as xrecargas,'0' as xcoste"
                    . " from ms_proveedores a"
                    . " where a.xeliminado=0 $cond"
                    . " order by a.xproveedor_id";
        }
        $data = $this->db->fetchAll($sql);

        foreach ($data as $k => $v) {
            $data[$k]['xcoste'] = number_format($v['xcoste'], 2, ',', '');
        }

        return $data;
    }

    private function _precios($param) {
        $data = array();

        $sql = "select a.*"
                . " from ms_articulos a"
                . " where a.xeliminado=0"
                . " order by a.xarticulo_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);

        foreach ($data as $k => $v) {
            $data[$k]['xprecio'] = number_format($v['xprecio'], 2, ',', '');
            $data[$k]['xprecio1_rev'] = number_format($v['xprecio1_rev'], 2, ',', '');
            $data[$k]['xprecio2_rev'] = number_format($v['xprecio2_rev'], 2, ',', '');
            $data[$k]['xprecio3_rev'] = number_format($v['xprecio3_rev'], 2, ',', '');
        }
        return $data;
    }

    private function _precios_coste($param) {
        $data = array();

        $sql = "select a.*"
                . " from ms_articulos a"
                . " where a.xeliminado=0"
                . " order by a.xarticulo_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);

        foreach ($data as $k => $v) {
            $data[$k]['xprecio'] = number_format($v['xprecio'], 2, ',', '');
            $data[$k]['xcoste'] = number_format($v['xcoste'], 2, ',', '');
            $data[$k]['xprecio1_rev'] = number_format($v['xprecio1_rev'], 2, ',', '');
            $data[$k]['xprecio2_rev'] = number_format($v['xprecio2_rev'], 2, ',', '');
            $data[$k]['xprecio3_rev'] = number_format($v['xprecio3_rev'], 2, ',', '');
        }
        return $data;
    }

    private function _precios_coste_componente($param) {
        $data = array();
        $cond = '';

        if (isset($param['activo']) && $param['activo'] != '') {
            $cond .= " and a.xactivo='{$param['activo']}'";
        }

        $sql = "select a.*"
                . " from ms_componentes a"
                . " where a.xeliminado=0$cond"
                . " order by a.xcomponente_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);

        foreach ($data as $k => $v) {
            $data[$k]['xprecio'] = number_format($v['xprecio'], 2, ',', '');
            $data[$k]['xcoste'] = number_format($v['xcoste'], 2, ',', '');
        }
        return $data;
    }

    private function _del($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        $update = array(
            'xeliminado' => 1,
            'xusermodif_id' => $this->app->user_id,
            'xdatemodif' => date(dateSQL)
        );
        $where = array(
            'xarticulo_id' => $param['id']
        );
        $this->app->db->update('ms_articulos', $update, $where);

        $history = array(
            'xentity' => 'ARTICULOS',
            'xaction' => 'DEL-ARTICULO',
            'xid' => $param['id'],
            'xobs' => 'DEL ARTICULO: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }

    private function _del_componente($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'row' => $param['row']
        );

        $update = array(
            'xeliminado' => 1,
            'xusermodif_id' => $this->app->user_id,
            'xdatemodif' => date(dateSQL)
        );
        $where = array(
            'xcomponente_id' => $param['id']
        );
        $this->app->db->update('ms_componentes', $update, $where);

        $history = array(
            'xentity' => 'COMPONENTES',
            'xaction' => 'DEL-COMPONENTE',
            'xid' => $param['id'],
            'xobs' => 'DEL COMPONENTE: ' . $param['id']
        );
        $this->app->add_history($history);

        print(json_encode($data));
    }

    private function _save($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => ''
        );

        //print(json_encode($param));
        //die();

        $img = $param['ximg'];
        unset($param['ximg']);

        $img_rev = $param['ximg_rev'];
        unset($param['ximg_rev']);

        $img_mr = $param['ximg_mr'];
        unset($param['ximg_mr']);

        $img_an = $param['ximg_an'];
        unset($param['ximg_an']);

        $lin = $param['lin'];
        unset($param['lin']);

        $linc = $param['linc'];
        unset($param['linc']);

        $categorias = $param['xcategorias'];
        unset($param['xcategorias']);

        if (isset($param['xurl_amigable']) && $param['xurl_amigable'] != '') {
            if ($param['action'] == 'update') {
                $param['xurl_amigable'] = strtolower(str_replace(array('+', '*', 'ñ', '!', '?', '¡', '¿', '.'), '', $param['xurl_amigable']));
                $sql = "select * from ms_seo where xmodule='product' and xid!='{$param['xarticulo_id']}'"
                        . " and xurl_amigable='{$param['xurl_amigable']}'";
                $row = $this->db->fetchRow($sql);
                if ($row) {
                    $data['status'] = 0;
                    $data['msg'] = 'La expresión de la url amigable MS ya existe';
                }
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'La expresión de la url amigable MS es obligatoria para todas las webs.';
        }

        if (isset($param['xurl_amigable_mr']) && $param['xurl_amigable_mr'] != '') {
            if ($param['action'] == 'update') {
                $param['xurl_amigable_mr'] = strtolower(str_replace(array('+', '*', 'ñ', '!', '?', '¡', '¿', '.'), '', $param['xurl_amigable_mr']));
                $sql = "select * from ms_seo where xmodule='product' and xid!='{$param['xarticulo_id']}'"
                        . " and xurl_amigable_mr='{$param['xurl_amigable_mr']}'";
                $row = $this->db->fetchRow($sql);
                if ($row) {
                    $data['status'] = 0;
                    $data['msg'] = 'La expresión de la url amigable MR ya existe';
                }
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'La expresión de la url amigable MR es obligatoria para todas las webs.';
        }

        if (isset($param['xurl_amigable_an']) && $param['xurl_amigable_an'] != '') {
            if ($param['action'] == 'update') {
                $param['xurl_amigable_an'] = strtolower(str_replace(array('+', '*', 'ñ', '!', '?', '¡', '¿', '.'), '', $param['xurl_amigable_an']));
                $sql = "select * from ms_seo where xmodule='product' and xid!='{$param['xarticulo_id']}'"
                        . " and xurl_amigable_an='{$param['xurl_amigable_an']}'";
                $row = $this->db->fetchRow($sql);
                if ($row) {
                    $data['status'] = 0;
                    $data['msg'] = 'La expresión de la url amigable AN ya existe';
                }
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'La expresión de la url amigable AN es obligatoria para todas las webs.';
        }

        $insert = $param;

        $data['action'] = $insert['action'];

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            $insert['xseguro'] = str_replace(',', '.', $insert['xseguro']);
            $insert['xcoste'] = str_replace(',', '.', $insert['xcoste']);
            $insert['xprecio'] = str_replace(',', '.', $insert['xprecio']);
            $insert['xprecio_mr'] = str_replace(',', '.', $insert['xprecio_mr']);
            $insert['xprecio_old_mr'] = str_replace(',', '.', $insert['xprecio_old_mr']);
            $insert['xprecio_sd'] = str_replace(',', '.', $insert['xprecio_sd']);
            $insert['xprecio_old_sd'] = str_replace(',', '.', $insert['xprecio_old_sd']);
            $insert['xprecio1_rev'] = str_replace(',', '.', $insert['xprecio1_rev']);
            $insert['xprecio2_rev'] = str_replace(',', '.', $insert['xprecio2_rev']);
            $insert['xprecio3_rev'] = str_replace(',', '.', $insert['xprecio3_rev']);
            $insert['xprecio_fijo_cup'] = str_replace(',', '.', $insert['xprecio_fijo_cup']);
            $insert['xcoste_cup'] = str_replace(',', '.', $insert['xcoste_cup']);
            $insert['xcoste_reparto_cup'] = str_replace(',', '.', $insert['xcoste_reparto_cup']);

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xeliminado'] = '0';
                $insert['xhash'] = $this->app->rndString(20);
                $this->app->db->insert('ms_articulos', $insert);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'INSERT-ARTICULO',
                    'xid' => $data['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' ' . $insert['xarticulo']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xarticulo_id' => $update['xarticulo_id']
                );
                unset($update['xarticulo_id']);

                $this->app->db->update('ms_articulos', $update, $where);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $where['xarticulo_id'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'UPDATE-ARTICULO',
                    'xid' => $insert['xarticulo_id'],
                    'xobs' => 'ARTICULOS: ' . $insert['xarticulo_id'] . ' ' . $insert['xarticulo'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);

                //ACTUALIZAMOS EL PRECIO EN TODAS LAS CESTAS POR SI SE HUBIERA MODIFICADO
                $sql = "update ms_cesta_lin set xprecio={$update['xprecio']}"
                        . " where xarticulo_id={$data['id']}"
                        . " and xcesta_id in (select xcesta_id from ms_cesta where xestado='P')";
                $this->db->directExec($sql);

                //ACTUALIZAMOS EL REPARTIDOR EN TODOS LOS ENVIOS
                $sql = "update ms_pedidos_proveedor set xrepartidor_id={$update['xrepartidor_id']}"
                        . ",xcoste_cup={$update['xcoste_cup']},xcoste_reparto_cup={$update['xcoste_reparto_cup']}"
                        . " where xarticulo_id={$data['id']}"
                        . " and xpreparado='N' and xestado_id in (19,20)";
                $this->db->directExec($sql);

                //ACTUALIZAMOS EL PRECIO EN TODOS LOS PEDIDOS DE REVENDEDORES CON ESTADO PENDIENTE DE PAGO(1)
                //MODIFICAMOS LINEAS DE PEDIDOS DE REVENDEDORES
                //$sql="update ms_pedidos_lin set xprecio={$update['xprecio']},ximporte={$update['xprecio']}*x"
                //. " where xarticulo_id={$data['id']}"
                //. " and xpedido_id in (select xpedido_id from ms_cesta where xestado='P')";
                //$this->db->directExec($sql);
                //RECALCULAMOS EL TOTAL DE CADA PEDIDO
            }

            $val = array(
                'art' => $data['id']
            );
            $sql = "delete from ms_articulos_categorias where xarticulo_id=:art";
            $this->db->directExec($sql, $val);
            $tmp = explode(',', $categorias);
            foreach ($tmp as $k => $v) {
                $insert = array(
                    'xarticulo_id' => $data['id'],
                    'xcategoria_id' => $v
                );
                $this->db->insert('ms_articulos_categorias', $insert);
            }

            //ELIMINAMOS Y ACTUALIZAMOS SEO
            $where = array(
                'xmodule' => 'product',
                'xid' => $data['id']
            );
            $this->db->del('ms_seo', $where);
            $sql = "select xhash from ms_articulos where xarticulo_id={$data['id']}";
            $cat = $this->db->fetchRow($sql);
            if ($cat) {
                $insert_seo = array(
                    'xmodule' => 'product',
                    'xid' => $data['id'],
                    'xhash' => $cat['xhash'],
                    'xurl_amigable' => $param['xurl_amigable'],
                    'xurl_amigable_mr' => $param['xurl_amigable_mr'],
                    'xurl_amigable_an' => $param['xurl_amigable_an']
                );
                $this->db->insert('ms_seo', $insert_seo);
            }

            if ($img != '') {
                $src = TMP . "/$img";
                $dst = IMG_ARTICULOS . "/{$data['id']}.jpg";
                copy($src, $dst);
                unlink($src);
            }

            if ($img_rev != '') {
                $src = TMP . "/$img_rev";
                $dst = IMG_REVENDEDORES . "/{$data['id']}.jpg";
                copy($src, $dst);
                unlink($src);
            }

            if ($img_mr != '') {
                $src = TMP . "/$img_mr";
                $dst = IMG_ARTICULOS . "/{$data['id']}-mr.jpg";
                copy($src, $dst);

                $dst = IMG_ARTICULOS_MERCARAPID . "/{$data['id']}.jpg";
                copy($src, $dst);

                unlink($src);
            }

            if ($img_an != '') {
                $src = TMP . "/$img_an";
                $dst = IMG_ARTICULOS . "/{$data['id']}-an.jpg";
                copy($src, $dst);

                $dst = IMG_ARTICULOS_ALLNOVU . "/{$data['id']}.jpg";
                copy($src, $dst);

                unlink($src);
            }

            if ($lin != '') {
                $rl = explode('#', $lin);
                $where = array(
                    'xarticulo_id' => $data['id']
                );
                $this->db->del('ms_proveedores_recargas', $where);
                foreach ($rl as $k => $v) {
                    $d = explode('|', $v);
                    $insert = array(
                        'xproveedor_id' => $d[0],
                        'xarticulo_id' => $data['id'],
                        'xrecargas' => $d[1],
                        'xcoste' => $d[2]
                    );
                    $this->db->insert('ms_proveedores_recargas', $insert);
                }
            }

            //SI HAY COMPONENTES SE ELIMINAN Y SE ACTUALIZAN DE NUEVO
            if ($linc != '') {
                $rl = explode('#', $linc);
                $where = array(
                    'xarticulo_id' => $data['id']
                );
                $this->db->del('ms_articulos_componentes', $where);
                foreach ($rl as $k => $v) {
                    $d = explode('|', $v);
                    $insert = array(
                        'xcomponente_id' => $d[0],
                        'xarticulo_id' => $data['id'],
                        'xcantidad' => $d[1]
                    );
                    $this->db->insert('ms_articulos_componentes', $insert);
                }
            }

            //ACTUALIZAMOS COSTE DE COSTES ACTIVOS
            $val = array(
                'coste' => $param['xcoste'],
                'art' => $data['id']
            );
            $sql = "update ms_pedidos_proveedor set xcoste=:coste where xarticulo_id=:art and xcorte_id in (select xcorte_id from ms_cortes_envios where xactivo='S') or xcorte_id is null";
            $this->db->directExec($sql, $val);

            //ACTUALIZAMOS PRECIOS REVENDEDORES POR SI NO TIENE ESTE PRECIO
            $this->_update_precios_revendedor($data['id']);
        }

        return $data;
    }

    private function _copy($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => 'copy'
        );

        /*
          ini_set('display_errors', 1);
          ini_set('display_startup_errors', 1);
          error_reporting(E_ALL);
         * 
         */



        if ($data['status'] == 1) {

            $sql = "select * from ms_articulos where xarticulo_id={$param['id']}";
            $art = $this->db->fetchRow($sql);

            //DAMOS ALTA ARTÍCULO
            $insert = $art;
            unset($insert['xarticulo_id']);
            unset($insert['xusermodif_id']);
            unset($insert['xdatemodif']);
            $insert['xarticulo'] = $insert['xarticulo'] . ' COPY';
            $insert['xuseralta_id'] = $this->app->user_id;
            $insert['xdatealta'] = date(dateSQL);
            $insert['xeliminado'] = '0';
            $insert['xhash'] = $this->app->rndString(20);

            $this->app->db->insert('ms_articulos', $insert);
            $data['msg_title'] = OPERATION_SUCCESS;
            $data['msg'] = RECORD_INSERT;
            $data['id'] = $this->db->last_id();

            $sql = "select * from ms_articulos_categorias where xarticulo_id={$param['id']}";
            $cat = $this->db->fetchAll($sql);
            foreach ($cat as $k => $v) {
                $insert = $v;
                $insert['xarticulo_id'] = $data['id'];
                $this->app->db->insert('ms_articulos_categorias', $insert);
            }

            $sql = "select * from ms_proveedores_recargas where xarticulo_id={$param['id']}";
            $rec = $this->db->fetchAll($sql);
            foreach ($rec as $k => $v) {
                $insert = $v;
                $insert['xarticulo_id'] = $data['id'];
                $this->app->db->insert('ms_proveedores_recargas', $insert);
            }

            $sql = "select * from ms_articulos_componentes where xarticulo_id=1;";
            $com = $this->db->fetchAll($sql);
            foreach ($com as $k => $v) {
                $insert = $v;
                $insert['xarticulo_id'] = $data['id'];
                $this->app->db->insert('ms_articulos_componentes', $insert);
            }

            $history = array(
                'xentity' => 'ARTICULOS',
                'xaction' => 'COPY-ARTICULO',
                'xid' => $data['id'],
                'xobs' => 'ARTICULO: ' . $data['id'] . ' ' . $art['xarticulo']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    private function _save_componente($param) {
        $data = array(
            'status' => 1,
            'msg_title' => '',
            'msg' => '',
            'action' => ''
        );

        //print(json_encode($param));
        //die();

        foreach ($param as $k => $v) {
            $param[$k] = trim($v);
        }

        $insert = $param;

        $data['action'] = $insert['action'];

        if ($data['status'] == 1) {
            unset($insert['module']);
            unset($insert['method']);
            unset($insert['action']);

            $insert['xcoste'] = str_replace(',', '.', $insert['xcoste']);
            $insert['xprecio'] = str_replace(',', '.', $insert['xprecio']);

            if ($data['action'] == 'insert') {
                $insert['xuseralta_id'] = $this->app->user_id;
                $insert['xdatealta'] = date(dateSQL);
                $insert['xeliminado'] = '0';
                $this->app->db->insert('ms_componentes', $insert);

                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_INSERT;
                $data['id'] = $this->db->last_id();
                $data['date'] = date('d-m-Y H:i:s');

                $url_amigable = strtolower(str_replace(array('-', 'ñ', 'ó', 'í', 'á', 'ú', ',', ';', '@', '&', '#', '"'), '_', $param['xcomponente']));
                $param_art = array(
                    "module" => "articulos",
                    "method" => "save",
                    "ximg" => "",
                    "ximg_rev" => "",
                    "ximg_mr" => "",
                    "xarticulo" => $param['xcomponente'],
                    "xactivo" => "N",
                    "xpreventa" => "N",
                    "xprecio" => "0,00",
                    "xcoste" => "0,00",
                    "xdestacado" => "N",
                    "xactivo_web" => "N",
                    "xactivo_venta" => "S",
                    "xactivo_rev" => "N",
                    "xactivo_venta_rev" => "S",
                    "xalmacen_id" => "0",
                    "xorden" => "",
                    "xseguro" => "0,00",
                    "xservicio" => "N",
                    "xproveedor_id" => "0",
                    "xfactor" => "1",
                    "xrepartidor_id" => "0",
                    "xarticulo_pro" => "",
                    "xcoste_cup" => "0,00",
                    "xcoste_reparto_cup" => "0,00",
                    "xeditable_coste_cup" => "N",
                    "xprecio1_rev" => "0,00",
                    "xprecio2_rev" => "0,00",
                    "xprecio3_rev" => "0,00",
                    "xarticulo_rev" => "",
                    "xaduana" => "",
                    "xrecarga" => "N",
                    "ximage_alt" => "",
                    "xurl_amigable" => $url_amigable,
                    "xnota_stock" => "",
                    "xreposicion" => "S",
                    "xcantidad_maxima" => "",
                    "xkeywords" => "",
                    "xpage_title" => "",
                    "xpage_description" => "",
                    "xinfadd_title" => "",
                    "xinfadd_activo" => "N",
                    "xarticulo_mr" => "",
                    "xprecio_mr" => "0,00",
                    "xprecio_old_mr" => "0,00",
                    "xdestacado_mr" => "N",
                    "xactivo_web_mr" => "N",
                    "ximage_alt_mr" => "",
                    "xurl_amigable_mr" => $url_amigable,
                    "xarticulo_sd" => "",
                    "xprecio_sd" => "0,00",
                    "xprecio_old_sd" => "0,00",
                    "xdestacado_sd" => "S",
                    "xactivo_web_sd" => "N",
                    "ximage_alt_sd" => "",
                    "xurl_amigable_sd" => $url_amigable,
                    "action" => "insert",
                    "xcategorias" => "",
                    "xobs" => "<p><br><\/p>",
                    "xresumen" => "<p><br><\/p>",
                    "xobs_mr" => "<p><br><\/p>",
                    "xresumen_mr" => "<p><br><\/p>",
                    "xinfadd_content" => "<p><br><\/p>",
                    "lin" => "1||0.00#2||0.00#3||0.00#4||0.00#5||0.00#6||0.00#7||0.00#8||0.00#9||0.00#10||0.00#11||0.00#12||0.00#13||0.00#14||0.00#15||0.00#16||0.00#17||0.00#18||0.00#20||0.00#21||0.00",
                    "linc" => ""
                );

                //$data_art = $this->_save($param_art);
                //print(json_encode($data_art));
                //die();
                //ASOCIAMOS EL COMPONENTE CON EL ARTÍCULO
                /*
                  $insert_comp = array(
                  'xarticulo_id' => $data_art['id'],
                  'xcomponente_id' => $data['id'],
                  'xcantidad' => 1
                  );
                  $this->app->db->insert('ms_articulos_componentes', $insert_comp);
                 * 
                 */

                $history = array(
                    'xentity' => 'COMPONENTES',
                    'xaction' => 'INSERT-COMPONENTE',
                    'xid' => $data['id'],
                    'xobs' => 'COMPONENTE: ' . $data['id'] . ' ' . $insert['xcomponente']
                );
                $this->app->add_history($history);
            } else {
                //update
                $update = $insert;

                $update['xusermodif_id'] = $this->app->user_id;
                $update['xdatemodif'] = date(dateSQL);
                $where = array(
                    'xcomponente_id' => $update['xcomponente_id']
                );
                unset($update['xcomponente_id']);

                $this->app->db->update('ms_componentes', $update, $where);
                $data['msg_title'] = OPERATION_SUCCESS;
                $data['msg'] = RECORD_UPDATE;
                $data['id'] = $where['xcomponente_id'];

                $history = array(
                    'xentity' => 'COMPONENTES',
                    'xaction' => 'UPDATE-COMPONENTE',
                    'xid' => $insert['xcomponente_id'],
                    'xobs' => 'COMPONENTES: ' . $insert['xcomponente_id'] . ' ' . $insert['xcomponente'] // . ' ' . $fields_change
                );
                $this->app->add_history($history);
            }
        }

        return $data;
        //print(json_encode($data));
    }

    private function _add_stock($param) {
        /*
          ini_set('display_errors', 1);
          ini_set('display_startup_errors', 1);
          error_reporting(E_ALL);
         * 
         */

        $data = array(
            'status' => 1,
            'msg' => '',
            'id' => 0
        );

        if ($param['cantidad'] < 0 && $this->app->rol != 1) {
            $data['status'] = 0;
            $data['msg'] = 'No tiene permiso para realizar salidas de componentes. Solo puede añadir componentes.';
        }

        $sql = "select * from ms_componentes_almacen where xcomponente_id={$param['componente']} and xalmacen_id={$param['almacen']}";
        $sto = $this->db->fetchRow($sql);

        if ($data['status'] == 1) {
            $type = 'E';
            if ($param['cantidad'] < 0)
                $type = 'S';
            $insert = array(
                'xusuario_id' => $this->app->user_id,
                'xfecha' => date(dateSQL),
                'xcantidad' => $param['cantidad'],
                'xconcepto' => $param['concepto'],
                'xtype' => $type,
                'xalmacen_id' => $param['almacen'],
                'xcomponente_id' => $param['componente'],
                'xstock_before' => $sto['xstock'],
                'ximporte' => $param['coste']
            );
            $this->app->db->insert('ms_movimientos', $insert);
            $data['msg_title'] = OPERATION_SUCCESS;
            $data['msg'] = RECORD_INSERT;
            $data['id'] = $this->db->last_id();
            $data['date'] = date('d-m-Y H:i:s');

            if ($data['id'] > 0) {
                $update = array(
                    'xcoste' => $param['coste']
                );
                $where = array(
                    'xcomponente_id' => $param['componente']
                );
                $this->db->update('ms_componentes', $update, $where);

                include_once(BASE_CLASS . '/mdl.Almacenes.php');
                $alm = new Almacen($this->app);
                $alm->exec_movimiento($data['id']);

                $sql = "update ms_articulos set xactivo_web='S'"
                        . " where xarticulo_id in (select xarticulo_id"
                        . "                          from ms_articulos_componentes"
                        . "                          where xcomponente_id={$param['componente']})";
                //$this->db->directExec($sql);
            }

            $history = array(
                'xentity' => 'ALMACÉN',
                'xaction' => 'INSERT-STOCK',
                'xid' => $data['id'],
                'xobs' => 'ALMACÉN: ' . $data['id']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    private function _add_traspaso($param) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $data = array(
            'status' => 1,
            'msg' => 'Traspaso realizado correctamente.'
        );

        if (!in_array($this->app->rol, array(1, 12))) {
            $data['status'] = 0;
            $data['msg'] = 'No tiene permiso para realizar traspasos de componentes.';
        }

        if ($data['status'] == 1 & $param['cantidad'] < 0) {
            $data['status'] = 0;
            $data['msg'] = 'El campo cantidad no puede ser negativo.';
        }

        if ($data['status'] == 1 & $param['concepto'] == '') {
            $data['status'] = 0;
            $data['msg'] = 'El campo concepto no puede estar vacío.';
        }

        $sql = "select * from ms_componentes_almacen where xcomponente_id={$param['componente']} and xalmacen_id={$param['almacen-src']}";
        $sto_src = $this->db->fetchRow($sql);
        if ($sto_src) {
            if ($sto_src['xstock'] < 0) {
                $data['status'] = 0;
                $data['msg'] = 'El almacén origen no tiene stock suficiente para ejecutar el traspaso.';
            }
        } else {
            $data['status'] = 0;
            $data['msg'] = 'El almacén origen no tiene stock suficiente para ejecutar el traspaso.';
        }
        if ($data['status'] == 1) {
            $sql = "select * from ms_componentes_almacen where xcomponente_id={$param['componente']} and xalmacen_id={$param['almacen-dst']}";
            $sto_dst = $this->db->fetchRow($sql);
            if (!isset($sto_dst['xstock'])) {
                $sto_dst = array();
                $sto_dst['xstock'] = 0;
            }

            //GENERAR SALIDA DE ORIGEN
            $type = 'S';
            $insert = array(
                'xusuario_id' => $this->app->user_id,
                'xfecha' => date(dateSQL),
                'xcantidad' => $param['cantidad'] * (-1),
                'xconcepto' => "Traspaso mov.salida {$param['concepto']}",
                'xtype' => $type,
                'xalmacen_id' => $param['almacen-src'],
                'xcomponente_id' => $param['componente'],
                'xstock_before' => $sto_src['xstock'],
                'ximporte' => $param['coste']
            );
            $this->app->db->insert('ms_movimientos', $insert);
            $data['msg_title'] = OPERATION_SUCCESS;
            //$data['msg'] = RECORD_INSERT;
            $data['id-salida'] = $this->db->last_id();
            $data['date'] = date('d-m-Y H:i:s');

            if ($data['id-salida'] > 0) {
                $update = array(
                    'xcoste' => $param['coste']
                );
                $where = array(
                    'xcomponente_id' => $param['componente']
                );
                $this->db->update('ms_componentes', $update, $where);

                include_once(BASE_CLASS . '/mdl.Almacenes.php');
                $alm = new Almacen($this->app);
                $alm->exec_movimiento($data['id-salida']);
            }

            $history = array(
                'xentity' => 'ALMACÉN',
                'xaction' => 'TRASPASO-STOCK-SRC',
                'xid' => $data['id-salida'],
                'xobs' => 'MOVIMIENTO SALIDA: ' . $data['id-salida']
            );
            $this->app->add_history($history);

            //GENERAR ENTRADA DESTINO
            $type = 'E';
            $insert = array(
                'xusuario_id' => $this->app->user_id,
                'xfecha' => date(dateSQL),
                'xcantidad' => $param['cantidad'],
                'xconcepto' => "Traspaso mov.entrada {$param['concepto']}",
                'xtype' => $type,
                'xalmacen_id' => $param['almacen-dst'],
                'xcomponente_id' => $param['componente'],
                'xstock_before' => $sto_dst['xstock'],
                'ximporte' => $param['coste']
            );
            $this->app->db->insert('ms_movimientos', $insert);
            $data['msg_title'] = OPERATION_SUCCESS;
            //$data['msg'] = RECORD_INSERT;
            $data['id-entrada'] = $this->db->last_id();
            $data['date'] = date('d-m-Y H:i:s');

            if ($data['id-entrada'] > 0) {
                $update = array(
                    'xcoste' => $param['coste']
                );
                $where = array(
                    'xcomponente_id' => $param['componente']
                );
                $this->db->update('ms_componentes', $update, $where);

                include_once(BASE_CLASS . '/mdl.Almacenes.php');
                $alm = new Almacen($this->app);
                $alm->exec_movimiento($data['id-entrada']);
            }

            $history = array(
                'xentity' => 'ALMACÉN',
                'xaction' => 'TRASPASO-STOCK-DST',
                'xid' => $data['id-entrada'],
                'xobs' => 'MOVIMIENTO ENTRADA: ' . $data['id-entrada']
            );
            $this->app->add_history($history);
        }
        print(json_encode($data));
    }

    private function _update_precios_revendedor($art_id) {
        $sql = "select * from ms_articulos where xarticulo_id={$art_id}";
        $art = $this->db->fetchRow($sql);

        $sql = "select * from ms_revendedores where xeliminado=0 and xtarifa_id in (1,2,3)";
        $rev = $this->db->fetchAll($sql);

        foreach ($rev as $k => $v) {
            $precio = $art["xprecio{$v['xtarifa_id']}_rev"];

            $sql = "select * from ms_revendedores_precios where xrevendedor_id={$v['xrevendedor_id']} and xarticulo_id={$art_id}";
            $row = $this->db->fetchRow($sql);
            if ($row) {
                $update = array(
                    'xprecio' => $precio
                );
                $where = array(
                    'xrevendedor_id' => $v['xrevendedor_id'],
                    'xarticulo_id' => $art_id
                );
                $this->db->update('ms_revendedores_precios', $update, $where);
            } else {
                $insert = array(
                    'xrevendedor_id' => $v['xrevendedor_id'],
                    'xarticulo_id' => $art_id,
                    'xprecio' => $precio
                );
                $this->db->insert('ms_revendedores_precios', $insert);
            }
        }
    }

    private function _save_precios($param) {
        $data = array(
            'status' => 1,
            'msg_title' => 'Guardar Precios Artículos',
            'msg' => 'Precios Artículos modificados correctamente.',
            'action' => ''
        );

        $lin = $param['lin'];
        unset($param['lin']);

        if ($data['status'] == 1) {

            if ($lin != '') {
                $rl = explode('#', $lin);
                foreach ($rl as $k => $v) {
                    $d = explode('|', $v);
                    $update = array(
                        'xprecio' => $d[1]
                    );
                    $where = array(
                        'xarticulo_id' => $d[0]
                    );
                    $this->db->update('ms_articulos', $update, $where);

                    //ACTUALIZAMOS EL PRECIO EN TODAS LAS CESTAS POR SI SE HUBIERA MODIFICADO
                    $sql = "update ms_cesta_lin set xprecio={$update['xprecio']}"
                            . " where xarticulo_id={$where['xarticulo_id']}"
                            . " and xcesta_id in (select xcesta_id from ms_cesta where xestado='P')";
                    $this->db->directExec($sql);
                }
            }
        }
        print(json_encode($data));
    }

    private function _save_precios_coste($param) {
        $data = array(
            'status' => 1,
            'msg_title' => 'Guardar Precios de Coste Artículos',
            'msg' => 'Precios de coste artículos modificados correctamente.',
            'action' => ''
        );

        $lin = $param['lin'];
        unset($param['lin']);

        if ($data['status'] == 1) {

            if ($lin != '') {
                $rl = explode('#', $lin);
                foreach ($rl as $k => $v) {
                    $d = explode('|', $v);
                    $update = array(
                        'xcoste' => $d[1]
                    );
                    $where = array(
                        'xarticulo_id' => $d[0]
                    );
                    $this->db->update('ms_articulos', $update, $where);
                }
            }
        }
        print(json_encode($data));
    }

    private function _save_precios_coste_componente($param) {
        $data = array(
            'status' => 1,
            'msg_title' => 'Guardar Precios de Coste Componentes',
            'msg' => 'Precios de coste componentes modificados correctamente.',
            'action' => ''
        );

        $lin = $param['lin'];
        unset($param['lin']);

        if ($data['status'] == 1) {

            if ($lin != '') {
                $rl = explode('#', $lin);
                foreach ($rl as $k => $v) {
                    $d = explode('|', $v);
                    $update = array(
                        'xcoste' => $d[1]
                    );
                    $where = array(
                        'xcomponente_id' => $d[0]
                    );
                    $this->db->update('ms_componentes', $update, $where);
                }
            }
        }
        print(json_encode($data));
    }

    private function _list($param) {
        $data = array(
            'rows' => array()
        );
        $cond = '';

        if (isset($param['categorias'])) {
            $cond .= " and a.xarticulo_id in (select xarticulo_id from ms_articulos_categorias where xcategoria_id in ({$param['categorias']}))";
        }

        if (isset($param['activo'])) {
            $cond .= " and a.xactivo='{$param['activo']}'";
        }
        if (isset($param['activo-web'])) {
            $cond .= " and a.xactivo_web='{$param['activo-web']}'";
        }
        if (isset($param['archivado']) && $param['archivado'] != '') {
            $cond .= " and a.xarchivado='{$param['archivado']}'";
        }

        $sql = "select a.xarticulo_id as xid,a.xarticulo as xdesc,a.xactivo as activo,a.xdestacado as destacado,a.xdestacado_mr as destacado_mr"
                . ",a.xpreventa as preventa,a.xrecargas as recargas,a.xservicio as servicio"
                . ",a.xactivo_web as activo_web,a.xactivo_web_mr as activo_web_mr,a.xactivo_rev as activo_rev"
                . ",a.xactivo_venta as venta,a.xactivo_venta_mr as venta_mr,a.xorden,a.xarchivado"
                . ",a.xactivo_web_an as activo_web_an"
                . ",'toolbar' as toolbar"
                . " from ms_articulos a"
                . " where a.xeliminado=0$cond"
                . " order by a.xarticulo_id";
        //print($sql);
        //die();
        $data['rows'] = $this->db->fetchAll($sql);
        return $data;
    }

    private function _list_componentes($param) {
        $data = array(
            'rows' => array()
        );

        $cond = '';
        if (isset($param['almacen']) && $param['almacen'] != '') {
            $cond .= " and xalmacen_id={$param['almacen']}";
        }

        $sql = "select b.xcomponente_id,sum(a.xcantidad*b.xcantidad) as xcantidad
                from ms_pedidos_lin a
                left join ms_articulos_componentes b on a.xarticulo_id=b.xarticulo_id
                where a.xpedido_id in (select xpedido_id 
					from ms_pedidos
					where xestado in (5,6,7,23,24)
                                        and xfecha between (NOW() - INTERVAL 7 DAY) and NOW()$cond
                                        )
                group by b.xcomponente_id,b.xcantidad;";
        //print($sql);
        //die();
        $ventas7 = $this->db->fetchAll($sql);

        $cond = '';
        $orderby = 'a.xcomponente_id';

        if ($param['orderby'] == 'asc') {
            $orderby = 'a.xcomponente asc';
        }
        if ($param['orderby'] == 'desc') {
            $orderby = 'a.xcomponente desc';
        }
        //die('kdkdk');

        if (isset($param['activo']) && $param['activo'] != '') {
            $cond .= " and a.xactivo='{$param['activo']}'";
        }
        if (isset($param['archivado']) && $param['archivado'] != '') {
            $cond .= " and a.xarchivado='{$param['archivado']}'";
        }

        if (isset($param['almacen']) && $param['almacen'] != '') {
            //$param['almacen']=1;
            $cond .= " and xcomponente_id in (select xcomponente_id from ms_componentes_almacen where xalmacen_id={$param['almacen']})";
        }


        $sql = "select a.*"
                . " from ms_componentes a"
                . " where a.xeliminado=0$cond"
                . " order by $orderby";
        //print($sql);
        //die();
        $data['rows'] = $this->db->fetchAll($sql);

        foreach ($data['rows'] as $k => $v) {
            $data['rows'][$k]['xcoste_format'] = number_format($v['xcoste'], 2, ',', '');
            $data['rows'][$k]['xstock'] = 0;
            $sql = "select ifnull(sum(xstock),0) as xcount"
                    . " from ms_componentes_almacen"
                    . " where xcomponente_id={$v['xcomponente_id']}";
            if (isset($param['almacen']) && $param['almacen'] != '') {
                $sql .= " and xalmacen_id={$param['almacen']}";
            } else {
                $sql .= " and xalmacen_id not in(6)";
            }
            $row = $this->db->fetchRow($sql);
            if ($row) {
                $data['rows'][$k]['xstock'] = $row['xcount'];
            }
            $data['rows'][$k]['xventas7'] = 0;
            foreach ($ventas7 as $kv => $vv) {
                if ($v['xcomponente_id'] == $vv['xcomponente_id']) {
                    $data['rows'][$k]['xventas7'] = number_format($vv['xcantidad'], 0, '.', '');
                }
            }
        }

        if (isset($param['positivos']) && $param['positivos'] == '1') {
            $tmp = $data['rows'];
            $data['rows'] = array();
            foreach ($tmp as $k => $v) {
                if ($v['xstock'] > 0) {
                    $data['rows'][] = $v;
                }
            }
        }

        $data['footer'] = array(
            'xcomponente_id' => 'xxx',
            'idFormatter' => 'ooo'
        );

        //print(json_encode($data));
        //die();

        return $data;
    }

    private function _list_almacenes($param) {
        $data = array(
            'status' => 1,
            'items' => array()
        );
        //and a.xalmacen_id in (1,2)
        $sql = "select a.*"
                . " from ms_almacenes a"
                . " where a.xeliminado=0"
                . " order by a.xalmacen_id";
        //print($sql);
        //die();
        $data['items'] = $this->db->fetchAll($sql);

        //gcc get-coste-componente
        if (isset($param['gcc']) && $param['gcc'] != '') {
            $sql = "select a.*"
                    . " from ms_componentes a"
                    . " where a.xcomponente_id={$param['gcc']}";
            //print($sql);
            //die();
            $com = $this->db->fetchRow($sql);
            if ($com) {
                $data['gcc'] = number_format($com['xcoste'], 2, ',', '');
            }
        }

        return $data;
    }

    private function _list_stock_almacenes($param) {
        $data = array(
            'status' => 1,
            'items' => array()
        );
        //and a.xalmacen_id in (1,2)
        $sql = "select a.*,b.xalmacen"
                . " from ms_componentes_almacen a"
                . " left join ms_almacenes b on a.xalmacen_id=b.xalmacen_id"
                . " where a.xcomponente_id={$param['componente']}"
                . " order by a.xalmacen_id";
        //print($sql);
        //die();
        $data['items'] = $this->db->fetchAll($sql);

        return $data;
    }

    private function _copy_img_an($param) {
        //COPIA LAS IMÁGENES DEL PANEL DE MANDASALDO A LA WEB DE ALLNOVU.COM
        $data = array(
            'status' => 1,
            'msg' => 'Imágenes generadas correctamente'
        );

        $sql = "select * from ms_articulos where xactivo='S' and xactivo_web_an='S'";
        $rr = $this->db->fetchAll($sql);

        foreach ($rr as $k => $v) {
            $src = IMG_ARTICULOS . "/{$v['xarticulo_id']}-an.jpg";
            $dst = IMG_ARTICULOS_ALLNOVU . "/{$v['xarticulo_id']}.jpg";
            $d = array(
                'src' => $src,
                'dst' => $dst
            );
            //print(json_encode($d));
            //die();
            copy($src, $dst);
        }

        return $data;
    }

    private function _get_stock_pv($param) {
        $data = array(
            'status' => 1,
            'almacen' => 0,
            'stock_min' => 1000000, //NO TOCAR VALOR PUES SE USA PARA CÁLCULO
            'stock_max' => 0, //NO TOCAR VALOR PUES SE USA PARA CÁLCULO
            'items' => array()
        );
        //and a.xalmacen_id in (1,2)

        if ($this->app->punto_venta != '') {
            $sql = "select xalmacen_id from ms_revendedores where xrevendedor_id={$this->app->punto_venta}";
            $rev = $this->db->fetchRow($sql);
            if ($rev) {
                $data['almacen'] = $rev['xalmacen_id'];
            }

            /*
              $sql = "select a.xarticulo_id,a.xcomponente_id,a.xcantidad,ifnull(b.xalmacen_id,0),ifnull(b.xstock,0),floor(ifnull(xstock,0)/xcantidad) as xmax_stock"
              . " from ms_articulos_componentes a"
              . " left join ms_componentes_almacen b on a.xcomponente_id=b.xcomponente_id and b.xalmacen_id={$data['almacen']}"
              . " where xarticulo_id={$param['art']}";
             * 
             */
            $sql = "select a.xarticulo_id,a.xcomponente_id,a.xcantidad,c.xalmacen,d.xcomponente
                ,b.xalmacen_id,ifnull(b.xstock,0) as xstock,floor(ifnull(xstock,0)/xcantidad) as xstock_max
                from ms_articulos_componentes a 
                left join ms_componentes_almacen b on a.xcomponente_id=b.xcomponente_id
                left join ms_almacenes c on b.xalmacen_id=c.xalmacen_id
                left join ms_componentes d on a.xcomponente_id=d.xcomponente_id
                where xarticulo_id={$param['art']} and c.xactivo='S'";
            $coms = $this->db->fetchAll($sql);
            //print($data['almacen']);
            //print($sql);
            //print_r($coms);
            //die();
            if ($coms) {
                foreach ($coms as $k => $v) {
                    if ($data['stock_min'] > $v['xstock_max']) {
                        $data['stock_min'] = $v['xstock_max'];
                    }
                    if ($data['stock_max'] < $v['xstock_max']) {
                        $data['stock_max'] = $v['xstock_max'];
                    }
                }
                $data['items'] = $coms;
            } else {
                $data['stock'] = 0;
            }
        }

        return $data;
    }

    private function _checked($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'activo' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['activo'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-ACTIVO',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Activo: ' . $data['activo']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_componente($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'activo' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xcomponente_id' => $param['id']
            );
            if ($this->db->update('ms_componentes', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['activo'] = $param['value'];

                $history = array(
                    'xentity' => 'COMPONENTES',
                    'xaction' => 'CHG-ACTIVO',
                    'xid' => $param['id'],
                    'xobs' => 'COMPONENTE: ' . $data['id'] . ' Activo: ' . $data['activo']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_destacado($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xdestacado' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-DESTACADO',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Destacado: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_venta($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo_venta' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-VENTA',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Venta: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_web($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo_web' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-WEB',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Venta: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_destacado_mr($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xdestacado_mr' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-DESTACADO_MR',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Destacado: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_venta_mr($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo_venta_mr' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-VENTA-MR',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Venta: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _archived($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'value' => '',
            'row' => $param['row']
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $value = 'S';
            if ($param['value'] == 'S')
                $value = 'N';

            $update = array(
                'xarchivado' => $value,
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $value;

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-ARCHIVADO',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Value: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        return $data;
    }

    private function _archived_componente($param) {
        $data = array(
            'status' => 1,
            'id' => $param['id'],
            'value' => '',
            'row' => $param['row']
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $value = 'S';
            if ($param['value'] == 'S')
                $value = 'N';

            $update = array(
                'xarchivado' => $value,
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xcomponente_id' => $param['id']
            );
            if ($this->db->update('ms_componentes', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $value;

                $history = array(
                    'xentity' => 'COMPONENTES',
                    'xaction' => 'CHG-ARCHIVADO',
                    'xid' => $param['id'],
                    'xobs' => 'COMPONENTE: ' . $data['id'] . ' Value: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        return $data;
    }

    private function _checked_web_mr($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo_web_mr' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-WEB-MR',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Web: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_web_an($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo_web_an' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-WEB-AN',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Web: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_rev($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xactivo_rev' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-REV',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Venta: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_servicio($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'value' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xservicio' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['value'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-SERVICIO',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Servicio: ' . $data['value']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    private function _checked_preventa($param) {
        $data = array(
            'status' => 1,
            'id' => null,
            'activo' => null
        );

        if ($data['status'] == 1) {
            unset($param['module']);
            unset($param['method']);

            $update = array(
                'xpreventa' => $param['value'],
                'xusermodif_id' => $this->app->user_id,
                'xdatemodif' => date(dateSQL)
            );
            $where = array(
                'xarticulo_id' => $param['id']
            );
            if ($this->db->update('ms_articulos', $update, $where) == 1) {
                $data['id'] = $param['id'];
                $data['preventa'] = $param['value'];

                $history = array(
                    'xentity' => 'ARTICULOS',
                    'xaction' => 'CHG-ACTIVO',
                    'xid' => $param['id'],
                    'xobs' => 'ARTICULO: ' . $data['id'] . ' Activo: ' . $data['preventa']
                );
                $this->app->add_history($history);
            }
        }
        print(json_encode($data));
    }

    //<editor-fold defaultstate="collapsed" desc="GENERACIÓN INFORME EN A4">

    private function _dl_inventario_a4($data) {
        $this->page = 1;

        $h = 5;
        $c1 = 100;
        $c2 = 30;
        $c3 = 30;
        $c4 = 30;

        $pdf = new FPDF();
        $pdf->AddPage();

        $this->_inventario_a4_header($pdf, $data);

        $this->total = 0;
        $this->unidades = 0;

        foreach ($data['rows'] as $k => $v) {
            //$v['xcoste'] = number_format($v['xcoste'], 2, ',', '');
            $v['ximporte'] = number_format($v['xcoste'] * $v['xstock'], 2, ',', '');

            $pdf->Cell($c1, $h, $v['xcomponente_id'] . ' - ' . utf8_decode($v['xcomponente']), 1, 0, 'L', false);
            $pdf->Cell($c2, $h, $v['xstock'], 1, 0, 'R', false);
            $pdf->Cell($c3, $h, $v['xcoste_format'], 1, 0, 'R', false);
            $pdf->Cell($c4, $h, $v['ximporte'], 1, 1, 'R', false);

            $this->total = $this->total + str_replace(',', '.', $v['ximporte']);
            $this->unidades += $v['xstock'];

            if (($k + 1) % 47 == 0) {
                $this->_inventario_a4_page($pdf);
                //$this->_inventario_a4_footer($pdf);
                $pdf->AddPage();
                $this->_inventario_a4_header($pdf, $data);
            }
        }

        $this->_inventario_a4_page($pdf);
        $this->_inventario_a4_footer($pdf, 'F');

        $pdf->Output('inventario-' . date('YmdHis') . '.pdf', 'D');
    }

    private function _inventario_a4_header($pdf, $data) {
        $h = 5;
        $c1 = 100;
        $c2 = 30;
        $c3 = 30;
        $c4 = 30;

        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);

        $pdf->SetLeftMargin(10);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setXY(10, 10);
        $pdf->Cell(190, 7, utf8_decode("Inventario Almacén"), 0, 1, 'C', false);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 9);
        //$pdf->setXY(10, 15);
        $pdf->Cell($c1, $h, 'Componente', 1, 0, 'C', false);
        $pdf->Cell($c2, $h, 'Cantidad', 1, 0, 'R', false);
        $pdf->Cell($c3, $h, 'Coste', 1, 0, 'R', false);
        $pdf->Cell($c4, $h, 'Importe', 1, 1, 'R', false);

        $pdf->SetLeftMargin(10);
    }

    private function _inventario_a4_page($pdf) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->setXY(10, 270);
        $pdf->Cell(50, 4, utf8_decode("Página: {$this->page}"), 0, 0, 'L', false);
        $this->page = $this->page + 1;
    }

    private function _inventario_a4_footer($pdf) {
        $s = "Unidades " . number_format($this->unidades, 2, ',', '.') . ' Unds.      Total...:    ' . number_format($this->total, 2, ',', '.') . ' USD';

        $pdf->SetFont('Arial', 'B', 14);
        //$pdf->setXY(140, 270);
        $pdf->Ln(7);
        $pdf->Cell(190, 5, utf8_decode($s), 0, 0, 'R', false);
    }

    //
    //</editor-fold>

    public function _dl_inventario_ticket($data) {
        $logo = BASE . '/img/logo-400.jpg';

        $h = 50;
        $h += count($data['rows']) * 4;
        /*
          if (count($data['rows']) >= 5) {
          $h = 230;
          }
          if (count($data['rows']) >= 10) {
          $h = 235;
          }
          if (count($data['rows']) >= 15) {
          $h = 2000;
          } */
        //print($h);
        //die();
        //print(count($lbl['items']));
        //print($h);

        $pdf = new FPDF('P', 'mm', array(102, $h));
        //$pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(FALSE, 0);
        $pdf->SetMargins(3, 3);
        $pdf->SetFillColor(220, 220, 220);

        if (isset($logo) && $logo != '') {
            $pdf->Image($logo, 30, 3, 40, 0, 'JPG');
        }

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY(3, 15);
        $pdf->Cell(95, 5, utf8_decode('Inventario Almacén'), 'B', 0, 'C');

        $pdf->Ln(6);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(80, 5, 'Componentes', 'B', 0, 'L');
        $pdf->Cell(15, 5, 'Cant.', 'B', 1, 'C');

        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 12);

        foreach ($data['rows'] as $k => $v) {
            $v['xcomponente'] = $v['xcomponente_id'] . ' - ' . $v['xcomponente'];
            if (strlen($v['xcomponente']) > 30) {
                $v['xcomponente'] = substr($v['xcomponente'], 0, 30) . '...';
            }
            $pdf->Cell(80, 4, utf8_decode($v['xcomponente']), 0, 0, 'L', $k % 2);
            $pdf->Cell(15, 4, utf8_decode(number_format($v['xstock'], 0, ',', '')), 0, 1, 'R', $k % 2);
        }

        $pdf->Output('inventario-recibo-' . date('YmdHis') . '.pdf', 'D');
    }
}

?>
