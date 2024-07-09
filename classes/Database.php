<?php

// Autoloader
$autoloaderPath = __DIR__ . "/../AutoLoader.php";
// Include schema.php
include_once $autoloaderPath;

class Database
{
    public $conn;

    public function __construct()
    {
        $this->conn = new mysqli(Config::$servername, Config::$username, Config::$password, Config::$dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function closeConnection()
    {
        $this->conn->close();
    }

}
