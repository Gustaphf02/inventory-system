<?php
/**
 * Custom Session Handler for PostgreSQL
 * Stores sessions in database for persistence across container restarts
 */

class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;
    private $tableName = 'sessions';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    #[\ReturnTypeWillChange]
    public function open($save_path, $session_name) {
        // Create sessions table if it doesn't exist
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->tableName} (
                    id VARCHAR(255) PRIMARY KEY,
                    data TEXT,
                    last_activity INTEGER NOT NULL
                )
            ");
            return true;
        } catch (PDOException $e) {
            error_log("Session table creation error: " . $e->getMessage());
            return false;
        }
    }

    #[\ReturnTypeWillChange]
    public function close() {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function read($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT data FROM {$this->tableName} WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['data'] : '';
        } catch (PDOException $e) {
            error_log("Session read error: " . $e->getMessage());
            return '';
        }
    }

    #[\ReturnTypeWillChange]
    public function write($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->tableName} (id, data, last_activity) 
                VALUES (?, ?, ?) 
                ON CONFLICT (id) 
                DO UPDATE SET data = ?, last_activity = ?
            ");
            $stmt->execute([$id, $data, time(), $data, time()]);
            return true;
        } catch (PDOException $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }

    #[\ReturnTypeWillChange]
    public function destroy($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }

    #[\ReturnTypeWillChange]
    public function gc($maxlifetime) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE last_activity < ?");
            $stmt->execute([time() - $maxlifetime]);
            return true;
        } catch (PDOException $e) {
            error_log("Session gc error: " . $e->getMessage());
            return false;
        }
    }
}

