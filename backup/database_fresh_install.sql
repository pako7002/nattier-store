-- Nattier Store Database Schema - Version Segura
-- Ejecutar este script para crear la base de datos completa desde cero

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS nattier_store 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE nattier_store;

-- Desactivar verificación de claves foráneas temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar tablas existentes si hay problemas de esquema (en orden correcto por las FK)
DROP TABLE IF EXISTS detalle_pedido;
DROP TABLE IF EXISTS carrito;
DROP TABLE IF EXISTS pedido;
DROP TABLE IF EXISTS producto;
DROP TABLE IF EXISTS categoria;
DROP TABLE IF EXISTS usuario;

-- Reactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Tabla de categorías
CREATE TABLE categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    rol ENUM('cliente', 'admin') DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de productos
CREATE TABLE producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) DEFAULT 'fotos/default.jpg',
    categoria_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE CASCADE
);

-- Tabla de carrito de compras
CREATE TABLE carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (usuario_id, producto_id)
);

-- Tabla de pedidos
CREATE TABLE pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    estado_pago ENUM('pendiente', 'pagado', 'fallido', 'reembolsado') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50) DEFAULT 'efectivo',
    datos_entrega JSON,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Tabla de detalles del pedido
CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE CASCADE
);

-- Insertar categorías predeterminadas
INSERT INTO categoria (nombre, descripcion) VALUES 
('Ropa', 'Prendas de vestir tejidas a mano'),
('Accesorios', 'Complementos y accesorios artesanales'),
('Decoración', 'Artículos decorativos para el hogar');

-- Insertar usuario administrador predeterminado
-- Contraseña: admin123
INSERT INTO usuario (nombre, email, password, rol) VALUES 
('Administrador', 'admin@nattierstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar usuario cliente de prueba
-- Contraseña: cliente123
INSERT INTO usuario (nombre, email, password, rol) VALUES 
('Cliente Prueba', 'cliente@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');

-- Insertar productos de ejemplo
INSERT INTO producto (nombre, descripcion, precio, stock, imagen, categoria_id) VALUES 
('Suéter Artesanal', 'Hermoso suéter tejido a mano con lana de alta calidad', 85000, 10, 'fotos/FOTO1.jpeg', 1),
('Bufanda de Lana', 'Bufanda suave y cálida, perfecta para el invierno', 35000, 15, 'fotos/FOTO2.jpeg', 2),
('Gorro Tejido', 'Gorro cómodo y elegante para cualquier ocasión', 25000, 20, 'fotos/FOTO3.jpeg', 2),
('Chaleco de Punto', 'Chaleco versátil ideal para combinar', 65000, 8, 'fotos/FOTO4.jpeg', 1),
('Cojín Decorativo', 'Cojín tejido con patrones únicos', 45000, 12, 'fotos/FOTO5.jpeg', 3),
('Manta Tejida', 'Manta grande y acogedora para el sofá', 95000, 6, 'fotos/FOTO6.jpeg', 3),
('Bolso Artesanal', 'Bolso espacioso tejido con fibras naturales', 55000, 10, 'fotos/FOTO7.jpeg', 2),
('Cardigan Largo', 'Cardigan elegante de punto para toda ocasión', 75000, 7, 'fotos/FOTO8.jpeg', 1),
('Poncho Tradicional', 'Poncho colorido con diseños étnicos', 120000, 5, 'fotos/FOTO9.jpeg', 1),
('Guantes de Lana', 'Guantes cálidos y suaves para el invierno', 28000, 18, 'fotos/FOTO10.jpeg', 2),
('Almohadón Bordado', 'Almohadón con bordados tradicionales', 38000, 14, 'fotos/FOTO11.jpeg', 3),
('Chalina Multicolor', 'Chalina vibrante con múltiples colores', 42000, 12, 'fotos/FOTO12.jpeg', 2),
('Tapete Tejido', 'Tapete decorativo para mesa o suelo', 65000, 8, 'fotos/FOTO13.jpeg', 3),
('Chaqueta Artesanal', 'Chaqueta única con patrones exclusivos', 95000, 6, 'fotos/FOTO14.jpeg', 1),
('Collar Tejido', 'Collar artesanal con cuentas naturales', 32000, 22, 'fotos/FOTO15.jpeg', 2),
('Mantel Individual', 'Mantel tejido para una persona', 25000, 16, 'fotos/FOTO16.jpeg', 3),
('Suéter Infantil', 'Suéter colorido para niños', 55000, 12, 'fotos/FOTO17.jpeg', 1);

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_producto_categoria ON producto(categoria_id);
CREATE INDEX idx_producto_activo ON producto(activo);
CREATE INDEX idx_pedido_usuario ON pedido(usuario_id);
CREATE INDEX idx_pedido_fecha ON pedido(fecha);
CREATE INDEX idx_pedido_estado ON pedido(estado);
CREATE INDEX idx_carrito_usuario ON carrito(usuario_id);
CREATE INDEX idx_detalle_pedido ON detalle_pedido(pedido_id);

-- Crear vista para estadísticas de administración
CREATE OR REPLACE VIEW vista_estadisticas AS
SELECT 
    (SELECT COUNT(*) FROM producto WHERE activo = TRUE) as total_productos,
    (SELECT COUNT(*) FROM pedido) as total_pedidos,
    (SELECT COUNT(*) FROM usuario WHERE rol = 'cliente') as total_usuarios,
    (SELECT COALESCE(SUM(total), 0) FROM pedido WHERE estado_pago = 'pagado') as total_ventas;

-- Verificar que todo se creó correctamente
SELECT 
    'Categorías' as tabla, COUNT(*) as registros 
FROM categoria
UNION ALL
SELECT 
    'Usuarios' as tabla, COUNT(*) as registros 
FROM usuario
UNION ALL
SELECT 
    'Productos' as tabla, COUNT(*) as registros 
FROM producto;

-- Mensaje de finalización
SELECT 'Base de datos Nattier Store creada exitosamente con datos de prueba' as mensaje;
