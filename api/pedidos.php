<?php
// api/pedidos.php

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
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener detalles de un pedido específico
                $order_id = $_GET['id'];
                
                $sql = "SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email 
                        FROM pedido p 
                        JOIN usuario u ON p.usuario_id = u.id 
                        WHERE p.id = ?";
                
                // Si no es admin, solo puede ver sus propios pedidos
                if (!$user_id || !is_admin($pdo, $user_id)) {
                    if (!$user_id) {
                        http_response_code(401);
                        echo json_encode(['success' => false, 'message' => 'Token de autorización requerido.']);
                        exit;
                    }
                    $sql .= " AND p.usuario_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$order_id, $user_id]);
                } else {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$order_id]);
                }
                
                $order = $stmt->fetch();
                
                if (!$order) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
                    exit;
                }
                
                // Obtener detalles de productos del pedido
                $stmt = $pdo->prepare("
                    SELECT dp.*, p.nombre, p.imagen 
                    FROM detalle_pedido dp 
                    JOIN producto p ON dp.producto_id = p.id 
                    WHERE dp.pedido_id = ?
                ");
                $stmt->execute([$order_id]);
                $order['detalles'] = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'order' => $order]);
                
            } else {
                // Obtener lista de pedidos
                if ($user_id && is_admin($pdo, $user_id)) {
                    // Admin ve todos los pedidos con información optimizada
                    $sql = "SELECT p.id, p.usuario_id, p.total, p.estado, p.fecha_pedido, p.direccion_envio, 
                                   u.nombre as cliente_nombre, u.email as cliente_email
                            FROM pedido p 
                            INNER JOIN usuario u ON p.usuario_id = u.id 
                            ORDER BY p.fecha_pedido DESC
                            LIMIT 100";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                } elseif ($user_id) {
                    // Usuario normal solo ve sus pedidos
                    $sql = "SELECT id, total, estado, fecha_pedido, direccion_envio 
                            FROM pedido 
                            WHERE usuario_id = ? 
                            ORDER BY fecha_pedido DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$user_id]);
                } else {
                    // Sin autenticación, retornar lista vacía
                    echo json_encode(['success' => true, 'orders' => []]);
                    break;
                }
                
                $orders = $stmt->fetchAll();
                echo json_encode(['success' => true, 'orders' => $orders]);
            }
            break;

        case 'POST':
            // Crear nuevo pedido (requiere autenticación)
            if (!$user_id) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token de autorización requerido.']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['cart']) || empty($data['cart']) || !isset($data['total'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos del pedido incompletos.']);
                exit;
            }
            
            $pdo->beginTransaction();
            
            try {
                // Crear el pedido principal
                $estado_pedido = 'pendiente';
                $direccion_envio = '';
                
                if (isset($data['direccion_envio'])) {
                    $direccion_envio = $data['direccion_envio'];
                } else if (isset($data['datos_entrega']['direccion'])) {
                    $direccion_envio = $data['datos_entrega']['direccion'];
                } else {
                    $direccion_envio = 'Dirección no especificada';
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO pedido (usuario_id, total, estado, direccion_envio, fecha_pedido) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $user_id, 
                    $data['total'], 
                    $estado_pedido,
                    $direccion_envio
                ]);
                
                $order_id = $pdo->lastInsertId();
                
                // Crear detalles del pedido y actualizar stock
                foreach ($data['cart'] as $item) {
                    // Verificar stock disponible
                    $stmt = $pdo->prepare("SELECT stock FROM producto WHERE id = ?");
                    $stmt->execute([$item['id']]);
                    $current_stock = $stmt->fetchColumn();
                    
                    if ($current_stock < $item['quantity']) {
                        throw new Exception("Stock insuficiente para el producto: " . $item['nombre']);
                    }
                    
                    // Insertar detalle del pedido
                    $stmt = $pdo->prepare("
                        INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $order_id,
                        $item['id'],
                        $item['quantity'],
                        $item['precio']
                    ]);
                    
                    // Actualizar stock del producto
                    $stmt = $pdo->prepare("UPDATE producto SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['id']]);
                }
                
                // Limpiar carrito del usuario
                $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
                $stmt->execute([$user_id]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Pedido creado exitosamente.',
                    'order_id' => $order_id
                ]);
                
            } catch (Exception $e) {
                $pdo->rollback();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        case 'PUT':
            // Actualizar estado del pedido (requiere autenticación)
            if (!$user_id) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token de autorización requerido.']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID del pedido requerido.']);
                exit;
            }
            
            // Si es actualización de estado del pedido (solo admin)
            if (isset($data['estado'])) {
                if (!is_admin($pdo, $user_id)) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores pueden actualizar el estado del pedido.']);
                    exit;
                }
                
                $stmt = $pdo->prepare("UPDATE pedido SET estado = ? WHERE id = ?");
                $stmt->execute([$data['estado'], $data['id']]);
                
                if ($stmt->rowCount()) {
                    echo json_encode(['success' => true, 'message' => 'Estado del pedido actualizado.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
                }
            }
            // Si es actualización de estado de pago (puede ser el mismo usuario o admin)
            elseif (isset($data['estado_pago'])) {
                // Verificar que el pedido pertenece al usuario o que sea admin
                $stmt = $pdo->prepare("SELECT usuario_id FROM pedido WHERE id = ?");
                $stmt->execute([$data['id']]);
                $pedido_usuario_id = $stmt->fetchColumn();
                
                if (!$pedido_usuario_id || ($pedido_usuario_id != $user_id && !is_admin($pdo, $user_id))) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'No tienes permisos para actualizar este pedido.']);
                    exit;
                }
                
                // Actualizar estado de pago y estado del pedido si es necesario
                $nuevo_estado = null;
                if ($data['estado_pago'] === 'pagado') {
                    $nuevo_estado = 'procesando';
                } elseif ($data['estado_pago'] === 'fallido') {
                    $nuevo_estado = 'cancelado';
                }
                
                if ($nuevo_estado) {
                    $stmt = $pdo->prepare("UPDATE pedido SET estado_pago = ?, estado = ? WHERE id = ?");
                    $stmt->execute([$data['estado_pago'], $nuevo_estado, $data['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE pedido SET estado_pago = ? WHERE id = ?");
                    $stmt->execute([$data['estado_pago'], $data['id']]);
                }
                
                if ($stmt->rowCount()) {
                    echo json_encode(['success' => true, 'message' => 'Estado de pago actualizado.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
                }
            }
            else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Debe especificar estado o estado_pago para actualizar.']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
?>
