<?php

/**
 * Description of App
 *
 * @author alvaro
 */
class App
{

    var $name;
    var $user_id; //codigo de usuario de la aplicación
    var $db; //conexion a la base de datos.
    var $dbs; //conexion a la base de datos.
    var $rol; //rol.
    var $rol_name; //rol.
    var $empresa_id; // Default company ID

    public function __construct()
    {
        $this->name = '';
        $this->rol = '';
        $this->rol_name = '';
        session_start();

        $this->db = new MsSql(_DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_, '3306'); //sql server
        // Debug flag removed

        if (isset($_SESSION['guser_id'])) {
            $this->user_id = $_SESSION['guser_id'];
            $this->name = $_SESSION['gname'];
            $this->rol = $_SESSION['grol'];

            $sql = "select xrol from roles where xrol_id='{$this->rol}'";
            $row = $this->db->fetchRow($sql);
            if ($row) {
                $this->rol_name = $row['xrol'];
            }
            if (isset($_SESSION['grol_name']))
                $this->rol_name = $_SESSION['grol_name'];
        } else {
            $this->user_id = '';
        }

        $this->handle_company_change();
    }

    /**
     * Handle company change request
     */
    private function handle_company_change() {
        if (isset($_POST['cambiar_empresa']) && is_numeric($_POST['empresa_id'])) {
            $empresa_id = (int)$_POST['empresa_id'];
            // Verify the company exists
            $sql = "SELECT id FROM empresa WHERE id = ?";
            $result = $this->db->fetchAll($sql, [$empresa_id]);
            
            if (!empty($result)) {
                $_SESSION['empresa_id'] = $empresa_id;
                $this->empresa_id = $empresa_id;
                // Optionally add a success message
                $_SESSION['mensaje_exito'] = 'Empresa cambiada correctamente';
            }
        }
        if(isset($_SESSION['empresa_id'])){
            $this->empresa_id = $_SESSION['empresa_id'];
        }
        else{
            $this->empresa_id = 1;
            $_SESSION['empresa_id'] = 1;
        }
    }

    public function get_list_roles($val = array())
    {
        $data = array();
        $cond = '';

        if (isset($val['activo']))
            $cond .= " and a.xactivo='{$val['activo']}'";

        $sql = "select a.*"
            . " from " .   "roles a"
            . " order by a.xrol_id";

        $data = $this->db->fetchAll($sql);

        return $data;
    }
    public function get_list_cargos($val = array())
    {
        $data = array();
        $cond = '';

        if (isset($val['activo']))
            $cond .= " and a.xactivo='{$val['activo']}'";

        $sql = "select a.*"
            . " from " .   "cargos a"
            . " order by a.id";

        $data = $this->db->fetchAll($sql);

        return $data;
    }
    public function get_list_departamentos($val = array())
    {
        $data = array();
        $cond = '';

        if (isset($val['activo']))
            $cond .= " and a.activo='{$val['activo']}'";

        $sql = "select a.*"
            . " from " .   "departamentos a"
            . " where 1=1"
            . " and a.empresa_id = {$this->empresa_id}"
            . $cond
            . " order by a.nombre";

        $data = $this->db->fetchAll($sql);

        return $data;
    }
    
    public function get_list_bolsa_empleo($val = array())
    {
        $data = array();
        $cond = '';

        if (isset($val['activo']))
            $cond .= " and a.xactivo='{$val['activo']}'";

        $sql = "select a.*"
            . " from " .   "bolsa_empleo a"
            . " where 1=1"
            . " and a.empresa_id = {$this->empresa_id}"
            . $cond
            . " order by a.id";

        $data = $this->db->fetchAll($sql);

        return $data;
    }

    public function close()
    {
        $this->db->close();
    }

