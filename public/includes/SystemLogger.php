<?php
/**
 * Sistema de Logging en Tiempo Real
 * Registra todas las actividades del sistema
 */

class SystemLogger {
    private static $logFile = __DIR__ . '/logs/system.log';
    private static $maxLogSize = 10 * 1024 * 1024; // 10MB
    private static $maxLogFiles = 5;
    
    /**
     * Registra un evento en el log
     */
    public static function log($level, $module, $action, $details = '', $userId = null) {
        $timestamp = date('Y-m-d H:i:s');
        $user = $userId ?: (isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'system');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $logEntry = sprintf(
            "[%s] [%s] [%s] [%s] [%s] [%s] [%s]\n",
            $timestamp,
            strtoupper($level),
            $module,
            $user,
            $action,
            $ip,
            $details
        );
        
        // Rotar logs si es necesario
        self::rotateLogs();
        
        // Escribir al archivo de log
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // También enviar a la consola del navegador si estamos en desarrollo
        if (getenv('APP_ENV') !== 'production') {
            error_log($logEntry);
        }
    }
    
    /**
     * Log de autenticación
     */
    public static function logAuth($action, $username, $success = true, $details = '') {
        $level = $success ? 'INFO' : 'WARNING';
        $actionDetails = $success ? "Login exitoso" : "Login fallido";
        if ($details) {
            $actionDetails .= " - " . $details;
        }
        
        self::log($level, 'AUTH', $action, $actionDetails, $username);
    }
    
    /**
     * Log de actividades de usuario
     */
    public static function logUserActivity($action, $details = '') {
        $userId = $_SESSION['user']['username'] ?? 'anonymous';
        self::log('INFO', 'USER', $action, $details, $userId);
    }
    
    /**
     * Log de descargas
     */
    public static function logDownload($filename, $fileType, $fileSize = 0) {
        $details = "Archivo: $filename, Tipo: $fileType, Tamaño: " . self::formatBytes($fileSize);
        self::logUserActivity('DOWNLOAD', $details);
    }
    
    /**
     * Log de cambios en datos
     */
    public static function logDataChange($table, $action, $recordId = null, $changes = []) {
        $details = "Tabla: $table";
        if ($recordId) {
            $details .= ", ID: $recordId";
        }
        if (!empty($changes)) {
            $details .= ", Cambios: " . json_encode($changes);
        }
        
        self::logUserActivity("DATA_$action", $details);
    }
    
    /**
     * Log de errores del sistema
     */
    public static function logError($module, $error, $details = '') {
        $fullDetails = $details ? "$error - $details" : $error;
        self::log('ERROR', $module, 'SYSTEM_ERROR', $fullDetails);
    }
    
    /**
     * Log de respaldos
     */
    public static function logBackup($action, $filename = '', $size = 0, $success = true) {
        $level = $success ? 'INFO' : 'ERROR';
        $details = "Archivo: $filename";
        if ($size > 0) {
            $details .= ", Tamaño: " . self::formatBytes($size);
        }
        
        self::log($level, 'BACKUP', $action, $details);
    }
    
    /**
     * Log de configuración
     */
    public static function logConfig($action, $section = '', $changes = []) {
        $details = "Sección: $section";
        if (!empty($changes)) {
            $details .= ", Cambios: " . json_encode($changes);
        }
        
        self::logUserActivity("CONFIG_$action", $details);
    }
    
    /**
     * Obtener logs recientes
     */
    public static function getRecentLogs($limit = 100) {
        if (!file_exists(self::$logFile)) {
            return [];
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = [];
        
        // Tomar las últimas líneas
        $lines = array_slice($lines, -$limit);
        
        foreach ($lines as $line) {
            if (preg_match('/^\[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] \[(.*)\]$/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'module' => $matches[3],
                    'user' => $matches[4],
                    'action' => $matches[5],
                    'ip' => $matches[6],
                    'details' => $matches[7]
                ];
            }
        }
        
        return array_reverse($logs); // Más recientes primero
    }
    
    /**
     * Limpiar logs antiguos
     */
    public static function clearOldLogs($days = 30) {
        if (!file_exists(self::$logFile)) {
            return;
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
        $filteredLines = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^\[([^\]]+)\]/', $line, $matches)) {
                if ($matches[1] >= $cutoffDate) {
                    $filteredLines[] = $line;
                }
            }
        }
        
        file_put_contents(self::$logFile, implode("\n", $filteredLines) . "\n");
    }
    
    /**
     * Rotar archivos de log
     */
    private static function rotateLogs() {
        if (!file_exists(self::$logFile)) {
            return;
        }
        
        if (filesize(self::$logFile) > self::$maxLogSize) {
            // Rotar archivos existentes
            for ($i = self::$maxLogFiles - 1; $i > 0; $i--) {
                $oldFile = self::$logFile . ".$i";
                $newFile = self::$logFile . "." . ($i + 1);
                
                if (file_exists($oldFile)) {
                    if ($i === self::$maxLogFiles - 1) {
                        unlink($oldFile);
                    } else {
                        rename($oldFile, $newFile);
                    }
                }
            }
            
            // Mover archivo actual
            rename(self::$logFile, self::$logFile . ".1");
        }
    }
    
    /**
     * Formatear bytes a formato legible
     */
    private static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Exportar logs a CSV
     */
    public static function exportToCSV($filename = null) {
        $logs = self::getRecentLogs(1000);
        $filename = $filename ?: 'system_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $csv = "Timestamp,Level,Module,User,Action,IP,Details\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,\"%s\"\n",
                $log['timestamp'],
                $log['level'],
                $log['module'],
                $log['user'],
                $log['action'],
                $log['ip'],
                str_replace('"', '""', $log['details'])
            );
        }
        
        return [
            'filename' => $filename,
            'content' => $csv,
            'size' => strlen($csv)
        ];
    }
}
?>
