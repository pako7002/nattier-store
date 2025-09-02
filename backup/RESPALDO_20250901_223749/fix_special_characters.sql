-- Script para corregir caracteres especiales (eñes y tildes)
-- Actualizar productos con codificación UTF-8 correcta

USE nattier_store;

-- Actualizar nombres de productos con caracteres especiales corregidos
UPDATE producto SET nombre = 'Set Verano Top y Short Cachetero' WHERE id = 1;
UPDATE producto SET nombre = 'Set Love Top y Short Flecos' WHERE id = 2;
UPDATE producto SET nombre = 'Set Dúo Incandescente Blanco' WHERE id = 3;
UPDATE producto SET nombre = 'Bikini Conchas Beige' WHERE id = 4;
UPDATE producto SET nombre = 'Set Baby Tono Blanco' WHERE id = 5;
UPDATE producto SET nombre = 'Salida de Playa Flecos' WHERE id = 6;
UPDATE producto SET nombre = 'Bikini Fiesta Estrapless Lycra' WHERE id = 7;
UPDATE producto SET nombre = 'Set Tres Piezas Bikini y Salida' WHERE id = 8;
UPDATE producto SET nombre = 'Set Terracota Top, Bikini y Falda' WHERE id = 9;
UPDATE producto SET nombre = 'Set Flores en Trío Beige' WHERE id = 10;
UPDATE producto SET nombre = 'Bikini Imperial Macramé y Lycra' WHERE id = 11;
UPDATE producto SET nombre = 'Vestido Crochet Marfil' WHERE id = 12;
UPDATE producto SET nombre = 'Outfit Playa 2 Piezas Vestido y Pava' WHERE id = 13;
UPDATE producto SET nombre = 'Outfit Elegance Tres Piezas con Bolso' WHERE id = 14;
UPDATE producto SET nombre = 'Trío Bikini Bordados en Lycra' WHERE id = 15;
UPDATE producto SET nombre = 'Bolso Riñonera Tres Tonos' WHERE id = 16;
UPDATE producto SET nombre = 'Adorno de Pared Beige' WHERE id = 17;

-- Actualizar descripciones con caracteres especiales corregidos
UPDATE producto SET descripcion = 'Conjunto de dos piezas perfecto para el verano, incluye top cómodo y short con diseño cachetero' WHERE id = 1;
UPDATE producto SET descripcion = 'Hermoso conjunto con detalles de flecos, diseño romántico ideal para la playa' WHERE id = 2;
UPDATE producto SET descripcion = 'Elegante conjunto blanco de dos piezas con acabados brillantes' WHERE id = 3;
UPDATE producto SET descripcion = 'Bikini decorado con motivos de conchas marinas en tono beige natural' WHERE id = 4;
UPDATE producto SET descripcion = 'Conjunto delicado en tonos blancos, perfecto para looks frescos' WHERE id = 5;
UPDATE producto SET descripcion = 'Elegante salida de playa con detalles de flecos, perfecta sobre el bikini' WHERE id = 6;
UPDATE producto SET descripcion = 'Bikini estrapless en lycra de alta calidad, ideal para fiestas en la playa' WHERE id = 7;
UPDATE producto SET descripcion = 'Conjunto completo de tres piezas: bikini y salida de playa coordinada' WHERE id = 8;
UPDATE producto SET descripcion = 'Exclusivo conjunto en tono terracota con top, bikini y falda' WHERE id = 9;
UPDATE producto SET descripcion = 'Hermoso conjunto de tres piezas con estampado floral en tono beige' WHERE id = 10;
UPDATE producto SET descripcion = 'Bikini de lujo con detalles de macramé y lycra premium' WHERE id = 11;
UPDATE producto SET descripcion = 'Elegante vestido tejido en crochet color marfil, perfecto para ocasiones especiales' WHERE id = 12;
UPDATE producto SET descripcion = 'Conjunto playero de dos piezas con vestido y sombrero pava incluido' WHERE id = 13;
UPDATE producto SET descripcion = 'Conjunto elegante de tres piezas que incluye bolso de mano coordinado' WHERE id = 14;
UPDATE producto SET descripcion = 'Set de bikinis con bordados especiales en lycra de alta calidad' WHERE id = 15;
UPDATE producto SET descripcion = 'Práctica riñonera en combinación de tres tonos, perfecta para la playa' WHERE id = 16;
UPDATE producto SET descripcion = 'Hermoso adorno decorativo para pared en tono beige, estilo artesanal' WHERE id = 17;

-- Actualizar nombres de categorías con tildes correctas
UPDATE categoria SET nombre = 'Ropa' WHERE id = 1;
UPDATE categoria SET nombre = 'Accesorios' WHERE id = 2;
UPDATE categoria SET nombre = 'Decoración' WHERE id = 3;

-- Actualizar descripciones de categorías
UPDATE categoria SET descripcion = 'Prendas de vestir y trajes de baño artesanales' WHERE id = 1;
UPDATE categoria SET descripcion = 'Complementos y accesorios de playa' WHERE id = 2;
UPDATE categoria SET descripcion = 'Artículos decorativos para el hogar' WHERE id = 3;

-- Verificar que los caracteres se muestren correctamente
SELECT 
    p.id, 
    p.nombre, 
    p.descripcion,
    CONCAT('$', FORMAT(p.precio, 0, 'es_CO')) as precio_formateado,
    p.stock, 
    c.nombre as categoría
FROM producto p
JOIN categoria c ON p.categoria_id = c.id
ORDER BY p.id;

-- Verificar categorías
SELECT * FROM categoria;

SELECT '✅ Caracteres especiales (eñes y tildes) corregidos correctamente' as resultado;
