-- Script para actualizar el catálogo completo con productos de playa
-- Precios convertidos de miles a formato completo $000.000.00

USE nattier_store;

-- Primero, limpiar productos existentes
DELETE FROM detalle_pedido;
DELETE FROM carrito;
DELETE FROM pedido;
DELETE FROM producto;

-- Reiniciar auto_increment
ALTER TABLE producto AUTO_INCREMENT = 1;

-- Insertar productos actualizados con nuevas descripciones y precios
INSERT INTO producto (nombre, descripcion, precio, stock, imagen, categoria_id) VALUES 
-- Ropa de Playa / Bikinis (categoria_id = 1 - Ropa)
('Set Verano Top y Short Cachetero', 'Conjunto de dos piezas perfecto para el verano, incluye top cómodo y short con diseño cachetero', 90000.00, 12, 'fotos/FOTO1.jpeg', 1),
('Set Love Top y Short Flecos', 'Hermoso conjunto con detalles de flecos, diseño romántico ideal para la playa', 90000.00, 15, 'fotos/FOTO2.jpeg', 1),
('Set Dúo Incandescente Blanco', 'Elegante conjunto blanco de dos piezas con acabados brillantes', 120000.00, 8, 'fotos/FOTO3.jpeg', 1),
('Bikini Conchas Beige', 'Bikini decorado con motivos de conchas marinas en tono beige natural', 87000.00, 18, 'fotos/FOTO4.jpeg', 1),
('Set Baby Tono Blanco', 'Conjunto delicado en tonos blancos, perfecto para looks frescos', 125000.00, 10, 'fotos/FOTO5.jpeg', 1),
('Salida de Playa Flecos', 'Elegante salida de playa con detalles de flecos, perfecta sobre el bikini', 80000.00, 14, 'fotos/FOTO6.jpeg', 1),
('Bikini Fiesta Estrapless Lycra', 'Bikini estrapless en lycra de alta calidad, ideal para fiestas en la playa', 120000.00, 16, 'fotos/FOTO7.jpeg', 1),
('Set Tres Piezas Bikini y Salida', 'Conjunto completo de tres piezas: bikini y salida de playa coordinada', 170000.00, 6, 'fotos/FOTO8.jpeg', 1),
('Set Terracota Top, Bikini y Falda', 'Exclusivo conjunto en tono terracota con top, bikini y falda', 190000.00, 5, 'fotos/FOTO9.jpeg', 1),
('Set Flores en Trío Beige', 'Hermoso conjunto de tres piezas con estampado floral en tono beige', 170000.00, 8, 'fotos/FOTO10.jpeg', 1),
('Bikini Imperial Macramé y Lycra', 'Bikini de lujo con detalles de macramé y lycra premium', 118000.00, 7, 'fotos/FOTO11.jpeg', 1),
('Vestido Crochet Marfil', 'Elegante vestido tejido en crochet color marfil, perfecto para ocasiones especiales', 100000.00, 11, 'fotos/FOTO12.jpeg', 1),
('Outfit Playa 2 Piezas Vestido y Pava', 'Conjunto playero de dos piezas con vestido y sombrero pava incluido', 130000.00, 9, 'fotos/FOTO13.jpeg', 1),

-- Accesorios (categoria_id = 2)
('Outfit Elegance Tres Piezas con Bolso', 'Conjunto elegante de tres piezas que incluye bolso de mano coordinado', 150000.00, 6, 'fotos/FOTO14.jpeg', 2),
('Trío Bikini Bordados en Lycra', 'Set de bikinis con bordados especiales en lycra de alta calidad', 90000.00, 13, 'fotos/FOTO15.jpeg', 2),
('Bolso Riñonera Tres Tonos', 'Práctica riñonera en combinación de tres tonos, perfecta para la playa', 35000.00, 20, 'fotos/FOTO16.jpeg', 2),

-- Decoración (categoria_id = 3)
('Adorno de Pared Beige', 'Hermoso adorno decorativo para pared en tono beige, estilo artesanal', 25000.00, 15, 'fotos/FOTO17.jpeg', 3);

-- Verificar productos insertados
SELECT 
    p.id, 
    p.nombre, 
    CONCAT('$', FORMAT(p.precio, 2, 'es_CO')) as precio_formateado,
    p.stock, 
    c.nombre as categoria
FROM producto p
JOIN categoria c ON p.categoria_id = c.id
ORDER BY p.id;

-- Mostrar estadísticas del catálogo actualizado
SELECT 
    c.nombre as categoria,
    COUNT(p.id) as total_productos,
    CONCAT('$', FORMAT(MIN(p.precio), 2, 'es_CO')) as precio_minimo,
    CONCAT('$', FORMAT(MAX(p.precio), 2, 'es_CO')) as precio_maximo,
    CONCAT('$', FORMAT(AVG(p.precio), 2, 'es_CO')) as precio_promedio
FROM producto p
JOIN categoria c ON p.categoria_id = c.id
GROUP BY c.id, c.nombre
ORDER BY c.id;

SELECT '✅ Catálogo actualizado con productos de playa - Precios en formato $000.000.00' as resultado;
