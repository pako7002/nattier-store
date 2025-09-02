<?php
// api/productos.php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS"); // PUT is removed
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

// --- Authorization Helper --- 
function is_admin($pdo) {
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
        return false;
    }

    list($type, $token) = explode(' ', $auth_header, 2);
    if (strcasecmp($type, 'Bearer') != 0 || !is_numeric($token)) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT rol FROM usuario WHERE id = ?");
    $stmt->execute([(int)$token]);
    $rol = $stmt->fetchColumn();
    return $rol === 'admin';
}

try {
    switch ($method) {
        case 'GET':
            $sort_option = $_GET['sort'] ?? 'default';
            $order_by_clause = "ORDER BY p.id"; // Default sort

            switch ($sort_option) {
                case 'price_asc':
                    $order_by_clause = "ORDER BY p.precio ASC";
                    break;
                case 'price_desc':
                    $order_by_clause = "ORDER BY p.precio DESC";
                    break;
            }

            $query = "SELECT p.id, p.nombre, p.precio, p.descripcion, p.imagen, p.stock, p.activo, 
                             c.nombre as categoria_nombre 
                      FROM producto p 
                      JOIN categoria c ON p.categoria_id = c.id 
                      WHERE p.activo = 1 
                      " . $order_by_clause;
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($productos);
            break;

        case 'POST': // Handles both CREATE and UPDATE
            if (!is_admin($pdo)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requiere rol de administrador.']);
                exit;
            }

            $action = $_POST['action'] ?? '';
            $data = $_POST;
            
            // --- Handle Image Upload ---
            $image_path = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../fotos/';
                // Sanitize filename
                $image_name = preg_replace("/[^a-zA-Z0-9-_\.]/", "", basename($_FILES['imagen']['name']));
                $image_name = uniqid() . '-' . $image_name;
                
                $target_file = $upload_dir . $image_name;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
                    $image_path = 'fotos/' . $image_name; // Path to store in DB
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error al mover el archivo subido.']);
                    exit;
                }
            }

            if ($action === 'create') {
                if (empty($data['nombre']) || empty($data['precio']) || empty($data['stock']) || empty($data['categoria_id'])) {
                     http_response_code(400);
                     echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios para crear.']);
                     exit;
                }
                $sql = "INSERT INTO producto (nombre, descripcion, precio, stock, imagen, categoria_id) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['nombre'],
                    $data['descripcion'] ?? '',
                    $data['precio'],
                    $data['stock'],
                    $image_path ?? 'fotos/default.jpg', // Use new image or default
                    $data['categoria_id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Producto creado exitosamente.']);

            } elseif ($action === 'update') {
                if (empty($data['id']) || empty($data['nombre']) || empty($data['precio']) || empty($data['stock']) || empty($data['categoria_id'])) {
                     http_response_code(400);
                     echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios para actualizar.']);
                     exit;
                }

                $params = [
                    $data['nombre'],
                    $data['descripcion'] ?? '',
                    $data['precio'],
                    $data['stock'],
                    $data['categoria_id']
                ];
                
                $sql = "UPDATE producto SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ?";
                
                if ($image_path) {
                    $sql .= ", imagen = ?";
                    $params[] = $image_path;
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $data['id'];

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente.']);

            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Acción no especificada o no válida.']);
            }
            break;

        case 'DELETE':
            if (!is_admin($pdo)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requiere rol de administrador.']);
                exit;
            }

            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No se proporcionó el ID del producto a eliminar.']);
                exit;
            }
            $id = $_GET['id'];
            $sql = "DELETE FROM producto WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            if ($stmt->rowCount()) {
                echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente.']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['message' => 'Método no permitido']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    // Log the detailed error to a file instead of echoing it to the user
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
?>