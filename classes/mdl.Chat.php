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
                    // Get the last 50 messages (most recent), then reverse so we return oldest->newest
                    $sql = "SELECT id, user, content, date FROM chat ORDER BY date DESC LIMIT 50";
                    $result = $this->db->fetchAll($sql);
                    if (!$result) {
                        $result = array();
                    } else {
                        $result = array_reverse($result);
                    }
                    // Determinar el usuario actual: preferir $_SESSION['usuario'], sino buscar en tabla usuarios por id de sesión
                    $currentUser = '';
                    if (session_status() == PHP_SESSION_NONE) session_start();
                    if (isset($_SESSION['usuario']) && $_SESSION['usuario'] !== '') {
                        $currentUser = $_SESSION['usuario'];
                    } else {
                        if (isset($_SESSION['guser_id']) && $_SESSION['guser_id'] !== '') {
                            try {
                                $rowu = $this->db->fetchRow("SELECT xusuario FROM usuarios WHERE xusuario_id = :id", array('id' => $_SESSION['guser_id']));
                                if ($rowu && isset($rowu['xusuario']) && $rowu['xusuario'] !== '') {
                                    $currentUser = $rowu['xusuario'];
                                }
                            } catch (Exception $e) {
                                // ignore
                            }
                        }
                    }
                    foreach ($result as $k => $row) {
                        $result[$k]['is_current'] = ($row['user'] === $currentUser);
                    }
                    echo json_encode(array('status' => 1, 'messages' => $result, 'currentUser' => $currentUser));
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
                    if (session_status() == PHP_SESSION_NONE) session_start();
                    $user = '';
                    if (isset($_SESSION['usuario']) && $_SESSION['usuario'] !== '') {
                        $user = $_SESSION['usuario'];
                    } else if (isset($_SESSION['guser_id']) && $_SESSION['guser_id'] !== '') {
                        try {
                            $rowu = $this->db->fetchRow("SELECT xusuario FROM usuarios WHERE xusuario_id = :id", array('id' => $_SESSION['guser_id']));
                            if ($rowu && isset($rowu['xusuario']) && $rowu['xusuario'] !== '') {
                                $user = $rowu['xusuario'];
                            }
                        } catch (Exception $e) {
                            // ignore
                        }
                    }
                    if ($user === '') $user = 'Anónimo';
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
