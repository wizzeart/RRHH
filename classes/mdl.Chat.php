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

        // Helper: ensure table exists and has new columns
        $this->ensureSchema();

        switch ($param['method']) {
            case 'getChatMessages':
                try {
                    // Determine current user and role
                    $currentUser = $this->getCurrentUsername();
                    $isAdmin = $this->isCurrentUserAdmin();

                    // Build query per role
                    if ($isAdmin) {
                        $sql = "SELECT id, user, content, date, sender_role, receiver_user, reply_to_user, reply_to_content FROM chat ORDER BY date ASC";
                        $result = $this->db->fetchAll($sql);
                    } else {
                        $sql = "SELECT id, user, content, date, sender_role, receiver_user, reply_to_user, reply_to_content
                                FROM chat
                                WHERE user = :u
                                   OR (sender_role = 'admin' AND receiver_user = :u2)
                                ORDER BY date ASC";
                        $result = $this->db->fetchAll($sql, array('u' => $currentUser, 'u2' => $currentUser));
                    }
                    if (!$result) {
                        $result = array();
                    }
                    foreach ($result as $k => $row) {
                        $result[$k]['is_current'] = ($row['user'] === $currentUser);
                    }
                    echo json_encode(array('status' => 1, 'messages' => $result, 'currentUser' => $currentUser, 'is_admin' => $isAdmin ? 1 : 0));
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
                    $user = $this->getCurrentUsername();
                    if ($user === '') $user = 'Anónimo';

                    $isAdmin = $this->isCurrentUserAdmin();
                    $senderRole = $isAdmin ? 'admin' : 'worker';
                    $receiver = isset($param['receiver_user']) ? trim($param['receiver_user']) : '';
                    if (!$isAdmin) { $receiver = ''; }

                    // allow optional reply metadata when admins reply to a specific message
                    $replyUser = isset($param['original_user']) ? trim($param['original_user']) : null;
                    $replyContent = isset($param['original_message']) ? trim($param['original_message']) : null;

                    $sql = "INSERT INTO chat (user, content, date, sender_role, receiver_user, reply_to_user, reply_to_content)
                            SELECT ?, ?, NOW(), ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, '')
                            FROM DUAL
                            WHERE NOT EXISTS (
                                SELECT 1 FROM chat WHERE user = ? AND content = ? AND date >= (NOW() - INTERVAL 5 SECOND)
                            )";
                    $r = $this->db->directExec($sql, array($user, $message, $senderRole, $receiver, $replyUser, $replyContent, $user, $message));
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

    private function ensureSchema() {
        try {
            // Ensure table exists
            $this->db->directExec("CREATE TABLE IF NOT EXISTS chat (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user VARCHAR(50) NOT NULL,
                content TEXT NOT NULL,
                date DATETIME NOT NULL
            ) DEFAULT CHARSET=utf8");

            // Add sender_role if missing
            $col = $this->db->fetchRow("SHOW COLUMNS FROM chat LIKE 'sender_role'");
            if (!$col) {
                $this->db->directExec("ALTER TABLE chat ADD COLUMN sender_role ENUM('admin','worker') NOT NULL DEFAULT 'worker'");
            }
            // Add receiver_user if missing
            $col2 = $this->db->fetchRow("SHOW COLUMNS FROM chat LIKE 'receiver_user'");
            if (!$col2) {
                $this->db->directExec("ALTER TABLE chat ADD COLUMN receiver_user VARCHAR(100) NULL");
            }
            // Add reply_to_user if missing
            $col3 = $this->db->fetchRow("SHOW COLUMNS FROM chat LIKE 'reply_to_user'");
            if (!$col3) {
                $this->db->directExec("ALTER TABLE chat ADD COLUMN reply_to_user VARCHAR(100) NULL");
            }
            // Add reply_to_content if missing
            $col4 = $this->db->fetchRow("SHOW COLUMNS FROM chat LIKE 'reply_to_content'");
            if (!$col4) {
                $this->db->directExec("ALTER TABLE chat ADD COLUMN reply_to_content TEXT NULL");
            }
        } catch (Exception $e) {
            // silent fail, handled on use
        }
    }

    private function getCurrentUsername() {
        $currentUser = '';
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['usuario']) && $_SESSION['usuario'] !== '') {
            $currentUser = $_SESSION['usuario'];
        } else if (isset($_SESSION['guser_id']) && $_SESSION['guser_id'] !== '') {
            try {
                $rowu = $this->db->fetchRow("SELECT xusuario FROM usuarios WHERE xusuario_id = :id", array('id' => $_SESSION['guser_id']));
                if ($rowu && isset($rowu['xusuario']) && $rowu['xusuario'] !== '') {
                    $currentUser = $rowu['xusuario'];
                }
            } catch (Exception $e) { /* ignore */ }
        }
        return $currentUser;
    }

    private function isCurrentUserAdmin() {
        // Try to detect admin based on session/app role
        $role = '';
        if (isset($this->app->rol) && $this->app->rol !== '') $role = $this->app->rol;
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!$role && isset($_SESSION['grol'])) $role = $_SESSION['grol'];
        $roleStr = strtolower((string)$role);
        // Consider admin if role id == 1 or role string contains 'admin'
        if ($roleStr === '1' || strpos($roleStr, 'admin') !== false) return true;
        return false;
    }
}
