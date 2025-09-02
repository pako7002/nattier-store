<?php
// api/reset_password.php

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

if (!isset($data['token']) || !isset($data['password']) || empty($data['token']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Token y nueva contraseña son requeridos.']);
    exit;
}

$token = trim($data['token']);
$new_password = $data['password'];

// Validar contraseña
if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

try {
    // En un sistema real, aquí verificarías el token contra una tabla de tokens de reseteo
    // Por ahora, simulamos que cualquier token de 64 caracteres es válido por simplicidad
    
    if (strlen($token) !== 64) {
        echo json_encode(['success' => false, 'message' => 'Token de reseteo inválido o expirado.']);
        exit;
    }

    // Para propósitos de demostración, permitimos resetear la contraseña del usuario de prueba
    // En producción, aquí buscarías el user_id asociado al token válido
    
    // Simulamos que el token pertenece al usuario cliente de prueba
    $email = 'cliente@test.com'; // En producción, esto vendría del token
    
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE usuario SET password = ? WHERE email = ?");
    $stmt->execute([$password_hash, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Token de reseteo inválido o expirado.']);
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor. Inténtalo más tarde.']);
}
?>
