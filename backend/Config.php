<?php

class Config {
    private $host = "localhost";
    private $username = "root";  // Your database username
    private $password = "";      // Your database password
    private $database = "quiz_app";  // Your database name


    public function getHost() {
        return $this->host;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getDatabase() {
        return $this->database;
    }
}
?>
