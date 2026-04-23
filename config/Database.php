<?php
/**
 * ============================================================
 * Database Connection Manager
 * ============================================================
 * Handles PDO database connections and provides static access
 */

class Database
{
    private static $instance = null;
    private $pdo;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct($host, $name, $user, $pass)
    {
        try {
            // Build DSN
            $dsn = "mysql:host={$host};dbname={$name}";
            
            $this->pdo = new PDO(
                $dsn,
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            // Set charset after connection
            $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            throw new DatabaseException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get or create database instance (singleton)
     */
    public static function getInstance($host, $name, $user, $pass)
    {
        if (self::$instance === null) {
            self::$instance = new self($host, $name, $user, $pass);
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single column
     */
    public function fetchColumn($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Execute insert/update/delete
     */
    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params);
    }    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Start transaction
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     */
    public function __wakeup()
    {
        throw new DatabaseException("Cannot unserialize singleton");
    }
}

/**
 * Custom exception for database errors
 */
class DatabaseException extends Exception {}
