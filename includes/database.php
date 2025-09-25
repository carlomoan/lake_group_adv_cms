<?php
/**
 * Database Connection and Helper Functions
 */

// Create PDO connection
function get_database_connection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch(PDOException $e) {
            log_error("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    return $pdo;
}

// Initialize global PDO connection
$pdo = get_database_connection();

/**
 * Database Helper Class
 */
class Database {
    private $pdo;

    public function __construct() {
        $this->pdo = get_database_connection();
    }

    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            log_error("Database query failed: " . $e->getMessage() . " SQL: " . $sql);
            return false;
        }
    }

    /**
     * Fetch single row
     */
    public function fetchRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Insert record and return ID
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        if ($this->query($sql, $data)) {
            return $this->pdo->lastInsertId();
        }

        return false;
    }

    /**
     * Update records
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";

        return $this->query($sql, array_merge($data, $whereParams));
    }

    /**
     * Delete records
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $whereParams);
    }

    /**
     * Count records
     */
    public function count($table, $where = '1=1', $whereParams = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = $this->fetchRow($sql, $whereParams);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Check if record exists
     */
    public function exists($table, $where, $whereParams = []) {
        return $this->count($table, $where, $whereParams) > 0;
    }

    /**
     * Get table columns
     */
    public function getColumns($table) {
        $sql = "DESCRIBE {$table}";
        return $this->fetchAll($sql);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
}

// Create global database instance
$db = new Database();
?>