    public function eliminar_tildes($cadena)
    {

        //Codificamos la cadena en formato utf8 en caso de que nos de errores
        //$cadena = utf8_encode($cadena);
        //Ahora reemplazamos las letras
        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );

        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena
        );

        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena
        );

        $cadena = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena
        );

        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena
        );

        $cadena = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C'),
            $cadena
        );

        return $cadena;
    }

    public function add_history($val)
    {
        
        // Set default company ID if not set
        if (!isset($_SESSION['empresa_id'])) {
            $_SESSION['empresa_id'] = $this->empresa_id;
        } else {
            $this->empresa_id = $_SESSION['empresa_id'];
        }
        $val['xuser'] = $this->user_id;
        // Ensure xdate is properly formatted for database insertion
        if (defined('dateSQL')) {
            $val['xdate'] = date(dateSQL);
        } else {
            // Fallback to standard MySQL datetime format
            $val['xdate'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('historico', $val);
    }

    public function get_list_usuarios($param = array())
    {
        $data = array();
        $cond = '';
        $fields = 'a.*';

        if (isset($param['activo']) && $param['activo'] !== '') {
            $cond .= " and a.xactivo='{$param['activo']}'";
        }
        if (isset($param['rol']) && $param['rol'] !== '') {
            $cond .= " and a.xrol_id='{$param['rol']}'";
        }

        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }

        $sql = "select $fields"
            . " from " . "usuarios a"
            . " where a.xeliminado=0$cond"
            . " order by a.xusuario_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);

        return $data;
    }

    public function get_list_var_informes($param)
    {
        $data = array();
        $cond = '';

        if (isset($param['module']) && $param['module'] != '') {
            $cond .= " and xmodule='{$param['module']}'";
        }

        $sql = "select * from " .   "var_informes where 1=1$cond";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);

        return $data;
    }

    public function get_data_var_informes($param)
    {
        $data = array();
        $cond = '';

        if (isset($param['module']) && $param['module'] != '') {
            $cond .= " and xmodule='{$param['module']}'";
        }
        if (isset($param['var']) && $param['var'] != '') {
            $cond .= " and xvar='{$param['var']}'";
        }

        $sql = "select * from " .   "var_informes where 1=1$cond";
        //print($sql);
        //die();
        $data = $this->db->fetchRow($sql);

        return $data;
    }

    /**
     * Lista de estados
     *
     * @param array $filtro
     * @return void
     */
    public function _list_estados($filtro = array())
    {
        $data = array();
        $cond = '';

        if (isset($filtro['estado']) && $filtro['estado'] != '') {
            $cond .= " and xestado_id in ({$filtro['estado']})";
        }

        $sql = "select a.*"
            . " from " .   "estados a"
            . " where 1 $cond"
            . " order by a.xestado_id";
        //print($sql);
        //die();
        $data = $this->db->fetchAll($sql);

        return $data;
    }

    public function format_price($val)
    {
        return number_format($val, 2, $this->coma_decimal, '') . ' ' . $this->moneda;
    }

    public function get_configuraciones()
    {
        $data = array();
        $sql = "select * from " .   "configuraciones where xconfig_id=1";
        //print($sql);
        //die();
        $data = $this->db->fetchRow($sql);
        return $data;
    }

    public function clear_query_string($data)
    {
        $query = '';
        if ($_SERVER['QUERY_STRING'] != '') {
            $var = explode('&', $_SERVER['QUERY_STRING']);
            foreach ($var as $k => $v) {
                $var2 = explode('=', $v);
                //if ($var2[0] == 'module' || $var2[0] == $data)
                if ($var2[0] == $data)
                    unset($var[$k]);
            }
            $query = implode('&', $var);
        }
        return $query;
    }

    public function IsEmail($e)
    {
        if (preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $e)) {
            return 1;
        } else {
            return 0;
        }
    }

    function rndString($length = 10, $uc = TRUE, $n = TRUE, $sc = FALSE)
    {
        $source = 'abcdefghijklmnopqrstuvwxyz';
        if ($uc == 1)
            $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($n == 1)
            $source .= '1234567890';
        if ($sc == 1)
            $source .= '|@#~$%()=^*+[]{}-_';
        if ($length > 0) {
            $rstr = "";
            $source = str_split($source, 1);
            for ($i = 1; $i <= $length; $i++) {
                //mt_srand((double) microtime() * 1000000);
                //$num = mt_rand(1, count($source));
                $num = rand(1, count($source));
                $rstr .= $source[$num - 1];
            }
        }
        return $rstr;
    }

    public function month_long($m)
    {
        switch ($m) {
            case 1:
                return 'Enero';
                break;
            case 2:
                return 'Febrero';
                break;
            case 3:
                return 'Marzo';
                break;
            case 4:
                return 'Abril';
                break;
            case 5:
                return 'Mayo';
                break;
            case 6:
                return 'Junio';
                break;
            case 7:
                return 'Julio';
                break;
            case 8:
                return 'Agosto';
                break;
            case 9:
                return 'Septiembre';
                break;
            case 10:
                return 'Octubre';
                break;
            case 11:
                return 'Noviembre';
                break;
            case 12:
                return 'Diciembre';
                break;
        }
    }

    public function chgdmysql($cad)
    {
        if ($cad != "") {
            $valor = substr($cad, 6, 4) . "-" . substr($cad, 3, 2) . "-" . substr($cad, 0, 2);
        } else {
            $valor = $cad;
        }
        return $valor;
    }

    function limpiar_caracteres_especiales($s)
    {
        /*
          ini_set('display_errors', 1);
          ini_set('display_startup_errors', 1);
          error_reporting(E_ALL);
         * 
         */

        $s = preg_replace("[áàâãª]", "a", $s);
        $s = preg_replace("[ÁÀÂÃ]", "A", $s);
        $s = preg_replace("[éèê]", "e", $s);
        $s = preg_replace("[ÉÈÊ]", "E", $s);
        $s = preg_replace("[íìî]", "i", $s);
        $s = preg_replace("[ÍÌÎ]", "I", $s);
        $s = preg_replace("[óòôõº]", "o", $s);
        $s = preg_replace("[ÓÒÔÕ]", "O", $s);
        $s = preg_replace("[úùû]", "u", $s);
        $s = preg_replace("[ÚÙÛ]", "U", $s);
        //$s = str_replace(" ", "-", $s);
        //$s = str_replace("ñ", "n", $s);
        //$s = str_replace("Ñ", "N", $s);
        //para ampliar los caracteres a reemplazar agregar lineas de este tipo:
        //$s = str_replace("caracter-que-queremos-cambiar","caracter-por-el-cual-lo-vamos-a-cambiar",$s);
        return $s;
    }

    function eliminar_acentos($cadena)
    {

        //Reemplazamos la A y a
        $cadena = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
            $cadena
        );

        //Reemplazamos la E y e
        $cadena = str_replace(
            array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
            array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
            $cadena
        );

        //Reemplazamos la I y i
        $cadena = str_replace(
            array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
            array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
            $cadena
        );

        //Reemplazamos la O y o
        $cadena = str_replace(
            array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
            array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
            $cadena
        );

        //Reemplazamos la U y u
        $cadena = str_replace(
            array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
            array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
            $cadena
        );

        //Reemplazamos la N, n, C y c
        $cadena = str_replace(
            array('Ñ', 'ñ', 'Ç', 'ç'),
            array('N', 'n', 'C', 'c'),
            $cadena
        );

        return $cadena;
    }
}
