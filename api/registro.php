<?php
// api/registro.php

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

// Validar que los datos requeridos estén presentes
if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Nombre, email y contraseña son requeridos.']);
    exit;
}

$nombre = trim($data['nombre']);
$email = trim($data['email']);
$password = $data['password'];
$telefono = isset($data['telefono']) ? trim($data['telefono']) : null;
$direccion = isset($data['direccion']) ? trim($data['direccion']) : null;

// Validaciones básicas
if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

if (strlen($nombre) < 2) {
    echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 2 caracteres.']);
    exit;
}

try {
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este email ya está registrado.']);
        exit;
    }

    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuario (nombre, email, password, telefono, direccion, rol, fecha_registro) 
        VALUES (?, ?, ?, ?, ?, 'usuario', NOW())
    ");
    
    $stmt->execute([$nombre, $email, $password_hash, $telefono, $direccion]);
    
    $user_id = $pdo->lastInsertId();
    
    // Obtener los datos del usuario recién creado (sin la contraseña)
    $stmt = $pdo->prepare("SELECT id, nombre, email, telefono, direccion, rol, fecha_registro FROM usuario WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    echo json_encode([
        'success' => true, 
        'message' => 'Usuario registrado exitosamente. ¡Bienvenido!',
        'user' => $user
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    
    // Verificar si es error de email duplicado
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Este email ya está registrado.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor. Inténtalo más tarde.']);
    }
}
?>
