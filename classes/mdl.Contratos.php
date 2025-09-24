<?php
class Contrato {
    var $app;
    var $db;
    var $action;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param) {
        switch ($param['method']) {
            case 'list':
                $data = $this->_list($param);
                print(json_encode($data));
                break;
            case 'save':
                $this->_save($param);
                break;
        }
    }

    public function controlador($param) {
        global $data, $action, $page, $data_form;
        switch ($param['module']) {
            case 'list-contratos':
                $data = array();
                $page['title'] = 'Contratos';
                $page['subtitle'] = 'Listado de Contratos';
                break;
            case 'contratos':
                $data = array();
                $page['title'] = 'Nuevo Contrato';
                $page['subtitle'] = 'Registro de Contrato';
                $action = 'insert';

                // Cargar trabajadores activos para el select
                try {
                    $data_form['trabajadores'] = $this->db->fetchAll("SELECT id, nombre, apellidos FROM trabajadores WHERE trabajador_eliminado = '0' ORDER BY nombre ASC, apellidos ASC");
                } catch (Exception $e) {
                    $data_form['trabajadores'] = array();
                }
                if (isset($param['id'])) {
                    $page['title'] = 'Editar Contrato';
                    $action = 'update';
                    $val = array('id' => $param['id']);
                    $sql = "SELECT * FROM contratos WHERE id=:id";
                    $row = $this->db->fetchRow($sql, $val);
                    if ($row) { $data = $row; $page['subtitle'] = 'Contrato: ' . $row['id']; }
                }
                break;
        }
    }

    private function _list($param) {
        $data = array();
        try {
            $sql = "SELECT c.*, t.nombre, t.apellidos, CONCAT(t.nombre, ' ', t.apellidos) as trabajador_nombre
                    FROM contratos c
                    LEFT JOIN trabajadores t ON t.id = c.trabajador_id
                    ORDER BY c.id DESC";
            $data = $this->db->fetchAll($sql);
        } catch (Exception $e) {
            $data = array();
        }
        return $data;
    }

    private function _save($param) {
        $data = array('status'=>1, 'msg_title'=>'Éxito', 'msg'=>'Contrato guardado correctamente');

        // Validaciones básicas
        $trabajador_id = isset($param['trabajador_id']) ? intval($param['trabajador_id']) : 0;
        $tipo = isset($param['tipo']) ? trim($param['tipo']) : '';
        $fecha_inicio = isset($param['fecha_inicio']) ? $param['fecha_inicio'] : null;
        $fecha_fin = isset($param['fecha_fin']) ? $param['fecha_fin'] : null;

        if ($trabajador_id <= 0) { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El trabajador es obligatorio'; print(json_encode($data)); return; }
        if ($tipo === '') { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='El tipo de contrato es obligatorio'; print(json_encode($data)); return; }
        if (empty($fecha_inicio)) { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='La fecha de inicio es obligatoria'; print(json_encode($data)); return; }
        if (!empty($fecha_fin) && $fecha_fin < $fecha_inicio) { $data['status']=0; $data['msg_title']='Validación'; $data['msg']='La fecha fin no puede ser anterior a la fecha inicio'; print(json_encode($data)); return; }

        // Manejo de uploads
        $upload_dir = 'uploads/contratos/';
        if (!file_exists($upload_dir)) { @mkdir($upload_dir, 0777, true); }
        $archivo_contrato_path = null;
        $firma_digital_path = null;

        if (isset($_FILES['archivo_contrato']) && $_FILES['archivo_contrato']['error'] === UPLOAD_ERR_OK) {
            $name = uniqid('contrato_') . '_' . basename($_FILES['archivo_contrato']['name']);
            $path = $upload_dir . $name;
            if (move_uploaded_file($_FILES['archivo_contrato']['tmp_name'], $path)) { $archivo_contrato_path = $path; }
        }
        if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] === UPLOAD_ERR_OK) {
            $name = uniqid('firma_') . '_' . basename($_FILES['firma_digital']['name']);
            $path = $upload_dir . $name;
            if (move_uploaded_file($_FILES['firma_digital']['tmp_name'], $path)) { $firma_digital_path = $path; }
        }

        $insert = array(
            'trabajador_id'   => $trabajador_id,
            'tipo'            => $tipo,
            'fecha_inicio'    => $fecha_inicio,
            'fecha_fin'       => !empty($fecha_fin) ? $fecha_fin : null,
            'archivo_contrato'=> $archivo_contrato_path,
            'firma_digital'   => $firma_digital_path
        );

        try {
            if (!isset($param['id']) || $param['id'] == '') {
                $result = $this->db->insert('contratos', $insert);
                if ($result) {
                    $lastId = method_exists($this->db, 'last_id') ? $this->db->last_id() : (method_exists($this->db, 'lastInsertId') ? $this->db->lastInsertId() : null);
                    if ($lastId) { $data['id'] = $lastId; }
                } else { $data['status']=0; $data['msg_title']='Error'; $data['msg']='Error al insertar'; }
            } else {
                $id = intval($param['id']);
                // No sobrescribir archivos si no subieron nuevos
                if (!$archivo_contrato_path) unset($insert['archivo_contrato']);
                if (!$firma_digital_path) unset($insert['firma_digital']);
                $where = array('id' => $id);
                $result = $this->db->update('contratos', $insert, $where);
                if ($result === false) { $data['status']=0; $data['msg_title']='Error'; $data['msg']='Error al actualizar'; }
                else { $data['id'] = $id; }
            }
        } catch (Exception $e) {
            $data['status']=0; $data['msg_title']='Error'; $data['msg']='Error al guardar: ' . $e->getMessage();
        }
        print(json_encode($data));
    }
}
