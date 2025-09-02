<?php
// config/database.php

class Database {
    private static $instance = null;
    private $connection;
    
    private $host = '127.0.0.1';
    private $db_name = 'nattier_store';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (\PDOException $e) {
            throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Mantener compatibilidad con el código anterior
try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
}
?>