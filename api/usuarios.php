<?php
// api/usuarios.php - Gestión de usuarios para administradores

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

// --- Authorization Helper --- 
function get_user_id($pdo) {
    $auth_header = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
        }
    }

    if (!$auth_header) {
        return null;
    }

    list($type, $token) = explode(' ', $auth_header, 2);
    if (strcasecmp($type, 'Bearer') != 0 || !is_numeric($token)) {
        return null;
    }
    
    return (int)$token;
}

function is_admin($pdo, $user_id) {
    if (!$user_id) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT rol FROM usuario WHERE id = ?");
    $stmt->execute([$user_id]);
    $rol = $stmt->fetchColumn();
    return $rol === 'admin';
}

try {
    $user_id = get_user_id($pdo);
    
    // Permitir acceso directo para GET (panel de administración)
    if ($method === 'GET' && !$user_id) {
        // Acceso directo permitido para listar usuarios (solo GET)
    } else {
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Token de autorización requerido.']);
            exit;
        }

        if (!is_admin($pdo, $user_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores.']);
            exit;
        }
    }

    switch ($method) {
        case 'GET':
            // Listar todos los usuarios
            if (isset($_GET['id'])) {
                // Obtener usuario específico
                $usuario_id = $_GET['id'];
                $stmt = $pdo->prepare("SELECT id, nombre, email, telefono, direccion, rol, fecha_registro, activo FROM usuario WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch();
                
                if (!$usuario) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
                    exit;
                }
                
                // Obtener estadísticas del usuario
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_pedidos, COALESCE(SUM(total), 0) as total_gastado FROM pedido WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
                $stats = $stmt->fetch();
                
                $usuario['estadisticas'] = $stats;
                
                echo json_encode(['success' => true, 'usuario' => $usuario]);
                
            } else {
                // Listar todos los usuarios con consulta optimizada
                $stmt = $pdo->prepare("
                    SELECT u.id, u.nombre, u.email, u.telefono, u.rol, u.fecha_registro, u.activo,
                           COUNT(p.id) as total_pedidos,
                           COALESCE(SUM(p.total), 0) as total_gastado
                    FROM usuario u 
                    LEFT JOIN pedido p ON u.id = p.usuario_id 
                    WHERE u.activo = TRUE
                    GROUP BY u.id, u.nombre, u.email, u.telefono, u.rol, u.fecha_registro, u.activo
                    ORDER BY u.fecha_registro DESC
                    LIMIT 100
                ");
                $stmt->execute();
                $usuarios = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'usuarios' => $usuarios]);
            }
            break;

        case 'POST':
            // Crear nuevo usuario (solo para administradores)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos incompletos. Se requiere nombre, email y contraseña.']);
                exit;
            }
            
            // Verificar que el email no esté en uso
            $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Este email ya está registrado.']);
                exit;
            }
            
            $rol = isset($data['rol']) && in_array($data['rol'], ['admin', 'cliente']) ? $data['rol'] : 'cliente';
            $telefono = $data['telefono'] ?? null;
            $direccion = $data['direccion'] ?? null;
            
            $stmt = $pdo->prepare("INSERT INTO usuario (nombre, email, password, telefono, direccion, rol) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['nombre'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $telefono,
                $direccion,
                $rol
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente.', 'id' => $pdo->lastInsertId()]);
            break;

        case 'PUT':
            // Actualizar usuario
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                exit;
            }
            
            $campos = [];
            $valores = [];
            
            if (isset($data['nombre'])) {
                $campos[] = "nombre = ?";
                $valores[] = $data['nombre'];
            }
            if (isset($data['email'])) {
                // Verificar que el email no esté en uso por otro usuario
                $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $data['id']]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Este email ya está en uso por otro usuario.']);
                    exit;
                }
                $campos[] = "email = ?";
                $valores[] = $data['email'];
            }
            if (isset($data['telefono'])) {
                $campos[] = "telefono = ?";
                $valores[] = $data['telefono'];
            }
            if (isset($data['direccion'])) {
                $campos[] = "direccion = ?";
                $valores[] = $data['direccion'];
            }
            if (isset($data['rol']) && in_array($data['rol'], ['admin', 'usuario'])) {
                $campos[] = "rol = ?";
                $valores[] = $data['rol'];
            }
            if (isset($data['activo'])) {
                $campos[] = "activo = ?";
                $valores[] = $data['activo'] ? 1 : 0;
            }
            if (isset($data['password']) && !empty($data['password'])) {
                $campos[] = "password = ?";
                $valores[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (empty($campos)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No se especificaron campos para actualizar.']);
                exit;
            }
            
            $valores[] = $data['id'];
            $sql = "UPDATE usuario SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($valores);
            
            if ($stmt->rowCount()) {
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente.']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado o sin cambios.']);
            }
            break;

        case 'DELETE':
            // Eliminar usuario
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Obtener ID del cuerpo de la petición o de los parámetros GET
            $usuario_id = null;
            if (isset($data['id'])) {
                $usuario_id = $data['id'];
            } elseif (isset($_GET['id'])) {
                $usuario_id = $_GET['id'];
            }
            
            // Obtener tipo de eliminación (soft o hard delete)
            $tipo_eliminacion = isset($data['tipo']) ? $data['tipo'] : 'soft';
            if (isset($_GET['tipo'])) {
                $tipo_eliminacion = $_GET['tipo'];
            }
            
            if (!$usuario_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                exit;
            }
            
            // No permitir eliminar al usuario actual
            if ($usuario_id == $user_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.']);
                exit;
            }
            
            if ($tipo_eliminacion === 'hard') {
                // ELIMINACIÓN REAL - Verificar dependencias primero
                
                // Verificar si el usuario tiene pedidos
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedido WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
                $pedidos_count = $stmt->fetchColumn();
                
                if ($pedidos_count > 0) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false, 
                        'message' => "No se puede eliminar. El usuario tiene {$pedidos_count} pedidos asociados. Use eliminación suave en su lugar."
                    ]);
                    exit;
                }
                
                // Verificar si el usuario tiene items en carrito
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM carrito WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
                $carrito_count = $stmt->fetchColumn();
                
                if ($carrito_count > 0) {
                    // Eliminar items del carrito primero
                    $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
                    $stmt->execute([$usuario_id]);
                }
                
                // Eliminar usuario completamente
                $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");
                $stmt->execute([$usuario_id]);
                
                if ($stmt->rowCount()) {
                    echo json_encode(['success' => true, 'message' => 'Usuario eliminado permanentemente.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
                }
                
            } else {
                // ELIMINACIÓN SUAVE (por defecto)
                $stmt = $pdo->prepare("UPDATE usuario SET activo = FALSE WHERE id = ?");
                $stmt->execute([$usuario_id]);
                
                if ($stmt->rowCount()) {
                    echo json_encode(['success' => true, 'message' => 'Usuario desactivado exitosamente.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
                }
            }
            
            if ($stmt->rowCount()) {
                echo json_encode(['success' => true, 'message' => 'Usuario desactivado exitosamente.']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Database Error in usuarios.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
?>
