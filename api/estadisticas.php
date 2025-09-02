<?php
// api/estadisticas.php - Estadísticas para el Dashboard de Administración

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
        // Acceso directo permitido para estadísticas (solo GET)
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

    if ($method === 'GET') {
        // Obtener estadísticas generales de forma optimizada
        
        // Consulta optimizada para estadísticas principales
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM producto WHERE activo = TRUE) as total_productos,
                (SELECT COUNT(*) FROM pedido) as total_pedidos,
                (SELECT COUNT(*) FROM usuario WHERE rol = 'usuario') as total_usuarios,
                (SELECT COALESCE(SUM(total), 0) FROM pedido WHERE estado IN ('enviado', 'entregado')) as total_ingresos
        ");
        $stmt->execute();
        $estadisticas_principales = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Estadísticas por estado de pedidos
        $stmt = $pdo->prepare("SELECT estado, COUNT(*) as cantidad FROM pedido GROUP BY estado");
        $stmt->execute();
        $pedidos_por_estado = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Top productos más vendidos (optimizado)
        $stmt = $pdo->prepare("
            SELECT p.nombre, SUM(dp.cantidad) as total_vendido 
            FROM detalle_pedido dp 
            INNER JOIN producto p ON dp.producto_id = p.id 
            WHERE p.activo = TRUE
            GROUP BY p.id, p.nombre 
            ORDER BY total_vendido DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $top_productos = $stmt->fetchAll();
        
        $stats = [
            'total_productos' => (int)$estadisticas_principales['total_productos'],
            'total_pedidos' => (int)$estadisticas_principales['total_pedidos'],
            'total_usuarios' => (int)$estadisticas_principales['total_usuarios'],
            'total_ingresos' => (float)$estadisticas_principales['total_ingresos'],
            'pedidos_por_estado' => $pedidos_por_estado,
            'top_productos' => $top_productos,
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];

        echo json_encode([
            'success' => true, 
            'stats' => $stats,
            'message' => 'Estadísticas cargadas exitosamente'
        ]);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Database Error in estadisticas.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
?>
