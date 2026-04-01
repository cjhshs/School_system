<?php
class DB {
    private $conn;
    public function __construct($host, $user, $pass, $db) {
        $this->conn = new mysqli($host, $user, $pass, $db);
        if ($this->conn->connect_error) {
            throw new Exception('DB Connect Error: ' . $this->conn->connect_error);
        }
    }
    public function query($sql) {
        return $this->conn->query($sql);
    }
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    public function escape($s) {
        return $this->conn->real_escape_string($s);
    }
    public function getConn() {
        return $this->conn;
    }
    public function close() {
        $this->conn->close();
    }
}
?>
