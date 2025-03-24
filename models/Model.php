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
     * @param int $id The ID to find
     * @return array|null The record found or null if not found
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->queryOne($sql, "i", [$id]);
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
     * Create a new record
     *
     * @param array $data Associative array of column => value pairs
     * @return int|false The new record ID or false on failure
     */
    public function create($data) {
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
        return $this->execute($sql, $types, $values, true);
    }
    
    /**
     * Update an existing record
     *
     * @param int $id The ID of the record to update
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
     * @param int $id The ID of the record to delete
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->execute($sql, "i", [$id]) !== false;
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
        $type = is_int($value) ? "i" : (is_float($value) ? "d" : "s");
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
        $type = is_int($value) ? "i" : (is_float($value) ? "d" : "s");
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
     * Determine MySQL types from PHP values
     *
     * @param array $values Values to determine types for
     * @return string String of i, d, s, b representing types
     */
    protected function determineTypes($values) {
        $types = '';
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i';  // Integer
            } elseif (is_float($value)) {
                $types .= 'd';  // Double
            } elseif (is_string($value)) {
                $types .= 's';  // String
            } else {
                $types .= 's';  // Default to string for other types
            }
        }
        return $types;
    }
}
?>
