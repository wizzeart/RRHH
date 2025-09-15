<?php

/**
 * Módulo Template
 *
 * @author alvaro
 */
class Informe {

    var $app;
    var $db;
    var $action;
    var $page;
    var $total;
    var $unidades;
    var $module;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'save-ratios-mensual':
                $this->module = 'ratios-mensual';
                $data = $this->_save_ratios_mensual($param);
                print(json_encode($data));
                break;
            case 'ratios-mensual':
                $data = $this->_ratios_mensual($param);
                print(json_encode($data));
                break;

            /*             * **************** A PARTIR DE ESTAS LÍNEAS NO SE USA EN ESTA FUNCIÓN ****************** */
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
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;

        switch ($param['module']) {
            case 'ratios-mensual':
                $data = array();
                $page['title'] = 'Ratios Mensual';
                $page['subtitle'] = 'Resultado Ratios Mensual';

                $data_form = array();

                $filtro = array(
                    'activo' => 'S',
                    'rol' => 10,
                    'module' => 'ratios-mensual'
                );

                $data_form['comerciales'] = $this->app->get_list_usuarios($filtro);
                $data_form['params'] = json_encode($this->app->get_list_var_informes($filtro));

                //die('kdkdkdk');
                //$fecha_ini = $this->app->get_fecha_apertura();
                //print($fecha_ini);
                //die();
                //$data_form['fecha-ini'] = date('d/m/Y', strtotime('now') - ((date('d') - 1) * 24 * 3600));
                $data_form['fecha-ini'] = $this->app->get_fecha_apertura();
                $data_form['fecha-fin'] = date('d/m/Y');

                //print_r($data_form['comerciales']);
                //die();
                break;

            /*             * **************** A PARTIR DE ESTAS LÍNEAS NO SE USA EN ESTA FUNCIÓN ****************** */
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
        }
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
                . ",a.xactivo_venta as venta,a.xactivo_venta_mr as venta_mr,a.xorden"
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

    public function _save_ratios_mensual($param) {
        /*
          ini_set('display_errors', 1);
          ini_set('display_startup_errors', 1);
          error_reporting(E_ALL);
         * 
         */

        $data = array(
            'status' => 1,
            'msg' => 'Configuración guardada correctamente.'
        );

        $where = array(
            'xmodule' => $this->module
        );
        $this->db->del('ms_var_informes', $where);

        $insert = array(
            'xmodule' => $this->module,
            'xvar' => 'mc-comerciales',
            'xtype' => 'chosen',
            'xvalue' => $param['comerciales']
        );
        $this->db->insert('ms_var_informes', $insert);
        $insert = array(
            'xmodule' => $this->module,
            'xvar' => 'mc-count-fijo',
            'xtype' => 'string',
            'xvalue' => $param['count-fijo']
        );
        $this->db->insert('ms_var_informes', $insert);
        $insert = array(
            'xmodule' => $this->module,
            'xvar' => 'mc-sum-fijo',
            'xtype' => 'string',
            'xvalue' => $param['sum-fijo']
        );
        $this->db->insert('ms_var_informes', $insert);
        $insert = array(
            'xmodule' => $this->module,
            'xvar' => 'mc-dia-apertura',
            'xtype' => 'string',
            'xvalue' => $param['dia-apertura']
        );
        $this->db->insert('ms_var_informes', $insert);

        return $data;
    }

    public function _ratios_mensual($param) {
        //$year = $param['ejercicio'];
        //$month = ((int) $param['mes']) + 1;
        $cond = '';
        $cond_count_comerciales = '';

        $tmp = $param['fecha-ini'];
        $tmp = explode('/', $tmp);
        $fecha_ini = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 00:00:00';

        $tmp = $param['fecha-fin'];
        $tmp = explode('/', $tmp);
        $fecha_fin = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0] . ' 23:59:59';

        $data = array(
            //'year' => $year,
            //'month' => $month,
            'fecha_ini' => $param['fecha-ini'],
            'fecha_fin' => $param['fecha-fin'],
            //'month_format' => $this->app->month_long($month),
            'count' => 0, //NºPEDIDOS TOTALES
            'count_format' => '', //NºPEDIDOS TOTALES
            'count_fijo' => 0, //NºPEDIDOS TOTALES FIJO
            'count_fijo_format' => '', //NºPEDIDOS TOTALES FIJO FORMAT
            'count_real' => 0, //NºPEDIDOS TOTALES REAL
            'count_real_format' => '', //NºPEDIDOS TOTALES REAL
            'comerciales' => 0,
            'sum' => 0, //SUMA IMPORTES TOTALES
            'sum_format' => '', //SUMA IMPORTES TOTALES
            'sum_fijo' => 0, //SUMA IMPORTES TOTALES FIJO
            'sum_fijo_format' => '', //SUMA IMPORTES TOTALES FIJO FORMAT
            'sum_real' => 0, //SUMA IMPORTES TOTALES REAL
            'sum_real_format' => '', //SUMA IMPORTES TOTALES REAL
            'count_promedio' => 0, //PROMEDIO PEDIDOS
            'count_promedio_format' => '', //PROMEDIO COUNT
            'sum_promedio' => 0, //PROMEDIO IMPORTE
            'sum_promedio_format' => '', //PROMEDIO IMPORTE
            'rows' => array()
        );

        $comercialesNoIncluidos = '';
        $sql = "select * from ms_var_informes where xmodule='ratios-mensual' and xvar='mc-comerciales'";
        $row = $this->db->fetchRow($sql);
        if ($row && isset($row['xvalue']) && $row['xvalue'] != 'null') {
            $comercialesNoIncluidos = $row['xvalue'];
        }
        if ($comercialesNoIncluidos != '') {
            $cond .= " and a.xuseralta_id not in({$comercialesNoIncluidos})";
            $cond_count_comerciales .= " and a.xusuario_id not in({$comercialesNoIncluidos})";
        }

