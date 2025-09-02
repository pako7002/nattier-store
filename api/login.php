<?php
// api/login.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Se requiere email y contraseña.']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Ningún campo puede estar vacío.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // No enviar el hash de la contraseña al cliente
        unset($user['password']); 
        
        echo json_encode([
            'success' => true, 
            'message' => 'Inicio de sesión exitoso.',
            'user' => $user 
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de conexión con la base de datos.']);
}

?>