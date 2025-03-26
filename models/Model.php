<?php
class Model {
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Find a record by its primary key
     *
     * @param string $id The ID to find
     * @return array|null The record found or null if not found
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->queryOne($sql, "s", [$id]);
    }
    
    /**
     * Get all records from the table
     *
     * @param string $orderBy Column to order by
     * @param string $order Ascending (ASC) or Descending (DESC)
     * @return array All records
     */
    public function all($orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        return $this->query($sql);
    }
    
    /**
     * Create a new record with UUID
     *
     * @param array $data Associative array of column => value pairs
     * @return string|false The new record UUID or false on failure
     */
    public function create($data) {
        if (!isset($data['id'])) {
            $data['id'] = $this->generateUuid();
        }
        
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($values), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $types = $this->determineTypes($values);
        $success = $this->execute($sql, $types, $values);
        
        return $success ? $data['id'] : false;
    }
    
    /**
     * Update an existing record
     *
     * @param string $id The ID of the record to update (UUID)
     * @param array $data Associative array of column => value pairs
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $set = array_map(function($col) { return "{$col} = ?"; }, $columns);
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $set),
            $this->primaryKey
        );
        
        $values[] = $id;  // Add ID to values
        $types = $this->determineTypes($values);
        
        return $this->execute($sql, $types, $values) !== false;
    }
    
    /**
     * Delete a record
     *
     * @param string $id The ID of the record to delete (UUID)
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->execute($sql, "s", [$id]) !== false;
    }
    
    /**
     * Find records by a specific field value
     *
     * @param string $field The field name
     * @param mixed $value The value to search for
     * @return array Found records
     */
    public function findBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = ?";
        // Properly detect type for UUID or integer fields
        $type = $this->determineType($value);
        return $this->query($sql, $type, [$value]);
    }
    
    /**
     * Find a single record by a specific field value
     *
     * @param string $field The field name
     * @param mixed $value The value to search for
     * @return array|null Found record or null
     */
    public function findOneBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = ?";
        // Properly detect type for UUID or integer fields
        $type = $this->determineType($value);
        return $this->queryOne($sql, $type, [$value]);
    }
    
    /**
     * Execute a query that returns multiple rows
     *
     * @param string $sql SQL query with placeholders
     * @param string $types Parameter types (i, s, d, b)
     * @param array $params Array of parameters
     * @return array Query results
     */
    protected function query($sql, $types = "", $params = []) {
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("MySQL prepare error: " . mysqli_error($this->conn));
            return [];
        }
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("MySQL execute error: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return [];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            return [];
        }
        
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Execute a query that returns a single row
     *
     * @param string $sql SQL query with placeholders
     * @param string $types Parameter types (i, s, d, b)
     * @param array $params Array of parameters
     * @return array|null Query result or null if not found
     */
    protected function queryOne($sql, $types = "", $params = []) {
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("MySQL prepare error: " . mysqli_error($this->conn));
            return null;
        }
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("MySQL execute error: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return null;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            return null;
        }
        
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Execute an INSERT, UPDATE or DELETE query
     *
     * @param string $sql SQL query with placeholders
     * @param string $types Parameter types (i, s, d, b)
     * @param array $params Array of parameters
     * @param bool $getInsertId Whether to return insert ID instead of affected rows
     * @return int|bool Number of affected rows, insert ID, or false on failure
     */
    protected function execute($sql, $types = "", $params = [], $getInsertId = false) {
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("MySQL prepare error: " . mysqli_error($this->conn));
            return false;
        }
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("MySQL execute error: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return false;
        }
        
        $result = $getInsertId ? mysqli_insert_id($this->conn) : mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Determine MySQL type for a single PHP value
     * 
     * @param mixed $value The value to determine type for
     * @return string MySQL parameter type
     */
    protected function determineType($value) {
        if (is_int($value)) {
            return 'i'; // Integer
        } elseif (is_float($value)) {
            return 'd'; // Double
        } else {
            return 's'; // String (also used for UUIDs)
        }
    }
    
    /**
     * Determine MySQL types from PHP values
     *
     * @param array $values Values to determine types for
     * @return string String of i, d, s, b representing types
     */
    protected function determineTypes($values) {
        $types = '';
        foreach ($values as $value) {
            $types .= $this->determineType($value);
        }
        return $types;
    }
    
    /**
     * Generate a UUID v4
     * 
     * @return string UUID string
     */
    protected function generateUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
