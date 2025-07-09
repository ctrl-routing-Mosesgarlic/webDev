<?php
/**
 * Database Connection File
 * This file establishes a connection to the MySQL database
 */

// Database configuration
class database {
    private $host = "127.0.0.1";
    private $username = "root";
    private $password = "";
    private $database = "wsm_system";
    private $port = 3306;
    private $conn;

    public function __construct() {
        try {
            $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->database, $this->port);
            if (!$this->conn) {
                throw new Exception(mysqli_connect_error());
            }

            // Set charset to ensure proper encoding
            mysqli_set_charset($this->conn, "utf8mb4");

            // Set timezone
            date_default_timezone_set('Africa/Nairobi');

        } catch (Exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            mysqli_close($this->conn);
            $this->conn = null;
        }
    }

    public function __destruct() {
        $this->closeConnection();
    }

}

?>
