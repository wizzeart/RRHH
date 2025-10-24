<?php
class Document
{
    var $app;
    var $db;
    var $action;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $app->db;
        $action = 'insert';
    }

    public function api($param)
    {
        switch ($param['method']) {
            case 'list-id':
                $data = $this->_list_id($param);
                print(json_encode($data));
                break;
            case 'save':
                $this->_save($param);
                break;
            case 'del':
                $this->_del($param);
                break;
        }
    }

    public function controlador($param) {}

    private function _list_id($param)
    {
        $data = array();
        try {
            $sql = "SELECT * FROM documentos_trabajador WHERE trabajador_id=:id";
            $data = $this->db->fetchAll($sql, array('id' => $param['trabajador_id']));
        } catch (Exception $e) {
            $data = array();
        }
        return $data;
    }

    private function _save($param)
    {
        try {
            if (isset($param['id'])) {
                $this->app->db->update('documentos_trabajador', $param['id'], $param);
            } else {
                //copiar el archivo
                // Ruta base donde se guardarán los archivos
                $rutaBase = "uploads/trabajadores/" . $param['trabajador_id'] . "/";
               
                // Crear carpeta si no existe
                if (!is_dir($rutaBase)) {
                    mkdir($rutaBase, 0777, true);
                }

                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
                    //pa poner el nombre seleccionar el maximo id+1
                    $sql = "SELECT MAX(id) as max_id FROM documentos_trabajador WHERE trabajador_id=:id";
                    $data = $this->db->fetchAll($sql, array('id' => $param['trabajador_id']));
                    $max_id = $data[0]['max_id'] + 1;

                    $rutaDestino = $rutaBase . $max_id . "." . pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
                    
                    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaDestino)) {
                        // Aquí podrías guardar en tu BD si quieres
                        echo json_encode(['status' => 'success', 'path' => $rutaDestino]);
                    } else {
                        http_response_code(500);
                        echo json_encode(['status' => 'error', 'message' => 'Error al mover el archivo']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'No se recibió el archivo']);
                }

                $this->app->db->insert('documentos_trabajador', array(
                    'trabajador_id' => $param['trabajador_id'],
                    'tipo' => $param['tipo_doc'],
                    'archivo' => $rutaDestino,
                    'fecha_upload' => date('Y-m-d H:i:s'),
                ));
            }
        } catch (Exception $e) {
        }
    }

    private function _del($param)
    {
        try {

            $doc = $this->app->db->fetchAll('SELECT * FROM documentos_trabajador WHERE id=:id', array('id' => $param['id']));
            if (!empty($doc[0]['archivo'])) {
                unlink($doc[0]['archivo']);
            }

            $this->app->db->del('documentos_trabajador', array('id' => $param['id']));
            echo json_encode(['status' => 'success']);  
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
