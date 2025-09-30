<?php

/**
 * Description of Sql
 *
 * @author alvaro
 */
class MsSql
{

    var $conn;
    var $cr; //por defecto será 1 que equivale a rr si se cambia a 2 y se usa una query almacenará el conjunto en rs
    var $rr;
    var $rs;
    var $SQL_HOST;
    var $SQL_USER;
    var $SQL_PWD;
    var $SQL_DB;
    var $debug;

    /*
public function __construct($host, $db, $user, $pwd) {
    $this->SQL_HOST = $host;
    $this->SQL_USER = $user;
    $this->SQL_PWD = $pwd;
    $this->SQL_DB = $db;
    $this->debug = false;

    try {
        $dsn = "sqlsrv:Server=$host;Database=$db;Encrypt=Yes;TrustServerCertificate=Yes";
        $pdo = new PDO($dsn, $user, $pwd, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
        ]);

        $this->conn = $pdo;
    } catch (PDOException $e) {
        $this->conn = null;
        print('There was a problem connecting. ' . $e->getMessage());
        die();
    }

    //$this->cr = 1;
}
*/
    public function __construct($host, $db, $user, $pwd)
    {
        $this->SQL_HOST = $host;
        $this->SQL_USER = $user;
        $this->SQL_PWD = $pwd;
        $this->SQL_DB = $db;
        $this->debug = false;

        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pwd, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $this->conn = $pdo;
        } catch (PDOException $e) {
            $this->conn = null;
            print('There was a problem connecting. ' . $e->getMessage());
            die();
        }

        //$this->cr = 1;
    }


    public function fetchAll($sql, $param = '')
    {
        $data = null;
        $consulta = null;
        $consulta = $this->conn->prepare($sql);
        //print($sql);
        //print_r($param);
        //die('kdkd');
        if ($param != '')
            $consulta->execute($param);
        else
            $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function directExec($sql, $param = '')
    {
        $data = null;
        $consulta = null;

        $consulta = $this->conn->prepare($sql);
        if ($param != '')
            $consulta->execute($param);
        else
            $consulta->execute();

        if ($this->debug == true) {
            $this->debug = false;
            $sqld = $sql;
            foreach ($param as $k => $v) {
                $sqld = str_replace(':' . $k, "'" . $v . "'", $sqld);
            }
            print($sqld);
        }

        $r = $consulta->execute($sql);
        return $r;
    }

    public function fetchRow($sql, $param = '')
    {
        $data = null;
        $consulta = null;

        $consulta = $this->conn->prepare($sql);
        if ($param != '')
            $consulta->execute($param);
        else
            $consulta->execute();

        if ($this->debug == true) {
            $this->debug = false;
            $sqld = $sql;
            foreach ($param as $k => $v) {
                $sqld = str_replace(':' . $k, "'" . $v . "'", $sqld);
            }
            print($sqld);
        }

        // Obtener solo una fila; si no hay resultados, devolver null para evitar warnings
        $row = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return $row;
    }

    public function close()
    {
        //odbc_close($this->conn);
        $this->conn = null;
    }

    public function del($table, $where)
    {
        $consulta = null;
        $r = null;

        $sql = "DELETE FROM $table WHERE ";
        if (is_array($where)) {
            $w = '';
            foreach ($where as $k => $v) {
                if ($v !== '') {
                    $w .= $k . '=:' . $k . " AND ";
                }
            }
            $w = substr($w, 0, -5);
            $sql .= $w;
        } else {
            $sql .= $where;
        }

        $consulta = $this->conn->prepare($sql);
        $r = $consulta->execute($where);

        return $r;
    }

    public function insert($table, $insert)
    {
        $p1 = array();
        $p2 = array();
        $r = '';
        $consulta = null;

        foreach ($insert as $k => $v) {
            $p1[] = $k;
            $p2[] = ':' . $k;
        }

        $sql = "INSERT INTO $table(" . join(',', $p1) . ") VALUES(" . join(',', $p2) . ")";
        //print($sql);
        $consulta = $this->conn->prepare($sql);

        $r = $consulta->execute($insert);
        //$consulta->debugDumpParams();
        return $r;
    }

    public function update($table, $update, $where)
    {
        $consulta = null;
        $r = null;

        $sql = 'UPDATE ' . $table . ' SET ';
        $params = array();
        
        if (is_array($update)) {
            foreach ($update as $k => $v) {
                $sql .= $k . "=:upd_" . $k . ',';
                $params['upd_' . $k] = $v;
            }
        } else {
            $sql .= $update;
        }
        $sql = substr($sql, 0, -1);

        $sql .= ' WHERE ';
        if (is_array($where)) {
            foreach ($where as $k => $v) {
                $sql .= $k . "=:whr_" . $k . " AND ";
                $params['whr_' . $k] = $v;
            }
            $sql = substr($sql, 0, -5);
        } else {
            $sql .= $where;
        }
        
        if ($this->debug)
            print($sql);
        
        // Debug temporal: mostrar la consulta y parámetros
        error_log("DEBUG SQL: " . $sql);
        error_log("DEBUG PARAMS: " . json_encode($params));
        
        $consulta = $this->conn->prepare($sql);
        $r = $consulta->execute($params);
        //$rr = odbc_exec($this->conn, $msql) or die('La consulta falló: ' . odbc_error() . ' sql--> ' . $msql);
        return $r;
    }

    public function last_id()
    {
        //$this->conn->lastInsertId();
        //return odbc_insert_id($this->conn);
        /* $sql = 'SELECT @@IDENTITY AS ID';
          $rr = odbc_exec($this->conn, $sql);
          $row = odbc_fetch_array($rr); */
        //return $row['ID'];
        return $this->conn->lastInsertId();
    }
}
