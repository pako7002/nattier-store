<?php
// api/carrito.php

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

try {
    $user_id = get_user_id($pdo);
    
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token de autorización requerido.']);
        exit;
    }

    switch ($method) {
        case 'GET':
            // Obtener carrito del usuario
            $stmt = $pdo->prepare("
                SELECT c.*, p.nombre, p.precio, p.imagen, p.stock 
                FROM carrito c 
                JOIN producto p ON c.producto_id = p.id 
                WHERE c.usuario_id = ?
            ");
            $stmt->execute([$user_id]);
            $cart = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'cart' => $cart]);
            break;

        case 'POST':
            // Agregar producto al carrito
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['producto_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID del producto requerido.']);
                exit;
            }
            
            $producto_id = $data['producto_id'];
            $cantidad = isset($data['cantidad']) ? (int)$data['cantidad'] : 1;
            
            // Verificar si el producto existe y tiene stock
            $stmt = $pdo->prepare("SELECT stock FROM producto WHERE id = ?");
            $stmt->execute([$producto_id]);
            $stock = $stmt->fetchColumn();
            
            if ($stock === false) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
                exit;
            }
            
            if ($stock < $cantidad) {
                echo json_encode(['success' => false, 'message' => 'Stock insuficiente.']);
                exit;
            }
            
            // Verificar si el producto ya está en el carrito
            $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$user_id, $producto_id]);
            $existing_quantity = $stmt->fetchColumn();
            
            if ($existing_quantity !== false) {
                // Actualizar cantidad existente
                $new_quantity = $existing_quantity + $cantidad;
                
                if ($new_quantity > $stock) {
                    echo json_encode(['success' => false, 'message' => 'No puedes agregar más productos, stock insuficiente.']);
                    exit;
                }
                
                $stmt = $pdo->prepare("UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
                $stmt->execute([$new_quantity, $user_id, $producto_id]);
                
                echo json_encode(['success' => true, 'message' => 'Cantidad actualizada en el carrito.']);
            } else {
                // Insertar nuevo producto en el carrito
                $stmt = $pdo->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $producto_id, $cantidad]);
                
                echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito.']);
            }
            break;

        case 'PUT':
            // Actualizar cantidad de un producto en el carrito
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['producto_id']) || !isset($data['cantidad'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID del producto y cantidad requeridos.']);
                exit;
            }
            
            $producto_id = $data['producto_id'];
            $cantidad = (int)$data['cantidad'];
            
            if ($cantidad <= 0) {
                // Si la cantidad es 0 o negativa, eliminar del carrito
                $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?");
                $stmt->execute([$user_id, $producto_id]);
                
                echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito.']);
            } else {
                // Verificar stock disponible
                $stmt = $pdo->prepare("SELECT stock FROM producto WHERE id = ?");
                $stmt->execute([$producto_id]);
                $stock = $stmt->fetchColumn();
                
                if ($cantidad > $stock) {
                    echo json_encode(['success' => false, 'message' => 'Stock insuficiente.']);
                    exit;
                }
                
                $stmt = $pdo->prepare("UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
                $stmt->execute([$cantidad, $user_id, $producto_id]);
                
                echo json_encode(['success' => true, 'message' => 'Cantidad actualizada.']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['producto_id'])) {
                // Eliminar producto específico del carrito
                $producto_id = $_GET['producto_id'];
                
                $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?");
                $stmt->execute([$user_id, $producto_id]);
                
                if ($stmt->rowCount()) {
                    echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito.']);
                }
            } else {
                // Vaciar todo el carrito
                $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
                $stmt->execute([$user_id]);
                
                echo json_encode(['success' => true, 'message' => 'Carrito vaciado.']);
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