        $countFijo = '';
        $sql = "select * from ms_var_informes where xmodule='ratios-mensual' and xvar='mc-count-fijo'";
        $row = $this->db->fetchRow($sql);
        if ($row && isset($row['xvalue']) && $row['xvalue'] != 'null') {
            $countFijo = $row['xvalue'];
        }

        $sumFijo = '';
        $sql = "select * from ms_var_informes where xmodule='ratios-mensual' and xvar='mc-sum-fijo'";
        $row = $this->db->fetchRow($sql);
        if ($row && isset($row['xvalue']) && $row['xvalue'] != 'null') {
            $sumFijo = $row['xvalue'];
        }

        $sql = "select count(*) as xcount,sum(a.ximporte) as xsum
            from ms_pedidos a
            left join ms_usuarios b on a.xuseralta_id=b.xusuario_id
            where a.xrevendedor_id>0 and b.xrol_id=10$cond
            and a.xestado=7 and b.xactivo='S' and b.xeliminado=0
            and a.xfecha between '{$fecha_ini}' and '{$fecha_fin}'";
        //print($sql);
        //die();
        //and year(a.xfecha)=$year and month(a.xfecha)=$month";
        $totales = $this->db->fetchRow($sql);

        //COUNT
        $data['count'] = (int) $totales['xcount'];
        $data['count_format'] = number_format($totales['xcount'], 0, ',', '.');
        if ($countFijo != '') {
            $data['count_real'] = (int) $data['count'];
            $data['count_real_format'] = number_format($data['count_real'], 0, ',', '.');
            $data['count'] = (int) $countFijo;
            $data['count_fijo'] = (int) $countFijo;
            $data['count_fijo_format'] = number_format($data['count_fijo'], 0, ',', '.');
        }

        //SUM
        $data['sum'] = (float) $totales['xsum'];
        $data['sum_format'] = number_format($data['sum'], 2, ',', '.');
        if ($sumFijo != '') {
            $data['sum_real'] = (float) $data['sum'];
            $data['sum_real_format'] = number_format($data['sum_real'], 2, ',', '.');
            $data['sum'] = (float) $sumFijo;
            $data['sum_fijo'] = (float) $sumFijo;
            $data['sum_fijo_format'] = number_format($data['sum_fijo'], 2, ',', '.');
        }

        $sql = "select count(a.xusuario_id) as xcount
            from ms_usuarios a
            where a.xrol_id=10$cond_count_comerciales
            and a.xactivo='S' and a.xeliminado=0;";
        $rr = $this->db->fetchRow($sql);
        if ($rr) {
            $data['comerciales'] = $rr['xcount'];
        }

        $sql = "select a.xuseralta_id,b.xusuario,count(*) as xcount,sum(a.ximporte) as xsum
            from ms_pedidos a
            left join ms_usuarios b on a.xuseralta_id=b.xusuario_id
            where a.xrevendedor_id>0 and b.xrol_id=10$cond
            and a.xestado=7 and b.xactivo='S' and b.xeliminado=0
            and a.xfecha between '{$fecha_ini}' and '{$fecha_fin}'
            group by a.xuseralta_id,b.xusuario";
        //and year(a.xfecha)=$year and month(a.xfecha)=$month
        //print($sql);
        //die();
        $data['rows'] = $this->db->fetchAll($sql);
        //$data['comerciales'] = count($data['rows']);
        if ($data['comerciales'] > 0) {
            $data['sum_promedio'] = $data['sum'] / $data['comerciales'];
            $data['count_promedio'] = $data['count'] / $data['comerciales'];
        } else {
            $data['sum_promedio'] = 0;
            $data['count_promedio'] = 0;
        }
        $data['sum_promedio_format'] = number_format($data['sum_promedio'], 2, ',', '.');
        $data['count_promedio_format'] = number_format($data['count_promedio'], 2, ',', '.');

        foreach ($data['rows'] as $k => $v) {
            //$data['rows'][$k]['xcount_total'] = $data['count'];
            $data['rows'][$k]['xcount_ratio'] = (100 * $v['xcount']) / $data['count'];
            $data['rows'][$k]['xcount_ratio_format'] = number_format($data['rows'][$k]['xcount_ratio'], 2, ',', '.') . '%';
            //$data['rows'][$k]['xsum_total'] = $data['sum'];
            //$data['rows'][$k]['xsum_total_format'] = number_format($data['sum'], 2, ',', '');
            $data['rows'][$k]['xsum_ratio'] = (100 * (float) $v['xsum']) / $data['sum'];
            $data['rows'][$k]['xsum_ratio_format'] = number_format($data['rows'][$k]['xsum_ratio'], 2, ',', '.') . '%';
            $data['rows'][$k]['xsum_format'] = number_format($v['xsum'], 2, ',', '.');

            //SITUACIÓN PROMEDIOS
            $data['rows'][$k]['xcount_promedio'] = ($v['xcount'] - $data['count_promedio']) * 100 / $data['count_promedio'];
            $data['rows'][$k]['xsum_promedio'] = ($v['xsum'] - $data['sum_promedio']) * 100 / $data['sum_promedio'];
            $data['rows'][$k]['xcount_promedio_format'] = number_format($data['rows'][$k]['xcount_promedio'], 2, ',', '.') . '%';
            $data['rows'][$k]['xsum_promedio_format'] = number_format($data['rows'][$k]['xsum_promedio'], 2, ',', '.') . '%';
        }
        return $data;
    }
}
