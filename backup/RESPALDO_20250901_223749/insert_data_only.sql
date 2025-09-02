-- Script para insertar solo datos de ejemplo
-- Usar este script si las tablas ya existen

USE nattier_store;

-- Insertar categorías si no existen
INSERT IGNORE INTO categoria (id, nombre) VALUES 
(1, 'Ropa'),
(2, 'Accesorios'),
(3, 'Decoración');

-- Insertar usuario administrador si no existe
INSERT IGNORE INTO usuario (nombre, email, password, rol) VALUES 
('Administrador', 'admin@nattierstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar usuario cliente de prueba si no existe
INSERT IGNORE INTO usuario (nombre, email, password, rol) VALUES 
('Cliente Prueba', 'cliente@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');

-- Insertar productos de ejemplo
INSERT IGNORE INTO producto (nombre, descripcion, precio, stock, imagen, categoria_id) VALUES 
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

-- Verificar los datos insertados
SELECT 'Datos insertados correctamente' as resultado;
SELECT COUNT(*) as total_productos FROM producto;
SELECT COUNT(*) as total_usuarios FROM usuario;
SELECT COUNT(*) as total_categorias FROM categoria;
