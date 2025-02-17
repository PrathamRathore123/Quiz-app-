<?php
class Database {
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        require_once 'Config.php';
        $config = new Config();
        
        error_log("Attempting to connect to database...");
        error_log("Host: " . $config->getHost());
        error_log("Username: " . $config->getUsername());
        error_log("Database: " . $config->getDatabase());

        $this->connection = new mysqli(
            $config->getHost(),     // host
            $config->getUsername(), // username
            $config->getPassword(), // password
            $config->getDatabase()  // database name
        );

        if ($this->connection->connect_error) {
            error_log("Database connection failed: " . $this->connection->connect_error);
            die("Connection failed: " . $this->connection->connect_error);
        }

        error_log("Database connection successful.");
    }

    public function query($sql) {
        error_log("Executing query: " . $sql);
        $result = $this->connection->query($sql);
        if (!$result) {
            error_log("Query failed: " . $this->connection->error);
        }
        return $result;
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }

    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    public function getError() {
        return $this->connection->error;
    }

    private $isClosed = false;

    public function close() {
        if ($this->connection && !$this->isClosed) {
            $this->connection->close();
            $this->isClosed = true;
            error_log("Database connection closed.");
        }
    }

    public function __destruct() {
        $this->close();
    }
}
?>
