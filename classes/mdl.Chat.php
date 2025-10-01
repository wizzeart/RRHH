<?php
/**
 * Módulo Chat
 * API para el chat interno (getChatMessages, sendMessage)
 */
class Chat {

    var $app;
    var $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db;
    }

    public function api($param) {
        // Asegurarnos de que no hay salida antes del JSON
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!$this->db) {
            echo json_encode(array('status' => 0, 'msg' => 'Error de conexión a la base de datos'));
            return;
        }

        switch ($param['method']) {
            case 'getChatMessages':
                try {
                    try {
                        $this->db->directExec("SELECT 1 FROM chat LIMIT 1");
                    } catch (Exception $e) {
                        $createTableSQL = "CREATE TABLE IF NOT EXISTS chat (id INT AUTO_INCREMENT PRIMARY KEY, user VARCHAR(50) NOT NULL, content TEXT NOT NULL, date DATETIME NOT NULL) DEFAULT CHARSET=utf8;";
                        $this->db->directExec($createTableSQL);
                    }
                    $sql = "SELECT id, user, content, date FROM chat ORDER BY date DESC LIMIT 100";
                    $result = $this->db->fetchAll($sql);
                    if (!$result) $result = array();
                    echo json_encode(array('status' => 1, 'messages' => $result));
                } catch (Exception $ex) {
                    echo json_encode(array('status' => 0, 'msg' => $ex->getMessage()));
                }
                break;

            case 'sendMessage':
                try {
                    if (!isset($param['content']) || trim($param['content']) === '') {
                        throw new Exception("El mensaje no puede estar vacío");
                    }
                    $message = trim($param['content']);
                    if (strlen($message) > 200) {
                        throw new Exception("El mensaje no puede exceder los 200 caracteres");
                    }
                    try {
                        $this->db->directExec("SELECT 1 FROM chat LIMIT 1");
                    } catch (Exception $e) {
                        $createTableSQL = "CREATE TABLE IF NOT EXISTS chat (id INT AUTO_INCREMENT PRIMARY KEY, user VARCHAR(50) NOT NULL, content TEXT NOT NULL, date DATETIME NOT NULL) DEFAULT CHARSET=utf8;";
                        $this->db->directExec($createTableSQL);
                    }
                    $user = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Anónimo';
                    $sql = "INSERT INTO chat (user, content, date)
                            SELECT ?, ?, NOW()
                            FROM DUAL
                            WHERE NOT EXISTS (
                                SELECT 1 FROM chat WHERE user = ? AND content = ? AND date >= (NOW() - INTERVAL 5 SECOND)
                            )";
                    $r = $this->db->directExec($sql, array($user, $message, $user, $message));
                    echo json_encode(array('status' => 1, 'msg' => 'Mensaje enviado'));
                } catch (Exception $ex) {
                    echo json_encode(array('status' => 0, 'msg' => $ex->getMessage()));
                }
                break;

            default:
                echo json_encode(array('status' => 0, 'msg' => 'Método no soportado'));
                break;
        }
    }
}
