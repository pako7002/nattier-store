<?php
// api/forgot_password.php

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

if (!isset($data['email']) || empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'El email es requerido.']);
    exit;
}

$email = trim($data['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido.']);
    exit;
}

try {
    // Verificar si el email existe en la base de datos
    $stmt = $pdo->prepare("SELECT id, nombre FROM usuario WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Por seguridad, no revelamos si el email existe o no
        echo json_encode([
            'success' => true, 
            'message' => 'Si el email existe en nuestro sistema, recibirás las instrucciones para restablecer tu contraseña.'
        ]);
        exit;
    }

    // Generar token de reseteo (en un sistema real, esto se almacenaría en BD con expiración)
    $reset_token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // En un sistema real, guardarías este token en una tabla de tokens de reseteo
    // Por ahora, simulamos el proceso

    // Simular envío de email (en producción usarías PHPMailer, SendGrid, etc.)
    $reset_link = "http://localhost/Nattier_Store/ecommerce_artesanias.html#reset-token=" . $reset_token;
    
    // Log del token para propósitos de desarrollo (ELIMINAR EN PRODUCCIÓN)
    error_log("Reset token for $email: $reset_token");

    echo json_encode([
        'success' => true,
        'message' => 'Se han enviado las instrucciones para restablecer tu contraseña a tu email.',
        // Solo para desarrollo/testing - ELIMINAR EN PRODUCCIÓN
        'token_for_testing' => $reset_token,
        'user_name' => $user['nombre']
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor. Inténtalo más tarde.']);
}
?>
