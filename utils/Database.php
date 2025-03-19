<?php

class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct creation
     */
    private function __construct() {
        // These should be replaced with configuration constants
        $host = DB_HOST;
        $username = DB_USER;
        $password = DB_PASS;
        $database = DB_NAME;
        
        // Create connection
        $this->connection = new mysqli($host, $username, $password, $database);
        
        // Check connection
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        // Set charset
        $this->connection->set_charset('utf8mb4');
    }
    
    /**
     * Get singleton instance
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        
        return self::$instance;
    }
    
    /**
     * Get the database connection
     * @return mysqli
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a query
     * @param string $sql
     * @param string $types
     * @param array $params
     * @return bool|mysqli_stmt
     */
    public function query($sql, $types = "", $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->connection->error);
            return false;
        }
        
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        return $stmt;
    }
    
    /**
     * Close the database connection
     */
    public function close() {
        $this->connection->close();
    }
    
    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone() {}
    
    /**
     * Prevent serialization of the singleton instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
?>
